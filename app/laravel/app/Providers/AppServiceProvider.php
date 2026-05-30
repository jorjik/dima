<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');

            // Force HTTPS for storage URLs to prevent mixed content
            // when APP_URL has http:// but the site is served over https://
            $diskUrl = config('filesystems.disks.public.url');
            if ($diskUrl && str_starts_with($diskUrl, 'http://')) {
                config(['filesystems.disks.public.url' => 'https://' . substr($diskUrl, 7)]);
            }
        }

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            return route('filament.adminPanel.auth.password-reset.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
