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
     * @param  array{filter?: ?string, assignment?: ?string, church?: ?string, from?: ?string, to?: ?string}  $filters
     * @return array{
     *     statusKey: string,
     *     statuses: array<int, array{key: string, label: string, route: string}>,
     *     filters: array{filter: ?string, assignment: string, church: ?string, from: ?string, to: ?string},
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
    public function buildIndexData(User $user, ?string $statusKey, ?string $filterTerm, array $statusRoutes, string $context = 'auto', array $filters = []): array
    {
        $normalizedStatusKey = $this->normalizeStatusKey($statusKey);
        $status = $this->statusFromKey($normalizedStatusKey);
        $normalizedFilters = $this->normalizeFilters($filterTerm, $filters);

        $trainings = Training::query()
            ->select('trainings.*')
            ->join('courses', 'courses.id', '=', 'trainings.course_id')
            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
            ->with([
                'teacher',
                'assistantTeachers',
                'mentors',
                'church',
                'course.ministry',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->withCount(['newChurches', 'students'])
            ->whereHas('course', fn (Builder $query) => $query->where('execution', 0))
            ->where('status', $status->value)
            ->tap(fn (Builder $query) => $this->visibilityScope->apply($query, $user, $context))
            ->when($normalizedFilters['filter'] !== null, fn (Builder $query) => $this->applyFilter($query, $normalizedFilters['filter']))
            ->when($normalizedFilters['assignment'] !== 'all', fn (Builder $query) => $this->applyAssignmentFilter($query, $user, $normalizedFilters['assignment']))
            ->when($normalizedFilters['church'] !== null, fn (Builder $query) => $this->applyChurchFilter($query, $normalizedFilters['church']))
            ->when($normalizedFilters['from'] !== null || $normalizedFilters['to'] !== null, fn (Builder $query) => $this->applyPeriodFilter($query, $normalizedFilters['from'], $normalizedFilters['to']))
            ->orderBy('ministries.name')
            ->orderBy('courses.type')
            ->orderBy('courses.name')
            ->get();

        return [
            'statusKey' => $normalizedStatusKey,
            'statuses' => $this->statusTabs($statusRoutes, $normalizedFilters),
            'filters' => $normalizedFilters,
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

    private function applyAssignmentFilter(Builder $query, User $user, string $assignment): void
    {
        match ($assignment) {
            'lead_teacher' => $query->where('trainings.teacher_id', $user->id),
            'assistant_teacher' => $query->whereHas('assistantTeachers', fn (Builder $assistantQuery) => $assistantQuery->whereKey($user->id)),
            'mentor' => $query->whereHas('mentors', fn (Builder $mentorQuery) => $mentorQuery->whereKey($user->id)),
            default => null,
        };
    }

    private function applyChurchFilter(Builder $query, string $church): void
    {
        $like = '%'.$church.'%';

        $query->whereHas('church', function (Builder $churchQuery) use ($like): void {
            $churchQuery
                ->where('name', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('state', 'like', $like);
        });
    }

    private function applyPeriodFilter(Builder $query, ?string $from, ?string $to): void
    {
        $query->whereHas('eventDates', function (Builder $dateQuery) use ($from, $to): void {
            if ($from !== null) {
                $dateQuery->whereDate('date', '>=', $from);
            }

            if ($to !== null) {
                $dateQuery->whereDate('date', '<=', $to);
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
     * @param  array{filter: ?string, assignment: string, church: ?string, from: ?string, to: ?string}  $filters
     * @return array<int, array{key: string, label: string, route: string}>
     */
    private function statusTabs(array $statusRoutes, array $filters): array
    {
        return collect(TrainingStatus::cases())
            ->map(function (TrainingStatus $status) use ($statusRoutes, $filters): array {
                $routeName = $statusRoutes[$status->key()] ?? null;

                return [
                    'key' => $status->key(),
                    'label' => $status->label(),
                    'route' => $routeName !== null
                        ? route($routeName, $this->routeParameters($filters))
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

    /**
     * @param  array{filter?: ?string, assignment?: ?string, church?: ?string, from?: ?string, to?: ?string}  $filters
     * @return array{filter: ?string, assignment: string, church: ?string, from: ?string, to: ?string}
     */
    private function normalizeFilters(?string $filterTerm, array $filters): array
    {
        $filter = $filters['filter'] ?? $filterTerm;
        $filter = is_string($filter) ? trim($filter) : null;
        $church = $filters['church'] ?? null;
        $church = is_string($church) ? trim($church) : null;
        $assignment = $filters['assignment'] ?? 'all';
        $allowedAssignments = ['all', 'lead_teacher', 'assistant_teacher', 'mentor'];

        return [
            'filter' => $filter !== '' ? $filter : null,
            'assignment' => in_array($assignment, $allowedAssignments, true) ? $assignment : 'all',
            'church' => $church !== '' ? $church : null,
            'from' => $this->normalizeDateValue($filters['from'] ?? null),
            'to' => $this->normalizeDateValue($filters['to'] ?? null),
        ];
    }

    /**
     * @param  array{filter: ?string, assignment: string, church: ?string, from: ?string, to: ?string}  $filters
     * @return array<string, string>
     */
    private function routeParameters(array $filters): array
    {
        $parameters = [
            'filter' => $filters['filter'],
            'assignment' => $filters['assignment'] !== 'all' ? $filters['assignment'] : null,
            'church' => $filters['church'],
            'from' => $filters['from'],
            'to' => $filters['to'],
        ];

        return array_filter($parameters, fn (?string $value): bool => $value !== null && $value !== '');
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

    private function normalizeDateValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $date = trim($value);

        return $date !== '' ? $date : null;
    }
}
