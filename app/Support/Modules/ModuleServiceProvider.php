<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for self-contained application modules.
 *
 * Each module owns its migrations, routes, and domain code so it can
 * later be extracted into an independent service with minimal friction.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function moduleName(): string;

    public function boot(): void
    {
        $this->loadModuleMigrations();
        $this->loadModuleRoutes();
    }

    protected function loadModuleMigrations(): void
    {
        $path = $this->modulePath('Database/Migrations');

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    protected function loadModuleRoutes(): void
    {
        $webRoutes = $this->modulePath('Routes/web.php');

        if (file_exists($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }

        $apiRoutes = $this->modulePath('Routes/api.php');

        if (file_exists($apiRoutes)) {
            Route::middleware('api')->prefix('api/v1')->group($apiRoutes);
        }
    }

    protected function modulePath(string $path = ''): string
    {
        $base = base_path('Modules/'.$this->moduleName());

        return $path === '' ? $base : $base.'/'.$path;
    }
}
