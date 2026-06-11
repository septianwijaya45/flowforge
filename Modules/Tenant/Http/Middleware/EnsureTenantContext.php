<?php

declare(strict_types=1);

namespace Modules\Tenant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function __construct(
        private readonly TenantContextResolverContract $resolver,
        private readonly TenantContextContract $context,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        try {
            $tenant = $this->resolver->resolveFromRequest($request);
            $this->context->set($tenant);
        } catch (TenantResolutionException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->context->clear();
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->is(
            'api/v1/tenants',
            'api/v1/tenants/*',
            'api/v1/auth',
            'api/v1/auth/*',
        );
    }
}
