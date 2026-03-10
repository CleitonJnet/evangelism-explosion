<?php

namespace App\Services\Training;

use App\Enums\StpApproachStatus;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\Services\Metrics\TrainingStpMetricsService;
use App\Support\TrainingAccess\TrainingVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MentorTrainingOverviewService
{
    public function __construct(
        private TrainingVisibilityScope $visibilityScope,
        private TrainingStpMetricsService $stpMetrics,
    ) {}

    public function mentorTrainingsQuery(User $mentor): Builder
    {
        return Training::query()
            ->withMin('eventDates', 'date')
            ->with([
                'course.ministry',
                'teacher',
                'church',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->withCount([
                'stpSessions as mentor_sessions_count' => fn (Builder $query) => $query
                    ->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->where('mentor_user_id', $mentor->id)),
                'stpSessions as mentor_completed_sessions_count' => fn (Builder $query) => $query
                    ->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->where('mentor_user_id', $mentor->id))
                    ->whereHas('approaches', fn (Builder $approachQuery) => $approachQuery
                        ->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('mentor_user_id', $mentor->id))
                        ->whereIn('status', [
                            StpApproachStatus::Done->value,
                            StpApproachStatus::Reviewed->value,
                        ])),
                'stpSessions as mentor_teams_count' => fn (Builder $query) => $query
                    ->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->where('mentor_user_id', $mentor->id)),
            ])
            ->tap(fn (Builder $query) => $this->visibilityScope->apply($query, $mentor))
            ->whereHas('mentors', fn (Builder $query) => $query->whereKey($mentor->id))
            ->orderBy('event_dates_min_date')
            ->orderByDesc('id');
    }

    /**
     * @return Collection<int, Training>
     */
    public function mentorTrainings(User $mentor): Collection
    {
        return $this->mentorTrainingsQuery($mentor)->get();
    }

    /**
     * @return Collection<int, StpSession>
     */
    public function mentorSessions(User $mentor, ?Training $training = null): Collection
    {
        return $this->mentorSessionsQuery($mentor, $training)->get();
    }

    public function mentorSessionsQuery(User $mentor, ?Training $training = null): Builder
    {
        return StpSession::query()
            ->when($training !== null, fn (Builder $query) => $query->where('training_id', $training->id))
            ->whereHas('teams', fn (Builder $query) => $query->where('mentor_user_id', $mentor->id))
            ->with([
                'training.course.ministry',
                'training.teacher',
                'training.church',
                'teams' => fn ($query) => $query
                    ->where('mentor_user_id', $mentor->id)
                    ->with([
                        'mentor',
                        'students',
                        'approaches' => fn ($approachQuery) => $approachQuery
                            ->orderBy('position')
                            ->orderBy('id'),
                    ]),
            ])
            ->orderBy('starts_at')
            ->orderBy('ends_at')
            ->orderBy('sequence')
            ->orderBy('id');
    }

    /**
     * @return array{
     *     trainings_count: int,
     *     next_sessions: Collection<int, StpSession>,
     *     teams_count: int,
     *     completed_sessions_count: int,
     *     approaches_summary: array{
     *         total: int,
     *         concluidas: int,
     *         revisadas: int,
     *         decisoes: int,
     *         acompanhamentos: int
     *     }
     * }
     */
    public function dashboardData(User $mentor): array
    {
        $visibleTrainingIds = $this->mentorTrainingsQuery($mentor)->pluck('trainings.id');

        $nextSessions = $this->mentorSessionsQuery($mentor)
            ->limit(6)
            ->get();

        $teamsCount = StpTeam::query()
            ->where('mentor_user_id', $mentor->id)
            ->whereHas('session.training', fn (Builder $query) => $query->whereKey($visibleTrainingIds))
            ->count();

        $completedSessionsCount = StpSession::query()
            ->whereKey(
                StpApproach::query()
                    ->select('stp_session_id')
                    ->whereHas('team', fn (Builder $query) => $query->where('mentor_user_id', $mentor->id))
                    ->whereIn('status', [
                        StpApproachStatus::Done->value,
                        StpApproachStatus::Reviewed->value,
                    ])
                    ->distinct(),
            )
            ->count();

        $approaches = $this->mentorApproachesQuery($mentor)->get();

        return [
            'trainings_count' => $visibleTrainingIds->count(),
            'next_sessions' => $nextSessions,
            'teams_count' => $teamsCount,
            'completed_sessions_count' => $completedSessionsCount,
            'approaches_summary' => $this->stpMetrics->summarizeApproaches($approaches),
        ];
    }

    /**
     * @return array{
     *     sessions_count: int,
     *     teams_count: int,
     *     students_count: int,
     *     approaches_summary: array{
     *         total: int,
     *         concluidas: int,
     *         revisadas: int,
     *         decisoes: int,
     *         acompanhamentos: int
     *     }
     * }
     */
    public function trainingSummary(User $mentor, Training $training): array
    {
        $sessions = $this->mentorSessions($mentor, $training);
        $teams = $sessions
            ->flatMap(fn (StpSession $session) => $session->teams)
            ->values();
        $studentsCount = $teams
            ->flatMap(fn (StpTeam $team) => $team->students)
            ->unique('id')
            ->count();
        $approaches = $teams
            ->flatMap(fn (StpTeam $team) => $team->approaches)
            ->values();

        return [
            'sessions_count' => $sessions->count(),
            'teams_count' => $teams->count(),
            'students_count' => $studentsCount,
            'approaches_summary' => $this->stpMetrics->summarizeApproaches($approaches),
        ];
    }

    public function mentorApproachesQuery(User $mentor, ?Training $training = null): Builder
    {
        return StpApproach::query()
            ->when($training !== null, fn (Builder $query) => $query->where('training_id', $training->id))
            ->whereHas('team', fn (Builder $query) => $query->where('mentor_user_id', $mentor->id))
            ->with(['session', 'team', 'training.course'])
            ->orderByDesc('follow_up_scheduled_at')
            ->orderBy('position')
            ->orderBy('id');
    }
}
