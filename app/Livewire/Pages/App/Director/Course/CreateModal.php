<?php

namespace App\Livewire\Pages\App\Director\Course;

use App\Models\Course;
use App\Models\Ministry;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public Ministry $ministry;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $logoUpload = null;

    public mixed $bannerUpload = null;

    public int $execution = 0;

    public int $min_stp_sessions = 0;

    public string $type = '';

    public string $initials = '';

    public string $name = '';

    public ?string $slogan = null;

    public ?string $learnMoreLink = null;

    public string $color = '#4F4F4F';

    public ?string $price = null;

    public ?string $targetAudience = null;

    public ?string $knowhow = null;

    public ?string $description = null;

    public function mount(Ministry $ministry): void
    {
        $this->ministry = $ministry;
    }

    #[On('open-director-course-create-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updated(string $property): void
    {
        foreach (array_keys($this->rules()) as $ruleKey) {
            if (Str::is($ruleKey, $property)) {
                $this->validateOnly($property);

                break;
            }
        }
    }

    public function updatedPrice(?string $value): void
    {
        if ($value === null) {
            return;
        }

        $this->price = preg_replace('/[^0-9,.\-]/', '', $value) ?: null;
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate();

            $logoPath = null;
            if ($this->logoUpload) {
                $logoPath = $this->logoUpload->store('course-logos', 'public');
            }

            $bannerPath = null;
            if ($this->bannerUpload) {
                $bannerPath = $this->bannerUpload->store('course-banners', 'public');
            }

            $course = Course::query()->create([
                'order' => $this->nextOrderForExecution($validated['execution']),
                'execution' => $validated['execution'],
                'min_stp_sessions' => $validated['min_stp_sessions'],
                'type' => $validated['type'],
                'initials' => $validated['initials'],
                'name' => $validated['name'],
                'slogan' => $validated['slogan'] ?? null,
                'learnMoreLink' => $validated['learnMoreLink'] ?? null,
                'certificate' => null,
                'color' => $validated['color'],
                'price' => $validated['price'] ?? null,
                'description' => $validated['description'] ?? null,
                'targetAudience' => $validated['targetAudience'] ?? null,
                'knowhow' => $validated['knowhow'] ?? null,
                'logo' => $logoPath,
                'banner' => $bannerPath,
                'ministry_id' => $this->ministry->id,
            ]);

            $this->dispatch('director-course-created', ministryId: $this->ministry->id, courseId: $course->id);
            $this->dispatch('director-ministry-updated', ministryId: $this->ministry->id);

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.course.create-modal', [
            'logoPreviewUrl' => $this->logoPreviewUrl(),
            'bannerPreviewUrl' => $this->bannerPreviewUrl(),
            'executionOptions' => [
                ['value' => 0, 'label' => __('Liderança')],
                ['value' => 1, 'label' => __('Implementação')],
            ],
            'stpSessionOptions' => collect(range(1, 20))
                ->map(fn (int $number): array => [
                    'value' => $number,
                    'label' => $number === 1 ? '1 STP' : "{$number} STPs",
                ])
                ->prepend(['value' => 0, 'label' => '0 STPs'])
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'logoUpload' => ['nullable', 'image', 'max:5120'],
            'bannerUpload' => ['nullable', 'image', 'max:5120'],
            'execution' => ['required', 'integer', 'in:0,1'],
            'min_stp_sessions' => ['required', 'integer', 'in:0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20'],
            'type' => ['required', 'string', 'max:255'],
            'initials' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'learnMoreLink' => ['nullable', 'url', 'max:255'],
            'color' => ['required', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'price' => ['nullable', 'string', 'max:20', 'regex:/^-?\d+(?:[,.]\d{0,2})?$/'],
            'targetAudience' => ['nullable', 'string', 'max:1000'],
            'knowhow' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
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
            'url' => 'O campo :attribute deve conter uma URL válida.',
            'in' => 'O valor informado para :attribute é inválido.',
            'regex' => 'O campo :attribute deve estar no formato hexadecimal (#RGB ou #RRGGBB).',
            'image' => 'O campo :attribute deve ser uma imagem válida.',
            'price.regex' => 'O campo :attribute deve conter apenas números e separador decimal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'logoUpload' => 'logo',
            'bannerUpload' => 'banner',
            'execution' => 'tipo de execução',
            'min_stp_sessions' => 'sessões mínimas STP',
            'type' => 'tipo do curso',
            'initials' => 'sigla',
            'name' => 'nome do curso',
            'slogan' => 'slogan',
            'learnMoreLink' => 'link saiba mais',
            'color' => 'cor',
            'price' => 'preço',
            'targetAudience' => 'público-alvo',
            'knowhow' => 'conhecimento',
            'description' => 'descrição',
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset([
            'logoUpload',
            'bannerUpload',
            'type',
            'name',
            'initials',
            'slogan',
            'learnMoreLink',
            'price',
            'targetAudience',
            'knowhow',
            'description',
        ]);

        $this->execution = 0;
        $this->min_stp_sessions = 0;
        $this->color = '#4F4F4F';
    }

    private function nextOrderForExecution(int $execution): int
    {
        $lastOrder = Course::query()
            ->where('ministry_id', $this->ministry->id)
            ->where('execution', $execution)
            ->selectRaw('COALESCE(MAX(CAST(`order` AS SIGNED)), 0) as aggregate')
            ->value('aggregate');

        return ((int) $lastOrder) + 1;
    }

    private function logoPreviewUrl(): string
    {
        if ($this->logoUpload && str_starts_with((string) $this->logoUpload->getMimeType(), 'image/')) {
            return $this->logoUpload->temporaryUrl();
        }

        return asset('images/logo/ee-gold.webp');
    }

    private function bannerPreviewUrl(): string
    {
        if ($this->bannerUpload && str_starts_with((string) $this->bannerUpload->getMimeType(), 'image/')) {
            return $this->bannerUpload->temporaryUrl();
        }

        return asset('images/cover.webp');
    }
}
