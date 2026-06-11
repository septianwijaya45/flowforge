<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'execution_logs';

    public function up(): void
    {
        // Buat database pakai koneksi utama
        DB::connection('mysql')->statement('CREATE DATABASE IF NOT EXISTS flowforge_logs');

        // Putus & reconnect koneksi execution_logs agar PDO connect ulang ke DB yang sudah ada
        DB::purge($this->connection);
        DB::reconnect($this->connection);

        Schema::connection($this->connection)->create('execution_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('workflow_id')->nullable();
            $table->uuid('workflow_run_id')->nullable();
            $table->uuid('workflow_run_step_id')->nullable();
            $table->string('node_id')->nullable();
            $table->string('level', 16);
            $table->text('message');
            $table->jsonb('context')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('workflow_id');
            $table->index('workflow_run_id');
            $table->index('workflow_run_step_id');
            $table->index('logged_at');
            $table->index(['tenant_id', 'logged_at']);
            $table->index(['workflow_run_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('execution_logs');
        DB::connection('mysql')->statement('DROP DATABASE IF EXISTS flowforge_logs');
    }
};