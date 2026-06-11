# Migration Strategy

This document defines how FlowForge applies database schema changes safely, maintains backward compatibility during deploys, and rolls back when needed.

It applies to all migrations under `database/migrations/` and `Modules/*/Database/Migrations/`.

---

## Principles

| Principle | Meaning |
|-----------|---------|
| **Reversible** | Every migration implements a working `down()` method. |
| **Additive first** | Prefer adding columns, tables, and indexes before removing anything. |
| **Deploy-safe** | Old application code must keep running until the migration finishes. |
| **Module-owned** | Each module owns its migrations; order is controlled by `ModuleRegistry`. |
| **Indexed by default** | New tables index `tenant_id`, foreign keys, and common filter/sort columns. |

---

## 1. Safe schema changes

### 1.1 Migration file conventions

```
Modules/{Module}/Database/Migrations/YYYY_MM_DD_NNNNNN_{action}_{subject}_table.php
```

**Sequence numbers** reflect dependency order across modules:

| # | Module | Example |
|---|--------|---------|
| 000001 | Tenant | `create_tenants_table` |
| 000002 | Workflow | `create_workflows_and_versions_tables` |
| 000003 | WorkflowEngine | `create_workflow_runs_and_steps_tables` |
| 000004+ | Supporting modules | Retry, Auth, Trigger, ExecutionLog, … |

**Rules:**

- Use `declare(strict_types=1);` in every migration.
- Use UUID primary keys: `$table->uuid('id')->primary();`
- Never use `unsignedBigInteger` for domain IDs.
- Never run `CREATE DATABASE` / `DROP DATABASE` inside migrations — provision databases out-of-band.
- Register new modules in `app/Support/Modules/ModuleRegistry.php` in dependency order.

### 1.2 Expand–contract pattern (recommended)

For non-trivial changes, split work across **three deploys**:

```
Deploy 1 — Expand     Add new column/table/index (nullable or unused)
Deploy 2 — Migrate    Backfill data; application reads/writes both paths
Deploy 3 — Contract   Remove old column/index after all nodes are updated
```

This avoids downtime and keeps old code functional during rollout.

#### Example A — Rename a column safely

**Deploy 1 — add new column (nullable)**

```php
// 2026_06_12_000010_add_display_name_to_workflows_table.php
public function up(): void
{
    Schema::table('workflows', function (Blueprint $table): void {
        $table->string('display_name')->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('workflows', function (Blueprint $table): void {
        $table->dropColumn('display_name');
    });
}
```

**Deploy 2 — backfill in migration; app writes to both `name` and `display_name`**

```php
// 2026_06_13_000011_backfill_workflow_display_names.php
public function up(): void
{
    DB::table('workflows')
        ->whereNull('display_name')
        ->update(['display_name' => DB::raw('name')]);
}

public function down(): void
{
    // Data backfill — no schema change to reverse
}
```

**Deploy 3 — drop old column after all app instances use `display_name`**

```php
// 2026_06_14_000012_drop_name_from_workflows_table.php
public function up(): void
{
    Schema::table('workflows', function (Blueprint $table): void {
        $table->dropColumn('name');
    });
}

public function down(): void
{
    Schema::table('workflows', function (Blueprint $table): void {
        $table->string('name')->nullable();
    });

    DB::table('workflows')->update(['name' => DB::raw('display_name')]);
}
```

#### Example B — Add a NOT NULL column to an existing table

Never add a non-nullable column without a default on a table that already has rows.

```php
public function up(): void
{
    // Step 1: add nullable
    Schema::table('workflow_runs', function (Blueprint $table): void {
        $table->string('correlation_id', 64)->nullable()->after('id');
        $table->index('correlation_id');
    });

    // Step 2: backfill existing rows
    DB::table('workflow_runs')
        ->whereNull('correlation_id')
        ->update(['correlation_id' => DB::raw('id')]);

    // Step 3: enforce NOT NULL in a follow-up migration after deploy is stable
}
```

