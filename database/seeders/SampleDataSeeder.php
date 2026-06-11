<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\RefreshToken;
use Modules\Auth\Models\User;
use Modules\Retry\Enums\RetryHistoryStatus;
use Modules\Retry\Models\RetryHistory;
use Modules\Tenant\Models\Tenant;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class SampleDataSeeder extends Seeder
{
    /**
     * Demo credentials (password for all users): password
     */
    public function run(): void
    {
        $defaultTenant = Tenant::query()->updateOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Organization', 'is_active' => true],
        );

        $acmeTenant = Tenant::query()->updateOrCreate(
            ['slug' => 'acme-corp'],
            ['name' => 'Acme Corporation', 'is_active' => true],
        );

        $admin = $this->seedUser('Admin User', 'admin@flowforge.test', UserRole::Admin);
        $editor = $this->seedUser('Editor User', 'editor@flowforge.test', UserRole::Editor);
        $viewer = $this->seedUser('Viewer User', 'viewer@flowforge.test', UserRole::Viewer);
        $testUser = $this->seedUser('Test User', 'test@example.com', UserRole::Editor);

        $this->seedRefreshToken($admin);
        $this->seedRefreshToken($testUser);

        $onboardWorkflow = $this->seedWorkflow(
            tenant: $defaultTenant,
            creator: $admin,
            name: 'Onboard New User',
            slug: 'onboard-new-user',
            status: WorkflowStatus::Active,
            description: 'Creates a user account and sends a welcome email.',
            definition: $this->onboardWorkflowDefinition(),
            changeSummary: 'Initial onboarding flow with HTTP and delay nodes.',
        );

        $reportWorkflow = $this->seedWorkflow(
            tenant: $defaultTenant,
            creator: $editor,
            name: 'Daily Sales Report',
            slug: 'daily-sales-report',
            status: WorkflowStatus::Active,
            description: 'Aggregates sales data and posts summary to Slack.',
            definition: $this->reportWorkflowDefinition(),
            changeSummary: 'Scheduled report workflow.',
        );

        $webhookWorkflow = $this->seedWorkflow(
            tenant: $defaultTenant,
            creator: $editor,
            name: 'Webhook Order Processor',
            slug: 'webhook-order-processor',
            status: WorkflowStatus::Active,
            description: 'Processes incoming order webhooks from the storefront.',
            definition: $this->webhookWorkflowDefinition(),
            changeSummary: 'Webhook-driven order pipeline.',
        );

        $this->seedWorkflow(
            tenant: $acmeTenant,
            creator: $viewer,
            name: 'Legacy Data Import',
            slug: 'legacy-data-import',
            status: WorkflowStatus::Draft,
            description: 'Draft workflow for migrating legacy CRM records.',
            definition: $this->simpleWorkflowDefinition('validate-import', 'Validate Import'),
            changeSummary: 'Work in progress.',
        );

        $this->seedTriggers($defaultTenant, $onboardWorkflow, $reportWorkflow, $webhookWorkflow, $admin);
        $this->seedWorkflowRuns($defaultTenant, $onboardWorkflow, $reportWorkflow, $webhookWorkflow, $admin, $editor);
    }

    private function seedUser(string $name, string $email, UserRole $role): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => User::factory()->make()->password,
                'email_verified_at' => now(),
                'role' => $role,
            ],
        );

        if ($user->uuid === null) {
            $user->uuid = (string) Str::uuid();
            $user->save();
        }

        return $user->fresh();
    }

    private function seedRefreshToken(User $user): void
    {
        RefreshToken::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'token_hash' => hash('sha256', 'sample-refresh-token-'.$user->email),
            ],
            [
                'expires_at' => now()->addDays(7),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedWorkflow(
        Tenant $tenant,
        User $creator,
        string $name,
        string $slug,
        WorkflowStatus $status,
        string $description,
        array $definition,
        string $changeSummary,
    ): Workflow {
        $workflow = Workflow::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'slug' => $slug,
            ],
            [
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'created_by' => $creator->id,
            ],
        );

        $version = WorkflowVersion::query()->updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'version_number' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'definition' => $definition,
                'definition_hash' => hash('sha256', json_encode($definition)),
                'change_summary' => $changeSummary,
                'created_by' => $creator->id,
            ],
        );

        $workflow->update(['current_version_id' => $version->id]);

        return $workflow->fresh(['currentVersion']);
    }

    private function seedTriggers(
        Tenant $tenant,
        Workflow $onboardWorkflow,
        Workflow $reportWorkflow,
        Workflow $webhookWorkflow,
        User $creator,
    ): void {
        WorkflowTrigger::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'workflow_id' => $onboardWorkflow->id,
                'type' => TriggerType::Manual,
                'name' => 'Manual onboarding run',
            ],
            [
                'is_active' => true,
                'config' => ['description' => 'Run from dashboard or API'],
                'created_by' => $creator->id,
                'last_triggered_at' => now()->subHours(2),
            ],
        );

        WorkflowTrigger::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'workflow_id' => $reportWorkflow->id,
                'type' => TriggerType::Cron,
                'name' => 'Every weekday at 8am',
            ],
            [
                'is_active' => true,
                'config' => ['expression' => '0 8 * * 1-5', 'timezone' => 'UTC'],
                'created_by' => $creator->id,
                'last_triggered_at' => now()->subDay(),
            ],
        );

        WorkflowTrigger::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'workflow_id' => $webhookWorkflow->id,
                'type' => TriggerType::Webhook,
                'name' => 'Storefront order webhook',
            ],
            [
                'is_active' => true,
                'config' => ['method' => 'POST', 'headers' => ['X-Shop-Signature' => 'required']],
                'webhook_token' => 'whk_'.Str::lower(Str::random(32)),
                'created_by' => $creator->id,
                'last_triggered_at' => now()->subMinutes(30),
            ],
        );
    }

    private function seedWorkflowRuns(
        Tenant $tenant,
        Workflow $onboardWorkflow,
        Workflow $reportWorkflow,
        Workflow $webhookWorkflow,
        User $admin,
        User $editor,
    ): void {
        $onboardVersion = $onboardWorkflow->currentVersion;
        $reportVersion = $reportWorkflow->currentVersion;
        $webhookVersion = $webhookWorkflow->currentVersion;

        if ($onboardVersion === null || $reportVersion === null || $webhookVersion === null) {
            return;
        }

        $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $onboardWorkflow,
            version: $onboardVersion,
            status: WorkflowRunStatus::Running,
            triggerType: WorkflowTriggerType::Manual,
            triggeredBy: $admin,
            createdAt: now()->subMinutes(15),
            startedAt: now()->subMinutes(14),
            steps: [
                ['node_id' => 'fetch-user', 'label' => 'Fetch User Profile', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 420],
                ['node_id' => 'send-welcome', 'label' => 'Send Welcome Email', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Running, 'order' => 1, 'duration_ms' => null],
                ['node_id' => 'wait-provision', 'label' => 'Wait for Provisioning', 'type' => WorkflowNodeType::Delay, 'status' => WorkflowRunStepStatus::Pending, 'order' => 2, 'duration_ms' => null],
            ],
        );

        $failedRun = $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $onboardWorkflow,
            version: $onboardVersion,
            status: WorkflowRunStatus::Failed,
            triggerType: WorkflowTriggerType::Manual,
            triggeredBy: $editor,
            createdAt: now()->subDays(2),
            startedAt: now()->subDays(2)->addMinutes(1),
            completedAt: now()->subDays(2)->addMinutes(3),
            error: ['message' => 'SMTP connection refused', 'code' => 'mail_transport_error'],
            steps: [
                ['node_id' => 'fetch-user', 'label' => 'Fetch User Profile', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 380],
                ['node_id' => 'send-welcome', 'label' => 'Send Welcome Email', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Failed, 'order' => 1, 'duration_ms' => 1200, 'error' => ['message' => 'SMTP connection refused']],
                ['node_id' => 'wait-provision', 'label' => 'Wait for Provisioning', 'type' => WorkflowNodeType::Delay, 'status' => WorkflowRunStepStatus::Cancelled, 'order' => 2, 'duration_ms' => null],
            ],
        );

        $failedStep = $failedRun->steps()->where('node_id', 'send-welcome')->first();

        if ($failedStep !== null) {
            $this->seedRetryHistories($tenant, $failedStep);
        }

        $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $onboardWorkflow,
            version: $onboardVersion,
            status: WorkflowRunStatus::Success,
            triggerType: WorkflowTriggerType::Manual,
            triggeredBy: $admin,
            createdAt: now()->subDays(5),
            startedAt: now()->subDays(5)->addMinutes(1),
            completedAt: now()->subDays(5)->addMinutes(4),
            output: ['user_id' => 'usr_1024', 'email_sent' => true],
            steps: [
                ['node_id' => 'fetch-user', 'label' => 'Fetch User Profile', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 350],
                ['node_id' => 'send-welcome', 'label' => 'Send Welcome Email', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 1, 'duration_ms' => 890],
                ['node_id' => 'wait-provision', 'label' => 'Wait for Provisioning', 'type' => WorkflowNodeType::Delay, 'status' => WorkflowRunStepStatus::Success, 'order' => 2, 'duration_ms' => 5000],
            ],
        );

        $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $reportWorkflow,
            version: $reportVersion,
            status: WorkflowRunStatus::Success,
            triggerType: WorkflowTriggerType::Schedule,
            triggeredBy: null,
            createdAt: now()->subDay(),
            startedAt: now()->subDay()->addMinutes(1),
            completedAt: now()->subDay()->addMinutes(6),
            output: ['records_processed' => 1842, 'report_url' => 'https://reports.flowforge.test/daily/2026-06-10'],
            steps: [
                ['node_id' => 'aggregate-sales', 'label' => 'Aggregate Sales', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 2100],
                ['node_id' => 'post-slack', 'label' => 'Post to Slack', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 1, 'duration_ms' => 640],
            ],
        );

        $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $webhookWorkflow,
            version: $webhookVersion,
            status: WorkflowRunStatus::Success,
            triggerType: WorkflowTriggerType::Webhook,
            triggeredBy: null,
            createdAt: now()->subDays(3),
            startedAt: now()->subDays(3)->addSeconds(30),
            completedAt: now()->subDays(3)->addMinutes(2),
            triggerPayload: ['order_id' => 'ord_9981', 'source' => 'storefront'],
            output: ['fulfillment_id' => 'ful_2201'],
            steps: [
                ['node_id' => 'validate-order', 'label' => 'Validate Order', 'type' => WorkflowNodeType::Condition, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 45],
                ['node_id' => 'create-fulfillment', 'label' => 'Create Fulfillment', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Success, 'order' => 1, 'duration_ms' => 780],
            ],
        );

        $this->seedRunWithSteps(
            tenant: $tenant,
            workflow: $webhookWorkflow,
            version: $webhookVersion,
            status: WorkflowRunStatus::TimedOut,
            triggerType: WorkflowTriggerType::Webhook,
            triggeredBy: null,
            createdAt: now()->subDays(7),
            startedAt: now()->subDays(7)->addMinutes(1),
            completedAt: now()->subDays(7)->addMinutes(31),
            error: ['message' => 'Workflow exceeded maximum execution time'],
            steps: [
                ['node_id' => 'validate-order', 'label' => 'Validate Order', 'type' => WorkflowNodeType::Condition, 'status' => WorkflowRunStepStatus::Success, 'order' => 0, 'duration_ms' => 50],
                ['node_id' => 'create-fulfillment', 'label' => 'Create Fulfillment', 'type' => WorkflowNodeType::Http, 'status' => WorkflowRunStepStatus::Failed, 'order' => 1, 'duration_ms' => 1800000, 'error' => ['message' => 'Upstream API timeout']],
            ],
        );
    }

    /**
     * @param  list<array{node_id: string, label: string, type: WorkflowNodeType, status: WorkflowRunStepStatus, order: int, duration_ms: int|null, error?: array<string, mixed>}>  $steps
     * @param  array<string, mixed>|null  $error
     * @param  array<string, mixed>|null  $output
     * @param  array<string, mixed>|null  $triggerPayload
     */
    private function seedRunWithSteps(
        Tenant $tenant,
        Workflow $workflow,
        WorkflowVersion $version,
        WorkflowRunStatus $status,
        WorkflowTriggerType $triggerType,
        ?User $triggeredBy,
        CarbonInterface $createdAt,
        ?CarbonInterface $startedAt = null,
        ?CarbonInterface $completedAt = null,
        ?array $error = null,
        ?array $output = null,
        ?array $triggerPayload = null,
        array $steps = [],
    ): WorkflowRun {
        $run = WorkflowRun::query()->create([
            'tenant_id' => $tenant->id,
            'workflow_id' => $workflow->id,
            'workflow_version_id' => $version->id,
            'status' => $status,
            'trigger_type' => $triggerType,
            'trigger_payload' => $triggerPayload,
            'input' => ['sample' => true],
            'output' => $output,
            'error' => $error,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'triggered_by' => $triggeredBy?->id,
            'created_at' => $createdAt,
            'updated_at' => $completedAt ?? $startedAt ?? $createdAt,
        ]);

        foreach ($steps as $step) {
            WorkflowRunStep::query()->create([
                'tenant_id' => $tenant->id,
                'workflow_run_id' => $run->id,
                'node_id' => $step['node_id'],
                'node_type' => $step['type'],
                'node_label' => $step['label'],
                'status' => $step['status'],
                'attempt' => $step['status'] === WorkflowRunStepStatus::Failed ? 2 : 1,
                'execution_order' => $step['order'],
                'input' => ['sample' => true],
                'output' => $step['status'] === WorkflowRunStepStatus::Success ? ['ok' => true] : null,
                'error' => $step['error'] ?? null,
                'started_at' => $startedAt,
                'completed_at' => in_array($step['status'], [WorkflowRunStepStatus::Success, WorkflowRunStepStatus::Failed, WorkflowRunStepStatus::Cancelled], true)
                    ? ($completedAt ?? now())
                    : null,
                'duration_ms' => $step['duration_ms'],
            ]);
        }

        return $run->load('steps');
    }

    private function seedRetryHistories(Tenant $tenant, WorkflowRunStep $step): void
    {
        RetryHistory::query()->create([
            'tenant_id' => $tenant->id,
            'retryable_type' => WorkflowRunStep::class,
            'retryable_id' => $step->id,
            'attempt' => 1,
            'max_attempts' => 3,
            'strategy' => 'exponential_backoff',
            'delay_seconds' => 2,
            'status' => RetryHistoryStatus::Completed,
            'error' => ['message' => 'SMTP connection refused'],
            'metadata' => ['node_id' => $step->node_id],
            'scheduled_at' => now()->subDays(2)->addMinutes(2),
            'attempted_at' => now()->subDays(2)->addMinutes(2)->addSeconds(2),
        ]);

        RetryHistory::query()->create([
            'tenant_id' => $tenant->id,
            'retryable_type' => WorkflowRunStep::class,
            'retryable_id' => $step->id,
            'attempt' => 2,
            'max_attempts' => 3,
            'strategy' => 'exponential_backoff',
            'delay_seconds' => 4,
            'status' => RetryHistoryStatus::Failed,
            'error' => ['message' => 'SMTP connection refused'],
            'metadata' => ['node_id' => $step->node_id],
            'scheduled_at' => now()->subDays(2)->addMinutes(3),
            'attempted_at' => now()->subDays(2)->addMinutes(3)->addSeconds(4),
        ]);

        RetryHistory::query()->create([
            'tenant_id' => $tenant->id,
            'retryable_type' => WorkflowRunStep::class,
            'retryable_id' => $step->id,
            'attempt' => 3,
            'max_attempts' => 3,
            'strategy' => 'exponential_backoff',
            'delay_seconds' => 0,
            'status' => RetryHistoryStatus::Exhausted,
            'error' => ['message' => 'Max retry attempts reached'],
            'metadata' => ['node_id' => $step->node_id],
            'scheduled_at' => now()->subDays(2)->addMinutes(4),
            'attempted_at' => now()->subDays(2)->addMinutes(4),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function onboardWorkflowDefinition(): array
    {
        return [
            'entry_node_id' => 'fetch-user',
            'nodes' => [
                ['id' => 'fetch-user', 'type' => 'http', 'label' => 'Fetch User Profile', 'config' => ['method' => 'GET', 'url' => 'https://api.example.com/users/{{input.user_id}}']],
                ['id' => 'send-welcome', 'type' => 'http', 'label' => 'Send Welcome Email', 'config' => ['method' => 'POST', 'url' => 'https://api.example.com/emails/welcome']],
                ['id' => 'wait-provision', 'type' => 'delay', 'label' => 'Wait for Provisioning', 'config' => ['seconds' => 5]],
            ],
            'edges' => [
                ['from' => 'fetch-user', 'to' => 'send-welcome'],
                ['from' => 'send-welcome', 'to' => 'wait-provision'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportWorkflowDefinition(): array
    {
        return [
            'entry_node_id' => 'aggregate-sales',
            'nodes' => [
                ['id' => 'aggregate-sales', 'type' => 'http', 'label' => 'Aggregate Sales', 'config' => ['method' => 'GET', 'url' => 'https://api.example.com/reports/sales']],
                ['id' => 'post-slack', 'type' => 'http', 'label' => 'Post to Slack', 'config' => ['method' => 'POST', 'url' => 'https://hooks.slack.com/services/demo']],
            ],
            'edges' => [
                ['from' => 'aggregate-sales', 'to' => 'post-slack'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function webhookWorkflowDefinition(): array
    {
        return [
            'entry_node_id' => 'validate-order',
            'nodes' => [
                ['id' => 'validate-order', 'type' => 'condition', 'label' => 'Validate Order', 'config' => ['expression' => 'input.total > 0']],
                ['id' => 'create-fulfillment', 'type' => 'http', 'label' => 'Create Fulfillment', 'config' => ['method' => 'POST', 'url' => 'https://api.example.com/fulfillments']],
            ],
            'edges' => [
                ['from' => 'validate-order', 'to' => 'create-fulfillment'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function simpleWorkflowDefinition(string $nodeId, string $label): array
    {
        return [
            'entry_node_id' => $nodeId,
            'nodes' => [
                ['id' => $nodeId, 'type' => 'script', 'label' => $label, 'config' => ['language' => 'javascript']],
            ],
            'edges' => [],
        ];
    }
}
