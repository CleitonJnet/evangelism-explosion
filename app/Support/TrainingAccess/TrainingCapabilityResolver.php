<?php

namespace App\Support\TrainingAccess;

use App\Models\Training;
use App\Models\User;

class TrainingCapabilityResolver
{
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

    public function canView(User $user, Training $training): bool
    {
        if ($this->isDirector($user)) {
            return true;
        }

        if ($this->isAssignedTeacher($user, $training)) {
            return true;
        }

        return $this->isAssignedMentor($user, $training);
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

        if ($this->isAssignedTeacher($user, $training)) {
            return true;
        }

        return $this->isAssignedMentor($user, $training);
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

    private function isDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    private function isAssignedTeacher(User $user, Training $training): bool
    {
        if (! $user->hasRole('Teacher')) {
            return false;
        }

        if ((int) $training->teacher_id === (int) $user->id) {
            return true;
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
}