```php
// Separate migration once backfill is verified
public function up(): void
{
    Schema::table('workflow_runs', function (Blueprint $table): void {
        $table->string('correlation_id', 64)->nullable(false)->change();
    });
}
```

> Requires `doctrine/dbal` or native column modification support for `->change()`.

#### Example C — Index changes (add before drop)

Always **create new indexes before dropping old ones**. This is how `2026_06_11_000009_optimize_workflow_run_query_indexes` works:

```php
public function up(): void
{
    Schema::table('workflow_runs', function (Blueprint $table): void {
        // 1. Add replacements first
        $table->index(['tenant_id', 'created_at'], 'workflow_runs_tenant_created_idx');
        $table->index(['tenant_id', 'status', 'created_at'], 'workflow_runs_tenant_status_created_idx');

        // 2. Drop redundant indexes only after new ones exist
        $table->dropIndex(['tenant_id']);
        $table->dropIndex(['created_at']);
        $table->dropIndex(['tenant_id', 'status']);
    });
}
```

**Why:** Dropping an index first can cause full table scans on production traffic between deploy steps.

#### Example D — New table with foreign keys

```php
// Modules/Retry/Database/Migrations/2026_06_11_000004_create_retry_histories_table.php
Schema::create('retry_histories', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
    $table->uuidMorphs('retryable');
    $table->string('status', 32);
    $table->timestamps();

    $table->index(['retryable_type', 'retryable_id', 'attempt']);
    $table->index('tenant_id');
    $table->index('status');
    $table->index('created_at');
});
```

**FK delete behaviour:**
- `cascadeOnDelete()` — child rows removed with parent (e.g. run steps when run is deleted).
- `restrictOnDelete()` — prevent parent deletion if children exist (e.g. workflow versions referenced by runs).
- `nullOnDelete()` — optional tenant linkage on audit tables.

#### Example E — Separate database connection (ExecutionLog)

High-volume tables on a dedicated connection **must not use foreign keys** to the primary database.

```php
return new class extends Migration
{
    protected $connection = 'execution_logs';

    public function up(): void
    {
        Schema::connection($this->connection)->create('execution_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');           // logical reference, no FK
            $table->uuid('workflow_run_id')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('execution_logs');
    }
};
```

Provision `flowforge_logs` (or your configured `EXECUTION_LOG_DB_DATABASE`) **before** running migrations in each environment.

### 1.3 Operations to avoid

| Avoid | Why | Alternative |
|-------|-----|-------------|
| `CREATE DATABASE` in migrations | Breaks CI/SQLite tests; requires elevated DB privileges | Provision DB in infrastructure / runbook |
| Dropping columns in the same deploy as code that still reads them | Runtime errors on rolling deploys | Expand–contract across deploys |
| `renameColumn` on large tables under load | Locks table in some engines | Add new column → backfill → drop old |
| Changing column type in-place on large tables | Long lock, replication lag | Add new column → dual-write → swap |
| Non-reversible data transforms in `up()` only | `down()` cannot restore state | Keep transforms in reversible steps or accept data loss in rollback doc |

### 1.4 Pre-deploy checklist

- [ ] Migration has a tested `down()` method.
- [ ] New columns are nullable or have safe defaults until backfill completes.
- [ ] Indexes added before old indexes are dropped.
- [ ] `tenant_id` and FK columns are indexed.
- [ ] Module registered in `ModuleRegistry` at the correct position.
- [ ] Pest feature test covers the behaviour that depends on the schema.
- [ ] `php artisan migrate` passes on SQLite (CI) and target production engine.

---

## 2. Backward compatibility

### 2.1 Deploy ordering

**Default rule:** run migrations **before** switching application traffic to new code.

