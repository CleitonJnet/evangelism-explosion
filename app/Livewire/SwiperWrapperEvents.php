<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Training;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SwiperWrapperEvents extends Component
{
    /**
     * @var array<int, int>
     */
    public array $extraCourseIds = [2];

    public $events;

    public bool $showScheduleRequestCard = false;

    /**
     * @param  array<int>|int|null  $ministry
     * @param  array<int>|int|null  $ministryNot
     */
    public function mount(
        array|int|null $ministry = null,
        array|int|null $ministryNot = null,
        ?string $audience = null
    ): void {
        $query = Training::query()
            ->with([
                'course.ministry',
                'church',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->whereHas('course', function ($query): void {
                $query->where('execution', 0)
                    ->orWhereIn('id', $this->extraCourseIds);
            })
            ->whereHas('eventDates')
            ->whereDoesntHave('eventDates', function ($query) {
                $query->whereDate('date', '<', Carbon::today());
            })
            ->withMin('eventDates', 'date')
            ->withMin('eventDates', 'start_time')
            ->where('status', 1)
            ->orderBy('event_dates_min_date')
            ->orderBy('event_dates_min_start_time')
            ->orderBy('trainings.id');

        $query->when(
            $ministry !== null,
            function ($query) use ($ministry): void {
                $query->whereHas('course', fn ($query) => $query->whereIn(
                    'ministry_id',
                    is_array($ministry) ? $ministry : [$ministry]
                ));

                $query->reorder()
                    ->orderBy(
                        Course::query()
                            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
                            ->whereColumn('courses.id', 'trainings.course_id')
                            ->select('ministries.name')
                            ->limit(1)
                    )
                    ->orderBy('event_dates_min_date')
                    ->orderBy('event_dates_min_start_time')
                    ->orderBy('trainings.id');
            }
        );

        $query->when(
            $ministryNot !== null,
            fn ($query) => $query->whereDoesntHave('course', fn ($query) => $query->whereIn(
                'ministry_id',
                is_array($ministryNot) ? $ministryNot : [$ministryNot]
            ))
        );

        $query->when(
            $audience === 'leaders',
            fn ($query) => $query->whereHas('course', fn ($query) => $query->where('execution', 0))
        );

        $query->when(
            $audience === 'members',
            fn ($query) => $query->whereHas('course', fn ($query) => $query->where('execution', '>', 0))
        );

        $this->events = $query->get();

        $leadersEventsQuery = Training::query()
            ->whereHas('course', fn ($query) => $query->where('execution', 0))
            ->whereHas('eventDates')
            ->whereDoesntHave('eventDates', function ($query) {
                $query->whereDate('date', '<', Carbon::today());
            })
            ->where('status', 1);

        $leadersEventsQuery->when(
            $ministry !== null,
            fn ($query) => $query->whereHas('course', fn ($query) => $query->whereIn(
                'ministry_id',
                is_array($ministry) ? $ministry : [$ministry]
            ))
        );

        $leadersEventsQuery->when(
            $ministryNot !== null,
            fn ($query) => $query->whereDoesntHave('course', fn ($query) => $query->whereIn(
                'ministry_id',
                is_array($ministryNot) ? $ministryNot : [$ministryNot]
            ))
        );

        $this->showScheduleRequestCard = $audience !== 'members' && ! $leadersEventsQuery->exists();
    }

    public function render(): View
    {
        return view('livewire.swiper-wrapper-events');
    }
}
