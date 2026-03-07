<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $statusKey = 'scheduled';

    public string $searchTerm = '';

    public function mount(?string $statusKey = null): void
    {
        $this->statusKey = $this->normalizeStatusKey($statusKey);
    }

    public function render(): View
    {
        $status = $this->statusFromKey($this->statusKey);

        $trainingsQuery = Training::query()
            ->select('trainings.*')
            ->join('courses', 'courses.id', '=', 'trainings.course_id')
            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
            ->with([
                'teacher',
                'church',
                'course.ministry',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->withCount('newChurches')
            ->whereHas('course', fn ($query) => $query->where('execution', 0))
            ->where('status', $status->value)
            ->orderBy('ministries.name')
            ->orderBy('courses.type')
            ->orderBy('courses.name');

        $searchTerm = trim($this->searchTerm);
        if ($searchTerm !== '') {
            $trainingsQuery->where(function ($query) use ($searchTerm): void {
                $query->where('trainings.city', 'like', '%'.$searchTerm.'%')
                    ->orWhere('trainings.state', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('church', function ($churchQuery) use ($searchTerm): void {
                        $churchQuery->where('name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('city', 'like', '%'.$searchTerm.'%')
                            ->orWhere('state', 'like', '%'.$searchTerm.'%');
                    })
                    ->orWhereHas('teacher', function ($teacherQuery) use ($searchTerm): void {
                        $teacherQuery->where('name', 'like', '%'.$searchTerm.'%');
                    });
            });
        }

        if ($status === TrainingStatus::Scheduled) {
            $trainingsQuery->whereDoesntHave('eventDates', function ($query): void {
                $query->whereDate('date', '<', Carbon::today());
            });
        }

        $trainings = $trainingsQuery->get();

        return view('livewire.pages.app.director.training.index', [
            'statusKey' => $this->statusKey,
            'statuses' => $this->statusTabs(),
            'groups' => $this->groupByMinistry($trainings),
        ]);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{training: Training, dates: Collection<int, \App\Models\EventDate>}>
     */
    private function mapTrainings(Collection $trainings): Collection
    {
        return $trainings
            ->map(function (Training $training) {
                $dates = $training->eventDates
                    ->sortBy(fn ($eventDate) => sprintf(
                        '%s %s',
                        $eventDate->date,
                        $eventDate->start_time ?? '00:00:00'
                    ))
                    ->values();

                return [
                    'training' => $training,
                    'dates' => $dates,
                ];
            })
            ->sortBy(function (array $item) {
                $firstDate = $item['dates']->first();

                if (! $firstDate) {
                    return '9999-12-31 23:59:59';
                }

                return sprintf(
                    '%s %s',
                    $firstDate->date,
                    $firstDate->start_time ?? '00:00:00'
                );
            })
            ->values();
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{ministry: \App\Models\Ministry|null, courses: Collection<int, array{course: \App\Models\Course|null, items: Collection<int, array{training: Training, dates: Collection<int, \App\Models\EventDate>}>}>}>
     */
    private function groupByMinistry(Collection $trainings): Collection
    {
        return $trainings
            ->groupBy(fn (Training $training) => $training->course?->ministry_id ?? 0)
            ->map(function (Collection $ministryGroup): array {
                $ministry = $ministryGroup->first()?->course?->ministry;
                $courses = $ministryGroup
                    ->groupBy(fn (Training $training) => $training->course_id ?? 0)
                    ->map(function (Collection $courseGroup): array {
                        $course = $courseGroup->first()?->course;

                        return [
                            'course' => $course,
                            'items' => $this->mapTrainings($courseGroup),
                        ];
                    })
                    ->values();

                return [
                    'ministry' => $ministry,
                    'courses' => $courses,
                ];
            })
            ->values();
    }

    /**
     * @return array<int, array{key: string, label: string, route: string}>
     */
    private function statusTabs(): array
    {
        return collect(TrainingStatus::cases())
            ->map(function (TrainingStatus $status): array {
                $route = match ($status) {
                    TrainingStatus::Planning => route('app.director.training.planning'),
                    TrainingStatus::Scheduled => route('app.director.training.scheduled'),
                    TrainingStatus::Canceled => route('app.director.training.canceled'),
                    TrainingStatus::Completed => route('app.director.training.completed'),
                };

                return [
                    'key' => $status->key(),
                    'label' => $status->label(),
                    'route' => $route,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeStatusKey(?string $statusKey): string
    {
        $key = $statusKey ?? 'scheduled';

        foreach (TrainingStatus::cases() as $status) {
            if ($status->key() === $key) {
                return $key;
            }
        }

        return 'scheduled';
    }

    private function statusFromKey(string $key): TrainingStatus
    {
        foreach (TrainingStatus::cases() as $status) {
            if ($status->key() === $key) {
                return $status;
            }
        }

        return TrainingStatus::Scheduled;
    }
}
