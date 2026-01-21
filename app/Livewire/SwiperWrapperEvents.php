<?php

namespace App\Livewire;

use App\Models\Training;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SwiperWrapperEvents extends Component
{
    public $events;

    /**
     * @param  array<int>|int|null  $ministry
     * @param  array<int>|int|null  $ministryNot
     */
    public function mount(array|int|null $ministry = null, array|int|null $ministryNot = null): void
    {
        $query = Training::query()
            ->with([
                'course.ministry',
                'course' => fn ($query) => $query->where('execution',0),
                'church',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->whereHas('course')
            ->whereHas('eventDates')
            ->withMin('eventDates', 'date')
            ->orderBy('event_dates_min_date');

        $query->when(
            $ministry !== null,
            fn ($query) => $query->whereHas('course', fn ($query) => $query->whereIn(
                'ministry_id',
                is_array($ministry) ? $ministry : [$ministry]
            ))
        );

        $query->when(
            $ministryNot !== null,
            fn ($query) => $query->whereDoesntHave('course', fn ($query) => $query->whereIn(
                'ministry_id',
                is_array($ministryNot) ? $ministryNot : [$ministryNot]
            ))
        );

        $this->events = $query->get();
    }

    public function render(): View
    {
        return view('livewire.swiper-wrapper-events');
    }
}
