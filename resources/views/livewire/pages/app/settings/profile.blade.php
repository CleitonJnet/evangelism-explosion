<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    use AuthorizesRequests;
    use PasswordValidationRules;
    use ProfileValidationRules;

    public User $user;

    public array $personal = [
        'name' => '',
        'email' => '',
        'birthdate' => '',
        'gender' => '',
        'phone' => '',
        'pastor' => '',
        'notes' => '',
    ];

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

    protected $listeners = [
        'church-linked' => 'refreshFromChurchLink',
    ];

    public bool $showPersonalModal = false;
    public bool $showAddressModal = false;
    public bool $showPasswordModal = false;

    public function openChurchModal(): void
    {
        $this->dispatch('open-church-modal');
    }

    public function refreshFromChurchLink(): void
    {
        $this->refreshUser();
        $this->fillFromUser();
    }

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->authorize('view', $this->user);

        $this->refreshUser();
        $this->fillFromUser();
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
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        $this->user->update(['password' => $validated['password']]);

        $this->reset('current_password', 'password', 'password_confirmation');
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
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->showPasswordModal = false;
    }

    public function getIsPastorProperty(): bool
    {
        $value = strtoupper(trim((string) $this->user->pastor));

        if ($value === '') {
            return false;
        }

        return in_array($value, ['Y', 'S', 'SIM', 'YES', '1', 'TRUE'], true) || str_starts_with($value, 'PR');
    }

    public function formatValue(mixed $value): string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '') {
            return 'Não informado';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        return (string) $value;
    }

    public function formatDateTime(mixed $value): string
    {
        if (!$value) {
            return 'Não informado';
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        return $value->format('d/m/Y H:i');
    }

    public function formatDate(mixed $value): string
    {
        if (!$value) {
            return 'Não informado';
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        return (string) $value;
    }

    public function formatAddress(array $address): string
    {
        $parts = array_filter([$address['street'] ?? null, $address['number'] ?? null, $address['complement'] ?? null, $address['district'] ?? null, $address['city'] ?? null, $address['state'] ?? null, $address['postal_code'] ?? null]);

        return $parts ? implode(', ', $parts) : 'Não informado';
    }

    protected function personalRules(): array
    {
        return [
            'personal.name' => $this->nameRules(),
            'personal.email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->user->id)],
            'personal.birthdate' => ['nullable', 'string', 'max:255'],
            'personal.gender' => ['nullable', 'string', 'max:50'],
            'personal.phone' => ['nullable', 'string', 'max:30'],
            'personal.pastor' => ['nullable', 'string', 'max:255'],
            'personal.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

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
            'birthdate' => $this->user->birthdate ?? '',
            'gender' => $this->user->gender ?? '',
            'phone' => $this->user->phone ?? '',
            'pastor' => $this->user->pastor ?? '',
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
}; ?>

@include('components.app.settings.⚡profile')
