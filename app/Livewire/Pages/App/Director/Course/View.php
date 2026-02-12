<?php

namespace App\Livewire\Pages\App\Director\Course;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View as ViewView;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use WithPagination;

    public int $courseId;

    /**
     * @var array{name: string, banner: string|null, order: int|null, duration: string|null, devotional: string|null, description: string|null, knowhow: string|null}
     */
    public array $sectionForm = [];

    public string $teacherSearch = '';

    public int $sectionsPerPage = 5;

    public int $teachersPerPage = 5;

    /**
     * @var array{user_id: int|null, status: int}
     */
    public array $teacherForm = [];

    public bool $showSectionModal = false;

    public bool $showTeacherModal = false;

    public bool $showDeleteSectionModal = false;

    public bool $showDeleteTeacherModal = false;

    public ?int $editingSectionId = null;

    public ?int $editingTeacherId = null;

    public string $editingTeacherName = '';

    public ?int $deletingSectionId = null;

    public ?int $deletingTeacherId = null;

    public bool $teacherAlreadyAssignedWarning = false;

    public function mount(Course $course): void
    {
        $this->courseId = $course->id;
        $this->resetSectionForm();
        $this->resetTeacherForm();
    }

    public function render(): ViewView
    {
        $course = $this->course();
        $sections = $course->sections()
            ->orderBy('order')
            ->orderBy('id')
            ->paginate($this->sectionsPerPage, pageName: 'sectionsPage');

        $teachers = $course->teachers()
            ->orderBy('name')
            ->paginate($this->teachersPerPage, pageName: 'teachersPage');

        $teacherCandidates = $this->teacherCandidates($this->teacherSearch);
        $assignedTeacherIds = $teachers->pluck('id')->all();

        return view('livewire.pages.app.director.course.view', [
            'course' => $course,
            'sections' => $sections,
            'teachers' => $teachers,
            'teacherCandidates' => $teacherCandidates,
            'assignedTeacherIds' => $assignedTeacherIds,
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
            'banner' => $section->banner,
            'order' => $section->order,
            'duration' => $section->duration,
            'devotional' => $section->devotional,
            'description' => $section->description,
            'knowhow' => $section->knowhow,
        ];

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

        if ($this->editingSectionId) {
            $section = $this->findSection($this->editingSectionId);
            $section->update($validated['sectionForm']);
        } else {
            $this->course()->sections()->create($validated['sectionForm']);
        }

        $this->resetPage('sectionsPage');
        $this->closeSectionModal();
    }

    public function deleteSection(int $sectionId): void
    {
        $section = $this->findSection($sectionId);
        $section->delete();
        $this->resetPage('sectionsPage');
    }

    public function openCreateTeacherModal(): void
    {
        $this->editingTeacherId = null;
        $this->editingTeacherName = '';
        $this->teacherSearch = '';
        $this->teacherAlreadyAssignedWarning = false;
        $this->resetTeacherForm();
        $this->showTeacherModal = true;
    }

    public function openEditTeacherModal(int $teacherId): void
    {
        $teacher = $this->findTeacher($teacherId);

        $this->editingTeacherId = $teacher->id;
        $this->editingTeacherName = $teacher->name;
        $this->teacherAlreadyAssignedWarning = false;
        $this->teacherForm = [
            'user_id' => $teacher->id,
            'status' => (int) ($teacher->pivot->status ?? 0),
        ];

        $this->showTeacherModal = true;
    }

    public function closeTeacherModal(): void
    {
        $this->showTeacherModal = false;
        $this->resetTeacherForm();
        $this->teacherSearch = '';
        $this->teacherAlreadyAssignedWarning = false;
        $this->resetErrorBag();
    }

    public function updatedTeacherSearch(): void
    {
        $teachers = $this->course()->teachers()->get();
        $assignedTeacherIds = $teachers->pluck('id')->all();
        $candidates = $this->teacherCandidates($this->teacherSearch);

        $this->teacherAlreadyAssignedWarning = false;
        $this->teacherForm['user_id'] = $candidates
            ->first(fn (User $teacher) => ! in_array($teacher->id, $assignedTeacherIds, true))
            ?->id;
    }

    public function updatedTeacherFormUserId(?int $userId): void
    {
        if ($this->editingTeacherId) {
            $this->teacherAlreadyAssignedWarning = false;

            return;
        }

        if (! $userId) {
            $this->teacherAlreadyAssignedWarning = false;

            return;
        }

        $alreadyAssigned = $this->course()->teachers()
            ->where('users.id', $userId)
            ->exists();

        $this->teacherAlreadyAssignedWarning = $alreadyAssigned;
    }

    public function saveTeacher(): void
    {
        $this->teacherAlreadyAssignedWarning = false;
        $validated = $this->validate($this->teacherRules(), $this->teacherMessages());
        $teacherId = (int) $validated['teacherForm']['user_id'];
        $status = (int) $validated['teacherForm']['status'];

        if (! $this->isTeacher($teacherId)) {
            $this->addError('teacherForm.user_id', __('Selecione um professor válido.'));

            return;
        }

        if ($this->editingTeacherId) {
            $this->course()->teachers()->updateExistingPivot($this->editingTeacherId, [
                'status' => $status,
            ]);
        } else {
            $alreadyAssigned = $this->course()->teachers()
                ->where('users.id', $teacherId)
                ->exists();

            if ($alreadyAssigned) {
                $this->teacherAlreadyAssignedWarning = true;

                return;
            }

            $this->course()->teachers()->syncWithoutDetaching([
                $teacherId => ['status' => $status],
            ]);
        }

        $this->resetPage('teachersPage');
        $this->closeTeacherModal();
    }

    public function deleteTeacher(int $teacherId): void
    {
        $this->course()->teachers()->detach($teacherId);
        $this->resetPage('teachersPage');
    }

    public function reorderSectionByIndex(int $sectionId, int $targetIndex, bool $forceIndex = false): void
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

        $targetIndex = max(0, min($targetIndex, $sections->count()));

        $moving = $sections->pull($movingIndex);

        if (! $moving) {
            return;
        }

        if (! $forceIndex && $targetIndex > $movingIndex) {
            $targetIndex--;
        }

        $targetIndex = max(0, min($targetIndex, $sections->count()));

        $sections->splice($targetIndex, 0, [$moving]);

        $sections->each(function (Section $section, int $index): void {
            $section->update(['order' => $index + 1]);
        });
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

    public function openDeleteTeacherModal(int $teacherId): void
    {
        $this->deletingTeacherId = $teacherId;
        $this->showDeleteTeacherModal = true;
    }

    public function closeDeleteTeacherModal(): void
    {
        $this->showDeleteTeacherModal = false;
        $this->deletingTeacherId = null;
        $this->resetErrorBag();
    }

    public function confirmDeleteTeacher(): void
    {
        if (! $this->deletingTeacherId) {
            $this->closeDeleteTeacherModal();

            return;
        }

        $this->deleteTeacher($this->deletingTeacherId);
        $this->closeDeleteTeacherModal();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function sectionRules(): array
    {
        return [
            'sectionForm.name' => ['required', 'string', 'max:255'],
            'sectionForm.banner' => ['nullable', 'string', 'max:255'],
            'sectionForm.order' => ['nullable', 'integer', 'min:0'],
            'sectionForm.duration' => ['nullable', 'string', 'max:255'],
            'sectionForm.devotional' => ['nullable', 'string', 'max:255'],
            'sectionForm.description' => ['nullable', 'string'],
            'sectionForm.knowhow' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Exists>>
     */
    private function teacherRules(): array
    {
        return [
            'teacherForm.user_id' => [
                'required',
                Rule::exists('users', 'id'),
            ],
            'teacherForm.status' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function teacherMessages(): array
    {
        return [
            'teacherForm.user_id.required' => __('Este professor já se encontra na lista de professores do curso.'),
        ];
    }

    /**
     * @param  Collection<int, User>  $teachers
     * @return Collection<int, User>
     */
    private function teacherCandidates(string $search): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Teacher', 'teacher']))
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
            )
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    private function findSection(int $sectionId): Section
    {
        return Section::query()
            ->where('course_id', $this->courseId)
            ->findOrFail($sectionId);
    }

    private function findTeacher(int $teacherId): User
    {
        return $this->course()->teachers()
            ->where('users.id', $teacherId)
            ->firstOrFail();
    }

    private function resetSectionForm(): void
    {
        $this->sectionForm = [
            'name' => '',
            'banner' => null,
            'order' => null,
            'duration' => null,
            'devotional' => null,
            'description' => null,
            'knowhow' => null,
        ];
    }

    private function resetTeacherForm(): void
    {
        $this->teacherForm = [
            'user_id' => null,
            'status' => 1,
        ];
    }

    private function isTeacher(int $userId): bool
    {
        return User::query()
            ->whereKey($userId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Teacher', 'teacher']))
            ->exists();
    }

    private function course(): Course
    {
        return Course::query()->findOrFail($this->courseId);
    }
}
