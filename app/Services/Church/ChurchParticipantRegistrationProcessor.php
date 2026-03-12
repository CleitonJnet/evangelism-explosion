<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChurchParticipantRegistrationProcessor
{
    public const DEFAULT_PASSWORD = 'Master_01';

    /**
     * @param  array{
     *     email: string,
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     */
    public function process(Church $church, array $data): User
    {
        return DB::transaction(function () use ($church, $data): User {
            $participant = User::query()->where('email', $data['email'])->first();

            if ($participant) {
                $participant->forceFill($this->existingUserPayload($participant, $church, $data))->save();
            } else {
                $participant = User::query()->create($this->newUserPayload($church, $data));
            }

            return $participant;
        });
    }

    /**
     * @param  array{
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     * @return array<string, mixed>
     */
    private function existingUserPayload(User $participant, Church $church, array $data): array
    {
        return [
            'church_id' => $church->id,
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
     *     name?: string|null,
     *     mobile?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     ispastor?: string|null
     * }  $data
     * @return array<string, mixed>
     */
    private function newUserPayload(Church $church, array $data): array
    {
        return [
            'email' => $data['email'],
            'church_id' => $church->id,
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
}
