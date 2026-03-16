<?php

namespace App\Policies;

use App\Enums\EventReportType;
use App\Models\EventReport;
use App\Models\Training;
use App\Models\User;
use App\Services\Portals\PortalBaseCapabilityService;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;

class EventReportPolicy
{
    public function __construct(
        private PortalBaseCapabilityService $portalBaseCapabilityService,
        private UserPortalResolver $userPortalResolver,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->userPortalResolver->canAccess($user, Portal::Staff);
    }

    public function view(User $user, EventReport $eventReport): bool
    {
        return $this->canReview($user) || $this->canContribute($user, $eventReport);
    }

    public function create(User $user): bool
    {
        return $this->userPortalResolver->canAccess($user, Portal::Base)
            || $this->userPortalResolver->canAccess($user, Portal::Staff);
    }

    public function createForTraining(User $user, Training $training, EventReportType $type): bool
    {
        return $this->canContributeForTraining($user, $training, $type) || $this->canReview($user);
    }

    public function update(User $user, EventReport $eventReport): bool
    {
        return $this->canContribute($user, $eventReport);
    }

    public function submit(User $user, EventReport $eventReport): bool
    {
        return $this->canContribute($user, $eventReport);
    }

    public function review(User $user, EventReport $eventReport): bool
    {
        return $this->canGovern($user);
    }

    public function delete(User $user, EventReport $eventReport): bool
    {
        return false;
    }

    public function restore(User $user, EventReport $eventReport): bool
    {
        return false;
    }

    public function forceDelete(User $user, EventReport $eventReport): bool
    {
        return false;
    }

    private function canContribute(User $user, EventReport $eventReport): bool
    {
        $training = $eventReport->relationLoaded('training')
            ? $eventReport->training
            : $eventReport->training()->first();

        if (! $training instanceof Training) {
            return false;
        }

        return $this->canContributeForTraining($user, $training, $eventReport->type);
    }

    private function canContributeForTraining(User $user, Training $training, EventReportType $type): bool
    {
        return match ($type) {
            EventReportType::Church => $this->portalBaseCapabilityService->allows($user, 'submitChurchEventReport', $training),
            EventReportType::Teacher => $this->portalBaseCapabilityService->allows($user, 'submitTeacherEventReport', $training),
        };
    }

    private function canReview(User $user): bool
    {
        return $this->userPortalResolver->canAccess($user, Portal::Staff);
    }

    private function canGovern(User $user): bool
    {
        return $user->hasRole('Board') || $user->hasRole('Director');
    }
}
