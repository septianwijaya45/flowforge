<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;
use Modules\Auth\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return ApiResponse::error('Unauthenticated.', status: Response::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map(
            static fn (string $role): UserRole => UserRole::from($role),
            $roles,
        );

        if (! in_array($user->role, $allowedRoles, true)) {
            return ApiResponse::error(
                'You do not have permission to perform this action.',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        return $next($request);
    }
}
