<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $initials = '';
    public string $name = '';
    public ?string $logo = null;
    public ?string $color = null;
    public ?string $description = null;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'initials' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
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
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'initials' => 'sigla',
            'name' => 'nome',
            'logo' => 'logo',
            'color' => 'cor',
            'description' => 'descrição',
        ];
    }

    public function updated(string $property): void
    {
        if (!array_key_exists($property, $this->rules())) {
            return;
        }

        $this->validateOnly($property);
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Ministry::create($validated);
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.ministry.create');
    }
}
