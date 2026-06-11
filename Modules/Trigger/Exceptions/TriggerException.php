<?php

declare(strict_types=1);

namespace Modules\Trigger\Exceptions;

use Exception;

class TriggerException extends Exception
{
    public static function workflowHasNoCurrentVersion(string $workflowId): self
    {
        return new self("Workflow [{$workflowId}] does not have a published version to execute.");
    }

    public static function triggerInactive(string $triggerId): self
    {
        return new self("Trigger [{$triggerId}] is inactive.");
    }

    public static function webhookNotFound(): self
    {
        return new self('Webhook trigger was not found.');
    }

    public static function invalidCronExpression(string $expression): self
    {
        return new self("Cron expression [{$expression}] is invalid.");
    }

    public static function invalidTriggerType(string $expected, string $actual): self
    {
        return new self("Expected trigger type [{$expected}] but received [{$actual}].");
    }
}
