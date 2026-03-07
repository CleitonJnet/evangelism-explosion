<?php

namespace App\Livewire\Pages\App\Director\Course;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View as ViewView;
use Livewire\Component;
use Livewire\WithFileUploads;

class Sections extends Component
{
    use WithFileUploads;

    public int $courseId;

    /**
     * @var array{name: string, duration: string|null, devotional: string|null, description: string|null, knowhow: string|null}
     */
    public array $sectionForm = [];

    public mixed $bannerUpload = null;

    public ?string $currentBannerPath = null;

    public bool $showSectionModal = false;

    public bool $showDeleteSectionModal = false;

    public ?int $editingSectionId = null;

    public ?int $deletingSectionId = null;

    public function mount(Course $course): void
    {
        $this->courseId = $course->id;
        $this->resetSectionForm();
    }

    public function render(): ViewView
    {
        $course = $this->course();
        $sections = $course->sections()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('livewire.pages.app.director.course.sections', [
            'course' => $course->loadMissing('ministry'),
            'sections' => $sections,
            'sectionsCount' => $sections->count(),
        ]);
    }

    public function openCreateSectionModal(): void
    {
        $this->editingSectionId = null;
        $this->resetSectionForm();
        $this->showSectionModal = true;
    }

    public function openEditSectionModal(int $sectionId): void
    {
        $section = $this->findSection($sectionId);

        $this->editingSectionId = $section->id;
        $this->sectionForm = [
            'name' => $section->name,
            'duration' => $section->duration,
            'devotional' => $section->devotional,
            'description' => $section->description,
            'knowhow' => $section->knowhow,
        ];
        $this->currentBannerPath = $section->banner;
        $this->bannerUpload = null;

        $this->showSectionModal = true;
    }

    public function closeSectionModal(): void
    {
        $this->showSectionModal = false;
        $this->resetSectionForm();
        $this->resetErrorBag();
    }

    public function saveSection(): void
    {
        $validated = $this->validate($this->sectionRules());
        $bannerPath = $this->currentBannerPath;

        if ($this->editingSectionId) {
            $section = $this->findSection($this->editingSectionId);

            if ($this->bannerUpload) {
                $storedBannerPath = $this->bannerUpload->store('section-banners', 'public');

                if ($bannerPath && ! str_starts_with($bannerPath, 'http') && Storage::disk('public')->exists($bannerPath)) {
                    Storage::disk('public')->delete($bannerPath);
                }

                $bannerPath = $storedBannerPath;
            }

            $section->update([
                ...$validated['sectionForm'],
                'banner' => $bannerPath,
            ]);
        } else {
            if ($this->bannerUpload) {
                $bannerPath = $this->bannerUpload->store('section-banners', 'public');
            }

            $this->course()->sections()->create([
                ...$validated['sectionForm'],
                'banner' => $bannerPath,
                'order' => $this->nextSectionOrder(),
            ]);
        }

        $this->closeSectionModal();
    }

    public function deleteSection(int $sectionId): void
    {
        $section = $this->findSection($sectionId);
        $section->delete();
    }

    public function moveSectionAfter(int $sectionId, ?int $afterSectionId = null): void
    {
        $sections = Section::query()
            ->where('course_id', $this->courseId)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->values();

        $movingIndex = $sections->search(fn (Section $section) => $section->id === $sectionId);

        if ($movingIndex === false) {
            return;
        }

        $moving = $sections->pull($movingIndex);

        if (! $moving) {
            return;
        }

        $insertIndex = 0;

        if ($afterSectionId !== null) {
            $afterIndex = $sections->search(fn (Section $section) => $section->id === $afterSectionId);
            $insertIndex = $afterIndex === false ? $sections->count() : $afterIndex + 1;
        }

        $reorderedSections = $sections->all();
        array_splice($reorderedSections, $insertIndex, 0, [$moving]);

        $this->syncSectionOrder(collect($reorderedSections));
    }

    public function openDeleteSectionModal(int $sectionId): void
    {
        $this->deletingSectionId = $sectionId;
        $this->showDeleteSectionModal = true;
    }

    public function closeDeleteSectionModal(): void
    {
        $this->showDeleteSectionModal = false;
        $this->deletingSectionId = null;
        $this->resetErrorBag();
    }

    public function confirmDeleteSection(): void
    {
        if (! $this->deletingSectionId) {
            $this->closeDeleteSectionModal();

            return;
        }

        $this->deleteSection($this->deletingSectionId);
        $this->closeDeleteSectionModal();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function sectionRules(): array
    {
        return [
            'sectionForm.name' => ['required', 'string', 'max:255'],
            'sectionForm.duration' => ['nullable', 'integer', 'min:0'],
            'sectionForm.devotional' => ['nullable', 'string', 'max:255'],
            'sectionForm.description' => ['nullable', 'string'],
            'sectionForm.knowhow' => ['nullable', 'string'],
            'bannerUpload' => ['nullable', 'image', 'max:5120'],
        ];
    }

    private function findSection(int $sectionId): Section
    {
        return Section::query()
            ->where('course_id', $this->courseId)
            ->findOrFail($sectionId);
    }

    private function resetSectionForm(): void
    {
        $this->sectionForm = [
            'name' => '',
            'duration' => null,
            'devotional' => null,
            'description' => null,
            'knowhow' => null,
        ];
        $this->bannerUpload = null;
        $this->currentBannerPath = null;
    }

    private function course(): Course
    {
        return Course::query()->findOrFail($this->courseId);
    }

    private function nextSectionOrder(): int
    {
        $lastOrder = Section::query()
            ->where('course_id', $this->courseId)
            ->selectRaw('COALESCE(MAX(CAST(`order` AS SIGNED)), 0) as aggregate')
            ->value('aggregate');

        return ((int) $lastOrder) + 1;
    }

    public function sectionBannerPreviewUrl(): string
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

    /**
     * @param  Collection<int, Section>  $sections
     */
    private function syncSectionOrder(Collection $sections): void
    {
        $sections->values()->each(function (Section $section, int $index): void {
            $nextOrder = $index + 1;

            if ((int) $section->order === $nextOrder) {
                return;
            }

            $section->update(['order' => $nextOrder]);
        });
    }
}
