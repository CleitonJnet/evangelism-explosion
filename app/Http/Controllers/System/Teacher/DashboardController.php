<?php

namespace App\Http\Controllers\System\Teacher;

use App\Helpers\DayScheduleHelper;
use App\Http\Controllers\Controller;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $teacherId = Auth::id();
        $today = Carbon::today();

        $trainings = Training::query()
            ->where('teacher_id', $teacherId)
            ->whereIn('status', [
                TrainingStatus::Planning->value,
                TrainingStatus::Scheduled->value,
            ])
            ->whereHas('eventDates', function ($query) use ($today): void {
                $query->whereDate('date', '>=', $today);
            })
            ->with([
                'course:id,name,type',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
                'students' => fn ($query) => $query
                    ->select('users.id', 'church_id', 'church_temp_id')
                    ->with('church_temp:id,status'),
            ])
            ->get();

        $operationalTrainings = $this->mapOperationalTrainings($trainings)
            ->sortBy(fn (array $item): string => $item['first_date'] ?? '9999-12-31')
            ->values();

        $pendingTrainings = $operationalTrainings
            ->filter(fn (array $item): bool => $item['has_schedule_issue'] || $item['has_registration_issue'])
            ->values();

        return view('pages.app.roles.teacher.dashboard', [
            'pendingTrainings' => $pendingTrainings,
            'quickAccessTraining' => $operationalTrainings->first(),
        ]);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{
     *   training: Training,
     *   course_label: string,
     *   first_date: string|null,
     *   has_schedule_issue: bool,
     *   has_registration_issue: bool
     * }>
     */
    private function mapOperationalTrainings(Collection $trainings): Collection
    {
        return $trainings->map(function (Training $training): array {
            $dates = $training->eventDates
                ->sortBy(fn ($eventDate): string => (string) $eventDate->date)
                ->values();

            $firstDate = $dates->first()?->date;

            $hasScheduleIssue = ! DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
            $hasRegistrationIssue = $training->students->contains(function ($student): bool {
                $hasNoChurch = $student->church_id === null && $student->church_temp_id === null;
                $hasPendingChurchValidation = $student->church_id === null && $student->church_temp?->status === 'pending';

                return $hasNoChurch || $hasPendingChurchValidation;
            });

            return [
                'training' => $training,
                'course_label' => trim((string) (($training->course?->type ? $training->course->type.' - ' : '').($training->course?->name ?? 'Treinamento'))),
                'first_date' => $firstDate ? Carbon::parse((string) $firstDate)->format('Y-m-d') : null,
                'has_schedule_issue' => $hasScheduleIssue,
                'has_registration_issue' => $hasRegistrationIssue,
            ];
        });
    }
}
