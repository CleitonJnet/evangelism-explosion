<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Ministry;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class MaterialEditModal extends Component
{
    use WithFileUploads;

    private const PHOTO_UPLOAD_MAX_KB = 10240;

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

    public function updatedPrice(?string $value): void
    {
        if ($value === null) {
            return;
        }

        $this->price = preg_replace('/[^0-9,.\-]/', '', $value) ?: null;
    }

    public function saveEntry(): void
    {
        if ($this->busy || $this->inventoryId === null) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'entry_quantity' => ['required', 'integer', 'min:1'],
                'entry_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->messages(), [
                'entry_quantity' => 'quantidade de entrada',
                'entry_notes' => 'observação',
            ]);

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->addStock(
                $inventory,
                $material,
                (int) $validated['entry_quantity'],
                Auth::user(),
                notes: $validated['entry_notes'] ?? null,
            );

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Entrada manual registrada com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveExit(): void
    {
        if ($this->busy || $this->inventoryId === null) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'exit_quantity' => ['required', 'integer', 'min:1'],
                'exit_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->messages(), [
                'exit_quantity' => 'quantidade de saída',
                'exit_notes' => 'observação',
            ]);

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);
            $service = app(StockMovementService::class);

            if ($material->isComposite()) {
                $service->removeCompositeMaterial(
                    $inventory,
                    $material,
                    (int) $validated['exit_quantity'],
                    Auth::user(),
                    notes: $validated['exit_notes'] ?? null,
                    allowDynamicComposition: true,
                );
            } else {
                $service->removeStock(
                    $inventory,
                    $material,
                    (int) $validated['exit_quantity'],
                    Auth::user(),
                    notes: $validated['exit_notes'] ?? null,
                );
            }

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Saída manual registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException|InvalidCompositeMaterialException $exception) {
            $this->addError('exit_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function saveAdjustment(): void
    {
        if ($this->busy || $this->inventoryId === null) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'adjustment_target_quantity' => ['required', 'integer', 'min:0'],
                'adjustment_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->messages(), [
                'adjustment_target_quantity' => 'saldo alvo',
                'adjustment_notes' => 'observação',
            ]);

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->adjustStock(
                $inventory,
                $material,
                (int) $validated['adjustment_target_quantity'],
                Auth::user(),
                notes: $validated['adjustment_notes'] ?? null,
            );

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Ajuste de saldo aplicado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveLoss(): void
    {
        if ($this->busy || $this->inventoryId === null) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'loss_quantity' => ['required', 'integer', 'min:1'],
                'loss_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->messages(), [
                'loss_quantity' => 'quantidade perdida',
                'loss_notes' => 'observação',
            ]);

            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->registerLoss(
                $inventory,
                $material,
                (int) $validated['loss_quantity'],
                Auth::user(),
                notes: $validated['loss_notes'] ?? null,
            );

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Perda registrada com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveTransfer(): void
    {
        if ($this->busy || $this->inventoryId === null) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate([
                'transfer_destination_inventory_id' => ['required', 'integer', 'exists:inventories,id', 'different:inventoryId'],
                'transfer_quantity' => ['required', 'integer', 'min:1'],
                'transfer_notes' => ['nullable', 'string', 'max:2000'],
            ], array_merge($this->messages(), [
                'different' => 'Escolha um estoque de destino diferente do estoque atual.',
            ]), [
                'transfer_destination_inventory_id' => 'estoque de destino',
                'transfer_quantity' => 'quantidade',
                'transfer_notes' => 'observação',
            ]);

            $sourceInventory = Inventory::query()->findOrFail($this->inventoryId);
            $destinationInventory = Inventory::query()->findOrFail((int) $validated['transfer_destination_inventory_id']);
            $material = Material::query()->findOrFail($this->materialId);

            app(StockMovementService::class)->transferStock(
                $sourceInventory,
                $destinationInventory,
                $material,
                (int) $validated['transfer_quantity'],
                Auth::user(),
                notes: $validated['transfer_notes'] ?? null,
            );

            $this->dispatch('director-inventory-stock-updated', inventoryId: $sourceInventory->id);
            $this->dispatch('director-inventory-stock-updated', inventoryId: $destinationInventory->id);
            $this->dispatch('toast', type: 'success', message: __('Transferência registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException $exception) {
            $this->addError('transfer_quantity', $exception->getMessage());
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
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
                'minimum_stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string', 'max:2000'],
                'photoUpload' => ['nullable', 'image', 'max:'.self::PHOTO_UPLOAD_MAX_KB],
                'selectedCourseIds' => ['array'],
                'selectedCourseIds.*' => ['integer', 'exists:courses,id'],
            ], $this->messages(), [
                'name' => 'nome',
                'price' => 'preço',
                'minimum_stock' => 'estoque mínimo',
                'description' => 'descrição',
                'photoUpload' => 'foto',
                'selectedCourseIds' => 'cursos',
            ]);

            $material = Material::query()->findOrFail($this->materialId);
            $photoPath = $this->currentPhotoPath;

            if ($this->photoUpload instanceof UploadedFile) {
                $storedPhotoPath = $this->photoUpload->store('material-photos', 'public');

                if ($photoPath && ! str_starts_with($photoPath, 'http') && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }

                $photoPath = $storedPhotoPath;
            }

            $material->forceFill([
                'name' => $validated['name'],
                'photo' => $photoPath,
                'price' => $validated['price'] ?? '0',
                'minimum_stock' => $validated['minimum_stock'],
                'description' => $validated['description'] ?? null,
            ])->save();
            $material->courses()->sync($validated['selectedCourseIds'] ?? []);

            $this->dispatch(
                'director-material-updated',
                materialId: $material->id,
                type: $material->type,
                isActive: $material->is_active,
                hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(),
                inventoryId: $this->inventoryId,
                hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory(),
            );
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
            $material->forceFill([
                'is_active' => ! $material->is_active,
                'status' => $material->is_active ? 'inactive' : 'active',
            ])->save();

            $this->dispatch(
                'director-material-updated',
                materialId: $material->id,
                type: $material->type,
                isActive: $material->is_active,
                hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(),
                inventoryId: $this->inventoryId,
                hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory(),
            );

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
                foreach ($blockers as $index => $blocker) {
                    $this->addError('delete_blocker_'.$index, $blocker);
                }

                $this->confirmingPermanentDelete = false;

                return;
            }

            $photoPath = trim((string) $material->getRawOriginal('photo'));

            if ($photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            $material->delete();

            $this->dispatch(
                'director-material-deleted',
                materialId: $this->materialId,
                type: $material->type,
                hasActiveSimpleMaterials: $this->hasActiveSimpleMaterials(),
                inventoryId: $this->inventoryId,
                hasActiveSimpleMaterialsInInventory: $this->hasActiveSimpleMaterialsInInventory(),
            );
            $this->showModal = false;
            $this->confirmingPermanentDelete = false;
            $this->resetValidation();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.material-edit-modal');
    }

    /**
     * @return array<int, Ministry>
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
                        'groups' => [[
                            'label' => null,
                            'courses' => $courses->values(),
                        ]],
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
                $suffix = $inventory->kind === 'teacher'
                    ? ' - '.($inventory->responsibleUser?->name ?: __('Sem professor'))
                    : ' - '.__('Central');

                return [
                    'value' => $inventory->id,
                    'label' => $inventory->name.$suffix,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function availableTabs(): array
    {
        if ($this->type === 'composite') {
            return $this->inventoryId === null ? ['edit'] : ['exit', 'edit'];
        }

        return ['entry', 'exit', 'adjustment', 'loss', 'transfer', 'edit'];
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

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'image' => 'O campo :attribute deve ser uma imagem válida.',
            'price.regex' => 'O campo preço deve conter apenas números e separador decimal.',
        ];
    }

    private function fillForm(bool $resetTab = true): void
    {
        $material = Material::query()
            ->with('courses:id')
            ->findOrFail($this->materialId);
        $currentInventoryQuantity = $this->inventoryId !== null
            ? Inventory::query()->find($this->inventoryId)?->currentQuantityFor($material)
            : 0;

        $this->name = (string) $material->name;
        $this->type = (string) ($material->type ?: 'simple');
        $this->status = $material->is_active ? 'active' : 'inactive';
        $this->currentPhotoPath = $material->getRawOriginal('photo');
        $this->photoUpload = null;
        $this->price = $material->price !== null ? (string) $material->price : null;
        $this->minimum_stock = (int) $material->minimum_stock;
        $this->description = $material->description;
        $this->selectedCourseIds = $material->courses->pluck('id')->map(fn ($id) => (int) $id)->all();
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
        return Material::query()
            ->where('type', 'simple')
            ->where('is_active', true)
            ->exists();
    }

    private function hasActiveSimpleMaterialsInInventory(): bool
    {
        if ($this->inventoryId === null) {
            return $this->hasActiveSimpleMaterials();
        }

        return Inventory::query()
            ->find($this->inventoryId)
            ?->hasActiveSimpleMaterialsWithStock() ?? false;
    }

    private function resolveAllowedTab(?string $requestedTab = null): string
    {
        $availableTabs = $this->availableTabs();

        if ($requestedTab !== null && in_array($requestedTab, $availableTabs, true)) {
            return $requestedTab;
        }

        return $availableTabs[0];
    }
}
