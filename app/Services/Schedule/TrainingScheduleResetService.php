<?php

namespace App\Services\Schedule;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use Illuminate\Support\Facades\DB;

class TrainingScheduleResetService
{
    public function __construct(
        public TrainingScheduleGenerator $generator,
        public TrainingScheduleTimelineService $timelineService,
        public TrainingDayBlocksService $dayBlocksService,
        public TrainingScheduleDayBlocksApplier $dayBlocksApplier,
        public TrainingScheduleBreakPolicy $breakPolicy,
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

            $dayBlocks = $this->dayBlocksService->defaultsForTraining($training);
            $dayUi = $this->dayBlocksService->computeDayUi($training);
            $dayBlocks = $this->dayBlocksService->normalizeDayBlocksForVisibility($training, $dayBlocks, $dayUi);

            $training->schedule_settings = [
                'day_blocks' => $dayBlocks,
                'overrides' => [],
            ];
            $training->save();

            foreach ($training->eventDates as $eventDate) {
                if (! $eventDate->date) {
                    continue;
                }

                $dateKey = is_string($eventDate->date)
                    ? $eventDate->date
                    : $eventDate->date->format('Y-m-d');
                $blocksForDay = $dayBlocks[$dateKey] ?? [];

                foreach ($blocksForDay as $blockKey => $enabled) {
                    $this->dayBlocksApplier->apply($trainingId, $dateKey, $blockKey, (bool) $enabled);
                }

                $this->cleanupHiddenMeals($trainingId, $dateKey, $dayUi[$dateKey] ?? []);

                if (! ($blocksForDay['snack'] ?? true)) {
                    $this->breakPolicy->suggestBreakIfLongRun($trainingId, $dateKey, '15:30:00', 'snack_off');
                }

                $this->timelineService->reflowDay($trainingId, $dateKey);
            }
        });
    }

    /**
     * @param  array{showBreakfast?: bool, showLunch?: bool, showSnack?: bool, showDinner?: bool}  $flags
     */
    private function cleanupHiddenMeals(int $trainingId, string $dateKey, array $flags): void
    {
        $hiddenMeals = [];

        if (! ($flags['showBreakfast'] ?? false)) {
            $hiddenMeals[] = ['anchor' => ['breakfast'], 'subkind' => ['breakfast']];
        }

        if (! ($flags['showLunch'] ?? false)) {
            $hiddenMeals[] = ['anchor' => ['lunch'], 'subkind' => ['lunch']];
        }

        if (! ($flags['showSnack'] ?? false)) {
            $hiddenMeals[] = ['anchor' => ['afternoon_snack'], 'subkind' => ['snack']];
        }

        if (! ($flags['showDinner'] ?? false)) {
            $hiddenMeals[] = ['anchor' => ['dinner', 'night_snack'], 'subkind' => ['dinner']];
        }

        if ($hiddenMeals === []) {
            return;
        }

        TrainingScheduleItem::query()
            ->where('training_id', $trainingId)
            ->whereDate('date', $dateKey)
            ->where('type', 'MEAL')
            ->where(function ($subQuery) use ($hiddenMeals): void {
                foreach ($hiddenMeals as $meal) {
                    $anchors = $meal['anchor'];
                    $subkinds = $meal['subkind'];

                    $subQuery->orWhere(function ($or) use ($anchors, $subkinds): void {
                        $or->whereIn('meta->anchor', $anchors)
                            ->orWhereIn('meta->subkind', $subkinds);
                    });
                }
            })
            ->delete();
    }
}
