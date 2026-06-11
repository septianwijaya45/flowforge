<?php

declare(strict_types=1);

namespace Modules\Retry;

use App\Support\Modules\ModuleServiceProvider;
use Modules\Retry\Contracts\RetryStrategyContract;
use Modules\Retry\Services\ExponentialBackoffStrategy;
use Modules\Retry\Services\RetryStrategy;

class RetryServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Retry';
    }

    public function register(): void
    {
        $this->app->singleton(RetryStrategyContract::class, ExponentialBackoffStrategy::class);

        $this->app->singleton(RetryStrategy::class, function ($app): RetryStrategy {
            return new RetryStrategy(
                backoffStrategy: $app->make(RetryStrategyContract::class),
            );
        });
    }
}
