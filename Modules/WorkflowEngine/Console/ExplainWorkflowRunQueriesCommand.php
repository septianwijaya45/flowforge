<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\WorkflowEngine\Models\WorkflowRun;

class ExplainWorkflowRunQueriesCommand extends Command
{
    protected $signature = 'workflow-runs:explain {--tenant= : Tenant UUID for scoped queries}';

    protected $description = 'Run EXPLAIN ANALYZE on core workflow run queries';

    public function handle(): int
    {
        $tenantId = $this->option('tenant')
            ?? DB::table('tenants')->value('id');

        if ($tenantId === null) {
            $this->error('No tenant found. Pass --tenant=<uuid> or seed tenants first.');

            return self::FAILURE;
        }

        $runId = DB::table('workflow_runs')
            ->where('tenant_id', $tenantId)
            ->value('id') ?? '11111111-1111-1111-1111-111111111111';

        $driver = WorkflowRun::query()->getConnection()->getDriverName();
        $explainPrefix = $driver === 'mysql' ? 'EXPLAIN ANALYZE' : 'EXPLAIN ANALYZE';

        $queries = [
            'paginate_tenant_recent' => "
                {$explainPrefix}
                SELECT id, workflow_id, workflow_version_id, status, trigger_type,
                       started_at, completed_at, created_at
                FROM workflow_runs
                WHERE tenant_id = '{$tenantId}'
                ORDER BY created_at DESC
                LIMIT 15
            ",
            'paginate_tenant_status' => "
                {$explainPrefix}
                SELECT id, workflow_id, workflow_version_id, status, trigger_type,
                       started_at, completed_at, created_at
                FROM workflow_runs
                WHERE tenant_id = '{$tenantId}' AND status = 'running'
                ORDER BY created_at DESC
                LIMIT 15
            ",
            'active_count' => "
                {$explainPrefix}
                SELECT COUNT(*) AS aggregate
                FROM workflow_runs
                WHERE tenant_id = '{$tenantId}'
                  AND status IN ('pending', 'running')
            ",
            'metrics_period_totals' => "
                {$explainPrefix}
                SELECT status, started_at, completed_at, created_at
                FROM workflow_runs
                WHERE tenant_id = '{$tenantId}'
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ",
            'pk_tenant_lookup' => "
                {$explainPrefix}
                SELECT *
                FROM workflow_runs
                WHERE id = '{$runId}' AND tenant_id = '{$tenantId}'
            ",
            'steps_by_run_ordered' => "
                {$explainPrefix}
                SELECT id, workflow_run_id, node_id, status, execution_order
                FROM workflow_run_steps
                WHERE workflow_run_id = '{$runId}'
                ORDER BY execution_order
            ",
            'steps_active_for_timeout' => "
                {$explainPrefix}
                SELECT id, workflow_run_id, status, execution_order
                FROM workflow_run_steps
                WHERE workflow_run_id = '{$runId}'
                  AND status IN ('pending', 'running')
                ORDER BY execution_order
            ",
        ];

        if ($driver === 'pgsql') {
            $queries['metrics_period_totals'] = "
                EXPLAIN (ANALYZE, BUFFERS)
                SELECT status, started_at, completed_at, created_at
                FROM workflow_runs
                WHERE tenant_id = '{$tenantId}'
                  AND created_at >= NOW() - INTERVAL '30 days'
            ";
        }

        $this->info("Driver: {$driver}");
        $this->info("Tenant: {$tenantId}");
        $this->info("Run: {$runId}");
        $this->newLine();

        foreach ($queries as $name => $sql) {
            $this->line("<fg=cyan>=== {$name} ===</>");
            $this->line(trim($sql));
            $this->newLine();

            try {
                $rows = DB::select($sql);

                foreach ($rows as $row) {
                    $plan = $row->EXPLAIN ?? $row->{'QUERY PLAN'} ?? json_encode($row);
                    $this->line(is_string($plan) ? $plan : json_encode($plan, JSON_PRETTY_PRINT));
                }
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());
            }

            $this->newLine();
        }

        $this->info('=== workflow_runs indexes ===');
        $this->dumpIndexes('workflow_runs');

        $this->info('=== workflow_run_steps indexes ===');
        $this->dumpIndexes('workflow_run_steps');

        return self::SUCCESS;
    }

    private function dumpIndexes(string $table): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = DB::select('SHOW INDEX FROM '.$table);

            foreach ($indexes as $index) {
                $this->line(sprintf(
                    '%s (%s) seq=%d',
                    $index->Key_name,
                    $index->Column_name,
                    $index->Seq_in_index,
                ));
            }

            return;
        }

        if ($driver === 'pgsql') {
            $indexes = DB::select("
                SELECT indexname, indexdef
                FROM pg_indexes
                WHERE tablename = ?
            ", [$table]);

            foreach ($indexes as $index) {
                $this->line($index->indexname.': '.$index->indexdef);
            }

            return;
        }

        $indexes = DB::select("PRAGMA index_list('{$table}')");

        foreach ($indexes as $index) {
            $this->line((string) ($index->name ?? json_encode($index)));
        }
    }
}
