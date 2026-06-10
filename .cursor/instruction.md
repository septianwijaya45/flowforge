# FlowForge Engineering Standards

## Project Overview

Project Name: FlowForge

A multi-tenant workflow orchestration platform inspired by Zapier and GitHub Actions.

Tech Stack:

* Laravel 13
* React + Vite
* PostgreSQL
* Redis
* Docker
* GitHub Actions

Architecture Style:

* Modular Monolith
* Domain Driven Design (Lightweight)
* Service Layer Pattern
* Event Driven where applicable

---

# Core Principles

1. Readability over cleverness.
2. Explicit over implicit.
3. SOLID principles must be followed.
4. DRY but not over-engineered.
5. Fail fast.
6. Security by default.
7. Testability is mandatory.
8. Every business rule must live outside controllers.

---

# Architecture Standards

## Folder Structure

Backend:

app/

├── Domains/

│ ├── Tenant/

│ ├── User/

│ ├── Workflow/

│ ├── Execution/

│ ├── Monitoring/

│ └── AI/

├── Http/

├── Jobs/

├── Events/

├── Listeners/

├── Policies/

├── Exceptions/

├── DTOs/

├── Contracts/

└── Support/

Controllers must never contain business logic.

Controllers only:

* Validate Request
* Call Service
* Return Response

---

# Service Layer Standard

Business logic must live in Services.

Example:

WorkflowService
WorkflowExecutionService
WorkflowVersionService

Never place business logic inside:

* Controllers
* Models
* Requests

---

# DTO Standard

All complex data must use DTOs.

Example:

CreateWorkflowDTO
ExecuteWorkflowDTO
WorkflowNodeDTO

Never pass raw arrays between services.

---

# Repository Rule

Do not create repositories unless:

* Multiple data sources exist
* Query complexity becomes high

Use Eloquent directly otherwise.

Avoid repository-pattern boilerplate.

---

# Naming Standards

Classes:

PascalCase

Examples:

WorkflowService
WorkflowRun
WorkflowExecutionEngine

Methods:

camelCase

Examples:

createWorkflow()
executeWorkflow()
validateGraph()

Variables:

camelCase

Constants:

UPPER_SNAKE_CASE

Example:

MAX_RETRY_ATTEMPTS

Database Tables:

snake_case plural

Examples:

workflows
workflow_versions
workflow_runs

Columns:

snake_case

Examples:

tenant_id
workflow_id
created_at

---

# Laravel Standards

Always use:

declare(strict_types=1);

for all PHP files.

Use constructor injection.

Never use facades inside domain services when dependency injection is possible.

Prefer:

interface + implementation

for core business services.

---

# Controller Standards

Maximum:

150 lines

Controller responsibilities:

* Request validation
* Authorization
* Service invocation
* API response

Nothing else.

---

# Model Standards

Models should contain:

* Relationships
* Scopes
* Accessors

Models must NOT contain:

* Workflow execution logic
* Retry logic
* Business orchestration

Keep models thin.

---

# Request Validation Standards

Every endpoint must use FormRequest.

Never validate directly in controller.

Example:

StoreWorkflowRequest
UpdateWorkflowRequest

---

# API Standards

Base URL:

/api/v1

Response Format:

Success:

{
"success": true,
"message": "Workflow created",
"data": {}
}

Error:

{
"success": false,
"message": "Validation failed",
"errors": {}
}

---

# Authentication Standards

Use JWT.

Never expose internal IDs.

Expose UUIDs only.

RBAC Roles:

* admin
* editor
* viewer

Authorization must use Policies.

---

# Multi Tenant Standards

Every business table must contain:

tenant_id

Queries must always be tenant scoped.

Never expose cross-tenant data.

Global scope may be used.

Tenant isolation is mandatory.

---

# Workflow Standards

Workflow definitions must be stored as JSON DAG.

Node Types:

* http
* delay
* condition
* script

All workflow execution must be deterministic.

Workflow updates create a new version.

