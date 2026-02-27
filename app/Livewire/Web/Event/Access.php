<?php

namespace App\Livewire\Web\Event;

use App\Helpers\PhoneHelper;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Component;

class Access extends Component
{
    public Training $event;

    public string $mode = 'identify';

    public string $email = '';

    public string $password = '';

    public string $ispastor = '0';

    public string $name = '';

    public string $mobile = '';

    public string $password_confirmation = '';

    public ?string $birth_date = null;

    public ?string $gender = null;

    public ?string $emailNotice = null;

    public function mount(Training $event, string $mode = 'identify'): void
    {
        $this->event = $event;
        $this->mode = in_array($mode, ['identify', 'login', 'register'], true) ? $mode : 'identify';
    }

    public function identifyByEmail(): void
    {
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
                ? 'Conta encontrada. Voce ja esta inscrito neste evento, faca login para acessar.'
                : 'Conta encontrada. Informe sua senha para entrar e concluir a inscricao.';

            return;
        }

        $this->mode = 'register';
        $this->emailNotice = 'Nao encontramos este e-mail. Complete os dados para criar sua inscricao.';
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
        $this->email = $this->sanitizeEmail($this->email);
        $this->resetErrorBag();

        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], attributes: [
            'email' => 'E-mail',
            'password' => 'Senha',
        ]);

        if (! Auth::attempt($validated)) {
            $this->addError('email', 'Credenciais invalidas.');

            return;
        }

        session()->regenerate();

        $user = Auth::user();

        if (! $user) {
            $this->addError('email', 'Nao foi possivel autenticar.');

            return;
        }

        $this->ensureEnrollmentAndStudentRole($user);

        $this->redirectRoute('app.student.training.show', ['training' => $this->event->id]);
    }

    public function registerEvent(): void
    {
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

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user && $this->isAlreadyEnrolled($user)) {
            $this->mode = 'login';
            $this->emailNotice = 'Voce ja esta inscrito neste evento. Faca login para continuar.';
            $this->addError('email', 'Voce ja esta inscrito neste evento.');

            return;
        }

        if ($user) {
            if (! Hash::check($validated['password'], (string) $user->password)) {
                $this->addError('password', 'Senha incorreta.');

                return;
            }

            $user->forceFill([
                'is_pastor' => $validated['ispastor'],
                'name' => $validated['name'],
                'birthdate' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'phone' => $validated['mobile'],
            ])->save();
        } else {
            $user = User::query()->create([
                'is_pastor' => $validated['ispastor'],
                'name' => $validated['name'],
                'birthdate' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'phone' => $validated['mobile'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
        }

        $this->ensureEnrollmentAndStudentRole($user);

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('app.student.training.show', ['training' => $this->event->id]);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute e obrigatorio.',
            'email' => 'O campo :attribute deve ser um e-mail valido.',
            'unique' => 'Este :attribute ja esta cadastrado.',
            'min' => 'O campo :attribute precisa de pelo menos :min caracteres.',
            'max' => 'O campo :attribute nao pode ter mais de :max caracteres.',
            'confirmed' => 'A confirmacao de senha nao confere.',
            'in' => 'Selecione uma opcao valida para :attribute.',
            'date' => 'O campo :attribute deve ser uma data valida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'ispastor' => 'E pastor?',
            'name' => 'Nome completo',
            'mobile' => 'Celular',
            'email' => 'E-mail',
            'password' => 'Senha',
            'password_confirmation' => 'Confirmacao de senha',
            'birth_date' => 'Data de Nascimento',
            'gender' => 'Genero',
        ];
    }

    private function ensureEnrollmentAndStudentRole(User $user): void
    {
        if (! $user->hasRole('Student')) {
            $role = Role::query()->firstOrCreate(['name' => 'Student']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->event->students()->syncWithoutDetaching([
            $user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0],
        ]);
    }

    private function isAlreadyEnrolled(User $user): bool
    {
        return $this->event->students()->whereKey($user->id)->exists();
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

    public function render(): View
    {
        return view('livewire.web.event.access');
    }
}
