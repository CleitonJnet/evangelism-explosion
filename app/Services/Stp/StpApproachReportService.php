<?php

namespace App\Services\Stp;

use App\Enums\StpApproachStatus;
use App\Models\StpApproach;
use App\Models\User;

class StpApproachReportService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(StpApproach $approach, array $data, User $actor): StpApproach
    {
        $attributes = $this->extractAttributes($data);
        $payload = $this->extractPayload($data['payload'] ?? null);

        $nextStatus = $approach->status;

        if ($approach->status === StpApproachStatus::Planned && $approach->stp_team_id !== null) {
            $nextStatus = StpApproachStatus::Assigned;
        }

        $approach->fill($attributes);
        $approach->payload = $payload;
        $approach->status = $nextStatus;

        if ($approach->reported_by_user_id === null) {
            $approach->reported_by_user_id = $actor->id;
        }

        $approach->save();

        return $approach->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function finalize(StpApproach $approach, array $data, User $actor): StpApproach
    {
        $attributes = $this->extractAttributes($data);
        $payload = $this->extractPayload($data['payload'] ?? null);

        $approach->fill($attributes);
        $approach->payload = $payload;
        $approach->status = StpApproachStatus::Done;
        $approach->reported_by_user_id = $actor->id;
        $approach->save();

        return $approach->refresh();
    }

    public function review(StpApproach $approach, User $actor): StpApproach
    {
        $approach->status = StpApproachStatus::Reviewed;
        $approach->reviewed_by_user_id = $actor->id;
        $approach->reviewed_at = now();
        $approach->save();

        return $approach->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extractAttributes(array $data): array
    {
        return [
            'person_name' => $data['person_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'street' => $data['street'] ?? null,
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'district' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'reference_point' => $data['reference_point'] ?? null,
            'gospel_explained_times' => $data['gospel_explained_times'] ?? null,
            'people_count' => $data['people_count'] ?? null,
            'result' => $data['result'] ?? null,
            'means_growth' => (bool) ($data['means_growth'] ?? false),
            'follow_up_scheduled_at' => $this->normalizeDateTime($data['follow_up_scheduled_at'] ?? null),
            'public_q2_answer' => $data['public_q2_answer'] ?? null,
            'public_lesson' => $data['public_lesson'] ?? null,
            'type' => $data['type'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractPayload(mixed $payload): ?array
    {
        if (! is_array($payload)) {
            return null;
        }

        return $payload;
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === '' || $value === null) {
            return null;
        }

        return str_contains($value, 'T') ? str_replace('T', ' ', $value).':00' : $value;
    }
}
