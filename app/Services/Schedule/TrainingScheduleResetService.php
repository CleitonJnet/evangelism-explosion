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

            $dayBlocks = $this->dayBlocksService->defaultsForTraining($training);
            $dayUi = $this->dayBlocksService->computeDayUi($training);
            $dayBlocks = $this->dayBlocksService->normalizeDayBlocksForVisibility($training, $dayBlocks, $dayUi);

            $training->schedule_settings = [
                'days' => $this->buildGeneratorDaysSettings($dayBlocks),
            ];
            $training->save();

            $training->scheduleItems()->delete();

            $this->generator->generate($training);
            $this->generator->normalizeGeneratedDurationsToFive($training->fresh());

            $training->load('eventDates');

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
                    $this->breakPolicy->suggestBreakIfLongRun($trainingId, $dateKey, '15:00:00', 'snack_off');
                }

            }
        });
    }

    /**
     * @param  array<string, array<string, bool>>  $dayBlocks
     * @return array<string, array<string, mixed>>
     */
    private function buildGeneratorDaysSettings(array $dayBlocks): array
    {
        $days = [];

        foreach ($dayBlocks as $dateKey => $blocks) {
            $days[$dateKey] = [
                'welcome_enabled' => (bool) ($blocks['welcome'] ?? false),
                'devotional_enabled' => (bool) ($blocks['devotional'] ?? true),
                'meals' => [
                    'breakfast' => [
                        'enabled' => (bool) ($blocks['breakfast'] ?? false),
                        'duration_minutes' => 30,
                    ],
                    'lunch' => [
                        'enabled' => (bool) ($blocks['lunch'] ?? false),
                        'duration_minutes' => 60,
                    ],
                    'afternoon_snack' => [
                        'enabled' => (bool) ($blocks['snack'] ?? false),
                        'duration_minutes' => 30,
                    ],
                    'dinner' => [
                        'enabled' => (bool) ($blocks['dinner'] ?? false),
                        'duration_minutes' => 60,
                        'substitute_snack' => false,
                    ],
                ],
            ];
        }

        return $days;
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
