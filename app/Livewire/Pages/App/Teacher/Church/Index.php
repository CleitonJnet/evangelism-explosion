<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

        $churches = $this->churchesForTeacher($user);

        return view('livewire.pages.app.teacher.church.index', [
            'churches' => $churches,
        ]);
    }

    public function updatedChurchSearch(): void
    {
        $this->resetPage();
    }

    #[On('teacher-church-created')]
    public function handleChurchCreated(?int $churchId = null, ?string $churchName = null): void
    {
        $this->churchSearch = trim((string) $churchName);
        $this->resetPage();
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

        $churches = $this->churchesForTeacher($user);

        return $churches->isEmpty() && $churches->currentPage() > 1;
    }

    private function churchesForTeacher(User $user): LengthAwarePaginator
    {
        $accessibleChurchIds = $this->teacherAccessibleChurchIds($user);

        return Church::query()
            ->whereIn('id', $accessibleChurchIds)
            ->when($this->churchSearch !== '', function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->churchSearch.'%');
            })
            ->withCount([
                'members as total_members_count',
            ])
            ->orderBy('name')
            ->paginate($this->perPage);
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
