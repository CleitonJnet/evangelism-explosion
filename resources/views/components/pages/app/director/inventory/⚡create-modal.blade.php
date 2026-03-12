<?php

use App\Helpers\MoneyHelper;
use App\Models\Ministry;
use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $type = 'simple';

    public string $status = 'active';

    public ?string $price = null;

    public int $minimum_stock = 0;

    public ?string $description = null;

    public mixed $photoUpload = null;

    /**
     * @var array<int>
     */
    public array $selectedCourseIds = [];

    /**
     * @var array<int>
     */
    public array $selectedComponentIds = [];

    /**
     * @var array<int, int>
     */
    public array $componentQuantities = [];

    #[On('open-director-material-create-modal')]
    public function openModal(?string $type = null): void
    {
        $this->resetForm();

        if (in_array($type, ['simple', 'composite'], true)) {
            $this->type = $type;
        }

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
                'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
                'minimum_stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string', 'max:2000'],
                'photoUpload' => ['nullable', 'image', 'max:5120'],
                'selectedCourseIds' => ['array'],
                'selectedCourseIds.*' => ['integer', 'exists:courses,id'],
                'selectedComponentIds' => ['array'],
                'selectedComponentIds.*' => [
                    'integer',
                    Rule::exists('materials', 'id')->where(fn ($query) => $query->where('type', 'simple')),
                    'distinct',
                ],
            ], [
                'required' => 'O campo :attribute é obrigatório.',
                'in' => 'O valor informado para :attribute é inválido.',
                'integer' => 'O campo :attribute deve ser um número inteiro.',
                'min' => 'O campo :attribute deve ser no mínimo :min.',
                'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                'image' => 'O campo :attribute deve ser uma imagem válida.',
                'price.regex' => 'O campo preço deve conter apenas números e separador decimal.',
                'selectedComponentIds.*.exists' => 'Somente itens simples podem compor um produto composto.',
                'selectedComponentIds.*.distinct' => 'O mesmo componente não pode ser informado mais de uma vez.',
            ], [
                'name' => 'nome',
                'type' => 'tipo',
                'price' => 'preço',
                'minimum_stock' => 'estoque mínimo',
                'description' => 'descrição',
                'photoUpload' => 'foto',
                'selectedCourseIds' => 'cursos',
                'selectedComponentIds' => 'componentes',
            ]);

            $componentPayload = [];

            if ($validated['type'] === 'composite') {
                foreach ($validated['selectedComponentIds'] ?? [] as $selectedComponentId) {
                    $selectedComponentId = (int) $selectedComponentId;
                    $quantity = (int) ($this->componentQuantities[$selectedComponentId] ?? 0);

                    if ($quantity < 1) {
                        $this->addError('componentQuantities.'.$selectedComponentId, __('A quantidade deve ser maior que zero.'));

                        return;
                    }

                    $componentPayload[$selectedComponentId] = ['quantity' => $quantity];
                }
            }

            $material = Material::query()->create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'status' => 'active',
                'is_active' => true,
                'photo' => $this->storeUploadedPhoto(),
                'price' => $validated['price'] ?? '0',
                'minimum_stock' => $validated['minimum_stock'],
                'description' => $validated['description'] ?? null,
            ]);

            $material->courses()->sync($validated['selectedCourseIds'] ?? []);

            if ($validated['type'] === 'composite') {
                $material->componentMaterials()->sync($componentPayload);
            }

            $this->dispatch(
                'director-material-created',
                materialId: $material->id,
                type: $material->type,
                isActive: $material->is_active,
                hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(),
            );
            $this->dispatch('toast', type: 'success', message: __('Material cadastrado com sucesso.'));

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    /**
     * @return array<int, \App\Models\Ministry>
     */
    public function ministries(): array
    {
        return Ministry::query()
            ->with(['courses' => fn ($query) => $query->orderBy('order')->orderBy('id')])
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     groups: array<int, array{
     *         label: string|null,
     *         courses: \Illuminate\Support\Collection<int, \App\Models\Course>
     *     }>
     * }>
     */
    public function ministryCourseGroups(): array
    {
        return collect($this->ministries())
            ->map(function (Ministry $ministry): array {
                $courses = collect($ministry->courses);

                if ($courses->count() <= 2) {
                    return [
                        'id' => (int) $ministry->id,
                        'name' => (string) $ministry->name,
                        'groups' => [
                            [
                                'label' => null,
                                'courses' => $courses->values(),
                            ],
                        ],
                    ];
                }

                $groups = collect([
                    [
                        'label' => __('Liderança'),
                        'courses' => $courses->where('execution', 0)->values(),
                    ],
                    [
                        'label' => __('Implementação'),
                        'courses' => $courses->where('execution', 1)->values(),
                    ],
                ])->filter(fn (array $group): bool => $group['courses']->isNotEmpty())->values()->all();

                return [
                    'id' => (int) $ministry->id,
                    'name' => (string) $ministry->name,
                    'groups' => $groups,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, \App\Models\Material>
     */
    public function availableSimpleMaterials(): array
    {
        return Material::query()
            ->where('type', 'simple')
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function updatedSelectedComponentIds(): void
    {
        foreach ($this->selectedComponentIds as $selectedComponentId) {
            $selectedComponentId = (int) $selectedComponentId;

            if (! array_key_exists($selectedComponentId, $this->componentQuantities)) {
                $this->componentQuantities[$selectedComponentId] = 1;
            }
        }
    }

    private function hasActiveSimpleMaterials(): bool
    {
        return Material::query()
            ->where('type', 'simple')
            ->where('is_active', true)
            ->exists();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->type = 'simple';
        $this->status = 'active';
        $this->price = MoneyHelper::formatInput(0, '0,00');
        $this->minimum_stock = 0;
        $this->description = null;
        $this->photoUpload = null;
        $this->selectedCourseIds = [];
        $this->selectedComponentIds = [];
        $this->componentQuantities = [];
    }

    private function storeUploadedPhoto(): ?string
    {
        if (! $this->photoUpload instanceof UploadedFile) {
            return null;
        }

        return $this->photoUpload->store('material-photos', 'public');
    }

    public function photoPreviewUrl(): string
    {
        if ($this->photoUpload && str_starts_with((string) $this->photoUpload->getMimeType(), 'image/')) {
            return $this->photoUpload->temporaryUrl();
        }

        return asset('images/logo/ee-gold.webp');
    }
};
?>

<div>
    <flux:modal name="director-material-create-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">
                    {{ $type === 'composite' ? __('Cadastrar novo produto composto') : __('Cadastrar novo item simples') }}
                </h3>
                <p class="text-sm opacity-90">
                    {{ $type === 'composite'
                        ? __('Defina os dados do composto e monte sua composição usando itens simples já cadastrados.')
                        : __('Defina os dados principais do item simples para uso em estoque, cursos e kits compostos.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-8">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Identidade visual') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Foto usada para identificar o produto nas listagens e operações de estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-6">
                            <div
                                class="grid justify-items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 flex-auto basis-40">
                                <input id="director-material-photo-upload" type="file" accept="image/*"
                                    wire:model.live="photoUpload" class="sr-only">

                                <label for="director-material-photo-upload"
                                    class="cursor-pointer overflow-hidden rounded-xl flex justify-center items-center p-1">
                                    <img src="{{ $this->photoPreviewUrl() }}" alt="{{ __('Foto do produto') }}"
                                        class="h-28 w-28 rounded-lg object-cover">
                                </label>

                                <p class="text-center text-xs text-slate-600">
                                    {{ __('Clique na imagem para enviar a foto.') }}
                                </p>

                                @error('photoUpload')
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados principais') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informações base usadas em cadastro, vínculo com cursos e controle de estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-create-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="320" autofocus required />
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

                    @if ($type === 'composite')
                        <section class="space-y-5">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Composição selecionada') }}</h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Confira abaixo os itens simples que já entrarão neste produto composto. Ajuste as quantidades antes de salvar.') }}
                                </p>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                        <tr>
                                            <th class="px-4 py-3">{{ __('Item selecionado') }}</th>
                                            <th class="px-4 py-3">{{ __('Quantidade') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($selectedMaterials = collect($this->availableSimpleMaterials())->whereIn('id', $selectedComponentIds)->values())
                                        @forelse ($selectedMaterials as $selectedMaterial)
                                            <tr class="border-t border-slate-200"
                                                wire:key="director-material-create-selected-component-{{ $selectedMaterial->id }}">
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-slate-900">{{ $selectedMaterial->name }}</div>
                                                    @if ($selectedMaterial->description)
                                                        <div class="mt-1 text-xs text-slate-500">
                                                            {{ $selectedMaterial->description }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="max-w-32">
                                                        <input
                                                            id="director-material-create-component-quantity-{{ $selectedMaterial->id }}"
                                                            name="component_quantities[{{ $selectedMaterial->id }}]"
                                                            type="number" min="1"
                                                            wire:model.live="componentQuantities.{{ $selectedMaterial->id }}"
                                                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-sky-500 focus:outline-none focus:ring-0">
                                                    </div>
                                                    @error('componentQuantities.'.$selectedMaterial->id)
                                                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">
                                                    {{ __('Nenhum item simples selecionado ainda para a composição.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="space-y-5">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Itens simples disponíveis') }}</h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Marque os itens simples que devem compor este produto. Esta lista mostra apenas itens simples já existentes no sistema.') }}
                                </p>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                        <tr>
                                            <th class="px-4 py-3">{{ __('Usar') }}</th>
                                            <th class="px-4 py-3">{{ __('Item simples') }}</th>
                                            <th class="px-4 py-3">{{ __('Estoque mínimo') }}</th>
                                            <th class="px-4 py-3">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($this->availableSimpleMaterials() as $availableMaterial)
                                            <tr class="border-t border-slate-200"
                                                wire:key="director-material-create-component-{{ $availableMaterial->id }}">
                                                <td class="px-4 py-3 align-top">
                                                    <input id="director-material-create-component-checkbox-{{ $availableMaterial->id }}"
                                                        name="selected_component_ids[]"
                                                        type="checkbox" value="{{ $availableMaterial->id }}"
                                                        wire:model.live="selectedComponentIds"
                                                        class="mt-1 rounded border-slate-300">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-slate-900">{{ $availableMaterial->name }}</div>
                                                    @if ($availableMaterial->description)
                                                        <div class="mt-1 text-xs text-slate-500">
                                                            {{ $availableMaterial->description }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-slate-700">{{ $availableMaterial->minimum_stock }}</td>
                                                <td class="px-4 py-3">
                                                    <span
                                                        class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $availableMaterial->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                        {{ $availableMaterial->is_active ? __('Ativo') : __('Inativo') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                                    {{ __('Nenhum item simples cadastrado ainda. Cadastre pelo menos um item simples antes de montar um composto.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Cursos que usam este material') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Selecione agora os cursos vinculados. Isso evita um passo separado depois do cadastro.') }}
                            </p>
                        </div>

                        <div class="space-y-5">
                            @foreach ($this->ministryCourseGroups() as $ministry)
                                <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    wire:key="director-material-create-ministry-{{ $ministry['id'] }}">
                                    <div class="mb-3">
                                        <h5 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                                            {{ $ministry['name'] }}
                                        </h5>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach ($ministry['groups'] as $group)
                                            <div class="space-y-3">
                                                @if ($group['label'] !== null)
                                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                        {{ $group['label'] }}
                                                    </div>
                                                @endif

                                                <div class="grid gap-3 md:grid-cols-2">
                                                    @forelse ($group['courses'] as $course)
                                                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                                            <input type="checkbox" value="{{ $course->id }}"
                                                                wire:model.live="selectedCourseIds" class="mt-1 rounded border-slate-300">
                                                            <div class="space-y-1">
                                                                <div class="font-semibold text-slate-900">
                                                                    {{ $course->type ? $course->type . ': ' : '' }}{{ $course->name }}
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    {{ $course->initials ?: __('Sem sigla') }}
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @empty
                                                        <div class="text-sm text-slate-500">{{ __('Nenhum curso neste ministério.') }}</div>
                                                    @endforelse
                                                </div>
                                            </div>

                                            @if (! $loop->last)
                                                <div class="border-t border-slate-200"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </section>

                    @if ($type === 'simple')
                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <h4 class="text-sm font-semibold text-slate-900">{{ __('Próximos passos') }}</h4>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ __('Depois do cadastro, este item simples já poderá receber saldo em estoque e também ser usado na composição de produtos compostos.') }}
                            </p>
                        </section>
                    @endif
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save,photoUpload">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,photoUpload">
                        {{ __('Salvar') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
