# FlowForge — AWS Deployment Architecture

Production deployment for FlowForge on AWS using **ALB**, **ECS (Fargate)**, **RDS (PostgreSQL)**, and **ElastiCache (Redis)**. This maps the local Docker Compose stack (`frontend`, `backend`, `postgres`, `redis`) to managed AWS services.

## High-level flow

```
Internet
   │
   ▼
Route 53 (optional) ──► ACM TLS certificate
   │
   ▼
Application Load Balancer (public subnets, 2 AZs)
   │
   ├──► /api/* , /up ──► ECS Service: backend (Laravel API + queue worker)
   │
   └──► /* ───────────► ECS Service: frontend (nginx + React SPA)
                              │
                              └── proxies /api/* to backend (same as Docker)

ECS tasks (private subnets)
   ├── backend ──► RDS PostgreSQL (flowforge + flowforge_logs)
   │            └──► ElastiCache Redis (cache, sessions, queues)
   └── frontend ──► backend (internal target group or Service Connect)
```

## Component mapping (Docker → AWS)

| Local (Compose) | AWS service | Notes |
|-----------------|-------------|--------|
| `frontend` (nginx + static) | **ECS Fargate** service + ALB target group | Serves SPA; path-based or host-based routing |
| `backend` (PHP-FPM + nginx + queue) | **ECS Fargate** service (2 tasks recommended: web + worker) | Same image; worker as separate service or sidecar |
| `postgres` | **Amazon RDS** PostgreSQL 16 | Multi-AZ for production; `flowforge_logs` as second database |
| `redis` | **Amazon ElastiCache** Redis 7 | Replication group; auth token + TLS in transit |
| — | **ALB** | HTTPS termination, health checks on `/up` (backend) and `/` (frontend) |
| — | **ECR** | Container images for `flowforge-backend` and `flowforge-frontend` |
| — | **Secrets Manager** | `APP_KEY`, `DB_PASSWORD`, `REDIS_PASSWORD`, `JWT_SECRET` |
| — | **EFS** (optional) | Shared `storage/` for Laravel uploads if not using S3 |

## Network layout (VPC)

Recommended: **one VPC**, `/16`, split across **two Availability Zones**.

| Tier | Subnets | Resources |
|------|---------|-----------|
| **Public** | `10.0.1.0/24`, `10.0.2.0/24` | ALB, NAT Gateway (one per AZ or single NAT for cost) |
| **Private (app)** | `10.0.10.0/24`, `10.0.11.0/24` | ECS Fargate tasks (no public IP) |
| **Private (data)** | `10.0.20.0/24`, `10.0.21.0/24` | RDS subnet group, ElastiCache subnet group |

Security groups (principle of least privilege):

| SG | Inbound | Outbound |
|----|---------|----------|
| `alb-sg` | 443 from `0.0.0.0/0` | ECS frontend/backend ports |
| `ecs-frontend-sg` | 80 from `alb-sg` | backend SG, HTTPS to ECR/APIs |
| `ecs-backend-sg` | 80 from `alb-sg` + `ecs-frontend-sg` | RDS 5432, Redis 6379 |
| `rds-sg` | 5432 from `ecs-backend-sg` | — |
| `redis-sg` | 6379 from `ecs-backend-sg` | — |

## Application Load Balancer (ALB)

- **Scheme:** internet-facing
- **Listeners:**
  - `HTTPS:443` → default action → **frontend** target group
  - **Listener rules:**
    - Path `/api/*` → **backend** target group
    - Path `/up` → **backend** target group (health)
- **Health checks:**
  - Frontend: `GET /` → 200
  - Backend: `GET /up` → 200 (matches `docker/backend/Dockerfile` healthcheck)
- **TLS:** ACM certificate on the ALB (terminate SSL at load balancer)

Alternative: single **frontend** service only; nginx in the frontend container proxies `/api` to an internal backend URL (matches current `frontend/nginx.conf`).

## ECS (Fargate)

### Cluster

- **Launch type:** Fargate
- **Platform:** Linux x86_64
- **Capacity providers:** FARGATE (on-demand) or FARGATE_SPOT for workers

### Services

