<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowVersioning\Contracts\WorkflowVersioningServiceContract;
use Modules\WorkflowVersioning\DTOs\CreateWorkflowVersionDTO;
use Modules\WorkflowVersioning\DTOs\ListWorkflowVersionsDTO;
use Modules\WorkflowVersioning\DTOs\RollbackWorkflowVersionDTO;
use Modules\WorkflowVersioning\Exceptions\WorkflowVersioningException;

class WorkflowVersioningService implements WorkflowVersioningServiceContract
{
    public function __construct(
        private readonly WorkflowGraphValidatorContract $graphValidator,
    ) {}

    /**
     * @return LengthAwarePaginator<int, WorkflowVersion>
     */
    public function history(Workflow $workflow, ListWorkflowVersionsDTO $filters): LengthAwarePaginator
    {
        return WorkflowVersion::query()
            ->where('workflow_id', $workflow->id)
            ->orderByDesc('version_number')
            ->paginate(
                perPage: $filters->perPage,
                page: $filters->page,
            );
    }

    public function createVersion(Workflow $workflow, CreateWorkflowVersionDTO $dto): WorkflowVersion
    {
        $this->validateDefinition($dto->definition);

        return $this->persistVersion(
            workflow: $workflow,
            definition: $dto->definition,
            changeSummary: $dto->changeSummary,
            createdBy: $dto->createdBy,
        );
    }

    public function rollback(
        Workflow $workflow,
        WorkflowVersion $targetVersion,
        RollbackWorkflowVersionDTO $dto,
    ): WorkflowVersion {
        if ($targetVersion->workflow_id !== $workflow->id) {
            throw WorkflowVersioningException::versionNotFoundForWorkflow(
                $targetVersion->id,
                $workflow->id,
            );
        }

        $changeSummary = $dto->changeSummary
            ?? "Rolled back to version {$targetVersion->version_number}";

        return $this->persistVersion(
            workflow: $workflow,
            definition: $targetVersion->definition,
            changeSummary: $changeSummary,
            createdBy: $dto->createdBy,
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function persistVersion(
        Workflow $workflow,
        array $definition,
        ?string $changeSummary,
        ?int $createdBy,
    ): WorkflowVersion {
        $definitionHash = $this->hashDefinition($definition);

        return DB::transaction(function () use ($workflow, $definition, $definitionHash, $changeSummary, $createdBy): WorkflowVersion {
            $latestVersionNumber = WorkflowVersion::query()
                ->where('workflow_id', $workflow->id)
                ->lockForUpdate()
                ->max('version_number');

            $version = WorkflowVersion::query()->create([
                'workflow_id' => $workflow->id,
                'version_number' => ((int) $latestVersionNumber) + 1,
                'definition' => $definition,
                'definition_hash' => $definitionHash,
                'change_summary' => $changeSummary,
                'created_by' => $createdBy,
            ]);

            $workflow->update(['current_version_id' => $version->id]);

            return $version->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function validateDefinition(array $definition): void
    {
        $graph = WorkflowGraphDTO::fromArray($definition);
        $this->graphValidator->validate($graph);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function hashDefinition(array $definition): string
    {
        return hash('sha256', json_encode($this->normalizeDefinition($definition), \JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function normalizeDefinition(array $definition): array
    {
        ksort($definition);

        foreach ($definition as $key => $value) {
            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $definition[$key] = $this->normalizeDefinition($value);
            }
        }

        return $definition;
    }
}
