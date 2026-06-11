<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retry_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->uuidMorphs('retryable');
            $table->unsignedSmallInteger('attempt');
            $table->unsignedSmallInteger('max_attempts');
            $table->string('strategy', 64);
            $table->unsignedInteger('delay_seconds');
            $table->string('status', 32);
            $table->jsonb('error')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            $table->index(['retryable_type', 'retryable_id', 'attempt']);
            $table->index('tenant_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retry_histories');
    }
};