```
1. Put app in maintenance mode (optional, for breaking changes only)
2. php artisan migrate --force
3. Deploy new application code
4. Verify health checks
5. Remove maintenance mode
```

For zero-downtime changes, use expand–contract so **old code keeps working** after step 2.

### 2.2 Application compatibility matrix

| Change type | Old code + new schema | New code + old schema |
|-------------|----------------------|----------------------|
| Add nullable column | Safe | Safe |
| Add NOT NULL column with default | Safe | Safe |
| Add NOT NULL column without default | **Unsafe** if table has rows | Safe |
| Drop column | **Unsafe** | Safe |
| Rename column | **Unsafe** | **Unsafe** |
| Add index | Safe | Safe |
| Drop index | Safe (perf risk) | Safe |

During rolling deploys, assume **both old and new code** may run simultaneously. Schema must satisfy the stricter row in the table.

### 2.3 API backward compatibility

When migrations remove or rename columns exposed via API resources:

1. Keep the old JSON field in API responses for at least one release (return value from new column).
2. Accept both old and new request field names in FormRequests during transition.
3. Document deprecation in release notes.

**Example — dual-read in a resource during transition:**

```php
// WorkflowResource — temporary compatibility shim
'name' => $this->display_name ?? $this->name,
```

Remove the shim only after Deploy 3 (contract).

### 2.4 Nullable FK references across databases

`execution_logs` stores `tenant_id` and `workflow_run_id` as plain UUIDs. If a run is deleted from the primary DB, log rows remain — this is intentional. Application code must not assume FK enforcement on the logs connection.

### 2.5 Data backfill pattern (from Auth module)

Existing pattern in `2026_06_11_000005_add_uuid_and_role_to_users_table.php`:

```php
Schema::table('users', function (Blueprint $table): void {
    $table->uuid('uuid')->nullable()->unique()->after('id');
    $table->string('role')->default(UserRole::Viewer->value);
});

foreach (DB::table('users')->whereNull('uuid')->pluck('id') as $userId) {
    DB::table('users')
        ->where('id', $userId)
        ->update(['uuid' => (string) Str::uuid()]);
}
```

**Compatibility notes:**
- `nullable()` allows old rows to exist before backfill runs in the same migration.
- `default()` on `role` ensures existing rows get a value without a separate backfill step.
- Follow-up migration can make `uuid` non-nullable once verified.

### 2.6 Multi-connection testing

PHPUnit uses SQLite in-memory for the default connection. Migrations on `execution_logs` run on a separate connection. Module tests that touch execution logs must ensure the connection is migrated (see `Modules/ExecutionLog/Tests`).

---

## 3. Rollback procedures

### 3.1 Standard rollback (development / staging)

Rollback the last batch of migrations:

```bash
php artisan migrate:rollback
```

Rollback a specific number of steps:

```bash
php artisan migrate:rollback --step=1
```

Rollback all migrations (destructive — development only):

```bash
php artisan migrate:reset
```

Fresh database (development only):

```bash
php artisan migrate:fresh --seed
```

