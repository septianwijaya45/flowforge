<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->index(['tenant_id', 'created_at'], 'workflow_runs_tenant_created_idx');
            $table->index(['tenant_id', 'status', 'created_at'], 'workflow_runs_tenant_status_created_idx');

            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('workflow_run_steps', function (Blueprint $table): void {
            $table->index(['workflow_run_id', 'execution_order'], 'workflow_run_steps_run_order_idx');

            $table->dropIndex(['workflow_run_id']);
        });
    }

    public function down(): void
    {
        Schema::table('workflow_run_steps', function (Blueprint $table): void {
            $table->index('workflow_run_id');

            $table->dropIndex('workflow_run_steps_run_order_idx');
        });

        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->index('tenant_id');
            $table->index('created_at');
            $table->index(['tenant_id', 'status']);

            $table->dropIndex('workflow_runs_tenant_created_idx');
            $table->dropIndex('workflow_runs_tenant_status_created_idx');
        });
    }
};
