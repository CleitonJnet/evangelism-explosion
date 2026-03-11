<?php

namespace App\Services\Training;

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherParticipantRegistrationProcessor
{
    public const DEFAULT_PASSWORD = 'Master_01';

    /**
     * @param  array{
     *     email: string,
     *     selectedChurchId: int,
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     */
    public function process(Training $training, array $data): User
    {
        return DB::transaction(function () use ($training, $data): User {
            $participant = User::query()->where('email', $data['email'])->first();

            if ($participant) {
                $participant->forceFill($this->existingUserPayload($participant, $data))->save();
            } else {
                $participant = User::query()->create($this->newUserPayload($data));
            }

            $this->ensureStudentRoleAndEnrollment($training, $participant);

            return $participant;
        });
    }

    /**
     * @param  array{
     *     selectedChurchId: int,
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     * @return array<string, mixed>
     */
    private function existingUserPayload(User $participant, array $data): array
    {
        return [
            'church_id' => $data['selectedChurchId'],
            'church_temp_id' => null,
            'name' => filled($data['name'] ?? null) ? $data['name'] : $participant->name,
            'phone' => filled($data['mobile'] ?? null) ? $data['mobile'] : $participant->getRawOriginal('phone'),
            'birthdate' => $data['birth_date'] ?? $participant->getRawOriginal('birthdate'),
            'gender' => $data['gender'] ?? $participant->getRawOriginal('gender'),
            'is_pastor' => $data['ispastor'] ?? $participant->getRawOriginal('is_pastor'),
        ];
    }

    /**
     * @param  array{
     *     email: string,
     *     selectedChurchId: int,
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     * @return array<string, mixed>
     */
    private function newUserPayload(array $data): array
    {
        return [
            'email' => $data['email'],
            'church_id' => $data['selectedChurchId'],
            'church_temp_id' => null,
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'birthdate' => $data['birth_date'] ?? null,
            'gender' => $data['gender'],
            'is_pastor' => $data['ispastor'],
            'password' => self::DEFAULT_PASSWORD,
            'must_change_password' => true,
        ];
    }

    private function ensureStudentRoleAndEnrollment(Training $training, User $participant): void
    {
        $studentRole = Role::query()->firstOrCreate(['name' => 'Student']);
        $participant->roles()->syncWithoutDetaching([$studentRole->id]);

        $training->students()->syncWithoutDetaching([
            $participant->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0],
        ]);
    }
}
