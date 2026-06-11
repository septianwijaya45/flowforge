<?php

declare(strict_types=1);

namespace Modules\Trigger\DTOs;

use Modules\WorkflowEngine\Models\WorkflowRun;

final readonly class ProcessCronTriggersResultDTO
{
    /**
     * @param  list<WorkflowRun>  $runs
     */
    public function __construct(
        public array $runs,
        public int $processedCount,
    ) {}
}
