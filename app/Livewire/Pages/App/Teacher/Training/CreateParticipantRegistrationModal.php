<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Helpers\PhoneHelper;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateParticipantRegistrationModal extends Component
{
    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $mode = 'identify';

    public string $password = '';

    public string $password_confirmation = '';

    public string $ispastor = '0';

    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public ?string $birth_date = null;

    public ?string $gender = null;

    public ?string $emailNotice = null;

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
        $this->password = '';
        $this->ispastor = '0';
        $this->name = '';
        $this->email = '';
        $this->mobile = '';
        $this->password_confirmation = '';
        $this->birth_date = null;
        $this->gender = null;
        $this->emailNotice = null;
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
            $this->mode = 'login';
            $this->name = $user->name;
            $this->mobile = (string) ($user->getRawOriginal('phone') ?? '');
            $this->birth_date = $user->birthdate?->format('Y-m-d');
            $this->gender = $user->gender ? (string) $user->gender : null;
            $this->ispastor = ((int) ($user->getRawOriginal('is_pastor') ?? 0)) > 0 ? '1' : '0';
            $this->emailNotice = $this->isAlreadyEnrolled($user)
                ? 'Conta encontrada. Este aluno já está inscrito neste evento.'
                : 'Conta encontrada. Informe a senha para concluir a inscrição deste aluno.';

            if ($this->isAlreadyEnrolled($user)) {
                $this->addError('email', 'Este aluno já está inscrito neste evento.');
            }

            return;
        }

        $this->mode = 'register';
        $this->emailNotice = 'Não encontramos este e-mail. Preencha os dados para criar a inscrição do aluno.';
    }

    public function switchToLogin(): void
    {
        $this->mode = 'login';
        $this->resetErrorBag();
    }

    public function switchToRegister(): void
    {
        $this->mode = 'register';
        $this->resetErrorBag();
    }

    public function loginEvent(): void
    {
        if ($this->busy) {
            return;
        }

        $training = $this->training();
        $this->authorizeTraining($training);
        $this->email = $this->sanitizeEmail($this->email);
        $this->resetErrorBag();

        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], attributes: [
            'email' => 'E-mail',
            'password' => 'Senha',
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
            $this->addError('email', 'Credenciais inválidas.');

            return;
        }

        if ($this->isAlreadyEnrolled($user)) {
            $this->addError('email', 'Este aluno já está inscrito neste evento.');

            return;
        }

        $this->busy = true;

        try {
            $this->ensureEnrollmentAndStudentRole($training, $user);
            $this->dispatch('training-participant-registration-created', trainingId: $training->id);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function registerEvent(): void
    {
        if ($this->busy) {
            return;
        }

        $training = $this->training();
        $this->authorizeTraining($training);
        $this->sanitizeFormInput();
        $this->resetErrorBag();

        $emailRules = ['required', 'email', 'max:255'];

        if (! User::query()->where('email', $this->email)->exists()) {
            $emailRules[] = 'unique:users,email';
        }

        $validated = $this->validate([
            'ispastor' => ['required', 'in:1,0'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'mobile' => ['required', 'string', 'min:7', 'max:20'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'min:8', 'max:80', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:1,2'],
        ], $this->messages(), $this->validationAttributes());

        $participant = User::query()->where('email', $validated['email'])->first();

        if ($participant && $training->students()->whereKey($participant->id)->exists()) {
            $this->addError('email', 'Este aluno já está inscrito neste evento.');

            return;
        }

        if ($participant && ! Hash::check($validated['password'], (string) $participant->password)) {
            $this->addError('password', 'Senha incorreta.');

            return;
        }

        $this->busy = true;

        try {
            $participant = DB::transaction(function () use ($validated, $participant): User {
                if ($participant) {
                    $participant->forceFill([
                        'is_pastor' => $validated['ispastor'],
                        'name' => $validated['name'],
                        'birthdate' => $validated['birth_date'],
                        'gender' => $validated['gender'],
                        'phone' => $validated['mobile'],
                    ])->save();
                } else {
                    $participant = User::query()->create([
                        'is_pastor' => $validated['ispastor'],
                        'name' => $validated['name'],
                        'birthdate' => $validated['birth_date'],
                        'gender' => $validated['gender'],
                        'phone' => $validated['mobile'],
                        'email' => $validated['email'],
                        'password' => $validated['password'],
                    ]);
                }

                return $participant;
            });

            $this->ensureEnrollmentAndStudentRole($training, $participant);
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

    private function ensureEnrollmentAndStudentRole(Training $training, User $user): void
    {
        $studentRole = Role::query()->firstOrCreate(['name' => 'Student']);
        $user->roles()->syncWithoutDetaching([$studentRole->id]);

        $training->students()->syncWithoutDetaching([
            $user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0],
        ]);
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
            'password' => 'Senha',
            'password_confirmation' => 'Confirmação de senha',
            'birth_date' => 'Data de nascimento',
            'gender' => 'Gênero',
        ];
    }
}
