<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\WorkflowEngine\Contracts\DelaySleeperContract;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorFactoryContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowNodeDTO;
use Modules\WorkflowEngine\DTOs\WorkflowNodeDTO;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;
use Modules\WorkflowEngine\Services\Executors\ConditionalNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\DatabaseNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\DelayNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\EmailNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\HttpNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\ScriptNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\WebhookNodeExecutor;
use Modules\WorkflowEngine\Services\Support\WorkflowContextInterpolator;
use Modules\WorkflowEngine\Services\WorkflowStepExecutorFactory;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $context
 */
function makeStepCommand(
    string $nodeId,
    WorkflowNodeType $type,
    array $config = [],
    array $context = [],
): ExecuteWorkflowNodeDTO {
    $run = new WorkflowRun([
        'id' => '11111111-1111-1111-1111-111111111111',
        'tenant_id' => '22222222-2222-2222-2222-222222222222',
    ]);

    $step = new WorkflowRunStep([
        'id' => '33333333-3333-3333-3333-333333333333',
        'node_id' => $nodeId,
    ]);

    return new ExecuteWorkflowNodeDTO(
        run: $run,
        step: $step,
        node: new WorkflowNodeDTO($nodeId, $type, $config),
        context: $context,
    );
}

final class DelaySleepTracker implements DelaySleeperContract
{
    public int $sleptFor = 0;

    public function sleep(int $seconds): void
    {
        $this->sleptFor = $seconds;
    }
}

describe('WorkflowStepExecutorFactory', function (): void {
    it('resolves executors by node type', function (): void {
        $factory = app(WorkflowStepExecutorFactoryContract::class);

        expect($factory->make(WorkflowNodeType::Http))->toBeInstanceOf(HttpNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Delay))->toBeInstanceOf(DelayNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Condition))->toBeInstanceOf(ConditionalNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Script))->toBeInstanceOf(ScriptNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Email))->toBeInstanceOf(EmailNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Database))->toBeInstanceOf(DatabaseNodeExecutor::class)
            ->and($factory->make(WorkflowNodeType::Webhook))->toBeInstanceOf(WebhookNodeExecutor::class);
    });

    it('is bound in the service container', function (): void {
        expect(app(WorkflowStepExecutorFactoryContract::class))
            ->toBeInstanceOf(WorkflowStepExecutorFactory::class);
    });
});

