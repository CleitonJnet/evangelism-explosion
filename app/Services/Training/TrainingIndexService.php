<?php

namespace App\Services\Training;

use App\Models\Training;
use App\Models\User;
use App\Support\TrainingAccess\TrainingVisibilityScope;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TrainingIndexService
{
    public function __construct(private TrainingVisibilityScope $visibilityScope) {}

    /**
     * @param  array<string, string>  $statusRoutes
     * @return array{
     *     statusKey: string,
     *     statuses: array<int, array{key: string, label: string, route: string}>,
     *     groups: Collection<int, array{
     *         ministry: \App\Models\Ministry|null,
     *         courses: Collection<int, array{
     *             course: \App\Models\Course|null,
     *             items: Collection<int, array{
     *                 training: Training,
     *                 dates: Collection<int, \App\Models\EventDate>
     *             }>
     *         }>
     *     }>
     * }
     */
    public function buildIndexData(User $user, ?string $statusKey, ?string $filterTerm, array $statusRoutes, string $context = 'auto'): array
    {
        $normalizedStatusKey = $this->normalizeStatusKey($statusKey);
        $status = $this->statusFromKey($normalizedStatusKey);

        $trainings = Training::query()
            ->select('trainings.*')
            ->join('courses', 'courses.id', '=', 'trainings.course_id')
            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
            ->with([
                'teacher',
                'church',
                'course.ministry',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->withCount(['newChurches', 'students'])
            ->whereHas('course', fn (Builder $query) => $query->where('execution', 0))
            ->where('status', $status->value)
            ->when($filterTerm !== null, fn (Builder $query) => $this->applyFilter($query, $filterTerm))
            ->tap(fn (Builder $query) => $this->visibilityScope->apply($query, $user, $context))
            ->orderBy('ministries.name')
            ->orderBy('courses.type')
            ->orderBy('courses.name')
            ->get();

        return [
            'statusKey' => $normalizedStatusKey,
            'statuses' => $this->statusTabs($statusRoutes, $filterTerm),
            'groups' => $this->groupByMinistry($trainings, $normalizedStatusKey),
        ];
    }

    private function applyFilter(Builder $query, string $filterTerm): void
    {
        $date = $this->parseFilterDate($filterTerm);

        $query->where(function (Builder $nestedQuery) use ($filterTerm, $date): void {
            $like = '%'.$filterTerm.'%';

            $nestedQuery
                ->whereHas('teacher', fn (Builder $teacherQuery) => $teacherQuery->where('name', 'like', $like))
                ->orWhereHas('church', function (Builder $churchQuery) use ($like): void {
                    $churchQuery
                        ->where('name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('state', 'like', $like);
                })
                ->orWhere('trainings.city', 'like', $like)
                ->orWhere('trainings.state', 'like', $like)
                ->orWhereHas('course', function (Builder $courseQuery) use ($like): void {
                    $courseQuery
                        ->where('type', 'like', $like)
                        ->orWhere('name', 'like', $like);
                })
                ->orWhereHas('assistantTeachers', fn (Builder $assistantQuery) => $assistantQuery->where('name', 'like', $like))
                ->orWhereHas('mentors', fn (Builder $mentorQuery) => $mentorQuery->where('name', 'like', $like));

            if ($date !== null) {
                $nestedQuery->orWhereHas('eventDates', fn (Builder $dateQuery) => $dateQuery->whereDate('date', $date));
            }
        });
    }

    private function parseFilterDate(string $filterTerm): ?string
    {
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $filterTerm);

                if ($date !== false) {
                    return $date->toDateString();
                }
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $statusRoutes
     * @return array<int, array{key: string, label: string, route: string}>
     */
    private function statusTabs(array $statusRoutes, ?string $filterTerm): array
    {
        return collect(TrainingStatus::cases())
            ->map(function (TrainingStatus $status) use ($statusRoutes, $filterTerm): array {
                $routeName = $statusRoutes[$status->key()] ?? null;

                return [
                    'key' => $status->key(),
                    'label' => $status->label(),
                    'route' => $routeName !== null
                        ? route($routeName, array_filter(['filter' => $filterTerm]))
                        : '#',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{
     *     training: Training,
     *     dates: Collection<int, \App\Models\EventDate>
     * }>
     */
    private function mapTrainings(Collection $trainings, string $statusKey): Collection
    {
        $mappedTrainings = $trainings
            ->map(function (Training $training): array {
                $dates = $training->eventDates
                    ->sortBy(fn ($eventDate) => sprintf(
                        '%s %s',
                        $eventDate->date,
                        $eventDate->start_time ?? '00:00:00',
                    ))
                    ->values();

                return [
                    'training' => $training,
                    'dates' => $dates,
                ];
            })
            ->sortBy(function (array $item): string {
                $firstDate = $item['dates']->first();

                if (! $firstDate) {
                    return '9999-12-31 23:59:59';
                }

                return sprintf(
                    '%s %s',
                    $firstDate->date,
                    $firstDate->start_time ?? '00:00:00',
                );
            });

        if ($statusKey === TrainingStatus::Completed->key()) {
            return $mappedTrainings->values()->reverse()->values();
        }

        return $mappedTrainings->values();
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{
     *     ministry: \App\Models\Ministry|null,
     *     courses: Collection<int, array{
     *         course: \App\Models\Course|null,
     *         items: Collection<int, array{
     *             training: Training,
     *             dates: Collection<int, \App\Models\EventDate>
     *         }>
     *     }>
     * }>
     */
    private function groupByMinistry(Collection $trainings, string $statusKey): Collection
    {
        return $trainings
            ->groupBy(fn (Training $training) => $training->course?->ministry_id ?? 0)
            ->map(function (Collection $ministryGroup) use ($statusKey): array {
                $ministry = $ministryGroup->first()?->course?->ministry;
                $courses = $ministryGroup
                    ->groupBy(fn (Training $training) => $training->course_id ?? 0)
                    ->map(function (Collection $courseGroup) use ($statusKey): array {
                        $course = $courseGroup->first()?->course;

                        return [
                            'course' => $course,
                            'items' => $this->mapTrainings($courseGroup, $statusKey),
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

    private function normalizeStatusKey(?string $statusKey): string
    {
        $key = $statusKey ?? TrainingStatus::Scheduled->key();

        foreach (TrainingStatus::cases() as $status) {
            if ($status->key() === $key) {
                return $key;
            }
        }

        return TrainingStatus::Scheduled->key();
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
