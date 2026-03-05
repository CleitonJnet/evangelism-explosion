<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public mixed $logoUpload = null;

    public string $initials = '';

    public string $name = '';

    public ?string $color = null;

    public ?string $description = null;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'logoUpload' => ['nullable', 'image', 'max:5120'],
            'initials' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
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
            'name' => 'nome',
            'color' => 'cor',
            'description' => 'descrição',
        ];
    }

    public function updated(string $property): void
    {
        if (! array_key_exists($property, $this->rules())) {
            return;
        }

        $this->validateOnly($property);
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $logoPath = null;
        if ($this->logoUpload) {
            $logoPath = $this->logoUpload->store('ministry-logos', 'public');
        }

        Ministry::query()->create([
            'initials' => $validated['initials'],
            'name' => $validated['name'],
            'logo' => $logoPath,
            'color' => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.ministry.create');
    }
}
