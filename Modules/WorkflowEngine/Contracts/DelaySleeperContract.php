<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

interface DelaySleeperContract
{
    public function sleep(int $seconds): void;
}
