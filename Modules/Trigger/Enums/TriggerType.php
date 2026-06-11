<?php

declare(strict_types=1);

namespace Modules\Trigger\Enums;

enum TriggerType: string
{
    case Manual = 'manual';
    case Cron = 'cron';
    case Webhook = 'webhook';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
