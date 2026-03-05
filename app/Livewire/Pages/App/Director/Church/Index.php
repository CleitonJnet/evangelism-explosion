<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $this->authorize('viewAny', Church::class);

        $churches = $this->churchesForDirector();
        $churchSearch = trim($this->churchSearch);

        return view('livewire.pages.app.director.church.index', [
            'churches' => $churches,
            'churchSearchResults' => $this->churchSearchResults($churchSearch),
            'userSearchResults' => $this->userSearchResults($churchSearch),
            'unlinkedUsers' => $this->unlinkedUsersForDirector(),
            'linkableChurches' => $this->linkableChurchesForModal(),
            'selectedUserTrainings' => $this->selectedUserTrainingsForModal(),
        ]);
    }

    #[On('director-church-created')]
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
        $this->authorize('manageChurches');

        $user = User::query()
            ->whereKey($userId)
            ->whereNull('church_id')
            ->whereNull('church_temp_id')
            ->firstOrFail();

        if ((int) Auth::id() === $user->id) {
            abort(403);
        }

        $user->delete();

        session()->flash('success', __('Usuário removido com sucesso.'));
    }

    public function openUnlinkedUserModal(int $userId): void
    {
        $this->authorize('manageChurches');

        $user = $this->unlinkedUsersQueryForDirector()
            ->whereKey($userId)
            ->firstOrFail();

        $this->selectedUnlinkedUserId = $user->id;
        $this->selectedUnlinkedUserName = $user->name;
        $this->selectedUnlinkedUserEmail = (string) $user->email;
        $this->selectedUnlinkedUserPhone = (string) ($user->phone ?? '');
        $this->selectedUnlinkedUserCity = (string) ($user->city ?? '');
        $this->selectedUnlinkedUserState = (string) ($user->state ?? '');
        $this->linkChurchSearch = '';
        $this->linkChurchId = $this->linkableChurchesForModal()->first()?->id;
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
        $this->linkChurchId = $this->linkableChurchesForModal()->first()?->id;
    }

    public function associateChurchToSelectedUser(): void
    {
        $this->authorize('manageChurches');

        $this->validate([
            'selectedUnlinkedUserId' => ['required', 'integer', 'exists:users,id'],
            'linkChurchId' => ['required', 'integer', 'exists:churches,id'],
        ]);

        $user = $this->unlinkedUsersQueryForDirector()
            ->whereKey((int) $this->selectedUnlinkedUserId)
            ->firstOrFail();

        $church = Church::query()
            ->whereKey((int) $this->linkChurchId)
            ->firstOrFail();

        $user->forceFill([
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
        $churches = $this->churchesForDirector();

        return $churches->isEmpty() && $churches->currentPage() > 1;
    }

    private function churchesForDirector(): LengthAwarePaginator
    {
        $query = Church::query()
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
     * @return EloquentCollection<int, Church>
     */
    private function churchSearchResults(string $churchSearch): EloquentCollection
    {
        if ($churchSearch === '') {
            return new EloquentCollection;
        }

        return Church::query()
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
    private function userSearchResults(string $churchSearch): EloquentCollection
    {
        if ($churchSearch === '') {
            return new EloquentCollection;
        }

        return User::query()
            ->with('church')
            ->whereNotNull('church_id')
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

    private function unlinkedUsersForDirector(): LengthAwarePaginator
    {
        return $this->unlinkedUsersQueryForDirector()
            ->orderBy('name')
            ->paginate($this->perPage, ['*'], 'unlinkedUsersPage');
    }

    private function unlinkedUsersQueryForDirector(): Builder
    {
        return User::query()
            ->whereNull('church_id')
            ->whereNull('church_temp_id');
    }

    /**
     * @return EloquentCollection<int, Church>
     */
    private function linkableChurchesForModal(): EloquentCollection
    {
        $churchSearch = trim($this->linkChurchSearch);

        return Church::query()
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
    private function selectedUserTrainingsForModal(): EloquentCollection
    {
        if ($this->selectedUnlinkedUserId === null) {
            return new EloquentCollection;
        }

        return Training::query()
            ->with(['course:id,name,type', 'church:id,name'])
            ->whereHas('students', function ($query): void {
                $query->whereKey((int) $this->selectedUnlinkedUserId);
            })
            ->orderByDesc('id')
            ->get();
    }
}
