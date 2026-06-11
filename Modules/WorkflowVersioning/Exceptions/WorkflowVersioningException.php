<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Exceptions;

use Exception;

class WorkflowVersioningException extends Exception
{
    public static function versionNotFoundForWorkflow(string $versionId, string $workflowId): self
    {
        return new self("Workflow version [{$versionId}] does not belong to workflow [{$workflowId}].");
    }
}