describe('HttpNodeExecutor', function (): void {
    it('executes a successful HTTP request', function (): void {
        Http::fake([
            'https://example.com/*' => Http::response(['ok' => true], 200),
        ]);

        $result = (new HttpNodeExecutor)->execute(makeStepCommand(
            nodeId: 'http-step',
            type: WorkflowNodeType::Http,
            config: [
                'method' => 'GET',
                'url' => 'https://example.com/status',
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['status'])->toBe(200)
            ->and($result->output['body'])->toBe(['ok' => true]);
    });

    it('fails when url is missing', function (): void {
        $result = (new HttpNodeExecutor)->execute(makeStepCommand(
            nodeId: 'http-step',
            type: WorkflowNodeType::Http,
            config: [],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed);
    });

    it('fails when the HTTP response is unsuccessful', function (): void {
        Http::fake([
            'https://example.com/*' => Http::response(['error' => 'nope'], 500),
        ]);

        $result = (new HttpNodeExecutor)->execute(makeStepCommand(
            nodeId: 'http-step',
            type: WorkflowNodeType::Http,
            config: [
                'url' => 'https://example.com/fail',
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed)
            ->and($result->error['status'])->toBe(500);
    });
});

describe('DelayNodeExecutor', function (): void {
    it('delays execution using the configured sleeper', function (): void {
        $tracker = new DelaySleepTracker;

        $executor = new DelayNodeExecutor($tracker);

        $result = $executor->execute(makeStepCommand(
            nodeId: 'delay-step',
            type: WorkflowNodeType::Delay,
            config: ['seconds' => 5],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['delayed_seconds'])->toBe(5)
            ->and($tracker->sleptFor)->toBe(5);
    });
});

describe('ConditionalNodeExecutor', function (): void {
    it('evaluates explicit boolean results from config', function (): void {
        $result = (new ConditionalNodeExecutor)->execute(makeStepCommand(
            nodeId: 'condition-step',
            type: WorkflowNodeType::Condition,
            config: ['result' => false],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['result'])->toBeFalse();
    });

    it('evaluates context paths using equals operator', function (): void {
        $result = (new ConditionalNodeExecutor)->execute(makeStepCommand(
            nodeId: 'condition-step',
            type: WorkflowNodeType::Condition,
            config: [
                'path' => 'A.output.status',
                'operator' => 'equals',
                'value' => 200,
            ],
            context: [
                'A' => ['output' => ['status' => 200]],
            ],
        ));

        expect($result->output['result'])->toBeTrue();
    });

    it('evaluates contains operator', function (): void {
        $result = (new ConditionalNodeExecutor)->execute(makeStepCommand(
            nodeId: 'condition-step',
            type: WorkflowNodeType::Condition,
            config: [
                'path' => 'message',
                'operator' => 'contains',
                'value' => 'flow',
            ],
            context: [
                'message' => 'flowforge',
            ],
        ));

        expect($result->output['result'])->toBeTrue();
    });

    it('fails for unsupported operators', function (): void {
        $result = (new ConditionalNodeExecutor)->execute(makeStepCommand(
            nodeId: 'condition-step',
            type: WorkflowNodeType::Condition,
            config: [
                'operator' => 'unknown',
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed);
    });
});

describe('EmailNodeExecutor', function (): void {
    it('sends an email with interpolated content', function (): void {
        Mail::fake();

        $interpolator = new WorkflowContextInterpolator;
        $executor = new EmailNodeExecutor($interpolator);

        $result = $executor->execute(makeStepCommand(
            nodeId: 'email-step',
            type: WorkflowNodeType::Email,
            config: [
                'to' => 'user@example.com',
                'subject' => 'Status {{fetch.status}}',
                'body' => 'Result: {{fetch.body}}',
            ],
            context: [
                'fetch' => [
                    'status' => 200,
                    'body' => 'ok',
                ],
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['sent'])->toBeTrue()
            ->and($result->output['subject'])->toBe('Status 200')
            ->and($result->output['to'])->toBe('user@example.com');
    });

    it('fails when recipient is missing', function (): void {
        $result = (new EmailNodeExecutor(new WorkflowContextInterpolator))->execute(makeStepCommand(
            nodeId: 'email-step',
            type: WorkflowNodeType::Email,
            config: [],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed);
    });
});

describe('DatabaseNodeExecutor', function (): void {
    it('executes a read-only select query', function (): void {
        DB::statement('CREATE TABLE workflow_test_users (id INTEGER PRIMARY KEY, name TEXT)');
        DB::insert('INSERT INTO workflow_test_users (id, name) VALUES (?, ?)', [1, 'Alice']);

        $result = (new DatabaseNodeExecutor)->execute(makeStepCommand(
            nodeId: 'db-step',
            type: WorkflowNodeType::Database,
            config: [
                'query' => 'SELECT id, name FROM workflow_test_users WHERE id = ?',
                'bindings' => [1],
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['count'])->toBe(1)
            ->and($result->output['rows'][0]['name'])->toBe('Alice');
    });

    it('rejects non-select queries', function (): void {
        $result = (new DatabaseNodeExecutor)->execute(makeStepCommand(
            nodeId: 'db-step',
            type: WorkflowNodeType::Database,
            config: [
                'query' => 'DELETE FROM users',
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed);
    });
});

describe('WebhookNodeExecutor', function (): void {
    it('posts payload from a context path', function (): void {
        Http::fake([
            'https://hooks.example.com/*' => Http::response(['received' => true], 200),
        ]);

        $executor = new WebhookNodeExecutor(new WorkflowContextInterpolator);

        $result = $executor->execute(makeStepCommand(
            nodeId: 'webhook-step',
            type: WorkflowNodeType::Webhook,
            config: [
                'url' => 'https://hooks.example.com/notify',
                'payload_path' => 'fetch.body',
            ],
            context: [
                'fetch' => [
                    'body' => ['event' => 'created'],
                ],
            ],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Success)
            ->and($result->output['status'])->toBe(200);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://hooks.example.com/notify'
                && $request['event'] === 'created';
        });
    });

    it('fails when url is missing', function (): void {
        $result = (new WebhookNodeExecutor(new WorkflowContextInterpolator))->execute(makeStepCommand(
            nodeId: 'webhook-step',
            type: WorkflowNodeType::Webhook,
            config: [],
        ));

        expect($result->status)->toBe(WorkflowRunStepStatus::Failed);
    });
});
