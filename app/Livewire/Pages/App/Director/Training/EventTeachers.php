<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EventTeachers extends Component
{
    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $showPrincipalChangeConfirmation = false;

    public ?int $confirmedPrincipalTeacherId = null;

    public ?int $pendingPrincipalTeacherId = null;

    public ?int $teacherId = null;

    /**
     * @var array<int, int>
     */
    public array $assistantTeacherIds = [];

    public string $assistantSearch = '';

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
        $this->fillFormFromTraining();
    }

    #[On('open-manage-training-teachers-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->training->id) {
            abort(404);
        }

        $this->authorizeTraining($this->training);
        $this->refreshTraining();
        $this->fillFormFromTraining();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showPrincipalChangeConfirmation = false;
        $this->assistantSearch = '';
        $this->resetErrorBag();
        $this->fillFormFromTraining();
    }

    public function requestSave(): mixed
    {
        $this->authorizeTraining($this->training);

        $validated = $this->validate($this->rules(), $this->messages());

        $newTeacherId = (int) $validated['teacherId'];

        if ((int) $this->training->teacher_id !== $newTeacherId && $this->confirmedPrincipalTeacherId !== $newTeacherId) {
            $this->showPrincipalChangeConfirmation = true;
            $this->pendingPrincipalTeacherId = $newTeacherId;

            return null;
        }

        return $this->persistChanges($validated);
    }

    public function requestPrincipalTeacherChange(mixed $teacherId): void
    {
        $selectedTeacherId = is_numeric((string) $teacherId) ? (int) $teacherId : null;

        if ($selectedTeacherId === null) {
            return;
        }

        if ((int) $this->training->teacher_id === $selectedTeacherId) {
            $this->teacherId = $selectedTeacherId;
            $this->confirmedPrincipalTeacherId = $selectedTeacherId;
            $this->pendingPrincipalTeacherId = null;
            $this->showPrincipalChangeConfirmation = false;
            $this->removePrincipalFromAssistantTeachers($selectedTeacherId);

            return;
        }

        $this->pendingPrincipalTeacherId = $selectedTeacherId;
        $this->showPrincipalChangeConfirmation = true;
    }

    public function confirmPrincipalTeacherChange(): void
    {
        if ($this->pendingPrincipalTeacherId === null) {
            $this->showPrincipalChangeConfirmation = false;

            return;
        }

        $this->teacherId = $this->pendingPrincipalTeacherId;
        $this->confirmedPrincipalTeacherId = $this->pendingPrincipalTeacherId;
        $this->removePrincipalFromAssistantTeachers($this->pendingPrincipalTeacherId);
        $this->pendingPrincipalTeacherId = null;
        $this->showPrincipalChangeConfirmation = false;
    }

    public function cancelPrincipalTeacherChange(): void
    {
        $this->teacherId = $this->training->teacher_id ? (int) $this->training->teacher_id : null;
        $this->confirmedPrincipalTeacherId = $this->training->teacher_id ? (int) $this->training->teacher_id : null;
        $this->pendingPrincipalTeacherId = null;
        $this->showPrincipalChangeConfirmation = false;
    }

    /**
     * @param  array{
     *     teacherId: int|string,
     *     assistantTeacherIds?: array<int, int|string>
     * }  $validated
     */
    private function persistChanges(array $validated): mixed
    {
        $newTeacherId = (int) $validated['teacherId'];
        $assistantIds = collect($validated['assistantTeacherIds'] ?? [])
            ->map(static fn (mixed $id): int => (int) $id)
            ->reject(static fn (int $id): bool => $id === $newTeacherId)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($newTeacherId, $assistantIds): void {
            $this->training->update([
                'teacher_id' => $newTeacherId,
            ]);

            $this->training->assistantTeachers()->sync($assistantIds);

            foreach ($assistantIds as $assistantId) {
                $assistant = User::query()->find($assistantId);

                if (! $assistant) {
                    continue;
                }

                if (! $assistant->hasRole('Teacher')) {
                    continue;
                }

                $this->linkTrainingChurchesToAssistantTeacher($assistant);
            }
        });

        $this->refreshTraining();
        $this->fillFormFromTraining();
        $this->showModal = false;
        $this->showPrincipalChangeConfirmation = false;
        $this->assistantSearch = '';

        $this->dispatch('training-teachers-updated', trainingId: $this->training->id);

        return null;
    }

    public function addAssistantTeacher(int $userId): void
    {
        $this->authorizeTraining($this->training);

        if ($this->teacherId !== null && $userId === $this->teacherId) {
            return;
        }

        if (! in_array($userId, $this->assistantTeacherIds, true)) {
            $this->assistantTeacherIds[] = $userId;
            $this->assistantTeacherIds = collect($this->assistantTeacherIds)
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $this->assistantSearch = '';
    }

    public function removeAssistantTeacher(int $userId): void
    {
        $this->authorizeTraining($this->training);

        $this->assistantTeacherIds = collect($this->assistantTeacherIds)
            ->reject(static fn (mixed $id): bool => (int) $id === $userId)
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    public function updatedTeacherId(?int $teacherId): void
    {
        if ($teacherId === null) {
            return;
        }

        $this->removePrincipalFromAssistantTeachers((int) $teacherId);
    }

    public function render(): View
    {
        $allTeachers = $this->orderedTeachers();

        return view('livewire.pages.app.director.training.event-teachers', [
            'allTeachers' => $allTeachers,
            'principalTeacherCandidates' => $this->principalTeacherCandidates(),
            'assistantTeacherSearchResults' => $this->assistantTeacherSearchResults(),
            'selectedAssistantTeachers' => $this->selectedAssistantTeachers(),
            'isPluralTitle' => $allTeachers->count() > 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'teacherId' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::in($this->principalTeacherCandidates()->pluck('id')->all()),
            ],
            'assistantTeacherIds' => ['array'],
            'assistantTeacherIds.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'teacherId.required' => __('Selecione o professor titular.'),
            'teacherId.in' => __('O professor titular deve ser credenciado e com perfil de professor.'),
            'assistantTeacherIds.array' => __('A lista de professores auxiliares é inválida.'),
            'assistantTeacherIds.*.distinct' => __('Não repita professores auxiliares na lista.'),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function principalTeacherCandidates(): Collection
    {
        $course = $this->training->course;

        if (! $course) {
            return new Collection;
        }

        $query = $course->teachers()
            ->wherePivot('status', 1)
            ->whereHas('roles', function ($roles): void {
                $roles->where('name', 'Teacher');
            })
            ->orderBy('name');

        $candidates = $query->get();

        if ($this->training->teacher_id && ! $candidates->contains('id', $this->training->teacher_id)) {
            $currentTeacher = User::query()->find($this->training->teacher_id);

            if ($currentTeacher) {
                $candidates->push($currentTeacher);
            }
        }

        return $candidates
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function assistantTeacherCandidates(): Collection
    {
        $search = trim($this->assistantSearch);

        return User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit(60)
            ->get(['id', 'name', 'email', 'profile_photo_path']);
    }

    /**
     * @return Collection<int, User>
     */
    private function assistantTeacherSearchResults(): Collection
    {
        $search = trim($this->assistantSearch);

        if ($search === '') {
            return new Collection;
        }

        $excludedIds = collect($this->assistantTeacherIds)
            ->map(static fn (mixed $id): int => (int) $id)
            ->when($this->teacherId !== null, fn ($collection) => $collection->push((int) $this->teacherId))
            ->unique()
            ->values()
            ->all();

        return $this->assistantTeacherCandidates()
            ->reject(static fn (User $user): bool => in_array((int) $user->id, $excludedIds, true))
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function selectedAssistantTeachers(): Collection
    {
        if ($this->assistantTeacherIds === []) {
            return new Collection;
        }

        $users = User::query()
            ->whereIn('id', $this->assistantTeacherIds)
            ->get(['id', 'name', 'email', 'profile_photo_path']);

        return collect($this->assistantTeacherIds)
            ->map(function (int $assistantId) use ($users): ?User {
                return $users->first(fn (User $user): bool => (int) $user->id === $assistantId);
            })
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function orderedTeachers(): Collection
    {
        $teachers = new Collection;

        if ($this->training->teacher) {
            $teachers->push($this->training->teacher);
        }

        foreach ($this->training->assistantTeachers as $assistantTeacher) {
            if ($this->training->teacher && $assistantTeacher->id === $this->training->teacher->id) {
                continue;
            }

            $teachers->push($assistantTeacher);
        }

        return $teachers->values();
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()
            ->with([
                'course.teachers.roles',
                'teacher.roles',
                'assistantTeachers',
            ])
            ->findOrFail($this->trainingId);

        $this->authorizeTraining($this->training);
    }

    private function refreshTraining(): void
    {
        $this->training = Training::query()
            ->with([
                'course.teachers.roles',
                'teacher.roles',
                'assistantTeachers',
            ])
            ->findOrFail($this->training->id);
    }

    private function fillFormFromTraining(): void
    {
        $this->teacherId = $this->training->teacher_id ? (int) $this->training->teacher_id : null;
        $this->confirmedPrincipalTeacherId = $this->teacherId;
        $this->pendingPrincipalTeacherId = null;
        $this->assistantTeacherIds = $this->training->assistantTeachers
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    private function linkTrainingChurchesToAssistantTeacher(User $assistantTeacher): void
    {
        $churchIds = collect();

        if ($this->training->church_id) {
            $churchIds->push((int) $this->training->church_id);
        }

        $participantChurchIds = $this->training->students()
            ->whereNotNull('users.church_id')
            ->pluck('users.church_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $churchIds = $churchIds
            ->merge($participantChurchIds)
            ->filter(static fn (mixed $id): bool => (int) $id > 0)
            ->unique()
            ->values();

        if ($churchIds->isEmpty()) {
            return;
        }

        Church::query()
            ->whereIn('id', $churchIds->all())
            ->get()
            ->each(function (Church $church) use ($assistantTeacher): void {
                $church->missionaries()->syncWithoutDetaching([$assistantTeacher->id]);
            });
    }

    private function removePrincipalFromAssistantTeachers(int $teacherId): void
    {
        $this->assistantTeacherIds = collect($this->assistantTeacherIds)
            ->reject(static fn (mixed $id): bool => (int) $id === $teacherId)
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-director');
        Gate::authorize('update', $training);
    }
}
