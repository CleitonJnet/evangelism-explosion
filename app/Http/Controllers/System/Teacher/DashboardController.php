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

        $upcomingTrainings = $this->mapOperationalTrainings($trainings)
            ->sortBy(fn (array $item): string => $item['first_date'] ?? '9999-12-31')
            ->take(5)
            ->values();

        $pendingTrainings = $this->mapOperationalTrainings(
            $trainings->filter(function (Training $training): bool {
                $hasScheduleIssue = ! DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
                $hasRegistrationIssue = $training->students->contains(function ($student): bool {
                    $hasNoChurch = $student->church_id === null && $student->church_temp_id === null;
                    $hasPendingChurchValidation = $student->church_id === null && $student->church_temp?->status === 'pending';

                    return $hasNoChurch || $hasPendingChurchValidation;
                });

                return $hasScheduleIssue || $hasRegistrationIssue;
            }),
        )->values();

        return view('pages.app.roles.teacher.dashboard', [
            'upcomingTrainings' => $upcomingTrainings,
            'pendingTrainings' => $pendingTrainings,
            'quickAccessTraining' => $upcomingTrainings->first(),
        ]);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, array{
     *   training: Training,
     *   course_label: string,
     *   first_date: string|null,
     *   date_range_label: string,
     *   status_label: string,
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
            $lastDate = $dates->last()?->date;

            $firstDateLabel = $firstDate ? Carbon::parse((string) $firstDate)->format('d/m/Y') : 'Data a definir';
            $lastDateLabel = $lastDate ? Carbon::parse((string) $lastDate)->format('d/m/Y') : null;
            $dateRangeLabel = $lastDateLabel && $lastDateLabel !== $firstDateLabel
                ? $firstDateLabel.' a '.$lastDateLabel
                : $firstDateLabel;

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
                'date_range_label' => $dateRangeLabel,
                'status_label' => $training->status?->label() ?? TrainingStatus::Planning->label(),
                'has_schedule_issue' => $hasScheduleIssue,
                'has_registration_issue' => $hasRegistrationIssue,
            ];
        });
    }
}
