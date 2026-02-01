<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegenerateTrainingScheduleRequest;
use App\Http\Requests\StoreTrainingScheduleItemRequest;
use App\Http\Requests\UpdateTrainingScheduleItemRequest;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TrainingScheduleController extends Controller
{
    public function regenerate(
        RegenerateTrainingScheduleRequest $request,
        Training $training,
        TrainingScheduleGenerator $generator,
    ): JsonResponse {
        $mode = $request->validated('mode') ?? 'AUTO_ONLY';

        $result = $generator->generate($training, $mode);

        return response()->json([
            'ok' => true,
            'generated_count' => $result->items->count(),
            'unallocated_count' => $result->unallocated->count(),
        ]);
    }

    public function updateItem(
        UpdateTrainingScheduleItemRequest $request,
        Training $training,
        TrainingScheduleItem $item,
        TrainingScheduleGenerator $generator,
    ): JsonResponse {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        if ($item->is_locked) {
            abort(403);
        }

        $validated = $request->validated();
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        $startsAt = Carbon::createFromFormat('Y-m-d H:i:s', $validated['starts_at'])
            ->setDate($date->year, $date->month, $date->day);

        if (! array_key_exists('planned_duration_minutes', $validated)) {
            $startsAt = $startsAt->copy()->subSecond();
        }

        $duration = (int) ($validated['planned_duration_minutes'] ?? $item->planned_duration_minutes);

        if (array_key_exists('title', $validated)) {
            $item->title = $validated['title'];
        }

        if (array_key_exists('type', $validated)) {
            $item->type = $validated['type'];
        }

        $item->planned_duration_minutes = $duration;
        $item->origin = 'TEACHER';
        $item->date = $date->format('Y-m-d');
        $item->starts_at = $startsAt->copy();
        $item->ends_at = $startsAt->copy()->addMinutes($duration);
        $item->save();

        $generator->generate($training, 'AUTO_ONLY');

        return response()->json([
            'ok' => true,
        ]);
    }

    public function storeItem(
        StoreTrainingScheduleItemRequest $request,
        Training $training,
        TrainingScheduleGenerator $generator,
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
            'is_locked' => false,
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => null,
        ]);

        $generator->generate($training, 'AUTO_ONLY');

        return response()->json([
            'ok' => true,
        ]);
    }

    public function destroyItem(
        Training $training,
        TrainingScheduleItem $item,
        TrainingScheduleGenerator $generator,
    ): JsonResponse {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        $item->delete();

        $generator->generate($training, 'AUTO_ONLY');

        return response()->json([
            'ok' => true,
        ]);
    }

    public function lock(Training $training, TrainingScheduleItem $item): JsonResponse
    {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        $item->is_locked = true;
        $item->save();

        return response()->json([
            'ok' => true,
            'item' => $item->fresh(),
        ]);
    }

    public function unlock(Training $training, TrainingScheduleItem $item): JsonResponse
    {
        if ($item->training_id !== $training->id) {
            abort(404);
        }

        $item->is_locked = false;
        $item->save();

        return response()->json([
            'ok' => true,
            'item' => $item->fresh(),
        ]);
    }
}
