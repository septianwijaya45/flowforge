<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->foreignUuid('workflow_version_id')->constrained('workflow_versions')->restrictOnDelete();
            $table->string('status', 32)->default('pending');
            $table->string('trigger_type', 32)->default('manual');
            $table->jsonb('trigger_payload')->nullable();
            $table->jsonb('input')->nullable();
            $table->jsonb('output')->nullable();
            $table->jsonb('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('workflow_id');
            $table->index(['workflow_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index('workflow_version_id');
            $table->index('created_at');
            $table->index('started_at');
        });

        Schema::create('workflow_run_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->string('node_id');
            $table->string('node_type', 32);
            $table->string('node_label')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->unsignedSmallInteger('execution_order')->nullable();
            $table->jsonb('input')->nullable();
            $table->jsonb('output')->nullable();
            $table->jsonb('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->unique(['workflow_run_id', 'node_id']);
            $table->index('tenant_id');
            $table->index('workflow_run_id');
            $table->index(['workflow_run_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_run_steps');
        Schema::dropIfExists('workflow_runs');
    }
};
