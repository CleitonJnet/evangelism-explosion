<?php

namespace App\Policies;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;

class ChurchPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageChurches($user);
    }

    public function view(User $user, Church $church): bool
    {
        return $this->canAccessChurch($user, $church);
    }

    public function create(User $user): bool
    {
        return $this->canManageChurches($user);
    }

    public function update(User $user, Church $church): bool
    {
        return $this->canAccessChurch($user, $church);
    }

    public function delete(User $user, Church $church): bool
    {
        if (
            $user->hasRole('Teacher')
            && ! $user->hasRole('Director')
            && ! $user->hasRole('FieldWorker')
            && (int) $user->church_id === $church->id
        ) {
            return false;
        }

        return $this->canAccessChurch($user, $church);
    }

    private function canManageChurches(User $user): bool
    {
        return $user->hasRole('Teacher')
            || $user->hasRole('Director')
            || $user->hasRole('FieldWorker');
    }

    private function canAccessChurch(User $user, Church $church): bool
    {
        if (! $this->canManageChurches($user)) {
            return false;
        }

        if ($user->hasRole('Director') || $user->hasRole('FieldWorker')) {
            return true;
        }

        if (! $user->hasRole('Teacher')) {
            return true;
        }

        return in_array($church->id, $this->teacherAccessibleChurchIds($user)->all(), true);
    }

    /**
     * @return Collection<int, int>
     */
    private function teacherAccessibleChurchIds(User $user): Collection
    {
        $churchIds = collect([$user->church_id])->filter();

        $trainingChurchIds = Training::query()
            ->where('teacher_id', $user->id)
            ->whereNotNull('church_id')
            ->pluck('church_id');

        $studentChurchIds = User::query()
            ->select('users.church_id')
            ->join('training_user', 'training_user.user_id', '=', 'users.id')
            ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
            ->where('trainings.teacher_id', $user->id)
            ->whereNotNull('users.church_id')
            ->distinct()
            ->pluck('users.church_id');

        $missionaryChurchIds = Church::query()
            ->select('churches.id')
            ->join('church_missionary', 'church_missionary.church_id', '=', 'churches.id')
            ->where('church_missionary.user_id', $user->id)
            ->pluck('churches.id');

        return $churchIds
            ->merge($trainingChurchIds)
            ->merge($studentChurchIds)
            ->merge($missionaryChurchIds)
            ->map(static fn ($churchId): int => (int) $churchId)
            ->unique()
            ->values();
    }
}
