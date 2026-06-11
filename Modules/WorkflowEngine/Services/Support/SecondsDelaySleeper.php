<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Support;

use Modules\WorkflowEngine\Contracts\DelaySleeperContract;

class SecondsDelaySleeper implements DelaySleeperContract
{
    public function sleep(int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        sleep($seconds);
    }
}
