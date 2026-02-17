<?php

namespace App\Services\Training;

use App\Models\Mentor;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MentorAssignmentService
{
    public function addMentor(Training $training, User $user, User $actor): void
    {
        DB::transaction(function () use ($training, $user, $actor): void {
            Mentor::query()->firstOrCreate(
                [
                    'training_id' => $training->id,
                    'user_id' => $user->id,
                ],
                [
                    'created_by' => $actor->id,
                ],
            );

            $mentorRole = Role::query()
                ->whereRaw('LOWER(name) = ?', ['mentor'])
                ->first();

            if (! $mentorRole) {
                $mentorRole = Role::query()->create(['name' => 'Mentor']);
            }

            $alreadyMentor = $user->roles()
                ->whereKey($mentorRole->id)
                ->exists();

            if (! $alreadyMentor) {
                $user->roles()->syncWithoutDetaching([$mentorRole->id]);
            }
        });
    }

    public function removeMentor(Training $training, User $user, User $actor): void
    {
        DB::transaction(function () use ($training, $user): void {
            $training->mentors()->detach($user->id);
        });
    }
}
