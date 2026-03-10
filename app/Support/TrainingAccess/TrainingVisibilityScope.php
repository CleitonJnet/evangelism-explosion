<?php

namespace App\Support\TrainingAccess;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TrainingVisibilityScope
{
    public function apply(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Director')) {
            return $query;
        }

        return $query->where(function (Builder $visibleQuery) use ($user): void {
            $hasConstraint = false;

            if ($user->hasRole('Teacher')) {
                $hasConstraint = true;

                $visibleQuery->where(function (Builder $teacherQuery) use ($user): void {
                    $teacherQuery
                        ->where('trainings.teacher_id', $user->id)
                        ->orWhereHas('assistantTeachers', fn (Builder $assistantQuery) => $assistantQuery->whereKey($user->id));
                });
            }

            if ($user->hasRole('Mentor')) {
                if ($hasConstraint) {
                    $visibleQuery->orWhereHas('mentors', fn (Builder $mentorQuery) => $mentorQuery->whereKey($user->id));

                    return;
                }

                $hasConstraint = true;

                $visibleQuery->whereHas('mentors', fn (Builder $mentorQuery) => $mentorQuery->whereKey($user->id));
            }

            if (! $hasConstraint) {
                $visibleQuery->whereRaw('1 = 0');
            }
        });
    }
}
