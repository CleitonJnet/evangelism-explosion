<?php

namespace App\Livewire\Pages\App\Director\Website\Testimonials;

use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    /**
     * @var array<int, array{
     *     id: int,
     *     name: string,
     *     meta: string,
     *     quote: string,
     *     quote_preview: string,
     *     photo_url: string,
     *     is_active: bool,
     * }>
     */
    public array $testimonials = [];

    public int $totalTestimonials = 0;

    public int $activeTestimonials = 0;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?int $selectedTestimonialId = null;

    public string $selectedTestimonialName = '';

    public string $editName = '';

    public string $editMeta = '';

    public string $editQuote = '';

    public mixed $editPhotoUpload = null;

    public ?string $editCurrentPhotoPath = null;

    public string $editCurrentPhotoUrl = '';

    public function mount(): void
    {
        $this->refreshTestimonials();
    }

    #[On('testimonial-created')]
    public function refreshTestimonials(): void
    {
        $items = Testimonial::query()
            ->orderByDesc('position')
            ->orderByDesc('id')
            ->get();

        $this->testimonials = $items
            ->map(function (Testimonial $testimonial): array {
                $isActive = (bool) $testimonial->is_active;

                return [
                    'id' => $testimonial->id,
                    'name' => $testimonial->name,
                    'meta' => $testimonial->meta ?? '',
                    'quote' => $testimonial->quote,
                    'quote_preview' => Str::limit($testimonial->quote, 70),
                    'photo_url' => $this->resolvePhotoUrl($testimonial->photo),
                    'is_active' => $isActive,
                ];
            })
            ->values()
            ->all();

        $this->totalTestimonials = $items->count();
        $this->activeTestimonials = $items->where('is_active', true)->count();
    }

    public function openEditModal(int $testimonialId): void
    {
        $testimonial = Testimonial::query()->find($testimonialId);

        if (! $testimonial) {
            return;
        }

        $this->selectedTestimonialId = $testimonial->id;
        $this->selectedTestimonialName = $testimonial->name;
        $this->editName = $testimonial->name;
        $this->editMeta = $testimonial->meta ?? '';
        $this->editQuote = $testimonial->quote;
        $this->editCurrentPhotoPath = $testimonial->photo;
        $this->editCurrentPhotoUrl = $this->resolvePhotoUrl($testimonial->photo);
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->selectedTestimonialId = null;
        $this->selectedTestimonialName = '';
        $this->editName = '';
        $this->editMeta = '';
        $this->editQuote = '';
        $this->editPhotoUpload = null;
        $this->editCurrentPhotoPath = null;
        $this->editCurrentPhotoUrl = '';
        $this->resetValidation();
    }

    public function saveEditedTestimonial(): void
    {
        if (! $this->selectedTestimonialId) {
            return;
        }

        $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

        $testimonial = Testimonial::query()->find($this->selectedTestimonialId);

        if (! $testimonial) {
            $this->closeEditModal();

            return;
        }

        $photoPath = $testimonial->photo;

        if ($this->editPhotoUpload instanceof UploadedFile) {
            if (is_string($photoPath) && $photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            $photoPath = $this->editPhotoUpload->store('testimonials/photos', 'public');
        }

        $testimonial->update([
            'name' => trim($validated['editName']),
            'meta' => trim((string) $validated['editMeta']) ?: null,
            'quote' => trim($validated['editQuote']),
            'photo' => $photoPath,
        ]);

        $this->closeEditModal();
        $this->refreshTestimonials();
    }

    public function openDeleteModal(int $testimonialId): void
    {
        $testimonial = Testimonial::query()->find($testimonialId);

        if (! $testimonial) {
            return;
        }

        $this->selectedTestimonialId = $testimonial->id;
        $this->selectedTestimonialName = $testimonial->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedTestimonialId = null;
        $this->selectedTestimonialName = '';
    }

    public function deleteSelectedTestimonial(): void
    {
        if (! $this->selectedTestimonialId) {
            return;
        }

        $testimonial = Testimonial::query()->find($this->selectedTestimonialId);

        if (! $testimonial) {
            $this->closeDeleteModal();

            return;
        }

        if (is_string($testimonial->photo) && $testimonial->photo !== '' && Storage::disk('public')->exists($testimonial->photo)) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        $this->closeDeleteModal();
        $this->refreshTestimonials();
    }

    public function toggleStatus(int $id, bool $isActive): void
    {
        Testimonial::query()
            ->whereKey($id)
            ->update([
                'is_active' => $isActive,
            ]);

        $this->refreshTestimonials();
    }

    public function moveAfter(int $id, ?int $afterItemId): void
    {
        $orderedIds = collect($this->testimonials)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (! in_array($id, $orderedIds, true)) {
            return;
        }

        $withoutDragged = array_values(array_filter($orderedIds, fn (int $itemId): bool => $itemId !== $id));
        $insertIndex = 0;

        if ($afterItemId !== null) {
            if (! in_array($afterItemId, $withoutDragged, true)) {
                return;
            }

            $afterIndex = array_search($afterItemId, $withoutDragged, true);

            if ($afterIndex === false) {
                return;
            }

            $insertIndex = $afterIndex + 1;
        }

        if (isset($orderedIds[$insertIndex]) && (int) $orderedIds[$insertIndex] === $id) {
            return;
        }

        array_splice($withoutDragged, $insertIndex, 0, [$id]);

        DB::transaction(function () use ($withoutDragged): void {
            $total = count($withoutDragged);

            foreach ($withoutDragged as $index => $id) {
                Testimonial::query()
                    ->whereKey($id)
                    ->update(['position' => $total - $index]);
            }
        });

        $this->refreshTestimonials();
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.website.testimonials.index');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'editName' => ['required', 'string', 'max:120'],
            'editMeta' => ['nullable', 'string', 'max:180'],
            'editQuote' => ['required', 'string', 'max:350'],
            'editPhotoUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'editName.required' => 'Informe o nome para exibicao.',
            'editQuote.required' => 'Informe o testemunho.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'editName' => 'nome',
            'editMeta' => 'cargo/igreja',
            'editQuote' => 'testemunho',
            'editPhotoUpload' => 'foto',
        ];
    }

    private function resolvePhotoUrl(?string $photoPath): string
    {
        if (! is_string($photoPath) || trim($photoPath) === '') {
            return asset('images/profile.webp');
        }

        if (Str::startsWith($photoPath, ['http://', 'https://'])) {
            return $photoPath;
        }

        return Storage::disk('public')->exists($photoPath)
            ? Storage::disk('public')->url($photoPath)
            : asset('images/profile.webp');
    }
}
