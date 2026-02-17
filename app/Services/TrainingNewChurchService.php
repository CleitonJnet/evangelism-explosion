<?php

namespace App\Services;

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Training;
use App\Models\TrainingNewChurch;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TrainingNewChurchService
{
    public function markNewChurch(Training $training, Church $church, ?ChurchTemp $temp, User $actor): TrainingNewChurch
    {
        return TrainingNewChurch::query()->firstOrCreate(
            [
                'training_id' => $training->id,
                'church_id' => $church->id,
            ],
            [
                'source_church_temp_id' => $temp?->id,
                'created_by' => $actor->id,
            ],
        );
    }

    public function countNewChurches(Training $training): int
    {
        return $training->newChurches()->count();
    }

    /**
     * @return Collection<int, Training>
     */
    public function getTrainingsWithNewChurchCounts(): Collection
    {
        return Training::query()
            ->withCount('newChurches')
            ->orderByDesc('new_churches_count')
            ->orderByDesc('id')
            ->get();
    }
}
