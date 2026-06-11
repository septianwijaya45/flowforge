<?php

declare(strict_types=1);

namespace Modules\ExecutionLog;

use App\Support\Modules\ModuleServiceProvider;
use Modules\ExecutionLog\Console\PurgeExpiredExecutionLogsCommand;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogRetentionServiceContract;
use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\ExecutionLog\Repositories\ExecutionLogRepository;
use Modules\ExecutionLog\Services\ExecutionLogRetentionService;
use Modules\ExecutionLog\Services\ExecutionLogWriterService;

class ExecutionLogServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'ExecutionLog';
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->modulePath('Config/execution_log.php'),
            'execution_log',
        );

        $this->app->singleton(ExecutionLogRepositoryContract::class, ExecutionLogRepository::class);

        $this->app->singleton(ExecutionLogWriterServiceContract::class, function ($app): ExecutionLogWriterService {
            return new ExecutionLogWriterService(
                repository: $app->make(ExecutionLogRepositoryContract::class),
                batchSize: (int) config('execution_log.write_batch_size', 100),
            );
        });

        $this->app->singleton(ExecutionLogRetentionServiceContract::class, function ($app): ExecutionLogRetentionService {
            return new ExecutionLogRetentionService(
                repository: $app->make(ExecutionLogRepositoryContract::class),
                retentionDays: (int) config('execution_log.retention_days', 30),
                purgeBatchSize: (int) config('execution_log.purge_batch_size', 1000),
            );
        });

        $this->app->terminating(function (): void {
            $this->app->make(ExecutionLogWriterServiceContract::class)->flush();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PurgeExpiredExecutionLogsCommand::class,
            ]);
        }

        parent::boot();
    }
}
