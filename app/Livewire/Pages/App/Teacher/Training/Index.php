<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $statusKey = 'scheduled';

    /**
     * @var array<int, int>
     */
    public array $extraCourseIds = [2];

    public ?int $userId = null;

    public function mount(?string $statusKey = null): void
    {
        $this->statusKey = $this->normalizeStatusKey($statusKey);
        $this->userId = Auth::id();
    }

    public function render(): View
    {
        $status = $this->statusFromKey($this->statusKey);

        $trainings = collect();

        if ($this->userId) {
            $trainings = Training::query()
                ->select('trainings.*')
                ->join('courses', 'courses.id', '=', 'trainings.course_id')
                ->with([
                    'teacher',
                    'church',
                    'course.ministry',
                    'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                ])
                ->whereHas('course', function ($query): void {
                    $query->where('execution', 0)
                        ->orWhereIn('id', $this->extraCourseIds);
                })
                ->where('status', $status->value)
                ->where('teacher_id', $this->userId)
                ->orderBy('courses.type')
                ->orderBy('courses.name')
                ->get();
        }

        return view('livewire.pages.app.teacher.training.index', [
            'statusKey' => $this->statusKey,
            'statuses' => $this->statusTabs(),
            'groups' => $this->groupByCourse($trainings),
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
     * @return Collection<int, array{course: \App\Models\Course|null, items: Collection<int, array{training: Training, dates: Collection<int, \App\Models\EventDate>}>}>
     */
    private function groupByCourse(Collection $trainings): Collection
    {
        return $trainings
            ->groupBy(fn (Training $training) => $training->course_id ?? 0)
            ->map(function (Collection $group): array {
                $course = $group->first()?->course;

                return [
                    'course' => $course,
                    'items' => $this->mapTrainings($group),
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
                    TrainingStatus::Planning => route('app.teacher.trainings.planning'),
                    TrainingStatus::Scheduled => route('app.teacher.trainings.scheduled'),
                    TrainingStatus::Canceled => route('app.teacher.trainings.canceled'),
                    TrainingStatus::Completed => route('app.teacher.trainings.completed'),
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
