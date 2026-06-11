# FlowForge

FlowForge is a multi-tenant workflow automation platform. Teams define directed acyclic graph (DAG) workflows, version them, trigger runs manually or via webhooks/cron, and monitor execution with structured logs.

The backend is a **modular Laravel monolith** (`Modules/`). The UI is a **React SPA** in `frontend/`, served separately in production (nginx) with `/api` proxied to Laravel.

---

## Setup

### Requirements

| Tool | Version |
|------|---------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20+ |
| PostgreSQL | 16 (recommended) |
| Redis | 7 (recommended) |

SQLite works for local development and CI; Docker and production use PostgreSQL.

### Local development (Laragon / native)

```bash
# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# Frontend
cd frontend && npm install && npm run dev
```

In another terminal, start the API:

```bash
php artisan serve
```

The Vite dev server proxies `/api` to `http://localhost:8000` by default.

**Demo login** (after seeding): `admin@flowforge.test` / `password`

### Docker (recommended for a full stack)

```bash
docker compose --env-file .env.docker up -d --build
docker compose exec backend php artisan db:seed --force
```

Open **http://localhost:8080/login** (override with `FRONTEND_PORT` in compose).

Migrations run automatically on backend startup. See [docs/docker.md](docs/docker.md) for PostgreSQL access, production checklist, and troubleshooting.

### Environment highlights

| Variable | Purpose |
|----------|---------|
| `APP_KEY` | Laravel encryption (quote values in Docker env files) |
| `DB_*` | Primary application database |
| `EXECUTION_LOG_DB_*` | Separate store for high-volume execution logs |
| `JWT_SECRET` | API bearer tokens |
| `REDIS_*` | Cache, sessions, queues |
| `VITE_DEFAULT_TENANT_ID` | SPA tenant bootstrap (Docker frontend build arg) |

### Tests and quality checks

```bash
# Full suite (Pint + PHPStan + Pest)
composer test

# Individual checks
composer lint:check
composer types:check
php artisan test

# Frontend
cd frontend && npm run types:check && npm run build
```

CI runs Pint, PHPStan, Pest, and the frontend build on push/PR — see [.github/workflows/ci.yml](.github/workflows/ci.yml).

---

## Architecture

### High-level flow

```
Browser
   │
   ▼
Frontend (React SPA + nginx)
   │  /api/v1/*  ──►  Laravel API (modular monolith)
   │                         │
   │                         ├── PostgreSQL (workflows, runs, tenants, …)
   │                         ├── PostgreSQL flowforge_logs (execution logs)
   │                         └── Redis (cache, sessions, queues)
   │
   └── WebSocket (Reverb) — optional real-time updates
```

### Workflow lifecycle

1. **Define** — Workflow metadata (name, status) via the Workflow module; graph definition via WorkflowVersioning.
2. **Validate** — `WorkflowGraphValidator` enforces DAG rules (single root, no cycles, reachable nodes).
3. **Trigger** — Manual, webhook, or cron triggers create a `WorkflowRun` in `pending` status.
4. **Execute** — `WorkflowExecutionEngine` topologically sorts nodes into layers, runs each layer in parallel, and persists step state.
5. **Observe** — Monitoring API for run status; ExecutionLog module for structured logs; Retry module for failed steps.

### API surface

All module routes mount under **`/api/v1`**. Requests require:

- `Authorization: Bearer <token>` (except public webhook endpoints)
- `X-Tenant-Id` or `X-Tenant-Slug` for tenant-scoped resources

Roles: `admin`, `editor`, `viewer` — enforced per route via middleware.

### Deployment

Local Docker Compose maps to AWS as documented in [docs/aws-architecture.md](docs/aws-architecture.md):

| Local | AWS |
|-------|-----|
| `frontend` | ECS Fargate + ALB |
| `backend` | ECS Fargate (+ queue worker) |
| `postgres` | RDS PostgreSQL 16 |
| `redis` | ElastiCache Redis 7 |

Diagram: [docs/diagrams/flowforge-aws-architecture.drawio](docs/diagrams/flowforge-aws-architecture.drawio)

### Code layout

```
app/                  # Application shell, module registry
Modules/              # Domain modules (routes, migrations, services, tests)
frontend/             # React SPA (Vite, React Router, TanStack Query)
database/             # Core seeders
docker/               # Backend Dockerfile, Postgres init, entrypoint
tests/                # Cross-cutting Feature tests (E2E, lifecycle)
docs/                 # Docker, AWS, migration strategy
```

Each module follows the same shape: `Contracts/`, `Services/`, `Http/`, `Models/`, `Database/Migrations/`, `Routes/`, `Tests/`.

---

## Module Overview

Modules boot in dependency order via `App\Support\Modules\ModuleRegistry`.

