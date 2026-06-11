<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;

final readonly class AuthenticatedUserDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public UserRole $role,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            uuid: $user->uuid,
            name: $user->name,
            email: $user->email,
            role: $user->role,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
        ];
    }
}
