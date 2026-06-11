<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Tenant\Models\Tenant;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'tenant' => fn () => $this->resolveSharedTenant($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array{id: string, name: string, slug: string}|null
     */
    private function resolveSharedTenant(Request $request): ?array
    {
        if ($request->user() === null) {
            return null;
        }

        $tenantId = $request->session()->get('tenant_id');
        $tenant = $tenantId !== null
            ? Tenant::query()->find($tenantId)
            : Tenant::query()->where('is_active', true)->orderBy('created_at')->first();

        if ($tenant === null || ! $tenant->is_active) {
            return null;
        }

        if ($request->session()->get('tenant_id') !== $tenant->id) {
            $request->session()->put('tenant_id', $tenant->id);
        }

        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
        ];
    }
}
