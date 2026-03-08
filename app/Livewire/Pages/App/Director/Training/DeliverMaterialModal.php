<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\TrainingMaterialDeliveryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;

class DeliverMaterialModal extends Component
{
    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public ?int $inventory_id = null;

    public ?int $material_id = null;

    public ?int $participant_id = null;

    public string $participant_note = '';

    public int $quantity = 1;

    public string $notes = '';

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
        $this->inventory_id = $this->defaultInventoryId();
    }

    #[On('open-training-material-delivery-modal')]
    public function openModal(int $trainingId, ?int $participantId = null): void
    {
        if ($trainingId !== $this->trainingId) {
            return;
        }

        $this->loadTraining();
        $this->resetForm();
        $this->participant_id = $participantId;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetForm();
    }

    public function save(TrainingMaterialDeliveryService $deliveryService): void
    {
        if ($this->busy) {
            return;
        }

        $this->loadTraining();
        $validated = $this->validate();
        $this->busy = true;

        try {
            $inventory = Inventory::query()->findOrFail($validated['inventory_id']);
            $material = Material::query()->findOrFail($validated['material_id']);
            $participant = $validated['participant_id'] === null
                ? null
                : User::query()->findOrFail($validated['participant_id']);

            $deliveryService->deliver(
                training: $this->training,
                inventory: $inventory,
                material: $material,
                quantity: (int) $validated['quantity'],
                actor: Auth::user(),
                participant: $participant,
                participantLabel: $validated['participant_note'] ?: null,
                notes: $validated['notes'] ?: null,
            );

            $this->closeModal();
            $this->dispatch('training-material-delivered', trainingId: $this->training->id);
        } catch (InsufficientStockException|InvalidCompositeMaterialException|InvalidArgumentException $exception) {
            $this->addError('delivery', $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.training.deliver-material-modal', [
            'inventoryOptions' => $this->inventoryOptions(),
            'materialOptions' => $this->materialOptions(),
            'participantOptions' => $this->participantOptions(),
            'selectedMaterial' => $this->selectedMaterial(),
        ]);
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    protected function rules(): array
    {
        return [
            'inventory_id' => ['required', 'integer', Rule::exists('inventories', 'id')->where('is_active', true)],
            'material_id' => [
                'required',
                'integer',
                Rule::exists('course_material', 'material_id')->where('course_id', $this->training->course_id),
            ],
            'participant_id' => [
                'nullable',
                'integer',
                Rule::exists('training_user', 'user_id')->where('training_id', $this->training->id),
            ],
            'participant_note' => ['nullable', 'string', 'max:255', 'required_without:participant_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'participant_note.required_without' => __('Informe o participante avulso quando não houver inscrito selecionado.'),
            'material_id.exists' => __('Escolha um material vinculado ao curso deste treinamento.'),
            'participant_id.exists' => __('O participante selecionado não pertence a este treinamento.'),
        ];
    }

    /**
     * @return Collection<int, Inventory>
     */
    private function availableInventories(): Collection
    {
        return Inventory::query()
            ->with('responsibleUser:id,name')
            ->where('is_active', true)
            ->orderByRaw("case when kind = 'central' then 0 else 1 end")
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Material>
     */
    private function availableMaterials(): Collection
    {
        return $this->training->course?->materials
            ? $this->training->course->materials
                ->sortBy(fn (Material $material): string => sprintf(
                    '%s %s',
                    $material->isComposite() ? '0' : '1',
                    mb_strtolower($material->name, 'UTF-8')
                ))
                ->values()
            : new Collection;
    }

    /**
     * @return Collection<int, User>
     */
    private function participants(): Collection
    {
        return $this->training->students
            ->sortBy('name')
            ->values();
    }

    private function selectedMaterial(): ?Material
    {
        if ($this->material_id === null) {
            return null;
        }

        return $this->availableMaterials()->firstWhere('id', $this->material_id);
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function inventoryOptions(): array
    {
        return $this->availableInventories()
            ->map(fn (Inventory $inventory): array => [
                'value' => $inventory->id,
                'label' => trim(implode(' · ', array_filter([
                    $inventory->name,
                    $inventory->kind === 'central' ? __('Central') : __('Professor'),
                    $inventory->responsibleUser?->name,
                ]))),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function materialOptions(): array
    {
        return $this->availableMaterials()
            ->map(fn (Material $material): array => [
                'value' => $material->id,
                'label' => $material->name.' · '.($material->isComposite() ? __('Composto') : __('Simples')),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function participantOptions(): array
    {
        return $this->participants()
            ->map(fn (User $participant): array => [
                'value' => $participant->id,
                'label' => trim($participant->name.($participant->pivot?->kit ? ' · '.__('kit já marcado') : '')),
            ])
            ->values()
            ->all();
    }

    private function loadTraining(): void
    {
        $training = Training::query()
            ->with([
                'course.ministry',
                'course.materials.components.componentMaterial',
                'teacher',
                'students' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($this->trainingId);

        Gate::authorize('access-director');
        Gate::authorize('view', $training);

        $this->training = $training;
    }

    private function defaultInventoryId(): ?int
    {
        $teacherInventoryId = Inventory::query()
            ->where('kind', 'teacher')
            ->where('user_id', $this->training->teacher_id)
            ->where('is_active', true)
            ->value('id');

        if ($teacherInventoryId !== null) {
            return (int) $teacherInventoryId;
        }

        $centralInventoryId = Inventory::query()
            ->where('kind', 'central')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        return $centralInventoryId !== null ? (int) $centralInventoryId : null;
    }

    private function resetForm(): void
    {
        $this->inventory_id = $this->defaultInventoryId();
        $this->material_id = $this->availableMaterials()->first()?->id;
        $this->participant_id = null;
        $this->participant_note = '';
        $this->quantity = 1;
        $this->notes = '';
        $this->resetErrorBag();
    }
}
