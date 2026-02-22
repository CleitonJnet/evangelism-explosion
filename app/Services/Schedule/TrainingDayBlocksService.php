<?php

namespace App\Services\Schedule;

use App\Models\Training;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class TrainingDayBlocksService
{
    /**
     * @var array<int, string>
     */
    private const BLOCK_KEYS = [
        'welcome',
        'devotional',
        'breakfast',
        'lunch',
        'snack',
        'dinner',
    ];

    /**
     * @return array<string, array<string, bool>>
     */
    public function get(int $trainingId): array
    {
        $training = Training::query()
            ->with('eventDates')
            ->findOrFail($trainingId);

        return $this->resolveDayBlocks($training);
    }

    /**
     * @return array<string, bool>
     */
    public function getForDay(int $trainingId, string $dateKey): array
    {
        $dayBlocks = $this->get($trainingId);

        return $dayBlocks[$dateKey] ?? $this->defaultDayBlocks();
    }

    public function set(int $trainingId, string $dateKey, string $blockKey, bool $enabled): void
    {
        if (! in_array($blockKey, self::BLOCK_KEYS, true)) {
            return;
        }

        $training = Training::query()
            ->with('eventDates')
            ->findOrFail($trainingId);

        $dayBlocks = $this->resolveDayBlocks($training);
        $dayBlocks[$dateKey] = array_replace($this->defaultDayBlocks(), $dayBlocks[$dateKey] ?? []);
        $dayBlocks[$dateKey][$blockKey] = $enabled;

        $overrides = $this->resolveOverrides($training);

        $this->persistSettings($training, $dayBlocks, $overrides);
    }

    /**
     * @param  array<string, array<string, bool>>  $dayBlocks
     */
    public function persistDayBlocks(Training $training, array $dayBlocks): void
    {
        $overrides = $this->resolveOverrides($training);

        $this->persistSettings($training, $dayBlocks, $overrides);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function defaultsForTraining(Training $training): array
    {
        $training->loadMissing('eventDates');

        $firstDateKey = $training->eventDates
            ->sortBy(fn ($eventDate) => $this->resolveDateKey($eventDate->date))
            ->map(fn ($eventDate) => $this->resolveDateKey($eventDate->date))
            ->filter()
            ->first();

        $defaults = [];

        foreach ($training->eventDates as $eventDate) {
            $dateKey = $this->resolveDateKey($eventDate->date);

            if (! $dateKey) {
                continue;
            }

            $defaults[$dateKey] = array_replace(
                $this->defaultDayBlocks(),
                $this->resolveMealDefaultsForDay($dateKey, $eventDate->start_time, $eventDate->end_time),
            );
            $defaults[$dateKey]['welcome'] = $dateKey === $firstDateKey;
        }

        return $defaults;
    }

    /**
     * @return array<string, array{showBreakfast: bool, showLunch: bool, showSnack: bool, showDinner: bool}>
     */
    public function computeDayUi(Training $training): array
    {
        $training->loadMissing('eventDates');

        $ui = [];

        foreach ($training->eventDates as $eventDate) {
            $dateKey = $this->resolveDateKey($eventDate->date);

            if (! $dateKey) {
                continue;
            }

            $ui[$dateKey] = [
                'showBreakfast' => $this->isWithinDayWindow($dateKey, $eventDate->start_time, $eventDate->end_time, '07:50:00'),
                'showLunch' => $this->isWithinDayWindow($dateKey, $eventDate->start_time, $eventDate->end_time, '12:00:00'),
                'showSnack' => $this->isWithinDayWindow($dateKey, $eventDate->start_time, $eventDate->end_time, '15:00:00'),
                'showDinner' => $this->isWithinDayWindow($dateKey, $eventDate->start_time, $eventDate->end_time, '18:00:00'),
            ];
        }

        return $ui;
    }

    /**
     * @param  array<string, array<string, bool>>  $dayBlocks
     * @param  array<string, array{showBreakfast: bool, showLunch: bool, showSnack: bool, showDinner: bool}>  $dayUi
     * @return array<string, array<string, bool>>
     */
    public function normalizeDayBlocksForVisibility(Training $training, array $dayBlocks, array $dayUi): array
    {
        $training->loadMissing('eventDates');

        foreach ($training->eventDates as $eventDate) {
            $dateKey = $this->resolveDateKey($eventDate->date);

            if (! $dateKey) {
                continue;
            }

            $flags = $dayUi[$dateKey] ?? [];
            $dayBlocks[$dateKey] = array_replace($this->defaultDayBlocks(), $dayBlocks[$dateKey] ?? []);

            if (! ($flags['showBreakfast'] ?? false)) {
                $dayBlocks[$dateKey]['breakfast'] = false;
            }

            if (! ($flags['showLunch'] ?? false)) {
                $dayBlocks[$dateKey]['lunch'] = false;
            }

            if (! ($flags['showSnack'] ?? false)) {
                $dayBlocks[$dateKey]['snack'] = false;
            }

            if (! ($flags['showDinner'] ?? false)) {
                $dayBlocks[$dateKey]['dinner'] = false;
            }
        }

        return $dayBlocks;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function resolveDayBlocks(Training $training): array
    {
        $storedSettings = $this->normalizeSettings($training->schedule_settings);
        $storedDayBlocks = $storedSettings['day_blocks'] ?? [];
        $dayBlocks = $this->defaultsForTraining($training);

        $firstDateKey = $training->eventDates
            ->sortBy(fn ($eventDate) => $this->resolveDateKey($eventDate->date))
            ->map(fn ($eventDate) => $this->resolveDateKey($eventDate->date))
            ->filter()
            ->first();

        foreach ($dayBlocks as $dateKey => $defaults) {
            $stored = $storedDayBlocks[$dateKey] ?? [];
            $stored = is_array($stored) ? $stored : [];

            $merged = array_replace($defaults, $stored);

            foreach (self::BLOCK_KEYS as $blockKey) {
                $merged[$blockKey] = (bool) ($merged[$blockKey] ?? true);
            }

            if ($dateKey !== $firstDateKey && ($stored['welcome'] ?? null) === null) {
                $merged['welcome'] = false;
            }

            $dayBlocks[$dateKey] = $merged;
        }

        return $dayBlocks;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveOverrides(Training $training): array
    {
        $storedSettings = $this->normalizeSettings($training->schedule_settings);
        $overrides = $storedSettings['overrides'] ?? [];

        return is_array($overrides) ? $overrides : [];
    }

    /**
     * @param  array<string, array<string, bool>>  $dayBlocks
     * @param  array<string, mixed>  $overrides
     */
    private function persistSettings(Training $training, array $dayBlocks, array $overrides): void
    {
        $training->schedule_settings = [
            'day_blocks' => $dayBlocks,
            'overrides' => $overrides,
        ];

        $training->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSettings(mixed $settings): array
    {
        return is_array($settings) ? $settings : [];
    }

    /**
     * @return array<string, bool>
     */
    private function defaultDayBlocks(): array
    {
        return [
            'welcome' => true,
            'devotional' => true,
            'breakfast' => false,
            'lunch' => false,
            'snack' => false,
            'dinner' => false,
        ];
    }

    private function isWithinDayWindow(string $dateKey, ?string $startTime, ?string $endTime, string $targetTime): bool
    {
        if (! $startTime || ! $endTime) {
            return false;
        }

        $dayStart = Carbon::parse($dateKey.' '.$startTime);
        $dayEnd = Carbon::parse($dateKey.' '.$endTime);
        $target = Carbon::parse($dateKey.' '.$targetTime);

        return $dayStart->lte($target) && $target->lt($dayEnd);
    }

    /**
     * @return array{lunch: bool, snack: bool, dinner: bool}
     */
    private function resolveMealDefaultsForDay(string $dateKey, ?string $startTime, ?string $endTime): array
    {
        if (! $startTime || ! $endTime) {
            return [
                'lunch' => false,
                'snack' => false,
                'dinner' => false,
            ];
        }

        $dayStart = Carbon::parse($dateKey.' '.$startTime);
        $dayEnd = Carbon::parse($dateKey.' '.$endTime);
        $twelve = Carbon::parse($dateKey.' 12:00:00');
        $eighteen = Carbon::parse($dateKey.' 18:00:00');
        $twentyOne = Carbon::parse($dateKey.' 21:00:00');
        $snackTarget = Carbon::parse($dateKey.' 15:00:00');
        $lunchTarget = Carbon::parse($dateKey.' 12:00:00');
        $dinnerTarget = Carbon::parse($dateKey.' 18:00:00');

        $isMorningToAfternoon = $dayStart->lt($twelve) && $dayEnd->gt($twelve);
        $isBetweenNoonAndSix = $dayStart->gte($twelve) && $dayEnd->lte($eighteen);
        $isAfternoonToNight = $dayStart->gte($twelve) && $dayEnd->gte($twentyOne);

        return [
            'lunch' => $isMorningToAfternoon && $dayStart->lte($lunchTarget) && $lunchTarget->lt($dayEnd),
            'snack' => $isBetweenNoonAndSix && $dayStart->lte($snackTarget) && $snackTarget->lt($dayEnd),
            'dinner' => $isAfternoonToNight && $dayStart->lte($dinnerTarget) && $dinnerTarget->lt($dayEnd),
        ];
    }

    private function resolveDateKey(CarbonInterface|string|null $date): ?string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('Y-m-d');
        }

        if (is_string($date) && $date !== '') {
            return $date;
        }

        return null;
    }
}
