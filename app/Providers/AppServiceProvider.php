<?php

namespace App\Providers;

use App\Models\StpApproach;
use App\Models\Training;
use App\Models\User;
use App\Policies\RoleAccessPolicy;
use App\Policies\StpApproachPolicy;
use App\Policies\TrainingPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureAccessGates();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureAccessGates(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(StpApproach::class, StpApproachPolicy::class);
        Gate::define('access-board', [RoleAccessPolicy::class, 'accessBoard']);
        Gate::define('access-director', [RoleAccessPolicy::class, 'accessDirector']);
        Gate::define('access-teacher', [RoleAccessPolicy::class, 'accessTeacher']);
        Gate::define('access-facilitator', [RoleAccessPolicy::class, 'accessFacilitator']);
        Gate::define('access-fieldworker', [RoleAccessPolicy::class, 'accessFieldworker']);
        Gate::define('access-mentor', [RoleAccessPolicy::class, 'accessMentor']);
        Gate::define('access-student', [RoleAccessPolicy::class, 'accessStudent']);
        Gate::define('manageChurches', [RoleAccessPolicy::class, 'manageChurches']);
    }
}
