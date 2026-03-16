<?php

namespace App\Services\Portals;

use App\Models\Training;
use App\Models\User;

class PortalBaseCapabilityService
{
    /**
     * @return array<int, string>
     */
    public function capabilityKeys(): array
    {
        return [
            'viewBaseOverview',
            'manageBaseMembers',
            'viewBaseParticipants',
            'viewServedTrainings',
            'manageTrainingRegistrations',
            'manageEventSchedule',
            'manageMentors',
            'manageFacilitators',
            'submitChurchEventReport',
            'submitTeacherEventReport',
            'viewBaseInventory',
            'viewEventMaterials',
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function baseSummary(User $user): array
    {
        return [
            'viewBaseOverview' => $this->canViewBaseOverview($user),
            'manageBaseMembers' => $this->canManageBaseMembers($user),
            'viewBaseParticipants' => $this->canViewBaseParticipants($user),
            'viewServedTrainings' => false,
            'manageTrainingRegistrations' => false,
            'manageEventSchedule' => false,
            'manageMentors' => false,
            'manageFacilitators' => false,
            'submitChurchEventReport' => false,
            'submitTeacherEventReport' => false,
            'viewBaseInventory' => $this->canViewBaseInventory($user),
            'viewEventMaterials' => false,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function eventSummary(User $user, Training $training): array
    {
        $baseSummary = $this->baseSummary($user);
        $isHostedByBase = $this->isHostedByUsersBase($user, $training);
        $isAssignedTeacher = $this->isAssignedTeacher($user, $training);
        $isAssignedMentor = $this->isAssignedMentor($user, $training);
        $isHostedViewer = $this->isHostedBaseViewer($user, $training);

        return [
            ...$baseSummary,
            'viewBaseOverview' => $isAssignedTeacher || $isAssignedMentor || $isHostedViewer,
            'viewServedTrainings' => $isAssignedTeacher || $isAssignedMentor,
            'manageTrainingRegistrations' => $this->isDirector($user) || $isAssignedTeacher,
            'manageEventSchedule' => $this->isDirector($user) || $isAssignedTeacher,
            'manageMentors' => $this->isDirector($user) || $isAssignedTeacher,
            'manageFacilitators' => $this->isDirector($user) || $isAssignedTeacher || $this->isHostedBaseManager($user, $training),
            'submitChurchEventReport' => $isHostedByBase && $this->isHostedChurchReporter($user),
            'submitTeacherEventReport' => $this->isDirector($user) || $isAssignedTeacher,
            'viewBaseInventory' => $isHostedByBase && $this->canViewBaseInventory($user),
            'viewEventMaterials' => $isAssignedTeacher || $isAssignedMentor || $isHostedViewer,
        ];
    }

    public function allows(User $user, string $capability, ?Training $training = null): bool
    {
        $summary = $training ? $this->eventSummary($user, $training) : $this->baseSummary($user);

        return (bool) ($summary[$capability] ?? false);
    }

    /**
     * @return array<string, bool>
     */
    public function legacyTrainingSummary(User $user, Training $training): array
    {
        $capabilities = $this->eventSummary($user, $training);
        $canEdit = $capabilities['manageTrainingRegistrations']
            || $capabilities['manageEventSchedule']
            || $capabilities['manageMentors']
            || $capabilities['manageFacilitators']
            || $capabilities['submitTeacherEventReport'];

        return [
            'can_view' => $capabilities['viewBaseOverview'],
            'can_edit' => $canEdit,
            'can_delete' => $canEdit,
            'can_manage_schedule' => $capabilities['manageEventSchedule'],
            'can_view_stp_ojt' => $capabilities['viewServedTrainings'],
            'can_view_sensitive_data' => $capabilities['manageTrainingRegistrations'],
            'can_view_finance' => $capabilities['submitTeacherEventReport'],
            'can_manage_mentors' => $capabilities['manageMentors'],
            'can_see_discipleship' => $capabilities['viewServedTrainings'],
        ];
    }

    public function canViewBaseOverview(User $user): bool
    {
        return $this->isDirector($user) || $this->hasLinkedBaseChurch($user);
    }

    public function canManageBaseMembers(User $user): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        return $this->hasLinkedBaseChurch($user) && $user->hasRole('FieldWorker');
    }

    public function canViewBaseParticipants(User $user): bool
    {
        return $this->canManageBaseMembers($user);
    }

    public function canViewBaseInventory(User $user): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        if (! $this->hasLinkedBaseChurch($user)) {
            return false;
        }

        return $user->hasRole('FieldWorker') || $user->hasRole('Facilitator');
    }

    private function isDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    private function hasLinkedBaseChurch(User $user): bool
    {
        return (int) $user->church_id !== 0;
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

        return $training->assistantTeachers()->whereKey($user->id)->exists();
    }

    private function isAssignedMentor(User $user, Training $training): bool
    {
        if (! $user->hasRole('Mentor')) {
            return false;
        }

        if ($training->relationLoaded('mentors')) {
            return $training->mentors->contains('id', $user->id);
        }

        return $training->mentors()->whereKey($user->id)->exists();
    }

    private function isHostedByUsersBase(User $user, Training $training): bool
    {
        if (! $this->hasLinkedBaseChurch($user)) {
            return false;
        }

        return (int) $user->church_id === (int) $training->church_id;
    }

    private function isHostedBaseViewer(User $user, Training $training): bool
    {
        if (! $this->isHostedByUsersBase($user, $training)) {
            return false;
        }

        return $this->isDirector($user)
            || $user->hasRole('Teacher')
            || $user->hasRole('Mentor')
            || $user->hasRole('Facilitator')
            || $user->hasRole('FieldWorker');
    }

    private function isHostedBaseManager(User $user, Training $training): bool
    {
        return $this->isHostedByUsersBase($user, $training)
            && ($this->isDirector($user) || $user->hasRole('FieldWorker'));
    }

    private function isHostedChurchReporter(User $user): bool
    {
        return $this->isDirector($user)
            || $user->hasRole('FieldWorker')
            || $user->hasRole('Facilitator');
    }
}
