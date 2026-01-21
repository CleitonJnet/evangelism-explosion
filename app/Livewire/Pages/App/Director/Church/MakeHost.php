<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\Church;
use App\Models\HostChurch;
use Illuminate\View\View;
use Livewire\Component;

class MakeHost extends Component
{
    public ?int $church_id = null;
    public ?string $since_date = null;
    public ?string $notes = null;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'church_id' => ['required', 'integer', 'exists:churches,id', 'unique:host_churches,church_id'],
            'since_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'exists' => 'O :attribute selecionado é inválido.',
            'unique' => 'Esta igreja já está cadastrada como base.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'church_id' => 'igreja base',
            'since_date' => 'desde',
            'notes' => 'anotações',
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

        HostChurch::create($validated);
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.church.make-host', [
            'churches' => Church::query()
                ->with('hostChurch')
                ->orderBy('name')
                ->get()
                ->map(fn (Church $church): array => [
                    'value' => $church->id,
                    'label' => $church->name,
                    'disabled' => $church->hostChurch !== null,
                ])->toArray(),
        ]);
    }
}
