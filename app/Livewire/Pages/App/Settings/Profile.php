<?php

namespace App\Livewire\Pages\App\Settings;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Profile extends Component
{
    use AuthorizesRequests;
    use PasswordValidationRules;
    use ProfileValidationRules;

    public User $user;

    /**
     * @var array{
     *     name: string,
     *     email: string,
     *     birthdate: string,
     *     gender: string,
     *     phone: string,
     *     is_pastor: string,
     *     notes: string
     * }
     */
    public array $personal = [
        'name' => '',
        'email' => '',
        'birthdate' => '',
        'gender' => '',
        'phone' => '',
        'is_pastor' => '',
        'notes' => '',
    ];

    /**
     * @var array{
     *     postal_code: string,
     *     street: string,
     *     number: string,
     *     complement: string,
     *     district: string,
     *     city: string,
     *     state: string
     * }
     */
    public array $address = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $showPersonalModal = false;

    public bool $showAddressModal = false;

    public bool $showPasswordModal = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->authorize('view', $user);

        $this->user = $user;

        $this->refreshUser();
        $this->fillFromUser();
    }

    #[On('church-linked')]
    public function refreshFromChurchLink(): void
    {
        $this->refreshUser();
        $this->fillFromUser();
    }

    public function openChurchModal(): void
    {
        $this->dispatch('open-church-modal');
    }

    public function updatePersonal(): void
    {
        $this->authorize('update', $this->user);

        $validated = $this->validate($this->personalRules());

        $this->user->fill($validated['personal']);

        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        $this->refreshUser();
        $this->fillFromUser();
        $this->showPersonalModal = false;

        $this->dispatch('profile-personal-updated');
    }

    public function updateAddress(): void
    {
        $this->authorize('update', $this->user);

        $validated = $this->validate($this->addressRules());

        $this->user->fill($validated['address']);
        $this->user->save();

        $this->refreshUser();
        $this->fillFromUser();
        $this->showAddressModal = false;

        $this->dispatch('profile-address-updated');
    }

    public function updatePassword(): void
    {
        $this->authorize('update', $this->user);

        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $exception) {
            $this->resetPasswordFields();

            throw $exception;
        }

        $this->user->update([
            'password' => $validated['password'],
        ]);

        $this->refreshUser();
        $this->resetPasswordFields();
        $this->showPasswordModal = false;

        $this->dispatch('profile-password-updated');
    }

    public function closePersonalModal(): void
    {
        $this->resetValidation();
        $this->showPersonalModal = false;
        $this->fillFromUser();
    }

    public function closeAddressModal(): void
    {
        $this->resetValidation();
        $this->showAddressModal = false;
        $this->fillFromUser();
    }

    public function closePasswordModal(): void
    {
        $this->resetValidation();
        $this->resetPasswordFields();
        $this->showPasswordModal = false;
    }

    public function isPastor(): bool
    {
        return (bool) ($this->user->is_pastor ?? false);
    }

    public function formatValue(mixed $value): string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '') {
            return __('Não informado');
        }

        if (is_bool($value)) {
            return $value ? __('Sim') : __('Não');
        }

        return (string) $value;
    }

    public function formatDate(mixed $value): string
    {
        if (! $value) {
            return __('Não informado');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        return (string) $value;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    public function formatAddress(array $address): string
    {
        $parts = array_filter([
            $address['street'] ?? null,
            $address['number'] ?? null,
            $address['complement'] ?? null,
            $address['district'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['postal_code'] ?? null,
        ]);

        return $parts !== [] ? implode(', ', $parts) : __('Não informado');
    }

    public function render(): View
    {
        return view('livewire.pages.app.settings.profile')
            ->layout('components.layouts.app', [
                'title' => __('Perfil do usuário'),
            ]);
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>>
     */
    protected function personalRules(): array
    {
        return [
            'personal.name' => $this->nameRules(),
            'personal.email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->user->id)],
            'personal.birthdate' => ['nullable', 'date'],
            'personal.gender' => ['nullable', 'in:1,2'],
            'personal.phone' => ['nullable', 'string', 'max:30'],
            'personal.is_pastor' => ['nullable', 'in:1,0'],
            'personal.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function addressRules(): array
    {
        return [
            'address.postal_code' => ['nullable', 'string', 'max:12'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:30'],
            'address.complement' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:2'],
        ];
    }

    protected function refreshUser(): void
    {
        $this->user->refresh();
        $this->user->loadMissing(['roles', 'church', 'church_temp', 'hostChurches.church', 'churches']);
    }

    protected function fillFromUser(): void
    {
        $this->personal = [
            'name' => $this->user->name ?? '',
            'email' => $this->user->email ?? '',
            'birthdate' => $this->user->birthdate?->format('Y-m-d') ?? '',
            'gender' => $this->user->gender !== null ? (string) $this->user->gender : '',
            'phone' => $this->user->phone ?? '',
            'is_pastor' => $this->user->is_pastor === null ? '' : ($this->user->is_pastor ? '1' : '0'),
            'notes' => $this->user->notes ?? '',
        ];

        $this->address = [
            'postal_code' => $this->user->postal_code ?? '',
            'street' => $this->user->street ?? '',
            'number' => $this->user->number ?? '',
            'complement' => $this->user->complement ?? '',
            'district' => $this->user->district ?? '',
            'city' => $this->user->city ?? '',
            'state' => $this->user->state ?? '',
        ];
    }

    protected function resetPasswordFields(): void
    {
        $this->reset('current_password', 'password', 'password_confirmation');
    }
}
