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

class Register extends Component
{
    public Training $event;

    public string $ispastor = '0';

    public string $name = '';

    public string $mobile = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $birth_date = null;

    public ?string $gender = null;

    public bool $emailAlreadyRegistered = false;

    public bool $emailAlreadyEnrolled = false;

    public ?string $emailNotice = null;

    public function mount(Training $event): void
    {
        $this->event = $event;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        $emailRules = ['required', 'email', 'max:255'];

        if (! User::query()->where('email', $this->email)->exists()) {
            $emailRules[] = 'unique:users,email';
        }

        return [
            'ispastor' => ['required', 'in:1,0'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'mobile' => ['required', 'string', 'min:7', 'max:20'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'min:8', 'max:80', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:1,2'],
        ];
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

    public function registerEvent(): void
    {
        $this->sanitizeFormInput();

        $validated = $this->validate();

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user && $this->isAlreadyEnrolled($user)) {
            $this->emailAlreadyRegistered = true;
            $this->emailAlreadyEnrolled = true;
            $this->emailNotice = $this->buildLoginNotice('Você já está inscrito neste evento.');
            $this->addError('email', 'Você já está inscrito neste evento.');

            return;
        }

        if ($user) {
            if (! Hash::check($validated['password'], $user->password)) {
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
            $user = User::create([
                'is_pastor' => $validated['ispastor'],
                'name' => $validated['name'],
                'birthdate' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'phone' => $validated['mobile'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
        }

        $role = Role::firstOrCreate(['name' => 'Student']);
        $user->roles()->syncWithoutDetaching([$role->id]);
        $this->event->students()->syncWithoutDetaching([
            $user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0],
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('app.student.training.show', ['training' => $this->event->id]);
    }

    public function updatedEmail(string $value): void
    {
        $this->resetErrorBag('email');
        $this->emailAlreadyRegistered = false;
        $this->emailAlreadyEnrolled = false;
        $this->emailNotice = null;

        $email = $this->sanitizeEmail($value);
        $this->email = $email;

        if ($email === '') {
            return;
        }

        $this->validateOnly('email', [
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return;
        }

        $this->emailAlreadyRegistered = true;

        if ($this->isAlreadyEnrolled($user)) {
            $this->emailAlreadyEnrolled = true;
            $this->emailNotice = $this->buildLoginNotice('Você já está inscrito neste evento.');
            $this->addError('email', 'Você já está inscrito neste evento.');

            return;
        }

        $this->emailNotice = $this->buildLoginNotice('E-mail já cadastrado. Faça login para continuar.');
    }

    private function isAlreadyEnrolled(User $user): bool
    {
        return $this->event->students()->whereKey($user->id)->exists();
    }

    private function buildLoginNotice(string $message): string
    {
        $loginUrl = route('web.event.login', $this->event->id);

        return sprintf(
            '<p class="mt-1 text-xs text-amber-700">%s <a class="font-semibold underline" href="%s">Fazer login no evento</a>.</p>',
            $message,
            $loginUrl,
        );
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
        return view('livewire.web.event.register');
    }
}
