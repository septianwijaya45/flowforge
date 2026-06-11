<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;

uses(RefreshDatabase::class);

it('issues jwt tokens for an authenticated web session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/auth/session-token');

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.uuid', $user->uuid)
        ->assertJsonStructure([
            'data' => ['access_token', 'refresh_token', 'expires_in', 'user'],
        ]);
});

it('rejects session token requests from guests', function (): void {
    $response = $this->postJson('/api/v1/auth/session-token');

    $response->assertUnauthorized();
});
