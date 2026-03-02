<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
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

    public int $perPage = 15;

    public string $churchSearch = '';

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
        return Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->withCount([
                'members as total_members_count',
            ])
            ->orderBy('name')
            ->paginate($this->perPage);
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
                    ->orWhere('city', 'like', '%'.$churchSearch.'%')
                    ->orWhere('state', 'like', '%'.$churchSearch.'%');
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
                    ->orWhere('email', 'like', '%'.$churchSearch.'%');
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
}
