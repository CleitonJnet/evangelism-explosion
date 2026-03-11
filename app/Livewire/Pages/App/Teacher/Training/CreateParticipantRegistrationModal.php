<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Helpers\PhoneHelper;
use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\TeacherParticipantRegistrationProcessor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateParticipantRegistrationModal extends Component
{
    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $mode = 'identify';

    public string $ispastor = '0';

    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public ?string $birth_date = null;

    public ?string $gender = null;

    public ?string $emailNotice = null;

    public ?int $selectedChurchId = null;

    public string $churchSearch = '';

    public ?int $existingUserId = null;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->authorizeTraining($this->training());
    }

    #[On('open-create-participant-registration-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->trainingId) {
            abort(404);
        }

        $this->authorizeTraining($this->training());
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.create-participant-registration-modal', [
            'yesNoOptions' => [
                ['value' => '0', 'label' => __('Não')],
                ['value' => '1', 'label' => __('Sim')],
            ],
            'genderOptions' => [
                ['value' => '1', 'label' => __('Masculino')],
                ['value' => '2', 'label' => __('Feminino')],
            ],
            'churchResults' => $this->churchResults(),
            'selectedChurchName' => $this->selectedChurchId
                ? Church::query()->whereKey($this->selectedChurchId)->value('name')
                : null,
        ]);
    }

    private function training(): Training
    {
        return Training::query()->findOrFail($this->trainingId);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->mode = 'identify';
        $this->ispastor = '0';
        $this->name = '';
        $this->email = '';
        $this->mobile = '';
        $this->birth_date = null;
        $this->gender = null;
        $this->emailNotice = null;
        $this->selectedChurchId = null;
        $this->churchSearch = '';
        $this->existingUserId = null;
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('update', $training);
    }

    public function identifyByEmail(): void
    {
        $this->authorizeTraining($this->training());
        $this->email = $this->sanitizeEmail($this->email);
        $this->resetErrorBag();
        $this->emailNotice = null;

        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ], attributes: [
            'email' => 'E-mail',
        ]);

        $user = User::query()->where('email', $this->email)->first();

        if ($user) {
            $this->mode = 'register';
            $this->existingUserId = $user->id;
            $this->name = $user->name;
            $this->mobile = (string) ($user->getRawOriginal('phone') ?? '');
            $this->birth_date = $user->birthdate?->format('Y-m-d');
            $this->gender = $user->gender ? (string) $user->gender : null;
            $this->ispastor = ((int) ($user->getRawOriginal('is_pastor') ?? 0)) > 0 ? '1' : '0';
            $this->selectedChurchId = $user->church_id;
            $this->churchSearch = $user->church?->name ?? '';
            $this->emailNotice = $this->isAlreadyEnrolled($user)
                ? 'Conta encontrada. Este aluno já está inscrito neste evento.'
                : 'Conta encontrada. Revise a igreja e confirme a inscrição do aluno.';

            if ($this->isAlreadyEnrolled($user)) {
                $this->addError('email', 'Este aluno já está inscrito neste evento.');
            }

            return;
        }

        $this->mode = 'register';
        $this->existingUserId = null;
        $this->name = '';
        $this->mobile = '';
        $this->birth_date = null;
        $this->gender = null;
        $this->ispastor = '0';
        $this->selectedChurchId = null;
        $this->churchSearch = '';
        $this->emailNotice = sprintf(
            'Não encontramos este e-mail. Preencha os dados do aluno. O acesso será criado com a senha padrão "%s".',
            TeacherParticipantRegistrationProcessor::DEFAULT_PASSWORD,
        );
    }

    public function selectChurch(int $churchId): void
    {
        $this->authorizeTraining($this->training());

        $church = Church::query()->findOrFail($churchId);
        $this->selectedChurchId = $church->id;
        $this->churchSearch = $church->name;
    }

    public function openCreateChurchModal(): void
    {
        $this->authorizeTraining($this->training());
        $this->dispatch('open-create-mentor-church-modal', trainingId: $this->trainingId);
    }

    #[On('mentor-church-created')]
    public function handleChurchCreated(int $trainingId, int $churchId, string $churchName): void
    {
        if ($trainingId !== $this->trainingId) {
            return;
        }

        $this->authorizeTraining($this->training());
        $this->selectedChurchId = $churchId;
        $this->churchSearch = $churchName;
    }

    public function registerEvent(TeacherParticipantRegistrationProcessor $processor): void
    {
        if ($this->busy) {
            return;
        }

        $training = $this->training();
        $this->authorizeTraining($training);
        $this->sanitizeFormInput();
        $this->resetErrorBag();

        $participant = $this->participantForCurrentEmail();

        if ($participant && $training->students()->whereKey($participant->id)->exists()) {
            $this->addError('email', 'Este aluno já está inscrito neste evento.');

            return;
        }

        $validated = $this->validate(
            $participant ? $this->existingParticipantRules() : $this->newParticipantRules(),
            $this->messages(),
            $this->validationAttributes(),
        );

        $this->busy = true;

        try {
            $processor->process($training, $validated);
            $this->dispatch('training-participant-registration-created', trainingId: $training->id);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    private function sanitizeFormInput(): void
    {
        $this->name = $this->sanitizePersonName($this->name);
        $this->mobile = PhoneHelper::normalize($this->mobile) ?? '';
        $this->email = $this->sanitizeEmail($this->email);
    }

    private function sanitizeEmail(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    private function sanitizePersonName(string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));

        if ($normalized === null || $normalized === '') {
            return '';
        }

        $prepositions = ['da', 'das', 'de', 'do', 'dos', 'e'];
        $parts = explode(' ', $normalized);

        $formatted = array_map(function (string $part, int $index) use ($prepositions): string {
            $lowerPart = mb_strtolower($part, 'UTF-8');

            if ($index > 0 && in_array($lowerPart, $prepositions, true)) {
                return $lowerPart;
            }

            return $this->capitalizeNamePart($lowerPart);
        }, $parts, array_keys($parts));

        return implode(' ', $formatted);
    }

    private function capitalizeNamePart(string $value): string
    {
        $segments = explode('-', $value);
        $capitalizedSegments = array_map(function (string $segment): string {
            if ($segment === '') {
                return '';
            }

            $firstLetter = mb_strtoupper(mb_substr($segment, 0, 1, 'UTF-8'), 'UTF-8');
            $remaining = mb_substr($segment, 1, null, 'UTF-8');

            return $firstLetter.$remaining;
        }, $segments);

        return implode('-', $capitalizedSegments);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function newParticipantRules(): array
    {
        return [
            'ispastor' => ['required', 'in:1,0'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'mobile' => ['required', 'string', 'min:7', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:1,2'],
            'selectedChurchId' => ['required', 'integer', 'exists:churches,id'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function existingParticipantRules(): array
    {
        return [
            'ispastor' => ['nullable', 'in:1,0'],
            'email' => ['required', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:1,2'],
            'selectedChurchId' => ['required', 'integer', 'exists:churches,id'],
        ];
    }

    private function isAlreadyEnrolled(User $user): bool
    {
        return $this->training()->students()->whereKey($user->id)->exists();
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve conter um e-mail válido.',
            'unique' => 'Este :attribute já está cadastrado.',
            'min' => 'O campo :attribute precisa de pelo menos :min caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'confirmed' => 'A confirmação de senha não confere.',
            'in' => 'Selecione uma opção válida para :attribute.',
            'date' => 'O campo :attribute deve ser uma data válida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'ispastor' => 'É pastor?',
            'name' => 'Nome completo',
            'mobile' => 'Celular',
            'email' => 'E-mail',
            'birth_date' => 'Data de nascimento',
            'gender' => 'Gênero',
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

    private function participantForCurrentEmail(): ?User
    {
        $email = $this->sanitizeEmail($this->email);

        if ($email === '') {
            return null;
        }

        return User::query()->where('email', $email)->first();
    }
}