| Module | Responsibility |
|--------|----------------|
| **Auth** | Users, JWT login, roles (`admin` / `editor` / `viewer`), Fortify session bridge |
| **Tenant** | Multi-tenancy, `X-Tenant-Id` context, data isolation |
| **Workflow** | Workflow CRUD (metadata only — not the graph) |
| **WorkflowVersioning** | Immutable version snapshots, publish, rollback (copy-forward) |
| **WorkflowEngine** | DAG validation, topological sort, node executors (HTTP, delay, condition, script), run orchestration |
| **Trigger** | Manual, webhook, and cron triggers; dispatches pending runs |
| **ExecutionLog** | High-volume structured logs in a separate database connection |
| **Retry** | Exponential backoff strategy, retry decisions, persisted retry history |
| **Monitoring** | Workflow run listing, detail, and metrics APIs |
| **Scheduler** | Cron schedule management (cron triggers stored as `WorkflowTrigger`) |
| **AI** | Extension point for AI-assisted workflow features (scaffold) |

### Node types (WorkflowEngine)

| Type | Behavior |
|------|----------|
| `http` | Outbound HTTP request |
| `delay` | Sleep for configured seconds |
| `condition` | Branching predicate |
| `script` | Lightweight in-process script step |

---

## Trade-offs

### Modular monolith vs. microservices

**Choice:** Single deployable with strict module boundaries (`ModuleServiceProvider`, owned migrations, contract interfaces).

**Why:** Faster iteration, simpler local/Docker setup, transactional consistency across workflow state. Modules are structured for eventual extraction (e.g. WorkflowEngine as a worker service).

**Cost:** All modules share one process and database connection pool; blast radius of a bad deploy is application-wide.

### Separate execution log database

**Choice:** `flowforge_logs` on its own connection (second Postgres database in Docker/AWS).

**Why:** Execution logs are append-heavy and would bloat primary OLTP tables and backups.

**Cost:** Two databases to migrate, monitor, and purge (`execution-log:purge`); cross-DB joins are not available.

### Versioning by copy-forward (rollback = new version)

**Choice:** Rollback does not mutate history; it creates a new version copying a prior snapshot.

**Why:** Full audit trail, safe concurrent reads, no destructive edits.

**Cost:** Storage grows with every publish and rollback; deduplication is hash-based but rows remain.

### Trigger creates pending run; execution is explicit

**Choice:** HTTP trigger endpoints create a `pending` `WorkflowRun`. The `WorkflowExecutionEngine` runs synchronously when invoked (tests, jobs, or future queue worker).

**Why:** Clear separation between scheduling and execution; supports retries, cancellation, and monitoring before work starts.

**Cost:** No automatic “fire and forget” until a queue consumer is wired to every trigger path in production.

### React SPA decoupled from Laravel

**Choice:** Frontend in `frontend/` with its own build pipeline; nginx proxies API in Docker.

**Why:** Independent deploys, CDN-friendly static assets, team can scale UI separately.

**Cost:** Two images/containers, CORS and auth header discipline, tenant ID passed from SPA config.

### SQLite in CI, PostgreSQL in production

**Choice:** Tests default to in-memory/SQLite; Docker and AWS use PostgreSQL.

**Why:** Fast CI, no service containers required for Pest.

**Cost:** Minor dialect differences (mitigated by avoiding raw SQL where possible).

---

## Future Improvements

| Area | Improvement |
|------|-------------|
| **Execution** | Queue-driven run processor so every trigger automatically enqueues `WorkflowExecutionEngine::execute` |
| **Infrastructure** | Terraform/ECS task definitions and Secrets Manager wiring per [docs/aws-architecture.md](docs/aws-architecture.md) |
| **Real-time** | Reverb/WebSocket push for live run and step status in the SPA |
| **Workflow builder** | Complete SPA routes for visual DAG editing wired to versioning API |
| **AI module** | Node suggestions, natural-language-to-workflow, failure diagnosis |
| **Observability** | OpenTelemetry traces across trigger → execute → log; Datadog/CloudWatch dashboards |
| **Storage** | S3 for Laravel `storage/` and artifact outputs from script/HTTP steps |
| **Auth** | SSO (SAML/OIDC), API keys for machine triggers, per-tenant RBAC |
| **Scale** | Horizontal worker pool with run leasing; partition execution logs by tenant/date |
| **Quality** | Green CI on all Pint/PHPStan rules; expand E2E coverage for webhook and cron paths |

---

## Related documentation

- [Docker deployment](docs/docker.md)
- [AWS architecture](docs/aws-architecture.md)
- [Database migration strategy](docs/migration-strategy.md)
- [Workflow run query performance](Modules/WorkflowEngine/docs/workflow-run-query-performance.md)

## License

MIT
