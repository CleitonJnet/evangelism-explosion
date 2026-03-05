<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public int $perPage = 5;

    public string $churchSearch = '';

    public string $sortField = 'church';

    public string $sortDirection = 'asc';

    public bool $showUnlinkedUserModal = false;

    public ?int $selectedUnlinkedUserId = null;

    public string $selectedUnlinkedUserName = '';

    public string $selectedUnlinkedUserEmail = '';

    public string $selectedUnlinkedUserPhone = '';

    public string $selectedUnlinkedUserCity = '';

    public string $selectedUnlinkedUserState = '';

    public string $linkChurchSearch = '';

    public ?int $linkChurchId = null;

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $this->authorize('viewAny', Church::class);

        $accessibleChurchIds = $this->teacherAccessibleChurchIds($user);
        $churches = $this->churchesForTeacher($accessibleChurchIds);
        $churchSearch = trim($this->churchSearch);

        return view('livewire.pages.app.teacher.church.index', [
            'churches' => $churches,
            'churchSearchResults' => $this->churchSearchResults($accessibleChurchIds, $churchSearch),
            'userSearchResults' => $this->userSearchResults($accessibleChurchIds, $churchSearch),
            'unlinkedUsers' => $this->unlinkedUsersForTeacher($user),
            'linkableChurches' => $this->linkableChurchesForModal($accessibleChurchIds),
            'selectedUserTrainings' => $this->selectedUserTrainingsForModal($user),
        ]);
    }

    #[On('teacher-church-created')]
    public function handleChurchCreated(?int $churchId = null, ?string $churchName = null): void
    {
        $this->churchSearch = trim((string) $churchName);
    }

    public function removeChurch(int $churchId): void
    {
        $church = Church::query()->findOrFail($churchId);

        $this->authorize('delete', $church);

        $church->delete();

        if ($this->isCurrentPageEmpty()) {
            $this->previousPage();
        }

        session()->flash('success', __('Igreja removida com sucesso.'));
    }

    public function removeUnlinkedUser(int $userId): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $this->authorize('manageChurches');

        if ((int) $user->id === $userId) {
            abort(403);
        }

        $userToDelete = User::query()
            ->whereKey($userId)
            ->whereNull('users.church_id')
            ->whereNull('users.church_temp_id')
            ->whereExists(function ($query) use ($user): void {
                $query->selectRaw('1')
                    ->from('training_user')
                    ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
                    ->whereColumn('training_user.user_id', 'users.id')
                    ->where('trainings.teacher_id', $user->id);
            })
            ->firstOrFail();

        $userToDelete->delete();

        session()->flash('success', __('Usuário removido com sucesso.'));
    }

    public function openUnlinkedUserModal(int $userId): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        $this->authorize('manageChurches');

        $userToEdit = $this->unlinkedUsersQueryForTeacher($user)
            ->whereKey($userId)
            ->firstOrFail();

        $accessibleChurchIds = $this->teacherAccessibleChurchIds($user);

        $this->selectedUnlinkedUserId = $userToEdit->id;
        $this->selectedUnlinkedUserName = $userToEdit->name;
        $this->selectedUnlinkedUserEmail = (string) $userToEdit->email;
        $this->selectedUnlinkedUserPhone = (string) ($userToEdit->phone ?? '');
        $this->selectedUnlinkedUserCity = (string) ($userToEdit->city ?? '');
        $this->selectedUnlinkedUserState = (string) ($userToEdit->state ?? '');
        $this->linkChurchSearch = '';
        $this->linkChurchId = $this->linkableChurchesForModal($accessibleChurchIds)->first()?->id;
        $this->showUnlinkedUserModal = true;
    }

    public function closeUnlinkedUserModal(): void
    {
        $this->showUnlinkedUserModal = false;
        $this->selectedUnlinkedUserId = null;
        $this->selectedUnlinkedUserName = '';
        $this->selectedUnlinkedUserEmail = '';
        $this->selectedUnlinkedUserPhone = '';
        $this->selectedUnlinkedUserCity = '';
        $this->selectedUnlinkedUserState = '';
        $this->linkChurchSearch = '';
        $this->linkChurchId = null;
    }

    public function updatedLinkChurchSearch(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $accessibleChurchIds = $this->teacherAccessibleChurchIds($user);
        $this->linkChurchId = $this->linkableChurchesForModal($accessibleChurchIds)->first()?->id;
    }

    public function associateChurchToSelectedUser(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        $this->authorize('manageChurches');

        $this->validate([
            'selectedUnlinkedUserId' => ['required', 'integer', 'exists:users,id'],
            'linkChurchId' => ['required', 'integer', 'exists:churches,id'],
        ]);

        $userToUpdate = $this->unlinkedUsersQueryForTeacher($user)
            ->whereKey((int) $this->selectedUnlinkedUserId)
            ->firstOrFail();

        $accessibleChurchIds = $this->teacherAccessibleChurchIds($user);
        $church = Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->whereKey((int) $this->linkChurchId)
            ->firstOrFail();

        $userToUpdate->forceFill([
            'church_id' => $church->id,
            'church_temp_id' => null,
        ])->save();

        $this->closeUnlinkedUserModal();
        session()->flash('success', __('Igreja vinculada ao usuário com sucesso.'));
    }

    public function sortBy(string $field): void
    {
        $allowedFields = ['index', 'church', 'contact', 'location', 'members', 'accredited'];

        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    private function isCurrentPageEmpty(): bool
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $churches = $this->churchesForTeacher($this->teacherAccessibleChurchIds($user));

        return $churches->isEmpty() && $churches->currentPage() > 1;
    }

    /**
     * @param  array<int, int>  $accessibleChurchIds
     */
    private function churchesForTeacher(array $accessibleChurchIds): LengthAwarePaginator
    {
        $query = Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->withCount([
                'members as total_members_count',
            ])
            ->selectSub(function ($query): void {
                $query->from('users')
                    ->selectRaw('count(distinct users.id)')
                    ->whereColumn('users.church_id', 'churches.id')
                    ->whereExists(function ($query): void {
                        $query->selectRaw('1')
                            ->from('role_user')
                            ->join('roles', 'roles.id', '=', 'role_user.role_id')
                            ->whereColumn('role_user.user_id', 'users.id')
                            ->whereRaw('LOWER(roles.name) = ?', ['facilitator']);
                    });
            }, 'total_accredited_members_count');

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        match ($this->sortField) {
            'church' => $query
                ->orderBy('name', $sortDirection)
                ->orderBy('pastor', $sortDirection)
                ->orderBy('id', $sortDirection),
            'contact' => $query
                ->orderBy('contact', $sortDirection)
                ->orderBy('contact_email', $sortDirection)
                ->orderBy('id', $sortDirection),
            'location' => $query
                ->orderBy('state', $sortDirection)
                ->orderBy('city', $sortDirection)
                ->orderBy('id', $sortDirection),
            'members' => $query
                ->orderBy('total_members_count', $sortDirection)
                ->orderBy('id', $sortDirection),
            'accredited' => $query
                ->orderBy('total_accredited_members_count', $sortDirection)
                ->orderBy('id', $sortDirection),
            default => $query->orderBy('id', $sortDirection),
        };

        return $query->paginate($this->perPage);
    }

    /**
     * @param  array<int, int>  $accessibleChurchIds
     * @return EloquentCollection<int, Church>
     */
    private function churchSearchResults(array $accessibleChurchIds, string $churchSearch): EloquentCollection
    {
        if ($churchSearch === '') {
            return new EloquentCollection;
        }

        return Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->where(function ($query) use ($churchSearch): void {
                $query->where('name', 'like', '%'.$churchSearch.'%')
                    ->orWhere('pastor', 'like', '%'.$churchSearch.'%')
                    ->orWhere('city', 'like', '%'.$churchSearch.'%')
                    ->orWhere('state', 'like', '%'.$churchSearch.'%')
                    ->orWhere('contact_email', 'like', '%'.$churchSearch.'%');
            })
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /**
     * @return EloquentCollection<int, User>
     */
    private function userSearchResults(array $accessibleChurchIds, string $churchSearch): EloquentCollection
    {
        if ($churchSearch === '' || $accessibleChurchIds === []) {
            return new EloquentCollection;
        }

        return User::query()
            ->with('church')
            ->whereIn('church_id', $accessibleChurchIds)
            ->where(function ($query) use ($churchSearch): void {
                $query->where('name', 'like', '%'.$churchSearch.'%')
                    ->orWhere('email', 'like', '%'.$churchSearch.'%')
                    ->orWhere('city', 'like', '%'.$churchSearch.'%')
                    ->orWhere('state', 'like', '%'.$churchSearch.'%');
            })
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /**
     * @return array<int, int>
     */
    private function teacherAccessibleChurchIds(User $user): array
    {
        $churchIds = collect([$user->church_id])->filter();

        $trainingChurchIds = $this->trainingChurchIds($user);
        $studentChurchIds = $this->trainingStudentChurchIds($user);
        $missionaryChurchIds = $this->missionaryChurchIds($user);

        return $churchIds
            ->merge($trainingChurchIds)
            ->merge($studentChurchIds)
            ->merge($missionaryChurchIds)
            ->map(static fn ($churchId): int => (int) $churchId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, int>
     */
    private function trainingChurchIds(User $user): Collection
    {
        return Training::query()
            ->where('teacher_id', $user->id)
            ->whereNotNull('church_id')
            ->pluck('church_id');
    }

    /**
     * @return Collection<int, int>
     */
    private function trainingStudentChurchIds(User $user): Collection
    {
        return User::query()
            ->select('users.church_id')
            ->join('training_user', 'training_user.user_id', '=', 'users.id')
            ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
            ->where('trainings.teacher_id', $user->id)
            ->whereNotNull('users.church_id')
            ->distinct()
            ->pluck('users.church_id');
    }

    /**
     * @return Collection<int, int>
     */
    private function missionaryChurchIds(User $user): Collection
    {
        return Church::query()
            ->select('churches.id')
            ->join('church_missionary', 'church_missionary.church_id', '=', 'churches.id')
            ->where('church_missionary.user_id', $user->id)
            ->pluck('churches.id');
    }

    private function unlinkedUsersForTeacher(User $user): LengthAwarePaginator
    {
        return $this->unlinkedUsersQueryForTeacher($user)
            ->select('users.*')
            ->selectSub(function ($query) use ($user): void {
                $query->from('training_user')
                    ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
                    ->selectRaw('count(distinct training_user.training_id)')
                    ->whereColumn('training_user.user_id', 'users.id')
                    ->where('trainings.teacher_id', $user->id);
            }, 'teacher_training_registrations_count')
            ->orderBy('users.name')
            ->paginate($this->perPage, ['*'], 'unlinkedUsersPage');
    }

    private function unlinkedUsersQueryForTeacher(User $user): Builder
    {
        return User::query()
            ->whereNull('users.church_id')
            ->whereNull('users.church_temp_id')
            ->whereExists(function ($query) use ($user): void {
                $query->selectRaw('1')
                    ->from('training_user')
                    ->join('trainings', 'trainings.id', '=', 'training_user.training_id')
                    ->whereColumn('training_user.user_id', 'users.id')
                    ->where('trainings.teacher_id', $user->id);
            });
    }

    /**
     * @param  array<int, int>  $accessibleChurchIds
     * @return EloquentCollection<int, Church>
     */
    private function linkableChurchesForModal(array $accessibleChurchIds): EloquentCollection
    {
        if ($accessibleChurchIds === []) {
            return new EloquentCollection;
        }

        $churchSearch = trim($this->linkChurchSearch);

        return Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->when($churchSearch !== '', function ($query) use ($churchSearch): void {
                $query->where(function ($query) use ($churchSearch): void {
                    $query->where('name', 'like', '%'.$churchSearch.'%')
                        ->orWhere('city', 'like', '%'.$churchSearch.'%')
                        ->orWhere('state', 'like', '%'.$churchSearch.'%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    /**
     * @return EloquentCollection<int, Training>
     */
    private function selectedUserTrainingsForModal(User $user): EloquentCollection
    {
        if ($this->selectedUnlinkedUserId === null) {
            return new EloquentCollection;
        }

        return Training::query()
            ->with(['course:id,name,type', 'church:id,name'])
            ->where('teacher_id', $user->id)
            ->whereHas('students', function ($query): void {
                $query->whereKey((int) $this->selectedUnlinkedUserId);
            })
            ->orderByDesc('id')
            ->get();
    }
}
