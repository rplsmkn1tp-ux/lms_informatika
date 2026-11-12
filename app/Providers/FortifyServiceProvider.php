<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
        
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->input(Fortify::username()))
                ->orWhere('nisn', $request->input(Fortify::username()))
                ->orWhere('nip_nuptk', $request->input(Fortify::username()))
                ->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                if ($user->status !== 'active') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        Fortify::username() => __('Akun Anda belum aktif atau telah ditolak.'),
                    ]);
                }
                return $user;
            }
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages.auth.login'));
        Fortify::registerView(fn () => view('pages.auth.register'));
        Fortify::resetPasswordView(fn () => view('pages.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
