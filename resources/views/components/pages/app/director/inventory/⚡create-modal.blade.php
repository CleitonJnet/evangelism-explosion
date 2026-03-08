<?php

use App\Models\Material;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $type = 'simple';

    public string $status = 'active';

    public ?string $price = null;

    public int $minimum_stock = 0;

    public ?string $description = null;

    #[On('open-director-material-create-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
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

            $material = Material::query()->create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'is_active' => $validated['status'] === 'active',
                'price' => $validated['price'] ?? '0',
                'minimum_stock' => $validated['minimum_stock'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->dispatch('director-material-created', materialId: $material->id);

            $this->closeModal();
            $this->redirectRoute('app.director.inventory.show', ['inventory' => $material->id], navigate: true);
        } finally {
            $this->busy = false;
        }
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->type = 'simple';
        $this->status = 'active';
        $this->price = null;
        $this->minimum_stock = 0;
        $this->description = null;
    }
};
?>

<div>
    <flux:modal name="director-material-create-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar novo material') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Defina os dados principais do material e identifique se ele é simples ou composto.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-8">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados principais') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informações base usadas em cadastro, vínculos e controle de estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-create-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="320" required />
                            <x-src.form.select name="director-material-create-type" wire:model.live="type" label="Tipo"
                                width_basic="180" :options="[
                                    ['value' => 'simple', 'label' => __('Simples')],
                                    ['value' => 'composite', 'label' => __('Composto')],
                                ]" required />
                            <x-src.form.select name="director-material-create-status" wire:model.live="status"
                                label="Status" width_basic="180" :options="[
                                    ['value' => 'active', 'label' => __('Ativo')],
                                    ['value' => 'inactive', 'label' => __('Inativo')],
                                ]" required />
                            <x-src.form.input name="director-material-create-price" wire:model.live="price" label="Preço"
                                type="text" width_basic="180" inputmode="decimal" autocomplete="off"
                                oninput="this.value = this.value.replace(/[^0-9,.-]/g, '')" />
                            <x-src.form.input name="director-material-create-minimum-stock"
                                wire:model.live="minimum_stock" label="Estoque mínimo" type="number"
                                width_basic="180" min="0" required />
                            <x-src.form.textarea name="director-material-create-description"
                                wire:model.live="description" label="Descrição" rows="4" />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-semibold text-slate-900">{{ __('Próximos passos') }}</h4>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ __('Após salvar, você poderá vincular cursos, fornecedores e, se for composto, montar a composição do kit.') }}
                        </p>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        {{ __('Salvar') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
