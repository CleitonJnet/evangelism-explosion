<?php

namespace App\Providers;

use App\Models\Church;
use App\Models\EventReport;
use App\Models\Inventory;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\Policies\ChurchPolicy;
use App\Policies\EventReportPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\RoleAccessPolicy;
use App\Policies\StpApproachPolicy;
use App\Policies\StpSessionPolicy;
use App\Policies\StpTeamPolicy;
use App\Policies\TrainingPolicy;
use App\Policies\UserPolicy;
use App\Services\Portals\PortalBaseCapabilityService;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
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
        $this->app->singleton(UserPortalResolver::class);
        $this->app->singleton(PortalBaseCapabilityService::class);
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
        $portalResolver = $this->app->make(UserPortalResolver::class);
        $portalBaseCapabilityService = $this->app->make(PortalBaseCapabilityService::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(StpApproach::class, StpApproachPolicy::class);
        Gate::policy(StpSession::class, StpSessionPolicy::class);
        Gate::policy(StpTeam::class, StpTeamPolicy::class);
        Gate::policy(Church::class, ChurchPolicy::class);
        Gate::policy(EventReport::class, EventReportPolicy::class);
        Gate::policy(Inventory::class, InventoryPolicy::class);
        Gate::define('access-board', [RoleAccessPolicy::class, 'accessBoard']);
        Gate::define('access-director', [RoleAccessPolicy::class, 'accessDirector']);
        Gate::define('access-teacher', [RoleAccessPolicy::class, 'accessTeacher']);
        Gate::define('access-facilitator', [RoleAccessPolicy::class, 'accessFacilitator']);
        Gate::define('access-fieldworker', [RoleAccessPolicy::class, 'accessFieldworker']);
        Gate::define('access-mentor', [RoleAccessPolicy::class, 'accessMentor']);
        Gate::define('access-student', [RoleAccessPolicy::class, 'accessStudent']);
        Gate::define('access-portal-base', fn (User $user): bool => $portalResolver->canAccess($user, Portal::Base));
        Gate::define('access-portal-staff', fn (User $user): bool => $portalResolver->canAccess($user, Portal::Staff));
        Gate::define('govern-portal-staff', fn (User $user): bool => $user->hasRole('Board') || $user->hasRole('Director'));
        Gate::define('access-portal-student', fn (User $user): bool => $portalResolver->canAccess($user, Portal::Student));
        Gate::define('manageChurches', [RoleAccessPolicy::class, 'manageChurches']);
        Gate::define('viewBaseOverview', fn (User $user): bool => $portalBaseCapabilityService->allows($user, 'viewBaseOverview'));
        Gate::define('manageBaseMembers', fn (User $user): bool => $portalBaseCapabilityService->allows($user, 'manageBaseMembers'));
        Gate::define('viewBaseParticipants', fn (User $user): bool => $portalBaseCapabilityService->allows($user, 'viewBaseParticipants'));
        Gate::define('viewBaseInventory', fn (User $user): bool => $portalBaseCapabilityService->allows($user, 'viewBaseInventory'));
        Gate::define('viewServedTrainings', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'viewServedTrainings', $training));
        Gate::define('manageTrainingRegistrations', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'manageTrainingRegistrations', $training));
        Gate::define('manageEventSchedule', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'manageEventSchedule', $training));
        Gate::define('manageMentors', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'manageMentors', $training));
        Gate::define('manageFacilitators', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'manageFacilitators', $training));
        Gate::define('submitChurchEventReport', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'submitChurchEventReport', $training));
        Gate::define('submitTeacherEventReport', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'submitTeacherEventReport', $training));
        Gate::define('viewEventMaterials', fn (User $user, Training $training): bool => $portalBaseCapabilityService->allows($user, 'viewEventMaterials', $training));
    }
}
