<?php

namespace App\Livewire\Shared;

use App\Concerns\ProfileValidationRules;
use App\Models\Church;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class ChurchUserProfile extends Component
{
    use AuthorizesRequests;
    use ProfileValidationRules;

    public User $user;

    public string $backUrl = '';

    public string $backLabel = '';

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

    public bool $showPersonalModal = false;

    public bool $showAddressModal = false;

    public bool $showChurchModal = false;

    public string $churchSearch = '';

    public ?int $selectedChurchId = null;

    public function mount(User $user, string $backUrl, string $backLabel): void
    {
        Gate::authorize('manageChurches');

        $this->user = $user;
        $this->backUrl = $backUrl;
        $this->backLabel = $backLabel;

        $this->user->loadMissing(['roles', 'church', 'church_temp']);
        $this->fillFromUser();
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

    public function formatAddress(): string
    {
        $parts = array_filter([
            $this->user->street,
            $this->user->number,
            $this->user->complement,
            $this->user->district,
            $this->user->city,
            $this->user->state,
        ]);

        return $parts !== [] ? implode(', ', $parts) : __('Não informado');
    }

    public function openPersonalModal(): void
    {
        Gate::authorize('manageChurches');

        $this->resetValidation();
        $this->fillFromUser();
        $this->showPersonalModal = true;
    }

    public function closePersonalModal(): void
    {
        $this->resetValidation();
        $this->showPersonalModal = false;
        $this->fillFromUser();
    }

    public function updatePersonal(): void
    {
        Gate::authorize('manageChurches');

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

    public function openAddressModal(): void
    {
        Gate::authorize('manageChurches');

        $this->resetValidation();
        $this->fillFromUser();
        $this->showAddressModal = true;
    }

    public function closeAddressModal(): void
    {
        $this->resetValidation();
        $this->showAddressModal = false;
        $this->fillFromUser();
    }

    public function updateAddress(): void
    {
        Gate::authorize('manageChurches');

        $validated = $this->validate($this->addressRules());

        $this->user->fill($validated['address']);
        $this->user->save();

        $this->refreshUser();
        $this->fillFromUser();
        $this->showAddressModal = false;

        $this->dispatch('profile-address-updated');
    }

    public function openChurchModal(): void
    {
        Gate::authorize('manageChurches');

        $this->resetValidation();
        $this->selectedChurchId = $this->user->church_id ?? $this->churchOptions()->first()?->id;
        $this->churchSearch = '';
        $this->showChurchModal = true;
    }

    public function closeChurchModal(): void
    {
        $this->resetValidation();
        $this->showChurchModal = false;
        $this->churchSearch = '';
        $this->selectedChurchId = null;
    }

    public function updatedChurchSearch(): void
    {
        $this->selectedChurchId = $this->churchOptions()->first()?->id;
    }

    public function clearChurchSearch(): void
    {
        $this->churchSearch = '';
        $this->selectedChurchId = $this->churchOptions()->first()?->id;
    }

    public function updateChurch(): void
    {
        Gate::authorize('manageChurches');

        $this->validate([
            'selectedChurchId' => ['required', 'exists:churches,id'],
        ]);

        $this->user->forceFill([
            'church_id' => $this->selectedChurchId,
            'church_temp_id' => null,
        ])->save();

        $this->refreshUser();
        $this->fillFromUser();
        $this->closeChurchModal();

        $this->dispatch('profile-church-updated');
    }

    /**
     * @return EloquentCollection<int, Church>
     */
    public function churchOptions(): EloquentCollection
    {
        $churchSearch = trim($this->churchSearch);

        return Church::query()
            ->when($churchSearch !== '', function ($query) use ($churchSearch): void {
                $query->where(function ($query) use ($churchSearch): void {
                    $query->where('name', 'like', '%'.$churchSearch.'%')
                        ->orWhere('city', 'like', '%'.$churchSearch.'%')
                        ->orWhere('state', 'like', '%'.$churchSearch.'%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.shared.church-user-profile', [
            'churchOptions' => $this->churchOptions(),
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
            'personal.birthdate' => ['nullable', 'date_format:Y-m-d'],
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
        $this->user->loadMissing(['roles', 'church', 'church_temp']);
    }

    protected function fillFromUser(): void
    {
        $this->personal = [
            'name' => $this->user->name ?? '',
            'email' => $this->user->email ?? '',
            'birthdate' => $this->user->birthdate?->format('Y-m-d') ?? '',
            'gender' => (($normalizedGender = User::normalizeGenderValue($this->user->getRawOriginal('gender') ?? $this->user->gender)) !== null)
                ? (string) $normalizedGender
                : '',
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
}
