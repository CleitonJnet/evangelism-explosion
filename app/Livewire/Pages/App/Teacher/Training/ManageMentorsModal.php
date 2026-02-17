<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\User;
use App\Services\Training\MentorAssignmentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageMentorsModal extends Component
{
    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public string $userSearch = '';

    public bool $busy = false;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
    }

    #[On('open-manage-mentors-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->training->id) {
            abort(404);
        }

        $this->authorizeTraining($this->training);
        $this->refreshMentors();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->userSearch = '';
    }

    public function addMentor(int $userId, MentorAssignmentService $mentorAssignmentService): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        $mentorUser = User::query()->findOrFail($userId);
        $this->busy = true;

        try {
            $mentorAssignmentService->addMentor($this->training, $mentorUser, $actor);
            $this->refreshMentors();
            $this->dispatch('mentor-assignment-updated');
        } finally {
            $this->busy = false;
        }
    }

    public function removeMentor(int $userId, MentorAssignmentService $mentorAssignmentService): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        $mentorUser = User::query()->findOrFail($userId);
        $this->busy = true;

        try {
            $mentorAssignmentService->removeMentor($this->training, $mentorUser, $actor);
            $this->refreshMentors();
            $this->dispatch('mentor-assignment-updated');
        } finally {
            $this->busy = false;
        }
    }

    public function openCreateMentorUserModal(): void
    {
        $this->authorizeTraining($this->training);
        $this->dispatch('open-create-mentor-user-modal', trainingId: $this->training->id);
    }

    #[On('mentor-user-created')]
    public function handleMentorUserCreated(?int $trainingId = null): void
    {
        if ($trainingId !== null && $trainingId !== $this->training->id) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->refreshMentors();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.manage-mentors-modal', [
            'mentorUsers' => $this->training->mentors,
            'searchResults' => $this->searchResults(),
        ]);
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()
            ->with([
                'mentors' => fn ($query) => $query
                    ->with('church')
                    ->orderBy('name'),
            ])
            ->findOrFail($this->trainingId);

        $this->authorizeTraining($this->training);
    }

    private function refreshMentors(): void
    {
        $this->training = Training::query()
            ->with([
                'mentors' => fn ($query) => $query
                    ->with('church')
                    ->orderBy('name'),
            ])
            ->findOrFail($this->training->id);
    }

    /**
     * @return Collection<int, User>
     */
    private function searchResults(): Collection
    {
        $search = trim($this->userSearch);

        if ($search === '') {
            return new Collection;
        }

        $mentorIds = $this->training->mentors->pluck('id')->all();

        return User::query()
            ->with('church')
            ->whereNotIn('id', $mentorIds)
            ->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-teacher');

        $teacherId = Auth::id();

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }
}
