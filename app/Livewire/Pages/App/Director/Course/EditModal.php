<?php

namespace App\Livewire\Pages\App\Director\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $logoUpload = null;

    public mixed $bannerUpload = null;

    public int $courseId;

    public ?string $currentLogoPath = null;

    public ?string $currentBannerPath = null;

    public int $execution = 0;

    public int $min_stp_sessions = 0;

    public bool $is_accreditable = false;

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

    public function mount(int $courseId): void
    {
        $this->courseId = $courseId;
    }

    #[On('open-director-course-edit-modal')]
    public function openModal(): void
    {
        $this->fillFromModel();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
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
            $course = Course::query()->findOrFail($this->courseId);
            $logoPath = $course->logo;
            $bannerPath = $course->banner;

            if ($this->logoUpload) {
                $storedLogoPath = $this->logoUpload->store('course-logos', 'public');

                if ($logoPath && ! str_starts_with($logoPath, 'http') && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }

                $logoPath = $storedLogoPath;
            }

            if ($this->bannerUpload) {
                $storedBannerPath = $this->bannerUpload->store('course-banners', 'public');

                if ($bannerPath && ! str_starts_with($bannerPath, 'http') && Storage::disk('public')->exists($bannerPath)) {
                    Storage::disk('public')->delete($bannerPath);
                }

                $bannerPath = $storedBannerPath;
            }

            $course->forceFill([
                'execution' => $validated['execution'],
                'min_stp_sessions' => $validated['min_stp_sessions'],
                'is_accreditable' => $validated['is_accreditable'],
                'type' => $validated['type'],
                'initials' => $validated['initials'],
                'name' => $validated['name'],
                'slogan' => $validated['slogan'] ?? null,
                'learnMoreLink' => $validated['learnMoreLink'] ?? null,
                'color' => $validated['color'],
                'price' => $validated['price'] ?? null,
                'description' => $validated['description'] ?? null,
                'targetAudience' => $validated['targetAudience'] ?? null,
                'knowhow' => $validated['knowhow'] ?? null,
                'logo' => $logoPath,
                'banner' => $bannerPath,
            ])->save();

            $this->dispatch('director-course-updated', ministryId: $course->ministry_id, courseId: $course->id);
            $this->dispatch('director-ministry-updated', ministryId: $course->ministry_id);

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.course.edit-modal', [
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
            'is_accreditable' => ['boolean'],
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
            'is_accreditable' => 'curso credenciável',
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

    private function fillFromModel(): void
    {
        $course = Course::query()->findOrFail($this->courseId);

        $this->currentLogoPath = $course->logo;
        $this->currentBannerPath = $course->banner;
        $this->execution = (int) $course->execution;
        $this->min_stp_sessions = (int) $course->min_stp_sessions;
        $this->is_accreditable = (bool) $course->is_accreditable;
        $this->type = (string) $course->type;
        $this->initials = (string) $course->initials;
        $this->name = (string) $course->name;
        $this->slogan = $course->slogan;
        $this->learnMoreLink = $course->learnMoreLink;
        $this->color = (string) ($course->color ?: '#4F4F4F');
        $this->price = $course->price !== null ? (string) $course->price : null;
        $this->targetAudience = $course->targetAudience;
        $this->knowhow = $course->knowhow;
        $this->description = $course->description;
        $this->logoUpload = null;
        $this->bannerUpload = null;
    }

    private function logoPreviewUrl(): string
    {
        if ($this->logoUpload && str_starts_with((string) $this->logoUpload->getMimeType(), 'image/')) {
            return $this->logoUpload->temporaryUrl();
        }

        if ($this->currentLogoPath) {
            if (str_starts_with($this->currentLogoPath, 'http')) {
                return $this->currentLogoPath;
            }

            if (Storage::disk('public')->exists($this->currentLogoPath)) {
                return Storage::disk('public')->url($this->currentLogoPath);
            }
        }

        return asset('images/logo/ee-gold.webp');
    }

    private function bannerPreviewUrl(): string
    {
        if ($this->bannerUpload && str_starts_with((string) $this->bannerUpload->getMimeType(), 'image/')) {
            return $this->bannerUpload->temporaryUrl();
        }

        if ($this->currentBannerPath) {
            if (str_starts_with($this->currentBannerPath, 'http')) {
                return $this->currentBannerPath;
            }

            if (Storage::disk('public')->exists($this->currentBannerPath)) {
                return Storage::disk('public')->url($this->currentBannerPath);
            }
        }

        return asset('images/cover.webp');
    }
}