| Service | Image | CPU / Memory | Desired count | Notes |
|---------|-------|--------------|---------------|--------|
| `flowforge-frontend` | ECR `flowforge-frontend` | 0.25 vCPU / 512 MB | 2+ | Behind ALB; autoscale on CPU/request count |
| `flowforge-backend` | ECR `flowforge-backend` | 0.5 vCPU / 1 GB | 2+ | Web + PHP-FPM + nginx in one task (current Dockerfile) |
| `flowforge-worker` | Same backend image | 0.5 vCPU / 1 GB | 1+ | Command: `php artisan queue:work redis --sleep=3` |

### Task definition (backend) — key environment

| Variable | Source |
|----------|--------|
| `DB_HOST` | RDS endpoint |
| `DB_DATABASE` | `flowforge` |
| `EXECUTION_LOG_DB_HOST` | Same RDS endpoint |
| `EXECUTION_LOG_DB_DATABASE` | `flowforge_logs` |
| `REDIS_HOST` | ElastiCache primary endpoint |
| `APP_KEY`, `JWT_SECRET`, passwords | Secrets Manager → `secrets` in task definition |
| `APP_URL` | `https://app.example.com` |
| `CORS_ALLOWED_ORIGINS` | Same origin as frontend |

### Deployment

- **Rolling update:** min healthy 100%, max 200%
- **Execute command:** enable for `php artisan migrate` (or run migrations in CI/CD before deploy)
- **Service Connect** (optional): DNS name `backend` for frontend → backend without ALB hop

## RDS (PostgreSQL)

- **Engine:** PostgreSQL 16 (matches `postgres:16-alpine` locally)
- **Instance:** `db.r6g.large` or right-sized after load test
- **Storage:** gp3, encrypted at rest (KMS)
- **Multi-AZ:** enabled for production
- **Databases:**
  - `flowforge` — application data
  - `flowforge_logs` — execution log module (create via init script or migration)
- **Backups:** 7–35 day retention; automated snapshots
- **Parameter group:** tune `max_connections`, `shared_buffers` for workload
- **No public access** — only `ecs-backend-sg`

Optional: **RDS Proxy** between ECS and RDS for connection pooling under high concurrency.

## ElastiCache (Redis)

- **Engine:** Redis 7.x
- **Topology:** replication group, 1 primary + 1 replica (Multi-AZ)
- **Use cases (from `.env.docker`):**
  - `CACHE_STORE=redis`
  - `SESSION_DRIVER=redis`
  - `QUEUE_CONNECTION=redis`
- **Auth:** Redis AUTH token stored in Secrets Manager
- **Encryption:** in-transit + at-rest enabled
- **Subnet group:** private data subnets only

## CI/CD integration (reference)

GitHub Actions (see `.github/workflows/ci.yml`) builds images; deploy pipeline typically:

1. Run Pint, PHPStan, Pest, NPM build
2. Build & push images to **ECR**
3. Update ECS task definition revision
4. `ecs deploy` / CodeDeploy blue-green on ALB
5. Run `php artisan migrate --force` as one-off ECS task or init container

## Observability

| Concern | AWS service |
|---------|-------------|
| Logs | CloudWatch Logs (awslogs driver per container) |
| Metrics | CloudWatch + ECS service metrics |
| Alarms | ALB 5xx, ECS CPU/memory, RDS CPU/storage, Redis evictions |
| Tracing | X-Ray (optional) |

## HA and DR

| Layer | Approach |
|-------|----------|
| ALB | Cross-AZ by default |
| ECS | `desired_count ≥ 2`, spread across AZs |
| RDS | Multi-AZ failover |
| ElastiCache | Multi-AZ with automatic failover |
| Backups | RDS snapshots + optional cross-region copy |

## Cost optimization (non-prod)

- Single NAT Gateway instead of per-AZ
- Fargate Spot for worker service
- Smaller RDS (`db.t4g.medium`) and cache (`cache.t4g.small`)
- One AZ for dev/staging only (not recommended for production)

## Diagram

Import the Draw.io file:

**[`docs/diagrams/flowforge-aws-architecture.drawio`](diagrams/flowforge-aws-architecture.drawio)**

Open with [diagrams.net](https://app.diagrams.net) → **File → Open** → select the `.drawio` file.
