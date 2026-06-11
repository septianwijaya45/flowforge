<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Illuminate\Support\Collection;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionStatePersisterContract;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Contracts\WorkflowParallelExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorFactoryContract;
use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\DTOs\WorkflowExecutionResultDTO;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\DTOs\WorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Exceptions\WorkflowExecutionException;
use Modules\WorkflowEngine\Exceptions\WorkflowRunCancelledException;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;
use Throwable;

/**
 * Orchestrates workflow execution layer-by-layer with parallel node execution inside each layer.
 */
class WorkflowExecutionEngine implements WorkflowExecutionEngineContract
{
    public function __construct(
        private readonly WorkflowGraphValidatorContract $graphValidator,
        private readonly WorkflowTopologicalSorterContract $topologicalSorter,
        private readonly WorkflowParallelExecutorContract $parallelExecutor,
        private readonly WorkflowStepExecutorFactoryContract $executorFactory,
        private readonly WorkflowExecutionStatePersisterContract $statePersister,
    ) {}

    public function execute(ExecuteWorkflowRunDTO $command): WorkflowExecutionResultDTO
    {
        $run = WorkflowRun::query()
            ->with(['workflowVersion', 'steps'])
            ->find($command->runId);

        if ($run === null) {
            throw WorkflowExecutionException::runNotFound($command->runId);
        }

        if ($run->status === WorkflowRunStatus::Cancelled) {
            throw WorkflowRunCancelledException::forRun($run->id);
        }

        if ($run->status !== WorkflowRunStatus::Pending) {
            throw WorkflowExecutionException::invalidRunStatus($run->id, $run->status);
        }

        $graph = WorkflowGraphDTO::fromArray($run->workflowVersion->definition);
        $this->graphValidator->validate($graph);
        $layers = $this->topologicalSorter->sort($graph);

        /** @var Collection<string, WorkflowNodeDTO> $nodesById */
        $nodesById = collect($graph->nodes)->keyBy(static fn (WorkflowNodeDTO $node): string => $node->id);

        $this->statePersister->initializeSteps($run, $graph, $layers);
        $this->statePersister->markRunRunning($run);

        try {
            /** @var array<string, mixed> $executionContext */
            $executionContext = $run->input ?? [];

            foreach ($layers as $layer) {
                $run->refresh();

                if ($run->status === WorkflowRunStatus::Cancelled) {
                    return $this->statePersister->markRunCancelled($run);
                }

                $layerResults = $this->executeLayer($run, $layer, $nodesById, $executionContext);

                foreach ($layerResults as $result) {
                    if (! $result->isSuccessful()) {
                        return $this->statePersister->markRunFailed($run, $result);
                    }

                    $executionContext[$result->nodeId] = $result->output;
                }
            }

            return $this->statePersister->markRunSuccess($run, $executionContext);
        } catch (Throwable $throwable) {
            return $this->statePersister->markRunFailedByException($run, $throwable);
        }
    }

    /**
     * @param  list<string>  $layer
     * @param  Collection<string, WorkflowNodeDTO>  $nodesById
     * @param  array<string, mixed>  $executionContext
     * @return list<WorkflowStepExecutionResultDTO>
     */
    private function executeLayer(
        WorkflowRun $run,
        array $layer,
        Collection $nodesById,
        array $executionContext,
    ): array {
        $stepsByNodeId = $run->steps()->get()->keyBy(static fn (WorkflowRunStep $step): string => $step->node_id);

        $tasks = [];

        foreach ($layer as $nodeId) {
            $node = $nodesById->get($nodeId);
            $step = $stepsByNodeId->get($nodeId);

            if ($node === null || $step === null) {
                throw WorkflowExecutionException::fromThrowable(
                    new \RuntimeException("Workflow step for node [{$nodeId}] was not found."),
                );
            }

            $tasks[] = fn (): WorkflowStepExecutionResultDTO => $this->executeStep(
                $run,
                $step,
                $node,
                $executionContext,
            );
        }

        return $this->parallelExecutor->run($tasks);
    }

    /**
     * @param  array<string, mixed>  $executionContext
     */
    private function executeStep(
        WorkflowRun $run,
        WorkflowRunStep $step,
        WorkflowNodeDTO $node,
        array $executionContext,
    ): WorkflowStepExecutionResultDTO {
        $this->statePersister->markStepRunning($step->fresh());

        $startedAt = microtime(true);

        try {
            $executor = $this->executorFactory->make($node->type);
            $result = $executor->execute(new ExecuteWorkflowNodeDTO(
                run: $run,
                step: $step,
                node: $node,
                context: $executionContext,
            ));

            $result = new WorkflowStepExecutionResultDTO(
                nodeId: $result->nodeId,
                status: $result->status,
                output: $result->output,
                error: $result->error,
                durationMs: $result->durationMs ?? (int) ((microtime(true) - $startedAt) * 1000),
            );

            if ($result->isSuccessful()) {
                $this->statePersister->markStepSuccess($step->fresh(), $result);
            } else {
                $this->statePersister->markStepFailed($step->fresh(), $result);
            }

            return $result;
        } catch (Throwable $throwable) {
            $result = WorkflowStepExecutionResultDTO::failed(
                nodeId: $node->id,
                error: [
                    'message' => $throwable->getMessage(),
                    'exception' => $throwable::class,
                ],
                durationMs: (int) ((microtime(true) - $startedAt) * 1000),
            );

            $this->statePersister->markStepFailed($step->fresh(), $result);

            return $result;
        }
    }
}
