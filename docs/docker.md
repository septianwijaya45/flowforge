# Docker Deployment

Production-oriented stack with separate **backend** (Laravel API), **frontend** (React SPA), **PostgreSQL**, and **Redis**.

## Architecture

```
Browser → frontend (nginx:80) → /api/* proxied to backend (nginx + php-fpm)
                              → /* static React SPA
backend → postgres, redis
```

Frontend source lives in `frontend/` (moved from `resources/js`).

## Quick start

```bash
cp .env.docker .env.docker.local   # optional: customize secrets
docker compose --env-file .env.docker up -d --build
```

Migrations run automatically when the **backend** container starts (`php artisan migrate --force` in `docker/backend/entrypoint.sh`).

Seed demo users and sample data (**required before first login**):

```bash
docker compose exec backend php artisan db:seed --force
```

Open **http://localhost:8080/login** (or `FRONTEND_PORT` from compose).

Demo login: `admin@flowforge.test` / `password`

## PostgreSQL access

PostgreSQL is **not exposed to your host** by default (only containers on the `flowforge` network can reach it).

| Setting | Default value |
|---------|----------------|
| Host (from backend container) | `postgres` |
| Host (from your PC) | use `docker compose exec` below |
| Port | `5432` |
| Database | `flowforge` |
| Username | `flowforge` |
| Password | `flowforge` |
| Logs database | `flowforge_logs` |

**Open a psql shell inside Docker:**

```bash
docker compose exec postgres psql -U flowforge -d flowforge
```

**Run a one-off SQL query:**

```bash
docker compose exec postgres psql -U flowforge -d flowforge -c "SELECT email, role FROM users;"
```

**Connect from DBeaver / pgAdmin on Windows** — add to `docker-compose.yml` under `postgres`:

```yaml
ports:
  - '5432:5432'
```

Then connect to `localhost:5432` with user `flowforge` / password `flowforge`.

## Manual migrate (if needed)

```bash
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan migrate:status
```

## Services

| Service | Image / build | Role |
|---------|---------------|------|
| `frontend` | `frontend/Dockerfile` | Multi-stage Node build → nginx static + API proxy |
| `backend` | `docker/backend/Dockerfile` | Multi-stage Composer → PHP 8.3 FPM + nginx + queue worker |
| `postgres` | `postgres:16-alpine` | Primary DB + `flowforge_logs` database |
| `redis` | `redis:7-alpine` | Cache, sessions, queues |

## Local frontend development (without Docker)

```bash
cd frontend && npm install && npm run dev
```

Vite proxies `/api` to `http://localhost:8000` by default (`VITE_DEV_BACKEND_URL`).

## Production checklist

- [ ] Set strong `APP_KEY`, `DB_PASSWORD`, `REDIS_PASSWORD`, `JWT_SECRET` in `.env.docker`
- [ ] Quote `APP_KEY` values (e.g. `APP_KEY="base64:..."`) — Docker env files strip unquoted trailing `=`
- [ ] Set `APP_URL` to your public URL
- [ ] Set `CORS_ALLOWED_ORIGINS` if frontend and API are on different hosts
- [ ] Configure TLS termination (reverse proxy / load balancer in front of `frontend`)
- [ ] Run `php artisan execution-log:purge` on a schedule (cron / k8s job)

## Useful commands

```bash
npm run docker:up
npm run docker:down
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan workflow-runs:explain --tenant=<uuid>
docker compose logs -f backend queue-worker
```
