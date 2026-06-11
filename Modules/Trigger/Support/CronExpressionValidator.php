<?php

declare(strict_types=1);

namespace Modules\Trigger\Support;

use Cron\CronExpression;

final class CronExpressionValidator
{
    public static function isValid(string $expression): bool
    {
        return CronExpression::isValidExpression(trim($expression));
    }
}
