<?php

namespace App\Services\OJT;

use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OjtSessionGenerator
{
    public function generate(Training $training): OjtSessionGenerationResult
    {
        return DB::transaction(function () use ($training): OjtSessionGenerationResult {
            $training->load([
                'course',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at'),
            ]);

            $targetCount = $training->ojtExpectedCount();

            $sessions = OjtSession::query()
                ->where('training_id', $training->id)
                ->orderBy('date')
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get();

            $reportSessionIds = $this->reportedSessionIds($sessions);

            $activeSessions = $sessions->filter(fn (OjtSession $session) => $session->status !== 'canceled');
            $activeCount = $activeSessions->count();

            $created = collect();
            $canceled = collect();

            if ($activeCount > $targetCount) {
                $canceled = $this->cancelExtraSessions($activeSessions, $reportSessionIds, $targetCount);
            }

            if ($activeCount < $targetCount) {
                $missing = $targetCount - $activeCount;
                $created = $this->createMissingSessions($training, $sessions, $missing);
            }

            return new OjtSessionGenerationResult($created, $canceled);
        });
    }

    /**
     * @param  Collection<int, OjtSession>  $sessions
     * @return array<int, int>
     */
    private function reportedSessionIds(Collection $sessions): array
    {
        $sessionIds = $sessions->pluck('id')->filter()->all();

        if ($sessionIds === []) {
            return [];
        }

        return OjtReport::query()
            ->select('ojt_teams.ojt_session_id')
            ->join('ojt_teams', 'ojt_teams.id', '=', 'ojt_reports.ojt_team_id')
            ->whereIn('ojt_teams.ojt_session_id', $sessionIds)
            ->distinct()
            ->pluck('ojt_teams.ojt_session_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  Collection<int, OjtSession>  $activeSessions
     * @param  array<int, int>  $reportSessionIds
     * @return Collection<int, OjtSession>
     */
    private function cancelExtraSessions(Collection $activeSessions, array $reportSessionIds, int $targetCount): Collection
    {
        $reportLookup = array_flip($reportSessionIds);
        $excess = $activeSessions->count() - $targetCount;

        if ($excess <= 0) {
            return collect();
        }

        $candidates = $activeSessions
            ->filter(fn (OjtSession $session) => ! isset($reportLookup[$session->id]))
            ->sortByDesc(fn (OjtSession $session) => sprintf(
                '%s %s %010d',
                $session->date?->format('Y-m-d') ?? '0000-00-00',
                $session->starts_at ?? '00:00:00',
                $session->id,
            ))
            ->values()
            ->take($excess);

        $canceled = collect();

        foreach ($candidates as $session) {
            $session->status = 'canceled';
            $session->save();
            $canceled->push($session);
        }

        return $canceled;
    }

    /**
     * @param  Collection<int, OjtSession>  $sessions
     * @return Collection<int, OjtSession>
     */
    private function createMissingSessions(Training $training, Collection $sessions, int $missing): Collection
    {
        $suggestions = $this->suggestedSlots($training, $sessions, $missing);
        $nextWeekNumber = ((int) $sessions->max('week_number')) + 1;

        $created = collect();

        foreach ($suggestions as $slot) {
            $session = OjtSession::query()->create([
                'training_id' => $training->id,
                'date' => $slot['date'],
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'],
                'week_number' => $nextWeekNumber,
                'status' => 'planned',
                'meta' => null,
            ]);

            $created->push($session);
            $nextWeekNumber++;
        }

        return $created;
    }

    /**
     * @param  Collection<int, OjtSession>  $sessions
     * @return array<int, array{date: string, starts_at: string|null, ends_at: string|null}>
     */
    private function suggestedSlots(Training $training, Collection $sessions, int $missing): array
    {
        $existingKeys = $sessions->mapWithKeys(function (OjtSession $session): array {
            $dateKey = $session->date?->format('Y-m-d') ?? (string) $session->date;
            $timeKey = $session->starts_at ?? '';

            return ["{$dateKey}|{$timeKey}" => true];
        })->all();

        $baseSlots = $this->baseSlots($training);
        $baseCount = count($baseSlots);

        $lastBaseDate = Carbon::parse($baseSlots[$baseCount - 1]['date']);
        $lastBaseTime = $baseSlots[$baseCount - 1]['starts_at'] ?? null;
        $lastBaseEndTime = $baseSlots[$baseCount - 1]['ends_at'] ?? null;

        $suggestions = [];
        $index = 0;

        while (count($suggestions) < $missing) {
            if ($index < $baseCount) {
                $slot = $baseSlots[$index];
            } else {
                $lastBaseDate = $lastBaseDate->copy()->addWeek();
                $slot = [
                    'date' => $lastBaseDate->format('Y-m-d'),
                    'starts_at' => $lastBaseTime,
                    'ends_at' => $lastBaseEndTime,
                ];
            }

            $index++;

            $candidateDate = Carbon::parse($slot['date']);
            $key = $slot['date'].'|'.($slot['starts_at'] ?? '');

            while (isset($existingKeys[$key])) {
                $candidateDate->addWeek();
                $slot['date'] = $candidateDate->format('Y-m-d');
                $key = $slot['date'].'|'.($slot['starts_at'] ?? '');
            }

            $existingKeys[$key] = true;
            $suggestions[] = $slot;
        }

        return $suggestions;
    }

    /**
     * @return array<int, array{date: string, starts_at: string|null, ends_at: string|null}>
     */
    private function baseSlots(Training $training): array
    {
        if ($training->eventDates->isNotEmpty()) {
            return $training->eventDates
                ->map(fn ($eventDate) => [
                    'date' => $eventDate->date,
                    'starts_at' => $eventDate->start_time,
                    'ends_at' => $eventDate->end_time,
                ])
                ->all();
        }

        if ($training->scheduleItems->isNotEmpty()) {
            return $training->scheduleItems
                ->groupBy(fn ($item) => $item->date?->format('Y-m-d') ?? (string) $item->date)
                ->map(function (Collection $items, string $date): array {
                    $startsAt = $items
                        ->map(fn ($item) => $item->starts_at?->format('H:i:s') ?? (string) $item->starts_at)
                        ->filter()
                        ->min();
                    $endsAt = $items
                        ->map(fn ($item) => $item->ends_at?->format('H:i:s') ?? (string) $item->ends_at)
                        ->filter()
                        ->max();

                    return [
                        'date' => $date,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                    ];
                })
                ->values()
                ->all();
        }

        return [[
            'date' => Carbon::today()->format('Y-m-d'),
            'starts_at' => null,
            'ends_at' => null,
        ]];
    }
}
