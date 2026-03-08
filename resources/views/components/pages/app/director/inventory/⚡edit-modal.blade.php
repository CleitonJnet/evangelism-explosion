<?php

use App\Models\Material;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $materialId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $type = 'simple';

    public string $status = 'active';

    public ?string $price = null;

    public int $minimum_stock = 0;

    public ?string $description = null;

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
        $this->fillForm();
    }

    #[On('open-director-material-edit-modal')]
    public function openModal(?int $materialId = null): void
    {
        if ($materialId !== null && $materialId !== $this->materialId) {
            return;
        }

        $this->fillForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->fillForm();
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:simple,composite'],
                'status' => ['required', 'in:active,inactive'],
                'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
                'minimum_stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string', 'max:2000'],
            ], [
                'required' => 'O campo :attribute é obrigatório.',
                'in' => 'O valor informado para :attribute é inválido.',
                'integer' => 'O campo :attribute deve ser um número inteiro.',
                'min' => 'O campo :attribute deve ser no mínimo :min.',
                'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                'price.regex' => 'O campo preço deve conter apenas números e separador decimal.',
            ], [
                'name' => 'nome',
                'type' => 'tipo',
                'status' => 'status',
                'price' => 'preço',
                'minimum_stock' => 'estoque mínimo',
                'description' => 'descrição',
            ]);

            $material = Material::query()->findOrFail($this->materialId);
            $material->forceFill([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'is_active' => $validated['status'] === 'active',
                'price' => $validated['price'] ?? '0',
                'minimum_stock' => $validated['minimum_stock'],
                'description' => $validated['description'] ?? null,
            ])->save();

            $this->dispatch('director-material-updated', materialId: $material->id);
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    private function fillForm(): void
    {
        $material = Material::query()->findOrFail($this->materialId);

        $this->name = (string) $material->name;
        $this->type = (string) ($material->type ?: 'simple');
        $this->status = $material->is_active ? 'active' : 'inactive';
        $this->price = $material->price !== null ? (string) $material->price : null;
        $this->minimum_stock = (int) $material->minimum_stock;
        $this->description = $material->description;
    }
};
?>

<div>
    <flux:modal name="director-material-edit-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Editar material') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Atualize os dados principais do material sem sair da página atual.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="flex flex-wrap gap-x-4 gap-y-8">
                    <x-src.form.input name="director-material-edit-name" wire:model.live="name" label="Nome"
                        type="text" width_basic="320" required />
                    <x-src.form.select name="director-material-edit-type" wire:model.live="type" label="Tipo"
                        width_basic="180" :options="[
                            ['value' => 'simple', 'label' => __('Simples')],
                            ['value' => 'composite', 'label' => __('Composto')],
                        ]" required />
                    <x-src.form.select name="director-material-edit-status" wire:model.live="status" label="Status"
                        width_basic="180" :options="[
                            ['value' => 'active', 'label' => __('Ativo')],
                            ['value' => 'inactive', 'label' => __('Inativo')],
                        ]" required />
                    <x-src.form.input name="director-material-edit-price" wire:model.live="price" label="Preço"
                        type="text" width_basic="180" inputmode="decimal" autocomplete="off"
                        oninput="this.value = this.value.replace(/[^0-9,.-]/g, '')" />
                    <x-src.form.input name="director-material-edit-minimum-stock" wire:model.live="minimum_stock"
                        label="Estoque mínimo" type="number" width_basic="180" min="0" required />
                    <x-src.form.textarea name="director-material-edit-description" wire:model.live="description"
                        label="Descrição" rows="4" />
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        {{ __('Salvar alterações') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
