<?php

namespace App\Providers;

use App\Support\TopbarData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
    public function boot(TopbarData $topbarData): void
    {
        View::composer('layouts.partials.topbar', function ($view) use ($topbarData): void {
            $payload = $topbarData->forUser(Auth::user());

            $view->with('topbarUser', $payload['user']);
            $view->with('topbarNotifications', $payload['notifications']);
        });
    }
}
