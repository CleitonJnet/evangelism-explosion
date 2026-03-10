<?php

namespace App\Services;

use App\Models\Church;
use App\Models\EventDate;
use App\Models\HostChurch;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HostChurchReadModel
{
    public function registeredHostsQuery(): Builder
    {
        return HostChurch::query()
            ->join('churches', 'host_churches.church_id', '=', 'churches.id')
            ->select('host_churches.*')
            ->with('church')
            ->withCount([
                'admins',
                'admins as active_admins_count' => fn (Builder $query): Builder => $query->where('status', 'active'),
                'admins as certified_admins_count' => fn (Builder $query): Builder => $query->whereNotNull('certified_at'),
            ])
            ->addSelect([
                'trainings_count' => Training::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('trainings.church_id', 'host_churches.church_id'),
                'completed_trainings_count' => Training::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('trainings.church_id', 'host_churches.church_id')
                    ->where('status', TrainingStatus::Completed->value),
                'latest_training_event_date' => EventDate::query()
                    ->selectRaw('MAX(event_dates.date)')
                    ->join('trainings', 'trainings.id', '=', 'event_dates.training_id')
                    ->whereColumn('trainings.church_id', 'host_churches.church_id'),
            ])
            ->orderBy('churches.name');
    }

    /**
     * @return Collection<int, HostChurch>
     */
    public function registeredHosts(): Collection
    {
        return $this->registeredHostsQuery()->get();
    }

    public function derivedCandidateChurchesQuery(): Builder
    {
        return Church::query()
            ->whereDoesntHave('hostChurch')
            ->whereHas('trainings')
            ->withCount([
                'trainings',
                'trainings as completed_trainings_count' => fn (Builder $query): Builder => $query->where('status', TrainingStatus::Completed->value),
            ])
            ->addSelect([
                'latest_training_event_date' => EventDate::query()
                    ->selectRaw('MAX(event_dates.date)')
                    ->join('trainings', 'trainings.id', '=', 'event_dates.training_id')
                    ->whereColumn('trainings.church_id', 'churches.id'),
            ])
            ->orderByDesc('trainings_count')
            ->orderBy('name');
    }

    /**
     * @return Collection<int, Church>
     */
    public function derivedCandidateChurches(): Collection
    {
        return $this->derivedCandidateChurchesQuery()->get();
    }
}
