<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Helpers\PhoneHelper;
use App\Models\Church;
use App\Models\User;
use App\Services\Church\ChurchParticipantRegistrationProcessor;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateParticipantModal extends Component
{
    public int $churchId;

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

    public ?int $existingUserId = null;

    public function mount(int $churchId): void
    {
        $this->churchId = $churchId;
        $this->authorizeChurch($this->church());
    }

    #[On('open-teacher-church-participant-create-modal')]
    public function openModal(int $churchId): void
    {
        if ($churchId !== $this->churchId) {
            abort(404);
        }

        $this->authorizeChurch($this->church());
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
        return view('livewire.pages.app.teacher.church.create-participant-modal', [
            'churchName' => $this->church()->name,
            'defaultPassword' => ChurchParticipantRegistrationProcessor::DEFAULT_PASSWORD,
            'yesNoOptions' => [
                ['value' => '0', 'label' => __('Não')],
                ['value' => '1', 'label' => __('Sim')],
            ],
            'genderOptions' => [
                ['value' => '1', 'label' => __('Masculino')],
                ['value' => '2', 'label' => __('Feminino')],
            ],
        ]);
    }

    public function identifyByEmail(): void
    {
        $this->authorizeChurch($this->church());
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
            $this->emailNotice = $user->church_id === $this->churchId
                ? 'Conta encontrada. Revise os dados e confirme o participante da igreja.'
                : 'Conta encontrada. Ao confirmar, este participante será vinculado a esta igreja.';

            return;
        }

        $this->mode = 'register';
        $this->existingUserId = null;
        $this->name = '';
        $this->mobile = '';
        $this->birth_date = null;
        $this->gender = null;
        $this->ispastor = '0';
        $this->emailNotice = sprintf(
            'Não encontramos este e-mail. Preencha os dados do participante. O acesso será criado com a senha padrão "%s".',
            ChurchParticipantRegistrationProcessor::DEFAULT_PASSWORD,
        );
    }

    public function registerParticipant(ChurchParticipantRegistrationProcessor $processor): void
    {
        if ($this->busy) {
            return;
        }

        $church = $this->church();
        $this->authorizeChurch($church);
        $this->sanitizeFormInput();
        $this->resetErrorBag();

        $validated = $this->validate(
            $this->participantForCurrentEmail() ? $this->existingParticipantRules() : $this->newParticipantRules(),
            $this->messages(),
            $this->validationAttributes(),
        );

        $this->busy = true;

        try {
            $processor->process($church, $validated);
            $this->dispatch('teacher-church-participant-created', churchId: $church->id);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    private function church(): Church
    {
        return Church::query()->findOrFail($this->churchId);
    }

    private function authorizeChurch(Church $church): void
    {
        Gate::authorize('view', $church);
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
        $this->existingUserId = null;
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
            'min' => 'O campo :attribute precisa de pelo menos :min caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
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
        ];
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
