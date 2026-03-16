<?php

namespace App\Support\TrainingAccess;

use App\Models\Training;
use App\Models\User;
use App\Services\Portals\PortalBaseCapabilityService;

class TrainingCapabilityResolver
{
    public function __construct(private PortalBaseCapabilityService $portalBaseCapabilityService) {}

    public function canViewAsTeacherContext(User $user, Training $training): bool
    {
        return $this->isAssignedTeacher($user, $training);
    }

    public function canEditAsTeacherContext(User $user, Training $training): bool
    {
        return $this->isAssignedTeacher($user, $training);
    }

    public function canDeleteAsTeacherContext(User $user, Training $training): bool
    {
        return $this->canEditAsTeacherContext($user, $training);
    }

    public function canViewAsServingContext(User $user, Training $training): bool
    {
        return $this->isAssignedTeacher($user, $training) || $this->isAssignedMentor($user, $training);
    }

    public function canEditAsServingContext(User $user, Training $training): bool
    {
        return $this->isAssignedTeacher($user, $training);
    }

    public function canDeleteAsServingContext(User $user, Training $training): bool
    {
        return $this->canEditAsServingContext($user, $training);
    }

    public function canViewAsBaseContext(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'viewBaseOverview', $training);
    }

    public function canEditAsBaseContext(User $user, Training $training): bool
    {
        $summary = $this->portalBaseCapabilityService->eventSummary($user, $training);

        return $summary['manageTrainingRegistrations']
            || $summary['manageEventSchedule']
            || $summary['manageMentors']
            || $summary['manageFacilitators']
            || $summary['submitTeacherEventReport'];
    }

    public function canDeleteAsBaseContext(User $user, Training $training): bool
    {
        return $this->canEditAsBaseContext($user, $training);
    }

    public function canView(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->canViewAsServingContext($user, $training);
    }

    public function canEdit(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->isAssignedTeacher($user, $training);
    }

    public function canDelete(User $user, Training $training): bool
    {
        return $this->canEdit($user, $training);
    }

    public function canViewStpOjt(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->canViewAsServingContext($user, $training);
    }

    public function canViewSensitiveData(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->isAssignedTeacher($user, $training);
    }

    public function canViewFinance(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->isAssignedTeacher($user, $training);
    }

    public function canManageSchedule(User $user, Training $training): bool
    {
        return $this->canEdit($user, $training);
    }

    public function canManageMentors(User $user, Training $training): bool
    {
        return $this->canEdit($user, $training);
    }

    public function canSeeDiscipleship(User $user, Training $training): bool
    {
        return $this->canViewStpOjt($user, $training);
    }

    public function summary(User $user, Training $training): array
    {
        return [
            'can_view' => $this->canView($user, $training),
            'can_edit' => $this->canEdit($user, $training),
            'can_delete' => $this->canDelete($user, $training),
            'can_manage_schedule' => $this->canManageSchedule($user, $training),
            'can_view_stp_ojt' => $this->canViewStpOjt($user, $training),
            'can_view_sensitive_data' => $this->canViewSensitiveData($user, $training),
            'can_view_finance' => $this->canViewFinance($user, $training),
            'can_manage_mentors' => $this->canManageMentors($user, $training),
            'can_see_discipleship' => $this->canSeeDiscipleship($user, $training),
        ];
    }

    public function summaryForTeacherContext(User $user, Training $training): array
    {
        $canView = $this->canViewAsTeacherContext($user, $training);
        $canEdit = $this->canEditAsTeacherContext($user, $training);

        return [
            'can_view' => $canView,
            'can_edit' => $canEdit,
            'can_delete' => $this->canDeleteAsTeacherContext($user, $training),
            'can_manage_schedule' => $canEdit,
            'can_view_stp_ojt' => $canView,
            'can_view_sensitive_data' => $canEdit,
            'can_view_finance' => $canEdit,
            'can_manage_mentors' => $canEdit,
            'can_see_discipleship' => $canView,
        ];
    }

    public function summaryForServingContext(User $user, Training $training): array
    {
        $canView = $this->canViewAsServingContext($user, $training);
        $canEdit = $this->canEditAsServingContext($user, $training);

        return [
            'can_view' => $canView,
            'can_edit' => $canEdit,
            'can_delete' => $this->canDeleteAsServingContext($user, $training),
            'can_manage_schedule' => $canEdit,
            'can_view_stp_ojt' => $canView,
            'can_view_sensitive_data' => $canEdit,
            'can_view_finance' => $canEdit,
            'can_manage_mentors' => $canEdit,
            'can_see_discipleship' => $canView,
        ];
    }

    public function summaryForBaseContext(User $user, Training $training): array
    {
        return $this->portalBaseCapabilityService->legacyTrainingSummary($user, $training);
    }

    /**
     * @return array<int, string>
     */
    public function servingAssignments(User $user, Training $training): array
    {
        $assignments = [];

        if ($this->isLeadTeacher($user, $training)) {
            $assignments[] = 'Professor titular';
        }

        if ($this->isAssistantTeacher($user, $training)) {
            $assignments[] = 'Professor auxiliar';
        }

        if ($this->isAssignedMentor($user, $training)) {
            $assignments[] = 'Mentor';
        }

        return $assignments;
    }

    /**
     * @return array<int, string>
     */
    public function baseAssignments(User $user, Training $training): array
    {
        $assignments = $this->servingAssignments($user, $training);

        if ($this->isHostedBaseViewer($user, $training)) {
            $assignments[] = 'Igreja-base';
        }

        if ($this->isHostedBaseFacilitator($user, $training)) {
            $assignments[] = 'Facilitador';
        }

        if ($this->isHostedBaseFieldWorker($user, $training)) {
            $assignments[] = 'Field worker contextual';
        }

        if ($this->isHostedBaseManager($user, $training)) {
            $assignments[] = 'Gestor da base';
        }

        return array_values(array_unique($assignments));
    }

    private function isDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    private function isAssignedTeacher(User $user, Training $training): bool
    {
        return $this->isLeadTeacher($user, $training) || $this->isAssistantTeacher($user, $training);
    }

    private function isLeadTeacher(User $user, Training $training): bool
    {
        if (! $user->hasRole('Teacher')) {
            return false;
        }

        return (int) $training->teacher_id === (int) $user->id;
    }

    private function isAssistantTeacher(User $user, Training $training): bool
    {
        if (! $user->hasRole('Teacher')) {
            return false;
        }

        if ($training->relationLoaded('assistantTeachers')) {
            return $training->assistantTeachers->contains('id', $user->id);
        }

        return $training->assistantTeachers()
            ->whereKey($user->id)
            ->exists();
    }

    private function isAssignedMentor(User $user, Training $training): bool
    {
        if (! $user->hasRole('Mentor')) {
            return false;
        }

        if ($training->relationLoaded('mentors')) {
            return $training->mentors->contains('id', $user->id);
        }

        return $training->mentors()
            ->whereKey($user->id)
            ->exists();
    }

    private function isHostedBaseViewer(User $user, Training $training): bool
    {
        return $this->portalBaseCapabilityService->allows($user, 'viewBaseOverview', $training)
            && (int) $user->church_id !== 0
            && (int) $user->church_id === (int) $training->church_id;
    }

    private function isHostedBaseFacilitator(User $user, Training $training): bool
    {
        return $user->hasRole('Facilitator') && $this->isHostedBaseViewer($user, $training);
    }

    private function isHostedBaseFieldWorker(User $user, Training $training): bool
    {
        return $user->hasRole('FieldWorker') && $this->isHostedBaseViewer($user, $training);
    }

    private function isHostedBaseManager(User $user, Training $training): bool
    {
        return $user->hasRole('FieldWorker') && $this->isHostedBaseViewer($user, $training)
            || ($user->hasRole('Director') && (int) $user->church_id !== 0 && (int) $user->church_id === (int) $training->church_id);
    }
}
