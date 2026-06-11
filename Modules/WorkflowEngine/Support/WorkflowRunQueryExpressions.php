<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Support;

use Illuminate\Database\Connection;

/**
 * Driver-specific SQL fragments for workflow run analytics queries.
 */
final class WorkflowRunQueryExpressions
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public static function forConnection(Connection $connection): self
    {
        return new self($connection);
    }

    public function executionDurationMs(): string
    {
        return match ($this->connection->getDriverName()) {
            'pgsql' => 'EXTRACT(EPOCH FROM (completed_at - started_at)) * 1000',
            'sqlite' => 'CAST((julianday(completed_at) - julianday(started_at)) * 86400000 AS INTEGER)',
            default => 'TIMESTAMPDIFF(MICROSECOND, started_at, completed_at) / 1000',
        };
    }

    public function dateFromCreatedAt(): string
    {
        return match ($this->connection->getDriverName()) {
            'pgsql' => 'DATE(created_at)',
            default => 'DATE(created_at)',
        };
    }
}
