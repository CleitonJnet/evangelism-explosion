<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegenerateTrainingScheduleRequest;
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

        $validated = $request->validated();
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        $startsAt = Carbon::createFromFormat('Y-m-d H:i:s', $validated['starts_at'])
            ->setDate($date->year, $date->month, $date->day);

        $item->date = $date->format('Y-m-d');
        $item->starts_at = $startsAt->copy();
        $item->ends_at = $startsAt->copy()->addMinutes((int) $item->planned_duration_minutes);
        $item->origin = 'TEACHER';
        $item->save();

        $generator->markConflicts(
            $training->scheduleItems()
                ->whereDate('date', $date->format('Y-m-d'))
                ->orderBy('starts_at')
                ->get(),
        );

        return response()->json([
            'ok' => true,
            'item' => $item->fresh(),
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
