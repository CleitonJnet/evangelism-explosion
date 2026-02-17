<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\MentorAssignmentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateMentorUserModal extends Component
{
    public int $trainingId;

    public Training $training;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?int $selectedChurchId = null;

    public string $churchSearch = '';

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
    }

    #[On('open-create-mentor-user-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->training->id) {
            abort(404);
        }

        $this->authorizeTraining($this->training);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function selectChurch(int $churchId): void
    {
        $this->authorizeTraining($this->training);

        $church = Church::query()->findOrFail($churchId);
        $this->selectedChurchId = $church->id;
        $this->churchSearch = $church->name;
    }

    public function openCreateChurchModal(): void
    {
        $this->authorizeTraining($this->training);
        $this->dispatch('open-create-mentor-church-modal', trainingId: $this->training->id);
    }

    #[On('mentor-church-created')]
    public function handleChurchCreated(int $trainingId, int $churchId, string $churchName): void
    {
        if ($trainingId !== $this->training->id) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->selectedChurchId = $churchId;
        $this->churchSearch = $churchName;
    }

    public function save(MentorAssignmentService $mentorAssignmentService): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        $validated = $this->validate();
        $this->busy = true;

        try {
            DB::transaction(function () use ($validated, $actor, $mentorAssignmentService): void {
                $mentorUser = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?: null,
                    'church_id' => $validated['selectedChurchId'],
                    'password' => Str::password(24),
                ]);

                $mentorAssignmentService->addMentor($this->training, $mentorUser, $actor);
            });

            $this->dispatch('mentor-user-created', trainingId: $this->training->id);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.create-mentor-user-modal', [
            'churchResults' => $this->churchResults(),
            'selectedChurchName' => $this->selectedChurchId
                ? Church::query()->whereKey($this->selectedChurchId)->value('name')
                : null,
        ]);
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'selectedChurchId' => ['required', 'integer', 'exists:churches,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve conter um e-mail válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'unique' => 'Já existe um usuário com este e-mail.',
            'exists' => 'A igreja selecionada é inválida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'selectedChurchId' => 'igreja',
        ];
    }

    /**
     * @return Collection<int, Church>
     */
    private function churchResults(): Collection
    {
        $search = trim($this->churchSearch);

        if ($search === '') {
            return Church::query()
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return Church::query()
            ->where('name', 'like', '%'.$search.'%')
            ->orWhere('city', 'like', '%'.$search.'%')
            ->orWhere('state', 'like', '%'.$search.'%')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()->findOrFail($this->trainingId);
        $this->authorizeTraining($this->training);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->email = '';
        $this->phone = null;
        $this->selectedChurchId = null;
        $this->churchSearch = '';
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
