<?php

use App\Models\Ministry;
use App\Models\Inventory;
use App\Models\Material;
use App\Services\Inventory\StockMovementService;
use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public int $materialId;

    public ?int $inventoryId = null;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $type = 'simple';

    public string $status = 'active';

    public ?string $price = null;

    public int $minimum_stock = 0;

    public ?string $description = null;

    public mixed $photoUpload = null;

    public ?string $currentPhotoPath = null;

    public bool $confirmingPermanentDelete = false;

    public string $activeTab = 'entry';

    public ?int $entry_quantity = null;

    public ?string $entry_notes = null;

    public ?int $exit_quantity = null;

    public ?string $exit_notes = null;

    public ?int $adjustment_target_quantity = null;

    public ?string $adjustment_notes = null;

    public ?int $loss_quantity = null;

    public ?string $loss_notes = null;

    public ?int $transfer_destination_inventory_id = null;

    public ?int $transfer_quantity = null;

    public ?string $transfer_notes = null;

    /**
     * @var array<int>
     */
    public array $selectedCourseIds = [];

    public function mount(int $materialId, ?int $inventoryId = null): void
    {
        $this->materialId = $materialId;
        $this->inventoryId = $inventoryId;
        $this->fillForm();
    }

    #[On('open-director-material-edit-modal')]
    public function openModal(?int $materialId = null, ?string $tab = null): void
    {
        if ($materialId !== null && $materialId !== $this->materialId) {
            return;
        }

        $this->fillForm();
        $this->activeTab = $this->resolveAllowedTab($tab);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->confirmingPermanentDelete = false;
        $this->resetValidation();
        $this->fillForm();
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, $this->availableTabs(), true)) {
            $this->activeTab = $tab;
            $this->resetValidation();
        }
    }

    public function saveEntry(): void
    {
        if ($this->busy) {
            return;
        }

        if ($this->inventoryId === null) {
            $this->dispatch('toast', type: 'error', message: __('Este produto não está associado a um estoque para entrada manual.'));

            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'entry_quantity' => ['required', 'integer', 'min:1'],
                    'entry_notes' => ['nullable', 'string', 'max:2000'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                ],
                [
                    'entry_quantity' => 'quantidade de entrada',
                    'entry_notes' => 'observação',
                ],
            );

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->addStock($inventory, $material, (int) $validated['entry_quantity'], Auth::user(), notes: $validated['entry_notes'] ?? null);

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Entrada manual registrada com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveExit(): void
    {
        if ($this->busy) {
            return;
        }

        if ($this->inventoryId === null) {
            $this->dispatch('toast', type: 'error', message: __('Este produto não está associado a um estoque para saída manual.'));

            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'exit_quantity' => ['required', 'integer', 'min:1'],
                    'exit_notes' => ['nullable', 'string', 'max:2000'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                ],
                [
                    'exit_quantity' => 'quantidade de saída',
                    'exit_notes' => 'observação',
                ],
            );

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);
            $service = app(StockMovementService::class);

            if ($material->isComposite()) {
                $service->removeCompositeMaterial($inventory, $material, (int) $validated['exit_quantity'], Auth::user(), notes: $validated['exit_notes'] ?? null, allowDynamicComposition: true);
            } else {
                $service->removeStock($inventory, $material, (int) $validated['exit_quantity'], Auth::user(), notes: $validated['exit_notes'] ?? null);
            }

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Saída manual registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException | InvalidCompositeMaterialException $exception) {
            $this->addError('exit_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function saveAdjustment(): void
    {
        if ($this->busy) {
            return;
        }

        if ($this->inventoryId === null) {
            $this->dispatch('toast', type: 'error', message: __('Este produto não está associado a um estoque para ajuste.'));

            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'adjustment_target_quantity' => ['required', 'integer', 'min:0'],
                    'adjustment_notes' => ['nullable', 'string', 'max:2000'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                ],
                [
                    'adjustment_target_quantity' => 'saldo alvo',
                    'adjustment_notes' => 'observação',
                ],
            );

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->adjustStock($inventory, $material, (int) $validated['adjustment_target_quantity'], Auth::user(), notes: $validated['adjustment_notes'] ?? null);

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Ajuste de saldo aplicado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveLoss(): void
    {
        if ($this->busy) {
            return;
        }

        if ($this->inventoryId === null) {
            $this->dispatch('toast', type: 'error', message: __('Este produto não está associado a um estoque para perda.'));

            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'loss_quantity' => ['required', 'integer', 'min:1'],
                    'loss_notes' => ['nullable', 'string', 'max:2000'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                ],
                [
                    'loss_quantity' => 'quantidade perdida',
                    'loss_notes' => 'observação',
                ],
            );

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->registerLoss($inventory, $material, (int) $validated['loss_quantity'], Auth::user(), notes: $validated['loss_notes'] ?? null);

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Perda/avaria registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException $exception) {
            $this->addError('loss_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function saveTransfer(): void
    {
        if ($this->busy) {
            return;
        }

        if ($this->inventoryId === null) {
            $this->dispatch('toast', type: 'error', message: __('Este produto não está associado a um estoque para transferência.'));

            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'transfer_destination_inventory_id' => ['required', 'integer', 'exists:inventories,id', 'different:inventoryId'],
                    'transfer_quantity' => ['required', 'integer', 'min:1'],
                    'transfer_notes' => ['nullable', 'string', 'max:2000'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                    'different' => 'Escolha um estoque de destino diferente do estoque atual.',
                ],
                [
                    'transfer_destination_inventory_id' => 'estoque de destino',
                    'transfer_quantity' => 'quantidade',
                    'transfer_notes' => 'observação',
                ],
            );

            $sourceInventory = Inventory::query()->findOrFail($this->inventoryId);
            $destinationInventory = Inventory::query()->findOrFail((int) $validated['transfer_destination_inventory_id']);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->transferStock($sourceInventory, $destinationInventory, $material, (int) $validated['transfer_quantity'], Auth::user(), notes: $validated['transfer_notes'] ?? null);

            $this->dispatch('director-inventory-stock-updated', inventoryId: $sourceInventory->id);
            $this->dispatch('director-inventory-stock-updated', inventoryId: $destinationInventory->id);
            $this->dispatch('toast', type: 'success', message: __('Transferência registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException $exception) {
            $this->addError('transfer_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } catch (\InvalidArgumentException $exception) {
            $this->addError('transfer_destination_inventory_id', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                    'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
                    'minimum_stock' => ['required', 'integer', 'min:0'],
                    'description' => ['nullable', 'string', 'max:2000'],
                    'photoUpload' => ['nullable', 'image', 'max:5120'],
                    'selectedCourseIds' => ['array'],
                    'selectedCourseIds.*' => ['integer', 'exists:courses,id'],
                ],
                [
                    'required' => 'O campo :attribute é obrigatório.',
                    'in' => 'O valor informado para :attribute é inválido.',
                    'integer' => 'O campo :attribute deve ser um número inteiro.',
                    'min' => 'O campo :attribute deve ser no mínimo :min.',
                    'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
                    'image' => 'O campo :attribute deve ser uma imagem válida.',
                    'price.regex' => 'O campo preço deve conter apenas números e separador decimal.',
                ],
                [
                    'name' => 'nome',
                    'price' => 'preço',
                    'minimum_stock' => 'estoque mínimo',
                    'description' => 'descrição',
                    'photoUpload' => 'foto',
                    'selectedCourseIds' => 'cursos',
                ],
            );

            $material = Material::query()->findOrFail($this->materialId);
            $photoPath = $this->currentPhotoPath;

            if ($this->photoUpload instanceof UploadedFile) {
                $storedPhotoPath = $this->photoUpload->store('material-photos', 'public');

                if ($photoPath && !str_starts_with($photoPath, 'http') && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }

                $photoPath = $storedPhotoPath;
            }

            $material
                ->forceFill([
                    'name' => $validated['name'],
                    'photo' => $photoPath,
                    'price' => $validated['price'] ?? '0',
                    'minimum_stock' => $validated['minimum_stock'],
                    'description' => $validated['description'] ?? null,
                ])
                ->save();
            $material->courses()->sync($validated['selectedCourseIds'] ?? []);

            $this->dispatch('director-material-updated', materialId: $material->id, type: $material->type, isActive: $material->is_active, hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(), inventoryId: $this->inventoryId, hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory());
            $this->dispatch('toast', type: 'success', message: __('Material atualizado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function toggleActive(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $material = Material::query()->findOrFail($this->materialId);
            $material
                ->forceFill([
                    'is_active' => !$material->is_active,
                    'status' => $material->is_active ? 'inactive' : 'active',
                ])
                ->save();

            $this->dispatch('director-material-updated', materialId: $material->id, type: $material->type, isActive: $material->is_active, hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(), inventoryId: $this->inventoryId, hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory());
            $this->dispatch('toast', type: 'success', message: $material->is_active ? __('Material reativado com sucesso.') : __('Material inativado com sucesso.'));

            $activeTab = $this->activeTab;

            $this->fillForm(resetTab: false);
            $this->activeTab = $activeTab;
            $this->confirmingPermanentDelete = false;
        } finally {
            $this->busy = false;
        }
    }

    public function confirmPermanentDelete(): void
    {
        $this->confirmingPermanentDelete = true;
    }

    public function cancelPermanentDelete(): void
    {
        $this->confirmingPermanentDelete = false;
    }

    public function deletePermanently(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $material = Material::query()->findOrFail($this->materialId);
            $blockers = $material->deletionBlockers();

            if ($blockers !== []) {
                $this->dispatch('toast', type: 'error', message: __('Este material não pode ser excluído permanentemente.'));

                foreach ($blockers as $index => $blocker) {
                    $this->addError('delete_blocker_' . $index, $blocker);
                }

                $this->confirmingPermanentDelete = false;

                return;
            }

            $photoPath = trim((string) $material->getRawOriginal('photo'));

            if ($photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            $material->delete();

            $this->dispatch('director-material-deleted', materialId: $this->materialId, type: $material->type, hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(), inventoryId: $this->inventoryId, hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory());
            $this->dispatch('toast', type: 'success', message: __('Material excluído permanentemente.'));
            $this->showModal = false;
            $this->confirmingPermanentDelete = false;
            $this->resetValidation();
        } catch (\Throwable) {
            $this->dispatch('toast', type: 'error', message: __('Não foi possível excluir o material agora.'));
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
            ->with(['courses' => fn($query) => $query->orderBy('order')->orderBy('id')])
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
                ])
                    ->filter(fn(array $group): bool => $group['courses']->isNotEmpty())
                    ->values()
                    ->all();

                return [
                    'id' => (int) $ministry->id,
                    'name' => (string) $ministry->name,
                    'groups' => $groups,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{value:int,label:string}>
     */
    public function destinationOptions(): array
    {
        if ($this->inventoryId === null) {
            return [];
        }

        return Inventory::query()
            ->with('responsibleUser:id,name')
            ->whereKeyNot($this->inventoryId)
            ->orderBy('name')
            ->get()
            ->map(function (Inventory $inventory): array {
                $suffix = $inventory->kind === 'teacher' ? ' - ' . ($inventory->responsibleUser?->name ?: __('Sem professor')) : ' - ' . __('Central');

                return [
                    'value' => $inventory->id,
                    'label' => $inventory->name . $suffix,
                ];
            })
            ->all();
    }

    private function fillForm(bool $resetTab = true): void
    {
        $material = Material::query()->with('courses:id')->findOrFail($this->materialId);
        $currentInventoryQuantity = $this->inventoryId !== null ? Inventory::query()->find($this->inventoryId)?->currentQuantityFor($material) : 0;

        $this->name = (string) $material->name;
        $this->type = (string) ($material->type ?: 'simple');
        $this->status = $material->is_active ? 'active' : 'inactive';
        $this->currentPhotoPath = $material->getRawOriginal('photo');
        $this->photoUpload = null;
        $this->price = $material->price !== null ? (string) $material->price : null;
        $this->minimum_stock = (int) $material->minimum_stock;
        $this->description = $material->description;
        $this->selectedCourseIds = $material->courses->pluck('id')->map(fn($id) => (int) $id)->all();
        $this->confirmingPermanentDelete = false;

        if ($resetTab) {
            $this->activeTab = $this->resolveAllowedTab();
        }

        $this->entry_quantity = null;
        $this->entry_notes = null;
        $this->exit_quantity = null;
        $this->exit_notes = null;
        $this->adjustment_target_quantity = $currentInventoryQuantity;
        $this->adjustment_notes = null;
        $this->loss_quantity = null;
        $this->loss_notes = null;
        $this->transfer_destination_inventory_id = null;
        $this->transfer_quantity = null;
        $this->transfer_notes = null;
        $this->resetErrorBag();
    }

    private function hasActiveSimpleMaterials(): bool
    {
        return Material::query()->where('type', 'simple')->where('is_active', true)->exists();
    }

    private function hasActiveSimpleMaterialsInInventory(): bool
    {
        if ($this->inventoryId === null) {
            return $this->hasActiveSimpleMaterials();
        }

        return Inventory::query()->find($this->inventoryId)?->hasActiveSimpleMaterialsWithStock() ?? false;
    }

    /**
     * @return array<int, string>
     */
    private function availableTabs(): array
    {
        if ($this->type === 'composite') {
            return $this->inventoryId === null ? ['edit'] : ['exit', 'edit'];
        }

        return ['entry', 'exit', 'adjustment', 'loss', 'transfer', 'edit'];
    }

    private function resolveAllowedTab(?string $requestedTab = null): string
    {
        $availableTabs = $this->availableTabs();

        if ($requestedTab !== null && in_array($requestedTab, $availableTabs, true)) {
            return $requestedTab;
        }

        return $availableTabs[0];
    }

    public function photoPreviewUrl(): string
    {
        if ($this->photoUpload && str_starts_with((string) $this->photoUpload->getMimeType(), 'image/')) {
            return $this->photoUpload->temporaryUrl();
        }

        $photoPath = trim((string) $this->currentPhotoPath);

        if ($photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
            return Storage::disk('public')->url($photoPath);
        }

        return asset('images/logo/ee-gold.webp');
    }
};
?>

<div>
    <flux:modal name="director-material-edit-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        @php
            $headerCourses = collect($this->ministries())
                ->flatMap->courses->whereIn('id', $selectedCourseIds)
                ->values();

            $badgeTextColor = static function (?string $hexColor): string {
                $color = trim((string) $hexColor);

                if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                    return '#0f172a';
                }

                $normalized = ltrim($color, '#');

                if (strlen($normalized) === 3) {
                    $normalized = collect(str_split($normalized))
                        ->map(fn(string $char): string => $char . $char)
                        ->implode('');
                }

                $red = hexdec(substr($normalized, 0, 2));
                $green = hexdec(substr($normalized, 2, 2));
                $blue = hexdec(substr($normalized, 4, 2));

                $luminance = ($red * 299 + $green * 587 + $blue * 114) / 1000;

                return $luminance > 150 ? '#0f172a' : '#f8fafc';
            };

            $badgeBackground = static function (?string $hexColor): string {
                $color = trim((string) $hexColor);

                if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                    return '#e2e8f0';
                }

                return $color;
            };
        @endphp
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold">{{ $name ?: __('Produto sem nome') }}</h3>
                            @if ($status === 'inactive')
                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ __('Inativo') }}
                                </span>
                            @endif
                        </div>
                        <div class="pt-1">
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($headerCourses as $course)
                                    @php($courseTooltip = $course->type ? $course->type . ': ' . $course->name : $course->name)
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                        title="{{ $courseTooltip }}" aria-label="{{ $courseTooltip }}"
                                        style="background-color: {{ $badgeBackground($course->color) }}; color: {{ $badgeTextColor($course->color) }};">
                                        {{ $course->initials ?: $course->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-sky-100/75">{{ __('Nenhum curso vinculado') }}</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="pt-1 text-right">
                        <div
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $type === 'composite' ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">
                            {{ $type === 'composite' ? __('Produto composto') : __('Item simples') }}
                        </div>
                    </div>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                @if (count($this->availableTabs()) > 1)
                    <div class="mb-6 overflow-x-auto border-b border-slate-200 pb-4">
                        <div class="flex min-w-max gap-2 rounded-2xl bg-slate-100/80 p-2 md:min-w-0 md:w-full">
                            @if (in_array('entry', $this->availableTabs(), true))
                                <button type="button" wire:click="selectTab('entry')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'entry' ? 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-emerald-100 hover:bg-emerald-50/60 hover:text-emerald-800' }}">
                                    {{ __('Entrada manual') }}
                                </button>
                            @endif
                            <button type="button" wire:click="selectTab('exit')"
                                class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'exit' ? 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-amber-100 hover:bg-amber-50/60 hover:text-amber-800' }}">
                                {{ __('Saída manual') }}
                            </button>
                            @if (in_array('adjustment', $this->availableTabs(), true))
                                <button type="button" wire:click="selectTab('adjustment')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'adjustment' ? 'rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-stone-100 hover:bg-stone-50/70 hover:text-stone-700' }}">
                                    {{ __('Ajuste') }}
                                </button>
                            @endif
                            @if (in_array('loss', $this->availableTabs(), true))
                                <button type="button" wire:click="selectTab('loss')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'loss' ? 'rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-rose-100 hover:bg-rose-50/60 hover:text-rose-800' }}">
                                    {{ __('Perda') }}
                                </button>
                            @endif
                            @if (in_array('transfer', $this->availableTabs(), true))
                                <button type="button" wire:click="selectTab('transfer')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'transfer' ? 'rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-sky-100 hover:bg-sky-50/60 hover:text-sky-800' }}">
                                    {{ __('Transferir') }}
                                </button>
                            @endif
                            @if (in_array('composition', $this->availableTabs(), true))
                                <button type="button" wire:click="selectTab('composition')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'composition' ? 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-amber-100 hover:bg-amber-50/60 hover:text-amber-800' }}">
                                    {{ __('Composição') }}
                                </button>
                            @endif
                            <button type="button" wire:click="selectTab('edit')"
                                class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'edit' ? 'rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-indigo-100 hover:bg-indigo-50/60 hover:text-indigo-800' }}">
                                {{ __('Editar produto') }}
                            </button>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'composition')
                    <section class="space-y-4">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Itens do produto composto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Confira abaixo os itens simples que compõem este produto e a quantidade atualmente disponível neste estoque.') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-amber-200 bg-white">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-amber-100 text-xs uppercase text-amber-900">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Item') }}</th>
                                        <th class="w-40 px-4 py-3 text-center">{{ __('Qtd. no composto') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($compositionItems as $component)
                                        <tr class="border-t border-amber-100 odd:bg-white even:bg-amber-50/40">
                                            <td class="px-4 py-3 font-medium text-slate-900">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span>{{ $component['name'] }}</span>
                                                    @if (!$component['is_active'])
                                                        <span
                                                            class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                            {{ __('Inativo') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                                {{ $component['quantity'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-5 text-center text-sm text-slate-500">
                                                {{ __('Este produto composto ainda não possui itens vinculados.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif ($activeTab === 'entry')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Entrada manual do produto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informe quantas unidades devem entrar neste estoque. A quantidade informada será somada ao saldo atual do produto.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-entry-quantity" wire:model.live="entry_quantity"
                                label="Quantidade de entrada" type="number" width_basic="180" min="1"
                                required />
                            <x-src.form.textarea name="director-material-entry-notes" wire:model.live="entry_notes"
                                label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'exit')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Saída manual do produto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informe quantas unidades devem sair deste estoque. A quantidade informada será subtraída do saldo atual do produto. Produtos compostos também baixam seus componentes automaticamente.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-exit-quantity" wire:model.live="exit_quantity"
                                label="Quantidade de saída" type="number" width_basic="180" min="1" required />
                            <x-src.form.textarea name="director-material-exit-notes" wire:model.live="exit_notes"
                                label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'adjustment')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Ajuste de saldo do produto') }}
                            </h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Defina o saldo final consolidado deste produto no estoque atual.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-adjustment-target-quantity"
                                wire:model.live="adjustment_target_quantity" label="Saldo alvo" type="number"
                                width_basic="180" min="0" required />
                            <x-src.form.textarea name="director-material-adjustment-notes"
                                wire:model.live="adjustment_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'loss')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Registrar perda ou avaria') }}
                            </h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Use esta guia para registrar perdas, danos ou baixas não recuperáveis deste produto.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-material-loss-quantity" wire:model.live="loss_quantity"
                                label="Quantidade perdida" type="number" width_basic="180" min="1"
                                required />
                            <x-src.form.textarea name="director-material-loss-notes" wire:model.live="loss_notes"
                                label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'transfer')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Transferir produto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('A transferência gera saída no estoque atual e entrada no estoque de destino com o mesmo lote rastreável.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.select name="director-material-transfer-destination"
                                wire:model.live="transfer_destination_inventory_id" label="Estoque de destino"
                                width_basic="320" :options="$this->destinationOptions()" required />
                            <x-src.form.input name="director-material-transfer-quantity"
                                wire:model.live="transfer_quantity" label="Quantidade" type="number"
                                width_basic="180" min="1" required />
                            <x-src.form.textarea name="director-material-transfer-notes"
                                wire:model.live="transfer_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @else
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
                                    <input id="director-material-edit-photo-upload" type="file" accept="image/*"
                                        wire:model.live="photoUpload" class="sr-only">

                                    <label for="director-material-edit-photo-upload"
                                        class="cursor-pointer overflow-hidden rounded-xl flex justify-center items-center p-1">
                                        <img src="{{ $this->photoPreviewUrl() }}" alt="{{ __('Foto do produto') }}"
                                            class="h-28 w-28 rounded-lg object-cover">
                                    </label>

                                    <p class="text-center text-xs text-slate-600">
                                        {{ __('Clique na imagem para alterar a foto.') }}
                                    </p>

                                    @error('photoUpload')
                                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="space-y-5">
                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.input name="director-material-edit-name" wire:model.live="name"
                                    label="Nome" type="text" width_basic="320" required />
                                <x-src.form.input name="director-material-edit-price" wire:model.live="price"
                                    label="Preço" type="text" width_basic="180" inputmode="decimal"
                                    autocomplete="off" oninput="this.value = this.value.replace(/[^0-9,.-]/g, '')" />
                                <x-src.form.input name="director-material-edit-minimum-stock"
                                    wire:model.live="minimum_stock" label="Estoque mínimo" type="number"
                                    width_basic="180" min="0" required />
                                <x-src.form.textarea name="director-material-edit-description"
                                    wire:model.live="description" label="Descrição" rows="4" />
                            </div>
                        </section>

                        @if ($type === 'composite')
                            <section class="space-y-5">
                                <div>
                                    <h4 class="text-base font-semibold text-sky-950">{{ __('Gerenciar composição') }}
                                    </h4>
                                    <p class="text-sm text-slate-600">
                                        {{ __('Defina quais itens simples fazem parte deste produto composto e ajuste a quantidade de cada componente.') }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ __('Composição do produto composto') }}
                                            </div>
                                            <div class="text-sm text-slate-600">
                                                {{ __('Abra a composição para marcar, desmarcar e ajustar os componentes deste item.') }}
                                            </div>
                                        </div>

                                        <x-src.btn-silver type="button"
                                            onclick="window.Livewire.dispatch('open-director-material-components-modal', { materialId: {{ $materialId }} }); return false;">
                                            {{ __('Gerenciar composição') }}
                                        </x-src.btn-silver>
                                    </div>
                                </div>
                            </section>
                        @endif

                        <section class="space-y-5">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Cursos vinculados') }}</h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Marque ou desmarque os cursos que utilizam este produto. Ao salvar, os vínculos serão atualizados imediatamente.') }}
                                </p>
                            </div>

                            <div class="space-y-5">
                                @foreach ($this->ministryCourseGroups() as $ministry)
                                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                        wire:key="director-material-edit-ministry-{{ $ministry['id'] }}">
                                        <div class="mb-3">
                                            <h5 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                                                {{ $ministry['name'] }}
                                            </h5>
                                        </div>

                                        <div class="space-y-4">
                                            @foreach ($ministry['groups'] as $group)
                                                <div class="space-y-3">
                                                    @if ($group['label'] !== null)
                                                        <div
                                                            class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                            {{ $group['label'] }}
                                                        </div>
                                                    @endif

                                                    <div class="grid gap-3 md:grid-cols-2">
                                                        @forelse ($group['courses'] as $course)
                                                            <label
                                                                class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                                                <input type="checkbox" value="{{ $course->id }}"
                                                                    wire:model.live="selectedCourseIds"
                                                                    class="mt-1 rounded border-slate-300">
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
                                                            <div class="text-sm text-slate-500">
                                                                {{ __('Nenhum curso neste ministério.') }}</div>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                @if (!$loop->last)
                                                    <div class="border-t border-slate-200"></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endif

                @php($material = Material::query()->find($materialId))

                @if ($material && $activeTab === 'edit')
                    @php($blockers = $material->deletionBlockers())
                    @php($canDeletePermanently = $blockers === [])
                    <section class="mt-8 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-rose-900">{{ __('Ações sensíveis') }}</h4>
                                <p class="text-sm text-rose-800">
                                    {{ __('Inative este material para retirá-lo das operações. Exclua permanentemente apenas se ele nunca tiver sido usado.') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <x-src.btn-silver type="button" wire:click="toggleActive"
                                    wire:loading.attr="disabled" wire:target="toggleActive,deletePermanently,save">
                                    {{ $material->is_active ? __('Inativar material') : __('Reativar material') }}
                                </x-src.btn-silver>

                                @if (!$confirmingPermanentDelete)
                                    <button type="button" wire:click="confirmPermanentDelete"
                                        @disabled(!$canDeletePermanently) wire:loading.attr="disabled"
                                        wire:target="toggleActive,deletePermanently,save"
                                        class="{{ $canDeletePermanently
                                            ? 'inline-flex items-center rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 disabled:opacity-60'
                                            : 'inline-flex items-center rounded-xl border border-slate-300 bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500 opacity-80 cursor-not-allowed' }}">
                                        {{ __('Excluir permanentemente') }}
                                    </button>
                                @else
                                    <button type="button" wire:click="deletePermanently"
                                        wire:loading.attr="disabled" wire:target="deletePermanently"
                                        class="inline-flex items-center rounded-xl border border-rose-400 bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-60">
                                        {{ __('Confirmar exclusão') }}
                                    </button>
                                    <button type="button" wire:click="cancelPermanentDelete"
                                        wire:loading.attr="disabled" wire:target="deletePermanently"
                                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-60">
                                        {{ __('Cancelar exclusão') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($blockers !== [])
                            <div class="mt-4 rounded-xl border border-amber-200 bg-white px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                                    {{ __('Exclusão bloqueada no momento') }}
                                </div>
                                <ul class="mt-2 space-y-1 text-sm text-slate-700">
                                    @foreach ($blockers as $blocker)
                                        <li>{{ $blocker }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div
                                class="mt-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-emerald-800">
                                {{ __('Este material ainda não possui uso operacional e pode ser excluído permanentemente, se necessário.') }}
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-sky-100/80">
                        @if ($activeTab === 'edit')
                            {{ __('Os dados cadastrais só serão atualizados quando você clicar em salvar alterações.') }}
                        @elseif ($activeTab === 'transfer')
                            {{ __('Ao confirmar, a transferência será registrada imediatamente com saída neste estoque e entrada no destino.') }}
                        @else
                            {{ __('Ao confirmar, esta movimentação será registrada imediatamente no estoque e no histórico auditável.') }}
                        @endif
                    </div>

                    <div class="flex justify-between gap-3 md:justify-end">
                        <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                            wire:target="save,saveEntry,saveExit,saveAdjustment,saveLoss,saveTransfer,photoUpload">
                            {{ __('Fechar') }}
                        </x-src.btn-silver>
                        @if ($activeTab === 'entry')
                            <x-src.btn-gold type="button" wire:click="saveEntry" wire:loading.attr="disabled"
                                wire:target="saveEntry">
                                {{ __('Registrar entrada agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'exit')
                            <x-src.btn-gold type="button" wire:click="saveExit" wire:loading.attr="disabled"
                                wire:target="saveExit">
                                {{ __('Registrar saída agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'adjustment')
                            <x-src.btn-gold type="button" wire:click="saveAdjustment" wire:loading.attr="disabled"
                                wire:target="saveAdjustment">
                                {{ __('Aplicar ajuste agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'loss')
                            <x-src.btn-gold type="button" wire:click="saveLoss" wire:loading.attr="disabled"
                                wire:target="saveLoss">
                                {{ __('Registrar perda agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'transfer')
                            <x-src.btn-gold type="button" wire:click="saveTransfer" wire:loading.attr="disabled"
                                wire:target="saveTransfer">
                                {{ __('Transferir agora') }}
                            </x-src.btn-gold>
                        @else
                            <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                                wire:target="save,photoUpload">
                                {{ __('Salvar alterações') }}
                            </x-src.btn-gold>
                        @endif
                    </div>
                </div>
            </footer>
        </div>
    </flux:modal>

    @if ($type === 'composite')
        <livewire:pages.app.director.inventory.manage-components-modal :material-id="$materialId"
            wire:key="director-material-components-modal-{{ $materialId }}" />
    @endif
</div>
