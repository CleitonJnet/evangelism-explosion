<?php

namespace App\Services;

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChurchTempResolverService
{
    public function __construct(
        protected TrainingNewChurchService $trainingNewChurchService,
    ) {}

    /**
     * @param  array<string, mixed>  $officialData
     */
    public function approveAsNewOfficial(Training $training, ChurchTemp $temp, array $officialData, User $actor): Church
    {
        return DB::transaction(function () use ($training, $temp, $officialData, $actor): Church {
            $church = Church::query()->create(
                array_merge(
                    $this->baseChurchDataFromTemp($temp),
                    $this->sanitizeChurchData($officialData),
                ),
            );

            $this->moveUsersToOfficialChurch($temp, $church);
            $this->markTempAsResolved($temp, $church, $actor, 'approved_new');
            $this->markTrainingNewChurchWhenPersisted($training, $church, $temp, $actor);

            return $church;
        });
    }

    public function mergeIntoOfficial(Training $training, ChurchTemp $temp, Church $official, User $actor): void
    {
        DB::transaction(function () use ($temp, $official, $actor): void {
            $this->moveUsersToOfficialChurch($temp, $official);
            $this->markTempAsResolved($temp, $official, $actor, 'merged');
        });
    }

    private function moveUsersToOfficialChurch(ChurchTemp $temp, Church $official): void
    {
        User::query()
            ->where('church_temp_id', $temp->id)
            ->update([
                'church_id' => $official->id,
                'church_temp_id' => null,
                'updated_at' => now(),
            ]);
    }

    private function markTempAsResolved(ChurchTemp $temp, Church $official, User $actor, string $status): void
    {
        $temp->forceFill([
            'status' => $status,
            'normalized_name' => $this->normalizeName((string) $temp->name),
            'resolved_church_id' => $official->id,
            'resolved_by' => $actor->id,
            'resolved_at' => now(),
        ])->save();
    }

    private function markTrainingNewChurchWhenPersisted(
        Training $training,
        Church $church,
        ChurchTemp $temp,
        User $actor,
    ): void {
        if (! $training->exists) {
            return;
        }

        $this->trainingNewChurchService->markNewChurch($training, $church, $temp, $actor);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseChurchDataFromTemp(ChurchTemp $temp): array
    {
        return [
            'name' => $temp->name,
            'pastor' => $temp->pastor,
            'email' => $temp->email,
            'phone' => $temp->phone,
            'street' => $temp->street,
            'number' => $temp->number,
            'complement' => $temp->complement,
            'district' => $temp->district,
            'city' => $temp->city,
            'state' => $temp->state,
            'postal_code' => $temp->postal_code,
            'contact' => $temp->contact,
            'contact_phone' => $temp->contact_phone,
            'contact_email' => $temp->contact_email,
            'notes' => $temp->notes,
            'logo' => $temp->logo,
        ];
    }

    /**
     * @param  array<string, mixed>  $officialData
     * @return array<string, mixed>
     */
    private function sanitizeChurchData(array $officialData): array
    {
        return array_intersect_key($officialData, array_flip((new Church)->getFillable()));
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)
            ->squish()
            ->lower()
            ->ascii()
            ->value();
    }
}
