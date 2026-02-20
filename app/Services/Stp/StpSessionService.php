<?php

namespace App\Services\Stp;

use App\Models\StpSession;
use App\Models\Training;

class StpSessionService
{
    /**
     * @param  array{label?: ?string, starts_at?: mixed, ends_at?: mixed, status?: ?string}  $data
     */
    public function createNextSession(Training $training, array $data = []): StpSession
    {
        $lastSequence = (int) StpSession::query()
            ->where('training_id', $training->id)
            ->max('sequence');

        return StpSession::query()->create([
            'training_id' => $training->id,
            'sequence' => $lastSequence + 1,
            'label' => $data['label'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'] ?? null,
        ]);
    }
}
