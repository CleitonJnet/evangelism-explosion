<?php

namespace App\Livewire\Pages\App\Settings;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Training;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use AuthorizesRequests;
    use PasswordValidationRules;
    use ProfileValidationRules;
    use WithFileUploads;

    public User $user;

    public bool $isManagingAnotherUser = false;

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

    public mixed $profilePhotoUpload = null;

    public bool $showPersonalModal = false;

    public bool $showAddressModal = false;

    public bool $showPasswordModal = false;

    public bool $showPhotoModal = false;

    public bool $showDeleteModal = false;

    public bool $savingProfilePhoto = false;

    public string $deletePassword = '';

    public function mount(?User $user = null): void
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        abort_unless($authenticatedUser instanceof User, 403);

        if ($user instanceof User && $user->isNot($authenticatedUser)) {
            Gate::authorize('manageChurches');

            $this->user = $user;
            $this->isManagingAnotherUser = true;
        } else {
            $this->authorize('view', $authenticatedUser);

            $this->user = $authenticatedUser;
            $this->isManagingAnotherUser = false;
        }

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
        if ($this->isManagingAnotherUser) {
            return;
        }

        $this->dispatch('open-church-modal');
    }

    public function updatePersonal(): void
    {
        $this->authorizeProfileUpdate();

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
        $this->authorizeProfileUpdate();

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
        abort_if($this->isManagingAnotherUser, 404);

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

    public function openPhotoModal(): void
    {
        $this->resetValidation();
        $this->profilePhotoUpload = null;
        $this->showPhotoModal = true;
    }

    public function updatedProfilePhotoUpload(): void
    {
        $this->resetErrorBag('profilePhotoUpload');

        if ($this->profilePhotoUpload === null) {
            return;
        }

        $this->validateOnly('profilePhotoUpload', $this->profilePhotoRules(), $this->profilePhotoMessages());
    }

    public function updateProfilePhoto(): void
    {
        $this->authorizeProfileUpdate();

        $validated = $this->validate($this->profilePhotoRules(), $this->profilePhotoMessages());
        $this->savingProfilePhoto = true;

        try {
            $previousPhotoPath = trim((string) $this->user->getRawOriginal('profile_photo_path'));
            $newPhotoPath = $validated['profilePhotoUpload']->storePublicly("profile-photos/{$this->user->id}", 'public');

            $this->user->forceFill([
                'profile_photo_path' => $newPhotoPath,
            ])->save();

            if ($previousPhotoPath !== '' && Storage::disk('public')->exists($previousPhotoPath)) {
                Storage::disk('public')->delete($previousPhotoPath);
            }

            $this->refreshUser();
            $this->profilePhotoUpload = null;
            $this->showPhotoModal = false;

            $this->dispatch('profile-photo-updated');
        } finally {
            $this->savingProfilePhoto = false;
        }
    }

    public function removeProfilePhoto(): void
    {
        $this->authorizeProfileUpdate();

        $photoPath = trim((string) $this->user->getRawOriginal('profile_photo_path'));

        if ($photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $this->user->forceFill([
            'profile_photo_path' => null,
        ])->save();

        $this->refreshUser();
        $this->profilePhotoUpload = null;
        $this->showPhotoModal = false;

        $this->dispatch('profile-photo-removed');
    }

    public function deleteProfile(): void
    {
        abort_if(! $this->isManagingAnotherUser, 404);

        Gate::authorize('manageChurches');

        $this->validate([
            'deletePassword' => $this->currentPasswordRules(),
        ], attributes: [
            'deletePassword' => __('senha'),
        ]);

        $photoPath = trim((string) $this->user->getRawOriginal('profile_photo_path'));

        $this->user->delete();

        if ($photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $this->dispatch('profile-deleted');
        $this->redirect($this->managedProfileRedirectUrl(), navigate: true);
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

    public function closePhotoModal(): void
    {
        $this->resetValidation();
        $this->profilePhotoUpload = null;
        $this->showPhotoModal = false;
    }

    public function openDeleteModal(): void
    {
        abort_if(! $this->isManagingAnotherUser, 404);

        Gate::authorize('manageChurches');

        $this->resetValidation();
        $this->deletePassword = '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->resetValidation();
        $this->deletePassword = '';
        $this->showDeleteModal = false;
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
        ]);

        return $parts !== [] ? implode(', ', $parts) : __('Não informado');
    }

    public function profilePhotoUrl(): ?string
    {
        if ($this->profilePhotoUpload && str_starts_with((string) $this->profilePhotoUpload->getMimeType(), 'image/')) {
            return $this->profilePhotoUpload->temporaryUrl();
        }

        return $this->user->profile_photo_url;
    }

    public function render(): View
    {
        return view('livewire.pages.app.settings.profile')
            ->with([
                'managedTeacherTrainings' => $this->managedTeacherTrainings(),
            ])
            ->layout('components.layouts.app', [
                'title' => $this->user->name ?: __('Perfil do usuário'),
            ]);
    }

    /**
     * @return Collection<int, array{assignment_label: string, training: Training, first_event_date: ?string}>
     */
    private function managedTeacherTrainings(): Collection
    {
        if (! $this->isManagingAnotherUser || ! $this->user->hasRole('Teacher')) {
            return collect();
        }

        $relationshipLoader = fn ($query) => $query
            ->with([
                'course:id,name,type',
                'church:id,name',
                'eventDates:id,training_id,date,start_time',
            ])
            ->withCount('students');

        $ledTrainings = $relationshipLoader($this->user->ledTrainings())
            ->get()
            ->map(fn (Training $training): array => [
                'assignment_label' => __('Titular'),
                'training' => $training,
                'first_event_date' => $training->eventDates
                    ->sortBy(fn ($eventDate) => sprintf(
                        '%s %s',
                        (string) ($eventDate->date ?? ''),
                        (string) ($eventDate->start_time ?? ''),
                    ))
                    ->first()?->date,
            ]);

        $assistedTrainings = $relationshipLoader($this->user->assistedTrainings())
            ->get()
            ->map(fn (Training $training): array => [
                'assignment_label' => __('Auxiliar'),
                'training' => $training,
                'first_event_date' => $training->eventDates
                    ->sortBy(fn ($eventDate) => sprintf(
                        '%s %s',
                        (string) ($eventDate->date ?? ''),
                        (string) ($eventDate->start_time ?? ''),
                    ))
                    ->first()?->date,
            ]);

        return $ledTrainings
            ->concat($assistedTrainings)
            ->sortBy([
                fn (array $item): int => $item['first_event_date'] === null ? 1 : 0,
                fn (array $item): string => $item['first_event_date'] ?? '',
                fn (array $item): int => $item['training']->id,
            ])
            ->reverse()
            ->values();
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
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
        $this->user->loadMissing(['roles', 'church', 'church_temp', 'hostChurches.church', 'churches']);
    }

    protected function authorizeProfileUpdate(): void
    {
        if ($this->isManagingAnotherUser) {
            Gate::authorize('manageChurches');

            return;
        }

        $this->authorize('update', $this->user);
    }

    protected function managedProfileRedirectUrl(): string
    {
        if (Gate::allows('access-director')) {
            return route('app.director.dashboard');
        }

        return route('app.profile');
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

    /**
     * @return array<string, array<int, string>>
     */
    protected function profilePhotoRules(): array
    {
        return [
            'profilePhotoUpload' => ['required', 'image', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function profilePhotoMessages(): array
    {
        return [
            'profilePhotoUpload.required' => __('Selecione uma imagem para a foto do perfil.'),
            'profilePhotoUpload.image' => __('O arquivo enviado precisa ser uma imagem válida.'),
            'profilePhotoUpload.max' => __('A foto do perfil deve ter no máximo 5 MB.'),
        ];
    }
}
