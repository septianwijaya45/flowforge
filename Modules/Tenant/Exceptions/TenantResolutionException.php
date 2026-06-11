<?php

declare(strict_types=1);

namespace Modules\Tenant\Exceptions;

use Exception;

class TenantResolutionException extends Exception
{
    public static function notProvided(): self
    {
        return new self('A tenant identifier must be provided via the X-Tenant-Id or X-Tenant-Slug header.');
    }

    public static function notFound(string $identifier): self
    {
        return new self("Tenant [{$identifier}] was not found.");
    }

    public static function inactive(string $tenantId): self
    {
        return new self("Tenant [{$tenantId}] is inactive.");
    }
}
