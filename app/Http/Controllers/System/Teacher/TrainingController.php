<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\Schedule\TrainingScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index()
    {
        return view('pages.app.roles.teacher.trainings.index', [
            'statusKey' => 'scheduled',
        ]);
    }

    public function indexByStatus(string $status)
    {
        return view('pages.app.roles.teacher.trainings.index', [
            'statusKey' => $status,
        ]);
    }

    public function create()
    {
        return view('pages.app.roles.teacher.trainings.create');
    }

    public function show(string $id)
    {
        $training = Training::findOrFail($id);

        return view('pages.app.roles.teacher.trainings.show', ['training' => $training]);
    }

    public function schedule(Training $training): View
    {
        $training->load([
            'course',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
        ]);

        $generator = app(TrainingScheduleGenerator::class);
        $scheduleSettings = $generator->settingsFor($training);
        $preview = $generator->preview($training);
        $unallocatedByDate = collect($preview['unallocated'] ?? [])
            ->groupBy(fn (array $item) => $item['assigned_date'] ?? null);

        $formatDuration = function (int $minutes): string {
            if ($minutes <= 0) {
                return '00h';
            }

            $hours = intdiv($minutes, 60);
            $remaining = $minutes % 60;

            return $remaining > 0
                ? sprintf('%02dh %02dmin', $hours, $remaining)
                : sprintf('%02dh', $hours);
        };

        $totalWorkloadMinutes = $training->eventDates->reduce(function (int $total, $eventDate): int {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                return $total;
            }

            $start = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $end = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            return $total + max(0, $start->diffInMinutes($end, false));
        }, 0);

        $daySummaries = $training->eventDates->mapWithKeys(function ($eventDate) use ($training, $formatDuration, $scheduleSettings, $unallocatedByDate) {
            $dateKey = $eventDate->date;
            $items = $training->scheduleItems->filter(
                fn ($item) => $item->date?->format('Y-m-d') === $dateKey
            );

            $dayStart = $eventDate->start_time ? Carbon::parse($eventDate->date.' '.$eventDate->start_time) : null;
            $dayEnd = $eventDate->end_time ? Carbon::parse($eventDate->date.' '.$eventDate->end_time) : null;

            $dayMinutes = ($dayStart && $dayEnd) ? max(0, $dayStart->diffInMinutes($dayEnd, false)) : 0;
            $scheduledMinutes = (int) $items->sum('planned_duration_minutes');
            $remainingMinutes = max(0, $dayMinutes - $scheduledMinutes);

            $overflowMinutes = (int) ($unallocatedByDate->get($dateKey, collect())->sum('planned_minutes') ?? 0);

            $meals = $scheduleSettings['meals'] ?? [];
            $mealsEnabled = collect($meals)->contains(fn ($meal) => (bool) ($meal['enabled'] ?? false));
            $longDayWarning = $dayMinutes > 360 && ! $mealsEnabled;

            return [
                $dateKey => [
                    'day_minutes' => $dayMinutes,
                    'day_label' => $formatDuration($dayMinutes),
                    'scheduled_minutes' => $scheduledMinutes,
                    'remaining_minutes' => $remainingMinutes,
                    'remaining_label' => $formatDuration($remainingMinutes),
                    'overflow_minutes' => $overflowMinutes,
                    'overflow_label' => $formatDuration($overflowMinutes),
                    'long_day_warning' => $longDayWarning,
                ],
            ];
        });

        return view('pages.app.roles.teacher.trainings.schedule', [
            'training' => $training,
            'eventDates' => $training->eventDates,
            'scheduleByDate' => $training->scheduleItems->groupBy(
                fn ($item) => $item->date?->format('Y-m-d')
            ),
            'scheduleSettings' => $scheduleSettings,
            'totalWorkloadMinutes' => $totalWorkloadMinutes,
            'totalWorkloadLabel' => $formatDuration($totalWorkloadMinutes),
            'daySummaries' => $daySummaries,
        ]);
    }

    public function edit(string $id)
    {
        $training = Training::findOrFail($id);

        return view('pages.app.roles.teacher.trainings.edit', ['training' => $training]);
    }

    public function destroy(Training $training): RedirectResponse
    {
        $training->delete();

        return redirect()->route('app.teacher.training.index');
    }
}
