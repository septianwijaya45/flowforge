# Workflow Run Query Performance

This document describes the index strategy, optimized queries, and `EXPLAIN ANALYZE` examples for `workflow_runs` and `workflow_run_steps`.

## Index strategy

### `workflow_runs`

| Index | Columns | Purpose |
|-------|---------|---------|
| `PRIMARY` | `id` | PK lookup, route binding, `lockForUpdate` |
| `workflow_runs_tenant_created_idx` | `(tenant_id, created_at)` | Paginated run list (`ORDER BY created_at DESC`) |
| `workflow_runs_tenant_status_created_idx` | `(tenant_id, status, created_at)` | Filtered list + active-only queries |
| `workflow_runs_workflow_id_status_index` | `(workflow_id, status)` | Per-workflow history |
| `workflow_runs_workflow_version_id_index` | `workflow_version_id` | Version-scoped analytics |
| `workflow_runs_started_at_index` | `started_at` | Future timeout sweeper |

**Removed as redundant**

- `tenant_id` alone — left-prefix of composite indexes
- `created_at` alone — never queried without `tenant_id`
- `(tenant_id, status)` — superseded by `(tenant_id, status, created_at)`

### `workflow_run_steps`

| Index | Columns | Purpose |
|-------|---------|---------|
| `PRIMARY` | `id` | Step updates by PK |
| `workflow_run_steps_workflow_run_id_node_id_unique` | `(workflow_run_id, node_id)` | Idempotent step creation |
| `workflow_run_steps_workflow_run_id_status_index` | `(workflow_run_id, status)` | Timeout cancellation filter |
| `workflow_run_steps_run_order_idx` | `(workflow_run_id, execution_order)` | Ordered step timeline |

**Removed as redundant**

- `workflow_run_id` alone — covered by unique index left-prefix

### Design rules

1. **Tenant first** — every read path is tenant-scoped via `TenantScope`.
2. **Match sort order** — list queries sort by `created_at DESC`; composite indexes include `created_at`.
3. **Push aggregation to SQL** — metrics dashboard uses `COUNT`/`SUM`/`AVG` instead of loading rows into PHP.
4. **Narrow projections** — list endpoints omit large JSONB columns (`input`, `output`, `error`).

---

## Optimized queries

### 1. Paginated run list

**Service:** `WorkflowRunMonitorService::paginate`

```sql
SELECT id, workflow_id, workflow_version_id, status, trigger_type,
       started_at, completed_at, created_at
FROM workflow_runs
WHERE tenant_id = ?
ORDER BY created_at DESC
LIMIT ? OFFSET ?;
```

Uses `workflow_runs_tenant_created_idx` (or `workflow_runs_tenant_status_created_idx` when filtering by status).

### 2. Metrics totals (SQL aggregation)

**Service:** `WorkflowRunMonitorService::metrics`

```sql
SELECT
    COUNT(*) AS completed,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
    SUM(CASE WHEN status = 'timed_out' THEN 1 ELSE 0 END) AS timed_out,
    AVG(CASE WHEN started_at IS NOT NULL AND completed_at IS NOT NULL
        THEN TIMESTAMPDIFF(MICROSECOND, started_at, completed_at) / 1000 END) AS avg_execution_time_ms
FROM workflow_runs
WHERE tenant_id = ?
  AND created_at >= ?
  AND status IN ('success', 'failed', 'cancelled', 'timed_out');
```

Scans only the 30-day window via `(tenant_id, created_at)`.

### 3. Ordered steps per run

**Services:** `WorkflowRunMonitorService::show`, `WorkflowExecutionEngine::executeLayer`

```sql
SELECT id, workflow_run_id, node_id, node_type, node_label, status,
       attempt, execution_order, error, started_at, completed_at, duration_ms
FROM workflow_run_steps
WHERE workflow_run_id = ?
ORDER BY execution_order;
```

Uses `workflow_run_steps_run_order_idx` — no filesort.

### 4. Timeout step cancellation

**Service:** `WorkflowTimeoutManager::cancelActiveSteps`

```sql
SELECT *
FROM workflow_run_steps
WHERE workflow_run_id = ?
  AND status IN ('pending', 'running')
ORDER BY execution_order
FOR UPDATE;
```

Uses `(workflow_run_id, status)` or run-order index; `ORDER BY execution_order` prevents deadlocks.

---

## EXPLAIN ANALYZE examples

Run live plans against your database:

```bash
php artisan workflow-runs:explain --tenant=<tenant-uuid>
```

### Before optimization — paginated list (filesort)

```
-> Limit: 15 row(s)
    -> Sort: workflow_runs.created_at DESC          # extra sort step
        -> Index lookup on workflow_runs_tenant_id_index
```

### After optimization — paginated list (index scan)

```
-> Limit: 15 row(s)
    -> Index scan on workflow_runs_tenant_created_idx
       (tenant_id = ?), ordered by created_at DESC
```

### Before optimization — steps by run (filesort)

```
-> Sort: execution_order
    -> Index lookup on workflow_run_steps_workflow_run_id_node_id_unique
```

### After optimization — steps by run

```
-> Index scan on workflow_run_steps_run_order_idx
   (workflow_run_id = ?), ordered by execution_order
```

### PK + tenant check (unchanged — already optimal)

```
-> Single-row lookup on PRIMARY (id)
   Filter: tenant_id = ?
```

### Metrics period scan

**Before:** full tenant scan + `created_at` filter in memory.

**After:** range scan on `(tenant_id, created_at)` with SQL-side aggregation — row count proportional to window size, not tenant lifetime history.

---

## PostgreSQL notes

- Use `EXPLAIN (ANALYZE, BUFFERS)` for buffer hit statistics.
- Duration expression: `EXTRACT(EPOCH FROM (completed_at - started_at)) * 1000`.
- Consider a partial index for active runs: `CREATE INDEX ... ON workflow_runs (tenant_id, created_at) WHERE status IN ('pending', 'running');`

---

## Migration

Index changes ship in:

`Modules/WorkflowEngine/Database/Migrations/2026_06_11_000009_optimize_workflow_run_query_indexes.php`

Apply with:

```bash
php artisan migrate
```
