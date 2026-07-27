<?php

namespace App\Providers;

use App\Models\LeaveApplication;
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
        View::composer('layouts.partials.sidebar', function ($view): void {
            $count = auth()->check() && ! auth()->user()->teacher && auth()->user()->can('view.leave-application')
                ? LeaveApplication::query()->where('submitted_by_applicant', true)->whereNull('admin_viewed_at')->count()
                : 0;
            $view->with('newLeaveApplicationCount', $count);
        });
    }
}
