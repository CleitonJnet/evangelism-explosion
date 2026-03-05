<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $logoUpload = null;

    public string $initials = '';

    public string $name = '';

    public string $color = '#4F4F4F';

    public ?string $description = null;

    #[On('open-director-ministry-create-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
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

            $logoPath = null;
            if ($this->logoUpload) {
                $logoPath = $this->logoUpload->store('ministry-logos', 'public');
            }

            $ministry = Ministry::query()->create([
                'initials' => $validated['initials'],
                'name' => $validated['name'],
                'logo' => $logoPath,
                'color' => $validated['color'] ?? '#4F4F4F',
                'description' => $validated['description'] ?? null,
            ]);

            $this->dispatch('director-ministry-created', ministryId: $ministry->id, ministryName: $ministry->name);

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.ministry.create-modal', [
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

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['logoUpload', 'initials', 'name', 'description']);
        $this->color = '#4F4F4F';
    }

    private function logoPreviewUrl(): string
    {
        if ($this->logoUpload && str_starts_with((string) $this->logoUpload->getMimeType(), 'image/')) {
            return $this->logoUpload->temporaryUrl();
        }

        return asset('images/logo/ee-gold.webp');
    }
}
