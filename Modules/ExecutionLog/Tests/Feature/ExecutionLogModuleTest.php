<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogRetentionServiceContract;
use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;
use Modules\ExecutionLog\Enums\ExecutionLogLevel;
use Modules\ExecutionLog\Models\ExecutionLog;
use Modules\ExecutionLog\Repositories\ExecutionLogRepository;
use Modules\ExecutionLog\Services\ExecutionLogRetentionService;
use Modules\ExecutionLog\Services\ExecutionLogWriterService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $connection = (string) config('execution_log.connection', 'execution_logs');
    $schema = Schema::connection($connection);

    $schema->dropIfExists('execution_logs');
    $schema->dropIfExists('migrations');

    Artisan::call('migrate', [
        '--database' => $connection,
        '--path' => base_path('Modules/ExecutionLog/Database/Migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);
});

describe('ExecutionLog module', function (): void {
    it('binds contracts in the service container', function (): void {
        expect(app(ExecutionLogRepositoryContract::class))->toBeInstanceOf(ExecutionLogRepository::class)
            ->and(app(ExecutionLogWriterServiceContract::class))->toBeInstanceOf(ExecutionLogWriterService::class)
            ->and(app(ExecutionLogRetentionServiceContract::class))->toBeInstanceOf(ExecutionLogRetentionService::class);
    });

    it('buffers and bulk-writes structured execution logs', function (): void {
        config(['execution_log.write_batch_size' => 2]);
        app()->forgetInstance(ExecutionLogWriterServiceContract::class);

        $writer = app(ExecutionLogWriterServiceContract::class);
        $tenantId = '11111111-1111-1111-1111-111111111111';
        $workflowId = '22222222-2222-2222-2222-222222222222';
        $runId = '33333333-3333-3333-3333-333333333333';

        $writer->log(new AppendExecutionLogDTO(
            tenantId: $tenantId,
            level: ExecutionLogLevel::Info,
            message: 'Run started',
            workflowId: $workflowId,
            workflowRunId: $runId,
            context: ['trigger' => 'manual'],
        ));

        expect(ExecutionLog::query()->count())->toBe(0);

        $writer->log(new AppendExecutionLogDTO(
            tenantId: $tenantId,
            level: ExecutionLogLevel::Info,
            message: 'Step executed',
            workflowId: $workflowId,
            workflowRunId: $runId,
            nodeId: 'http-step',
        ));

        expect(ExecutionLog::query()->count())->toBe(2);

        $logs = app(ExecutionLogRepositoryContract::class)->forRun($runId);

        expect($logs)->toHaveCount(2)
            ->and($logs->first()?->tenant_id)->toBe($tenantId)
            ->and($logs->first()?->workflow_id)->toBe($workflowId)
            ->and($logs->first()?->workflow_run_id)->toBe($runId)
            ->and($logs->last()?->node_id)->toBe('http-step');
    });

    it('flushes remaining buffered logs on explicit flush', function (): void {
        config(['execution_log.write_batch_size' => 10]);
        app()->forgetInstance(ExecutionLogWriterServiceContract::class);

        $writer = app(ExecutionLogWriterServiceContract::class);

        $writer->log(new AppendExecutionLogDTO(
            tenantId: 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            level: ExecutionLogLevel::Debug,
            message: 'Buffered log',
        ));

        expect(ExecutionLog::query()->count())->toBe(0);

        $written = $writer->flush();

        expect($written)->toBe(1)
            ->and(ExecutionLog::query()->count())->toBe(1);
    });

    it('purges logs older than the retention window in batches', function (): void {
        config([
            'execution_log.retention_days' => 7,
            'execution_log.purge_batch_size' => 2,
        ]);
        app()->forgetInstance(ExecutionLogRetentionServiceContract::class);

        $repository = app(ExecutionLogRepositoryContract::class);
        $retention = app(ExecutionLogRetentionServiceContract::class);

        $repository->bulkInsert([
            new AppendExecutionLogDTO(
                tenantId: 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                level: ExecutionLogLevel::Info,
                message: 'Old log 1',
                loggedAt: Carbon::now()->subDays(10),
            ),
            new AppendExecutionLogDTO(
                tenantId: 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                level: ExecutionLogLevel::Info,
                message: 'Old log 2',
                loggedAt: Carbon::now()->subDays(9),
            ),
            new AppendExecutionLogDTO(
                tenantId: 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                level: ExecutionLogLevel::Info,
                message: 'Recent log',
                loggedAt: Carbon::now()->subDay(),
            ),
        ]);

        expect(ExecutionLog::query()->count())->toBe(3);

        $result = $retention->purgeExpired();

        expect($result->deletedCount)->toBe(2)
            ->and($result->retentionDays)->toBe(7)
            ->and(ExecutionLog::query()->count())->toBe(1)
            ->and(ExecutionLog::query()->value('message'))->toBe('Recent log');
    });
});
