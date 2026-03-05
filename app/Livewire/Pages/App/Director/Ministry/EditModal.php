<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $logoUpload = null;

    public int $ministryId;

    public string $initials = '';

    public string $name = '';

    public ?string $currentLogoPath = null;

    public string $color = '#4F4F4F';

    public ?string $description = null;

    public function mount(int $ministryId): void
    {
        $this->ministryId = $ministryId;
    }

    #[On('open-director-ministry-edit-modal')]
    public function openModal(): void
    {
        $this->fillFromModel();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        if (! array_key_exists($property, $this->rules())) {
            return;
        }

        $this->validateOnly($property);
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate();
            $ministry = Ministry::query()->findOrFail($this->ministryId);
            $logoPath = $ministry->logo;

            if ($this->logoUpload) {
                $storedLogoPath = $this->logoUpload->store('ministry-logos', 'public');

                if ($logoPath && ! str_starts_with($logoPath, 'http') && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }

                $logoPath = $storedLogoPath;
            }

            $ministry->forceFill([
                'initials' => $validated['initials'],
                'name' => $validated['name'],
                'logo' => $logoPath,
                'color' => $validated['color'] ?? '#4F4F4F',
                'description' => $validated['description'] ?? null,
            ])->save();

            $this->dispatch('director-ministry-updated', ministryId: $ministry->id);

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.ministry.edit-modal', [
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
            'initials' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'regex' => 'O campo :attribute deve estar no formato hexadecimal (#RGB ou #RRGGBB).',
            'image' => 'O campo :attribute deve ser uma imagem válida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'logoUpload' => 'logo',
            'initials' => 'sigla',
            'name' => 'nome do ministério',
            'color' => 'cor',
            'description' => 'descrição',
        ];
    }

    private function fillFromModel(): void
    {
        $ministry = Ministry::query()->findOrFail($this->ministryId);

        $this->initials = (string) $ministry->initials;
        $this->name = (string) $ministry->name;
        $this->currentLogoPath = $ministry->logo;
        $this->color = (string) ($ministry->color ?: '#4F4F4F');
        $this->description = $ministry->description;
        $this->logoUpload = null;
    }

    private function logoPreviewUrl(): string
    {
        if ($this->logoUpload && str_starts_with((string) $this->logoUpload->getMimeType(), 'image/')) {
            return $this->logoUpload->temporaryUrl();
        }

        if ($this->currentLogoPath) {
            if (str_starts_with($this->currentLogoPath, 'http')) {
                return $this->currentLogoPath;
            }

            if (Storage::disk('public')->exists($this->currentLogoPath)) {
                return Storage::disk('public')->url($this->currentLogoPath);
            }
        }

        return asset('images/logo/ee-gold.webp');
    }
}
