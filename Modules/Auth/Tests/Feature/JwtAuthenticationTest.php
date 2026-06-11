<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\RefreshToken;
use Modules\Auth\Models\User;

uses(RefreshDatabase::class);

it('logs in and returns jwt tokens', function (): void {
    $user = User::factory()->create([
        'email' => 'jwt-user@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jwt-user@example.com',
        'password' => 'password',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.uuid', $user->uuid)
        ->assertJsonPath('data.user.role', 'viewer')
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonStructure([
            'data' => ['access_token', 'refresh_token', 'expires_in', 'user'],
        ]);

    expect(RefreshToken::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('rejects invalid login credentials', function (): void {
    User::factory()->create([
        'email' => 'jwt-user@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jwt-user@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('refreshes tokens with a valid refresh token', function (): void {
    User::factory()->create([
        'email' => 'refresh-user@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'refresh-user@example.com',
        'password' => 'password',
    ]);

    $refreshToken = $loginResponse->json('data.refresh_token');

    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $refreshToken,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Token refreshed')
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);
});

it('logs out and revokes the refresh token', function (): void {
    $user = User::factory()->create([
        'email' => 'logout-user@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'logout-user@example.com',
        'password' => 'password',
    ]);

    $accessToken = $loginResponse->json('data.access_token');
    $refreshToken = $loginResponse->json('data.refresh_token');

    $response = $this->postJson('/api/v1/auth/logout', [
        'refresh_token' => $refreshToken,
    ], [
        'Authorization' => 'Bearer '.$accessToken,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Logout successful');

    expect(RefreshToken::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('rejects protected routes without a bearer token', function (): void {
    $response = $this->postJson('/api/v1/auth/logout', [
        'refresh_token' => 'invalid-token',
    ]);

    $response->assertUnauthorized();
});
