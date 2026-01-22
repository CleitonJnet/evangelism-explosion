<?php

namespace App\Livewire\Pages\App\Student\Training;

use App\Models\Training;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Training $training;

    public ?string $workloadDuration = null;

    public string $churchAddress = '';

    public function mount(Training $training): void
    {
        $this->training = $training->load([
            'course',
            'church',
            'teacher',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        ]);

        $this->churchAddress = implode(
            ', ',
            array_filter([
                $this->training->street ?? null,
                $this->training->number ?? null,
                $this->training->district ?? null,
                $this->training->city ?? null,
                $this->training->state ?? null,
            ]),
        );

        $this->workloadDuration = $this->calculateWorkloadDuration($this->training);

        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        $isEnrolled = $this->training->students()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            abort(403);
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.student.training.show');
    }

    private function calculateWorkloadDuration(Training $training): ?string
    {
        $workloadMinutes = $training->eventDates->reduce(function (int $total, $eventDate): int {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                return $total;
            }

            $start = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $end = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                return $total;
            }

            return $total + $start->diffInMinutes($end);
        }, 0);

        if ($workloadMinutes <= 0) {
            return null;
        }

        $hours = intdiv($workloadMinutes, 60);
        $minutes = $workloadMinutes % 60;

        return $minutes > 0
            ? sprintf('%02dh%02d', $hours, $minutes)
            : sprintf('%02dh', $hours);
    }
}
