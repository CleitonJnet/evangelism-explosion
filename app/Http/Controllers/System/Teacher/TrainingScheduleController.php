<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegenerateTrainingScheduleRequest;
use App\Http\Requests\StoreTrainingScheduleItemRequest;
use App\Http\Requests\UpdateTrainingScheduleItemRequest;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleResetService;
use App\Services\Schedule\TrainingScheduleTimelineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TrainingScheduleController extends Controller
{
    public function regenerate(
        RegenerateTrainingScheduleRequest $request,
        Training $training,
        TrainingScheduleResetService $resetService,
    ): JsonResponse {
        $resetService->resetFull($training->id);

        return response()->json([
            'ok' => true,
            'generated_count' => $training->scheduleItems()->count(),
            'unallocated_count' => 0,
        ]);
    }

    public function updateItem(
        UpdateTrainingScheduleItemRequest $request,
        Training $training,
        TrainingScheduleItem $item,
        TrainingScheduleTimelineService $timelineService,
    ): JsonResponse {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        $validated = $request->validated();
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        $startsAt = Carbon::createFromFormat('Y-m-d H:i:s', $validated['starts_at'])
            ->setDate($date->year, $date->month, $date->day);

        $oldDate = $item->date?->format('Y-m-d');
        $duration = (int) ($validated['planned_duration_minutes'] ?? $item->planned_duration_minutes);
        $dateKey = $date->format('Y-m-d');

        if (array_key_exists('title', $validated)) {
            $item->title = $validated['title'];
        }

        if (array_key_exists('type', $validated)) {
            $item->type = $validated['type'];
        }

        $item->planned_duration_minutes = $duration;
        $item->origin = 'TEACHER';
        $item->date = $dateKey;
        $item->starts_at = $startsAt->copy();
        $item->ends_at = $startsAt->copy()->addMinutes($duration);
        $item->save();

        $this->syncPositionsForDate($training->id, $dateKey);

        if ($oldDate && $oldDate !== $dateKey) {
            $this->syncPositionsForDate($training->id, $oldDate);
        }

        $timelineService->reflowDay($training->id, $dateKey);

        if ($oldDate && $oldDate !== $dateKey) {
            $timelineService->reflowDay($training->id, $oldDate);
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    public function storeItem(
        StoreTrainingScheduleItemRequest $request,
        Training $training,
        TrainingScheduleTimelineService $timelineService,
    ): JsonResponse {
        $validated = $request->validated();
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        $startsAt = Carbon::createFromFormat('Y-m-d H:i:s', $validated['starts_at'])
            ->setDate($date->year, $date->month, $date->day);

        $duration = (int) $validated['planned_duration_minutes'];

        $training->scheduleItems()->create([
            'section_id' => null,
            'date' => $date->format('Y-m-d'),
            'starts_at' => $startsAt->copy(),
            'ends_at' => $startsAt->copy()->addMinutes($duration),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'planned_duration_minutes' => $duration,
            'suggested_duration_minutes' => null,
            'min_duration_minutes' => null,
            'origin' => 'TEACHER',
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => null,
        ]);

        $this->syncPositionsForDate($training->id, $date->format('Y-m-d'));
        $timelineService->reflowDay($training->id, $date->format('Y-m-d'));

        return response()->json([
            'ok' => true,
        ]);
    }

    public function destroyItem(
        Training $training,
        TrainingScheduleItem $item,
        TrainingScheduleTimelineService $timelineService,
    ): JsonResponse {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        $dateKey = $item->date?->format('Y-m-d');
        $item->delete();

        if ($dateKey) {
            $this->syncPositionsForDate($training->id, $dateKey);
            $timelineService->reflowDay($training->id, $dateKey);
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    private function syncPositionsForDate(int $trainingId, string $dateKey): void
    {
        $items = TrainingScheduleItem::query()
            ->where('training_id', $trainingId)
            ->whereDate('date', $dateKey)
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();

        $position = 1;

        foreach ($items as $item) {
            $item->position = $position;
            $item->save();
            $position++;
        }
    }
}
