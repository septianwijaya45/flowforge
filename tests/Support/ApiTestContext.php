<?php

declare(strict_types=1);

namespace Tests\Support;

use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;

final class ApiTestContext
{
    /**
     * @return array<string, string>
     */
    public static function headers(Tenant $tenant, ?User $user = null, UserRole $role = UserRole::Editor): array
    {
        if ($user === null) {
            $factory = User::factory();

            $user = match ($role) {
                UserRole::Admin => $factory->admin()->create([
                    'email' => 'api-'.uniqid().'@example.com',
                    'password' => 'password',
                ]),
                UserRole::Viewer => $factory->create([
                    'email' => 'api-'.uniqid().'@example.com',
                    'password' => 'password',
                ]),
                default => $factory->editor()->create([
                    'email' => 'api-'.uniqid().'@example.com',
                    'password' => 'password',
                ]),
            };
        }

        $loginResponse = test()->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return [
            'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
            'X-Tenant-Id' => $tenant->id,
        ];
    }
}
