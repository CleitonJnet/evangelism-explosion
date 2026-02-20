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
        $listeners = $this->normalizeListeners($data['payload']['listeners'] ?? null);
        $firstListenerResult = $listeners[0]['result'] ?? null;
        $address = is_array($data['address'] ?? null) ? $data['address'] : [];

        return [
            'person_name' => $data['person_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'street' => $address['street'] ?? null,
            'number' => $address['number'] ?? null,
            'complement' => $address['complement'] ?? null,
            'district' => $address['district'] ?? null,
            'city' => $address['city'] ?? null,
            'state' => $address['state'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'reference_point' => $data['reference_point'] ?? null,
            'gospel_explained_times' => 1,
            'people_count' => count($listeners),
            'result' => $firstListenerResult,
            'means_growth' => (bool) ($data['means_growth'] ?? false),
            'follow_up_scheduled_at' => $this->normalizeDateTime($data['follow_up_scheduled_at'] ?? null),
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

        return [
            'approach_date' => $this->normalizeDate($payload['approach_date'] ?? null),
            'listeners' => $this->normalizeListeners($payload['listeners'] ?? null),
            'notes' => $payload['notes'] ?? null,
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === '' || $value === null) {
            return null;
        }

        return str_contains($value, 'T') ? str_replace('T', ' ', $value).':00' : $value;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === '' || $value === null) {
            return null;
        }

        return $value;
    }

    /**
     * @return array<int, array{name: ?string, diagnostic_answer: ?string, result: ?string}>
     */
    private function normalizeListeners(mixed $listeners): array
    {
        if (! is_array($listeners)) {
            return [];
        }

        return collect($listeners)
            ->map(function (mixed $row): ?array {
                if (! is_array($row)) {
                    return null;
                }

                return [
                    'name' => data_get($row, 'name'),
                    'diagnostic_answer' => data_get($row, 'diagnostic_answer'),
                    'result' => data_get($row, 'result'),
                ];
            })
            ->filter(fn (?array $row): bool => $row !== null)
            ->values()
            ->all();
    }
}
