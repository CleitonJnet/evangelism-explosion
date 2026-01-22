<?php

namespace App\Livewire\Web\Event;

use App\Models\Role;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Login extends Component
{
    public Training $event;

    public bool $isPaid;

    public string $email = '';

    public string $password = '';

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
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
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
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'email' => 'E-mail',
            'password' => 'Senha',
        ];
    }

    public function loginEvent(): void
    {
        $validated = $this->validate();

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

        $isEnrolled = $this->event->students()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            $this->event->students()->syncWithoutDetaching([
                $user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0],
            ]);
        }

        if (! $user->hasRole('Student')) {
            $role = Role::firstOrCreate(['name' => 'Student']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->redirectRoute('app.student.training.show', ['training' => $this->event->id]);
    }

    public function render(): View
    {
        return view('livewire.web.event.login');
    }
}
