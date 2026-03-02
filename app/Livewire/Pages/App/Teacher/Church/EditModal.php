<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditModal extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public int $churchId;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $logoUpload = null;

    public string $church_name = '';

    public string $pastor_name = '';

    public string $phone_church = '';

    public ?string $church_email = null;

    public string $church_contact = '';

    public string $church_contact_phone = '';

    public ?string $church_contact_email = null;

    public ?string $church_notes = null;

    /**
     * @var array{postal_code: string, street: string, number: string, complement: string, district: string, city: string, state: string}
     */
    public array $churchAddress = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public function mount(int $churchId): void
    {
        $this->churchId = $churchId;
        $this->fillFromChurch();
    }

    #[On('open-teacher-church-edit-modal')]
    public function openModal(int $churchId): void
    {
        if ($churchId !== $this->churchId) {
            return;
        }

        $this->fillFromChurch();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->logoUpload = null;
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        foreach (array_keys($this->rules()) as $ruleKey) {
            if (Str::is($ruleKey, $property)) {
                $this->validateOnly($property);

                break;
            }
        }
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $church = Church::query()->findOrFail($this->churchId);
        $this->authorize('update', $church);

        $validated = $this->validate();
        $this->busy = true;

        try {
            DB::transaction(function () use ($church, $validated): void {
                $logoPath = $church->getRawOriginal('logo');

                if ($this->logoUpload) {
                    $logoPath = $this->logoUpload->store('church-logos', 'public');
                }

                $church->update([
                    'logo' => $logoPath,
                    'name' => $validated['church_name'],
                    'pastor' => $validated['pastor_name'],
                    'email' => $validated['church_email'] ?? null,
                    'phone' => $validated['phone_church'],
                    'contact' => $validated['church_contact'],
                    'contact_phone' => $validated['church_contact_phone'],
                    'contact_email' => $validated['church_contact_email'] ?? null,
                    'notes' => $validated['church_notes'] ?? null,
                    'postal_code' => $validated['churchAddress']['postal_code'],
                    'street' => $validated['churchAddress']['street'],
                    'number' => $validated['churchAddress']['number'],
                    'complement' => $validated['churchAddress']['complement'] ?? null,
                    'district' => $validated['churchAddress']['district'],
                    'city' => $validated['churchAddress']['city'],
                    'state' => strtoupper($validated['churchAddress']['state']),
                ]);
            });

            $this->dispatch('teacher-church-updated', churchId: $this->churchId);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.church.edit-modal', [
            'logoPreviewUrl' => $this->logoPreviewUrl(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'logoUpload' => ['nullable', 'image', 'max:5120'],
            'church_name' => ['required', 'string', 'max:255'],
            'pastor_name' => ['required', 'string', 'max:255'],
            'phone_church' => ['required', 'string', 'max:30'],
            'church_email' => ['nullable', 'email', 'max:255'],
            'church_contact' => ['required', 'string', 'max:255'],
            'church_contact_phone' => ['required', 'string', 'max:30'],
            'church_contact_email' => ['nullable', 'email', 'max:255'],
            'church_notes' => ['nullable', 'string', 'max:2000'],
            'churchAddress.postal_code' => ['required', 'string', 'max:20'],
            'churchAddress.street' => ['required', 'string', 'max:255'],
            'churchAddress.number' => ['required', 'string', 'max:20'],
            'churchAddress.complement' => ['nullable', 'string', 'max:255'],
            'churchAddress.district' => ['required', 'string', 'max:255'],
            'churchAddress.city' => ['required', 'string', 'max:255'],
            'churchAddress.state' => ['required', 'string', 'size:2'],
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
            'size' => 'O campo :attribute deve conter exatamente :size caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'image' => 'O campo :attribute deve ser uma imagem válida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'logoUpload' => 'logo da igreja',
            'church_name' => 'nome da igreja',
            'pastor_name' => 'nome do pastor titular',
            'phone_church' => 'telefone da igreja',
            'church_email' => 'e-mail da igreja',
            'church_contact' => 'nome do contato',
            'church_contact_phone' => 'telefone do contato',
            'church_contact_email' => 'e-mail do contato',
            'church_notes' => 'observações',
            'churchAddress.postal_code' => 'CEP',
            'churchAddress.street' => 'logradouro',
            'churchAddress.number' => 'número',
            'churchAddress.complement' => 'complemento',
            'churchAddress.district' => 'bairro',
            'churchAddress.city' => 'cidade',
            'churchAddress.state' => 'UF',
        ];
    }

    private function fillFromChurch(): void
    {
        $church = Church::query()->findOrFail($this->churchId);
        $this->authorize('update', $church);

        $this->church_name = (string) $church->name;
        $this->pastor_name = (string) ($church->pastor ?? '');
        $this->phone_church = (string) ($church->phone ?? '');
        $this->church_email = $church->email;
        $this->church_contact = (string) ($church->contact ?? '');
        $this->church_contact_phone = (string) ($church->contact_phone ?? '');
        $this->church_contact_email = $church->contact_email;
        $this->church_notes = $church->notes;

        $this->churchAddress = [
            'postal_code' => (string) ($church->postal_code ?? ''),
            'street' => (string) ($church->street ?? ''),
            'number' => (string) ($church->number ?? ''),
            'complement' => (string) ($church->complement ?? ''),
            'district' => (string) ($church->district ?? ''),
            'city' => (string) ($church->city ?? ''),
            'state' => (string) ($church->state ?? ''),
        ];
    }

    private function logoPreviewUrl(): string
    {
        if ($this->logoUpload && str_starts_with((string) $this->logoUpload->getMimeType(), 'image/')) {
            return $this->logoUpload->temporaryUrl();
        }

        $church = Church::query()->findOrFail($this->churchId);
        $logoPath = trim((string) $church->getRawOriginal('logo'));

        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->url($logoPath);
        }

        return asset('images/svg/church.svg');
    }
}
