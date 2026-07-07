<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        // Track last login (timestamp, IP, count) without touching updated_at.
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            if ($user instanceof User) {
                $user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                    'login_count'   => (int) ($user->login_count ?? 0) + 1,
                ])->saveQuietly();
            }
        });
    }
}
