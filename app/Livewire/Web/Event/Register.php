<?php

namespace App\Livewire\Web\Event;

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

    public bool $isPaid;

    public string $ispastor = 'N';

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
        // ====== Flags do evento (pago x gratuito) ======
        // converte para float
        $pay = (float) preg_replace('/\D/', '', (string) $event->payment);

        $this->isPaid = $pay > 0;
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
            'ispastor' => ['required', 'in:Y,N'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'mobile' => ['required', 'string', 'min:7', 'max:20'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'min:8', 'max:80', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:M,F'],
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
                'pastor' => $validated['ispastor'],
                'name' => $validated['name'],
                'birthdate' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'phone' => $validated['mobile'],
            ])->save();
        } else {
            $user = User::create([
                'pastor' => $validated['ispastor'],
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

        $email = trim($value);

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

    public function render(): View
    {
        return view('livewire.web.event.register');
    }
}
