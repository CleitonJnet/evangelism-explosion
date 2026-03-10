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

    public int $membersPerPage = 10;

    public int $trainingsPerPage = 8;

    public int $accreditedMembersPerPage = 5;

    public string $memberSearch = '';

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

    public function updatedMemberSearch(): void
    {
        $this->resetPage('membersPage');
    }

    public function clearMemberSearch(): void
    {
        $this->memberSearch = '';
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
        $query = $church->members()->with('roles');

        if ($this->memberSearch !== '') {
            $query->where(function (Builder $query): void {
                $query
                    ->where('name', 'like', '%'.$this->memberSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->memberSearch.'%');
            });
        }

        return $query->getQuery();
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
