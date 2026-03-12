<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Helpers\MoneyHelper;
use App\Models\Material;
use App\Models\Ministry;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class MaterialCreateModal extends Component
{
    use WithFileUploads;

    private const PHOTO_UPLOAD_MAX_KB = 10240;

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

    public function updatedPrice(?string $value): void
    {
        if ($value === null) {
            return;
        }

        $this->price = preg_replace('/[^0-9,.\-]/', '', $value) ?: null;
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

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

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

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.material-create-modal');
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
     * @return array<int, Material>
     */
    public function availableSimpleMaterials(): array
    {
        return Material::query()
            ->where('type', 'simple')
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function photoPreviewUrl(): string
    {
        if ($this->photoUpload && str_starts_with((string) $this->photoUpload->getMimeType(), 'image/')) {
            return $this->photoUpload->temporaryUrl();
        }

        return asset('images/logo/ee-gold.webp');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:simple,composite'],
            'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photoUpload' => ['nullable', 'image', 'max:'.self::PHOTO_UPLOAD_MAX_KB],
            'selectedCourseIds' => ['array'],
            'selectedCourseIds.*' => ['integer', 'exists:courses,id'],
            'selectedComponentIds' => ['array'],
            'selectedComponentIds.*' => [
                'integer',
                Rule::exists('materials', 'id')->where(fn ($query) => $query->where('type', 'simple')),
                'distinct',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'in' => 'O valor informado para :attribute é inválido.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'image' => 'O campo :attribute deve ser uma imagem válida.',
            'price.regex' => 'O campo preço deve conter apenas números e separador decimal.',
            'selectedComponentIds.*.exists' => 'Somente itens simples podem compor um produto composto.',
            'selectedComponentIds.*.distinct' => 'O mesmo componente não pode ser informado mais de uma vez.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'type' => 'tipo',
            'price' => 'preço',
            'minimum_stock' => 'estoque mínimo',
            'description' => 'descrição',
            'photoUpload' => 'foto',
            'selectedCourseIds' => 'cursos',
            'selectedComponentIds' => 'componentes',
        ];
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
}
