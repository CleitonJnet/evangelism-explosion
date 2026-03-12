<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View as ViewContract;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Church $church;

    public int $membersPerPage = 5;

    public int $trainingsPerPage = 8;

    public int $accreditedMembersPerPage = 5;

    public string $memberSearch = '';

    public string $memberSortField = 'name';

    public string $memberSortDirection = 'asc';

    public function mount(Church $church): void
    {
        $this->church = $church;
        $this->authorize('view', $church);
    }

    #[On('teacher-church-updated')]
    public function handleChurchUpdated(?int $churchId = null): void
    {
        if ($churchId !== null && $churchId !== $this->church->id) {
            return;
        }

        $this->church = $this->church->fresh();
    }

    #[On('teacher-church-participant-created')]
    public function handleParticipantCreated(?int $churchId = null): void
    {
        if ($churchId !== null && $churchId !== $this->church->id) {
            return;
        }

        $this->church = $this->church->fresh();
        $this->resetPage('membersPage');
    }

    public function updatedMemberSearch(): void
    {
        $this->resetPage('membersPage');
    }

    public function clearMemberSearch(): void
    {
        $this->memberSearch = '';
        $this->resetPage('membersPage');
    }

    public function sortMembersBy(string $field): void
    {
        if (! in_array($field, ['name', 'profile', 'courses'], true)) {
            return;
        }

        if ($this->memberSortField === $field) {
            $this->memberSortDirection = $this->memberSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->memberSortField = $field;
            $this->memberSortDirection = $this->defaultDirectionForMemberSort($field);
        }

        $this->resetPage('membersPage');
    }

    public function render(): ViewContract
    {
        $teacherId = Auth::id();

        $church = Church::query()
            ->withCount(['missionaries'])
            ->findOrFail($this->church->id);

        $totalMembersCount = (clone $church->members()->getQuery())->count();

        $members = $this->membersQuery($church)
            ->orderBy('name')
            ->paginate($this->membersPerPage, pageName: 'membersPage');

        $trainings = $this->trainingsQuery($church)
            ->orderByRaw('event_dates_min_date is null')
            ->orderByDesc('event_dates_min_date')
            ->orderByDesc('id')
            ->paginate($this->trainingsPerPage, pageName: 'trainingsPage');

        $churchTrainingsCount = (clone $this->indicatorTrainingsQuery($church))->count();
        $teacherTrainingsCount = (clone $this->indicatorTrainingsQuery($church))
            ->where('teacher_id', $teacherId)
            ->count();
        $leaderCoursesWithAccreditedMembers = $this->leaderCoursesWithAccreditedMembers($church);

        $pastorMembersCount = (clone $church->members()->getQuery())
            ->where('is_pastor', 1)
            ->count();

        return view('livewire.pages.app.teacher.church.view', [
            'church' => $church,
            'totalMembersCount' => $totalMembersCount,
            'members' => $members,
            'memberSortField' => $this->memberSortField,
            'memberSortDirection' => $this->memberSortDirection,
            'trainings' => $trainings,
            'churchTrainingsCount' => $churchTrainingsCount,
            'teacherTrainingsCount' => $teacherTrainingsCount,
            'pastorMembersCount' => $pastorMembersCount,
            'leaderCoursesWithAccreditedMembers' => $leaderCoursesWithAccreditedMembers,
            'totalAccreditedMembersInLeaderCourses' => $this->totalAccreditedMembersInLeaderCourses($church),
            'logoUrl' => $this->logoUrl($church),
        ]);
    }

    private function membersQuery(Church $church): Builder
    {
        $query = $church->members()
            ->select('users.*')
            ->selectSub(function ($query): void {
                $query->from('training_user')
                    ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
                    ->selectRaw('count(distinct trainings.course_id)')
                    ->whereColumn('training_user.user_id', 'users.id');
            }, 'member_courses_count')
            ->with([
                'roles:id,name',
                'trainings.course:id,name,initials,color,type',
            ]);

        if ($this->memberSearch !== '') {
            $query->where(function (Builder $query): void {
                $query
                    ->where('name', 'like', '%'.$this->memberSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->memberSearch.'%');
            });
        }

        return $this->applyMembersSorting($query->getQuery());
    }

    private function applyMembersSorting(Builder $query): Builder
    {
        $direction = $this->memberSortDirection;

        return match ($this->memberSortField) {
            'profile' => $query
                ->orderByRaw(
                    "case
                        when exists (
                            select 1
                            from role_user
                            inner join roles on roles.id = role_user.role_id
                            where role_user.user_id = users.id
                              and lower(roles.name) = ?
                        ) then 2
                        when users.is_pastor = 1 then 1
                        else 0
                    end {$direction}",
                    ['facilitator'],
                )
                ->orderBy('name'),
            'courses' => $query
                ->orderBy('member_courses_count', $direction)
                ->orderBy('name'),
            default => $query
                ->orderBy('name', $direction)
                ->orderBy('email'),
        };
    }

    private function defaultDirectionForMemberSort(string $field): string
    {
        return in_array($field, ['profile', 'courses'], true) ? 'desc' : 'asc';
    }

    private function trainingsQuery(Church $church): Builder
    {
        return Training::query()
            ->with([
                'course',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->withMin('eventDates', 'date')
            ->where('church_id', $church->id);
    }

    private function indicatorTrainingsQuery(Church $church): Builder
    {
        return Training::query()
            ->where('church_id', $church->id)
            ->whereIn('status', [
                TrainingStatus::Scheduled->value,
                TrainingStatus::Completed->value,
            ]);
    }

    /**
     * @return Collection<int, array{course: Course, accreditedMembers: LengthAwarePaginator}>
     */
    private function leaderCoursesWithAccreditedMembers(Church $church): Collection
    {
        $courses = Course::query()
            ->select('courses.*')
            ->join('trainings', 'trainings.course_id', '=', 'courses.id')
            ->where('trainings.church_id', $church->id)
            ->where('courses.execution', 0)
            ->whereExists(function ($query) use ($church): void {
                $query->selectRaw('1')
                    ->from('training_user')
                    ->join('users', 'users.id', '=', 'training_user.user_id')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('training_user.training_id', 'trainings.id')
                    ->where('users.church_id', $church->id)
                    ->whereRaw('LOWER(roles.name) = ?', ['facilitator']);
            })
            ->distinct()
            ->orderBy('courses.name')
            ->get();

        return $courses->map(function (Course $course) use ($church): array {
            $pageName = 'accreditedMembersCourse'.$course->id.'Page';

            return [
                'course' => $course,
                'accreditedMembers' => $this->accreditedMembersInCourseQuery($church, $course)
                    ->orderBy('name')
                    ->paginate($this->accreditedMembersPerPage, pageName: $pageName),
            ];
        });
    }

    private function totalAccreditedMembersInLeaderCourses(Church $church): int
    {
        return $this->facilitatorMembersQuery($church)
            ->count();
    }

    private function accreditedMembersInCourseQuery(Church $church, Course $course): Builder
    {
        return User::query()
            ->whereIn('users.id', function ($query) use ($church, $course): void {
                $query->select('users.id')
                    ->from('users')
                    ->join('training_user', 'training_user.user_id', '=', 'users.id')
                    ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
                    ->join('courses', 'courses.id', '=', 'trainings.course_id')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('trainings.church_id', $church->id)
                    ->where('users.church_id', $church->id)
                    ->where('courses.id', $course->id)
                    ->where('courses.execution', 0)
                    ->whereRaw('LOWER(roles.name) = ?', ['facilitator'])
                    ->distinct();
            });
    }

    private function facilitatorMembersQuery(Church $church): Builder
    {
        return User::query()
            ->whereIn('users.id', function ($query) use ($church): void {
                $query->select('users.id')
                    ->from('users')
                    ->where('users.church_id', $church->id)
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereRaw('LOWER(roles.name) = ?', ['facilitator'])
                    ->distinct();
            });
    }

    private function logoUrl(Church $church): string
    {
        $logoPath = trim((string) $church->getRawOriginal('logo'));

        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->url($logoPath);
        }

        return asset('images/svg/church.svg');
    }
}
