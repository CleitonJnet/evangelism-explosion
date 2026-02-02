<?php

namespace App\Services\Schedule;

use App\Models\Training;
use Illuminate\Support\Facades\DB;

class TrainingScheduleResetService
{
    public function __construct(
        public TrainingScheduleGenerator $generator,
        public TrainingScheduleTimelineService $timelineService,
    ) {}

    public function resetFull(int $trainingId): void
    {
        DB::transaction(function () use ($trainingId): void {
            $training = Training::query()
                ->with('eventDates')
                ->findOrFail($trainingId);

            $training->scheduleItems()->delete();

            $this->generator->generate($training);

            $training->load('eventDates');

            foreach ($training->eventDates as $eventDate) {
                if (! $eventDate->date) {
                    continue;
                }

                $this->timelineService->reflowDay($trainingId, $eventDate->date);
            }
        });
    }
}