Workflow definitions are immutable after version creation.

---

# Queue Standards

Long-running tasks must use Queue Jobs.

Never execute workflow processing inside HTTP requests.

Use Redis queues.

Every Job must implement:

* retry strategy
* timeout
* failure handling

---

# Event Standards

Use events for:

* WorkflowStarted
* WorkflowCompleted
* WorkflowFailed
* StepStarted
* StepCompleted
* StepFailed

Realtime dashboard must consume events.

---

# Exception Standards

Create custom exceptions.

Examples:

WorkflowValidationException
CycleDetectedException
WorkflowTimeoutException

Never throw generic Exception.

---

# Logging Standards

Use structured logs.

Always include:

* tenant_id
* workflow_id
* run_id

Example:

{
"tenant_id": "...",
"workflow_id": "...",
"run_id": "...",
"message": "Step executed"
}

Never log secrets.

---

# Database Standards

Use PostgreSQL.

Every table must include:

* id (UUID)
* created_at
* updated_at

Soft delete only when required.

---

# Migration Standards

Migration files must:

* Be reversible
* Include indexes
* Include foreign keys

Always index:

* tenant_id
* workflow_id
* created_at

Use UUID primary keys.

Example:

$table->uuid('id')->primary();

Never use unsignedBigInteger IDs.

---

# Indexing Standards

Always index:

tenant_id

Frequently filtered columns

Foreign keys

Large query sorting columns

Every new table must be reviewed for indexing.

---

# Testing Standards

Framework:

Pest

Coverage Goals:

* Services → Unit Test
* APIs → Feature Test
* Workflow Engine → Integration Test

Minimum coverage:

70%

Every bug fix requires a test.

---

# Frontend Standards

Stack:

React
Vite
TypeScript

No JavaScript files.

Only TypeScript.

---

# React Standards

Use:

* Functional Components
* Hooks
* TanStack Query

Avoid:

* Class Components
* Prop Drilling

Use Context only when necessary.

---

# UI Standards

Use:

* TailwindCSS
* ShadCN UI

Design:

* Clean
* Minimal
* Enterprise Dashboard

Color Palette:

Primary:
#2563EB

Success:
#16A34A

Warning:
#F59E0B

Danger:
#DC2626

Neutral:
Gray Scale

---

# React Flow Standards

Workflow visualization must use:

React Flow

Node state colors:

Pending:
Gray

Running:
Blue

Success:
Green

Failed:
Red

---

# Realtime Standards

Use:

Laravel Reverb

Never poll when realtime events exist.

---

# Docker Standards

Every service must be containerized.

Services:

* frontend
* backend
* postgres
* redis

Must run using:

docker compose up

without manual steps.

---

# CI/CD Standards

GitHub Actions required.

Pipeline:

1. Install dependencies
2. Run Pint
3. Run PHPStan
4. Run Pest
5. Build frontend
6. Build Docker image

Fail pipeline on any error.

---

# Code Review Standards

Before merging:

Check:

* SOLID
* Naming
* Security
* Performance
* Tests

No TODO comments allowed in merged code.

No dead code.

No commented code.

---

# Security Standards

Validate every request.

Sanitize inputs.

Use rate limiting.

Prevent:

* SQL Injection
* XSS
* Mass Assignment

Never trust client data.

---

# AI Feature Standards

All LLM output must be validated.

Never execute raw AI-generated content.

AI responses must be converted into DTOs before processing.

Malformed AI output must fail gracefully.

---

# Documentation Standards

Every service class must contain PHPDoc.

Complex algorithms require architecture notes.

README must contain:

* Setup
* Architecture
* Database Design
* Trade-offs
* Future Improvements

---

# Performance Standards

Avoid N+1 queries.

Always eager load when needed.

Use indexes.

Paginate large datasets.

Never load unbounded collections.

---

# Definition of Done

Feature is complete only if:

* Code implemented
* Tests written
* API documented
* Types declared
* Lint passes
* Review passes
* Docker build passes
* CI passes
