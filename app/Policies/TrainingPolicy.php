<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use App\Services\Portals\PortalBaseCapabilityService;
use App\Support\TrainingAccess\TrainingCapabilityResolver;

class TrainingPolicy
{
    public function __construct(
        private TrainingCapabilityResolver $capabilityResolver,
        private PortalBaseCapabilityService $portalBaseCapabilityService,
    ) {}

    public function view(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canView($user, $training);
    }

    public function update(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canEdit($user, $training);
    }

    public function delete(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canDelete($user, $training);
    }

    public function viewTeacherContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canViewAsTeacherContext($user, $training);
    }

    public function updateTeacherContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canEditAsTeacherContext($user, $training);
    }

    public function deleteTeacherContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canDeleteAsTeacherContext($user, $training);
    }

    public function viewServingContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canViewAsServingContext($user, $training);
    }

    public function updateServingContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canEditAsServingContext($user, $training);
    }

    public function deleteServingContext(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canDeleteAsServingContext($user, $training);
    }

    public function viewBaseContext(User $user, Training $training): bool
    {
        return $this->viewBaseOverview($user, $training);
    }

    public function updateBaseContext(User $user, Training $training): bool
    {
        return $this->manageTrainingRegistrationsBaseContext($user, $training)
            || $this->manageEventScheduleBaseContext($user, $training)
            || $this->manageMentorsBaseContext($user, $training)
            || $this->manageFacilitatorsBaseContext($user, $training)
            || $this->submitTeacherEventReportBaseContext($user, $training);
    }

    public function deleteBaseContext(User $user, Training $training): bool
    {
        return $this->updateBaseContext($user, $training);
    }

    public function viewBaseOverview(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'viewBaseOverview', $training);
    }

    public function manageTrainingRegistrationsBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'manageTrainingRegistrations', $training);
    }

    public function manageEventScheduleBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'manageEventSchedule', $training);
    }

    public function manageMentorsBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'manageMentors', $training);
    }

    public function manageFacilitatorsBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'manageFacilitators', $training);
    }

    public function submitChurchEventReportBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'submitChurchEventReport', $training);
    }

    public function submitTeacherEventReportBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'submitTeacherEventReport', $training);
    }

    public function viewEventMaterialsBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'viewEventMaterials', $training);
    }
}
