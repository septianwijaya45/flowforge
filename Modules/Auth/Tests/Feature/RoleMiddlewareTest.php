<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Middleware\EnsureRole;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['api', 'auth:api', EnsureRole::class.':admin,editor'])
        ->get('/api/v1/test-role-access', fn () => response()->json(['ok' => true]));
});

it('allows users with an allowed role', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Role Tenant',
        'slug' => 'role-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    User::factory()->editor()->create([
        'email' => 'editor@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'editor@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/v1/test-role-access', [
        'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
        'X-Tenant-Id' => $tenant->id,
    ]);

    $response->assertSuccessful();
});

it('denies users without an allowed role', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Viewer Tenant',
        'slug' => 'viewer-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    User::factory()->create([
        'email' => 'viewer@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'viewer@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/v1/test-role-access', [
        'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
        'X-Tenant-Id' => $tenant->id,
    ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});