### 3.2 Production rollback playbook

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Assess: Is rollback schema-only or code + schema?        │
├─────────────────────────────────────────────────────────────┤
│ 2. If new code depends on new schema → roll back CODE first │
│ 3. Run: php artisan migrate:rollback --step=N               │
│ 4. Verify: health checks, monitoring metrics, sample queries│
│ 5. Document: data loss scope if down() cannot restore data  │
└─────────────────────────────────────────────────────────────┘
```

**Critical rule:** If new application code **requires** the new schema, revert the application deployment **before** rolling back migrations. Otherwise you will get column-not-found errors in production.

### 3.3 What `down()` can and cannot undo

| Reversible in `down()` | Not reversible |
|------------------------|----------------|
| Drop table created in `up()` | Rows deleted by `cascadeOnDelete` |
| Drop column added in `up()` | Transformed/overwritten values from backfill |
| Restore dropped index | Data inserted after `up()` that violates old constraints |
| Re-add column with `down()` + manual backfill from new column | `DROP DATABASE`, truncate operations |

Document irreversible steps in the migration docblock when applicable.

### 3.4 Rollback examples

#### Example A — Roll back index optimization

```bash
php artisan migrate:rollback --step=1
```

`2026_06_11_000009_optimize_workflow_run_query_indexes` `down()` restores:

- `workflow_runs`: re-adds `tenant_id`, `created_at`, `(tenant_id, status)` indexes
- Drops `workflow_runs_tenant_created_idx` and `workflow_runs_tenant_status_created_idx`
- `workflow_run_steps`: re-adds standalone `workflow_run_id` index

No data loss. Query performance returns to pre-migration plans.

#### Example B — Roll back new table

```bash
php artisan migrate:rollback --step=1
```

`create_execution_logs_table` `down()`:

```php
Schema::connection($this->connection)->dropIfExists('execution_logs');
```

**Data loss:** all execution log rows are permanently deleted. Export logs before rollback if needed:

```bash
# MySQL export before rollback
mysqldump flowforge_logs execution_logs > execution_logs_backup.sql
```

#### Example C — Roll back column addition

`add_uuid_and_role_to_users_table` `down()`:

```php
Schema::table('users', function (Blueprint $table): void {
    $table->dropIndex(['role']);
    $table->dropColumn(['uuid', 'role']);
});
```

**Data loss:** all `uuid` and `role` values are lost. Ensure no production service depends on them before rollback.

#### Example D — Emergency fix without rollback

When `down()` is risky but a forward fix is safe, **prefer a new forward migration**:

```php
// 2026_06_15_000013_fix_workflow_runs_status_length.php
public function up(): void
{
    Schema::table('workflow_runs', function (Blueprint $table): void {
        $table->string('status', 64)->change();
    });
}
```

Forward fixes are safer than rolling back in production when data has already been written under the new schema.

### 3.5 Rollback verification checklist

After any rollback:

- [ ] `php artisan migrate:status` shows expected state
- [ ] `php artisan test` passes
- [ ] Monitoring dashboard loads (`/api/v1/monitoring/metrics`)
- [ ] Workflow execution completes end-to-end
- [ ] No orphaned FK references (if parent tables were affected)

---

## 4. Environment-specific notes

### MySQL (local Laragon default)

- Create separate databases before migrate: `flowforge`, `flowforge_logs`.
- Index renames: use explicit index names (`workflow_runs_tenant_created_idx`) for predictable `down()`.
- Large table changes: consider `ALGORITHM=INPLACE, LOCK=NONE` for online DDL where supported.

### PostgreSQL (production target per project standards)

- Use `jsonb` for JSON columns.
- `EXPLAIN (ANALYZE, BUFFERS)` to validate index changes (see `php artisan workflow-runs:explain`).
- Partial indexes for hot filters (e.g. active runs only).

### SQLite (CI / PHPUnit)

- `RefreshDatabase` runs all module migrations including multi-connection ones.
- Avoid MySQL-specific SQL in migrations (`DATE_SUB`, `TIMESTAMPDIFF`) — keep raw SQL in services with driver detection, not in migrations.
- `:memory:` databases are isolated per connection name.

---

## 5. Quick reference

```bash
# Apply pending migrations
php artisan migrate --force

# Check status
php artisan migrate:status

# Roll back last batch
php artisan migrate:rollback

# Roll back N migrations
php artisan migrate:rollback --step=3

# Validate workflow run query plans after index changes
php artisan workflow-runs:explain --tenant=<uuid>
```

---

## 6. Related documents

- Project standards: `.cursor/instruction.md` (Migration Standards, Indexing Standards)
- Workflow run index strategy: `Modules/WorkflowEngine/docs/workflow-run-query-performance.md`
- Module registration: `app/Support/Modules/ModuleRegistry.php`
