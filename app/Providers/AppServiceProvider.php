<?php

namespace App\Providers;

use App\Http\View\Composers\PlatformBrandingComposer;
use App\Http\View\Composers\SchoolBrandingComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        View::composer([
            'admin.layouts.app',
            'admin.components.sidebar',
            'layouts.student',
            'teacher.components.sidebar',
            'student.*',
        ], SchoolBrandingComposer::class);

        View::share('platformName', config('platform.name', 'EduManager'));

        View::composer('platform.*', PlatformBrandingComposer::class);
    }
}
