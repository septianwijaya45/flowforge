<?php

declare(strict_types=1);

namespace Modules\Auth;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Modules\Auth\Actions\Fortify\CreateNewUser;
use Modules\Auth\Actions\Fortify\ResetUserPassword;
use Modules\Auth\Contracts\JwtAuthServiceContract;
use Modules\Auth\Contracts\JwtTokenManagerContract;
use Modules\Auth\Guards\JwtGuard;
use Modules\Auth\Http\Middleware\EnsureRole;
use Modules\Auth\Services\JwtAuthService;
use Modules\Auth\Services\JwtTokenManager;

class AuthServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Auth';
    }

    public function register(): void
    {
        $this->app->singleton(JwtTokenManagerContract::class, JwtTokenManager::class);

        $this->app->singleton(JwtAuthServiceContract::class, JwtAuthService::class);

        Auth::extend('jwt', function ($app, string $name, array $config): JwtGuard {
            return new JwtGuard(
                Auth::createUserProvider($config['provider']),
                $app->make('request'),
                $app->make(JwtAuthServiceContract::class),
            );
        });
    }

    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('role', EnsureRole::class);

        parent::boot();

        $this->configureFortify();
    }

    private function configureFortify(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password'));

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
