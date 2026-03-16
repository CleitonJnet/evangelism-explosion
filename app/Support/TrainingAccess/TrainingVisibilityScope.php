<?php

namespace App\Support\TrainingAccess;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TrainingVisibilityScope
{
    public function apply(Builder $query, User $user, string $context = 'auto'): Builder
    {
        return match ($context) {
            'teacher' => $this->applyTeacherContext($query, $user),
            'director' => $this->applyDirectorContext($query, $user),
            'mentor' => $this->applyMentorContext($query, $user),
            'serving' => $this->applyServingContext($query, $user),
            default => $this->applyAutomaticContext($query, $user),
        };
    }

    private function applyAutomaticContext(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Director')) {
            return $query;
        }

        return $this->applyServingContext($query, $user);
    }

    private function applyTeacherContext(Builder $query, User $user): Builder
    {
        if (! $user->hasRole('Teacher')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $teacherQuery) use ($user): void {
            $teacherQuery
                ->where('trainings.teacher_id', $user->id)
                ->orWhereHas('assistantTeachers', fn (Builder $assistantQuery) => $assistantQuery->whereKey($user->id));
        });
    }

    private function applyServingContext(Builder $query, User $user): Builder
    {
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

    private function applyDirectorContext(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Director')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyMentorContext(Builder $query, User $user): Builder
    {
        if (! $user->hasRole('Mentor')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('mentors', fn (Builder $mentorQuery) => $mentorQuery->whereKey($user->id));
    }
}
