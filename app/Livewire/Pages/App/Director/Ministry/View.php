<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Course;
use App\Models\Ministry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View as ViewContract;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public Ministry $ministry;

    public function mount(Ministry $ministry): void
    {
        $this->ministry = $ministry;
    }

    #[On('director-ministry-updated')]
    public function refreshMinistry(int $ministryId): void
    {
        if ($this->ministry->id !== $ministryId) {
            return;
        }

        $this->ministry = $this->ministry->fresh(['courses']);
    }

    public function moveCourseAfter(int $courseId, int $targetExecution, ?int $afterCourseId = null): void
    {
        if (! in_array($targetExecution, [0, 1], true)) {
            return;
        }

        $course = Course::query()
            ->where('ministry_id', $this->ministry->id)
            ->findOrFail($courseId);

        $sourceExecution = (int) $course->execution;
        $targetCourses = Course::query()
            ->where('ministry_id', $this->ministry->id)
            ->where('execution', $targetExecution)
            ->orderByRaw('CAST(`order` AS SIGNED)')
            ->orderBy('id')
            ->get()
            ->reject(fn (Course $listedCourse): bool => $listedCourse->id === $course->id)
            ->values();

        $insertIndex = 0;

        if ($afterCourseId !== null) {
            $insertIndex = $targetCourses
                ->search(fn (Course $listedCourse): bool => $listedCourse->id === $afterCourseId);

            $insertIndex = $insertIndex === false ? $targetCourses->count() : $insertIndex + 1;
        }

        $reorderedTargetCourses = $targetCourses->all();
        array_splice($reorderedTargetCourses, $insertIndex, 0, [$course]);

        DB::transaction(function () use (
            $course,
            $sourceExecution,
            $targetExecution,
            $reorderedTargetCourses
        ): void {
            if ($sourceExecution !== $targetExecution) {
                $course->forceFill(['execution' => $targetExecution])->save();
            }

            $this->syncCourseOrder(collect($reorderedTargetCourses));

            if ($sourceExecution !== $targetExecution) {
                $sourceCourses = Course::query()
                    ->where('ministry_id', $this->ministry->id)
                    ->where('execution', $sourceExecution)
                    ->orderByRaw('CAST(`order` AS SIGNED)')
                    ->orderBy('id')
                    ->get();

                $this->syncCourseOrder($sourceCourses);
            }
        });

        $this->refreshMinistry($this->ministry->id);
    }

    public function render(): ViewContract
    {
        $ministry = $this->ministry->loadMissing('courses');
        $courses = $ministry->courses->loadMissing(['teachers.roles', 'sections'])->sortBy([
            ['execution', 'asc'],
            ['order', 'asc'],
            ['name', 'asc'],
        ])->values();
        $leadershipCourses = $courses->where('execution', 0)->values();
        $implementationCourses = $courses->where('execution', 1)->values();
        $teachersCount = $this->countTeachersByRole($courses, 'Teacher');

        return view('livewire.pages.app.director.ministry.view', [
            'logoUrl' => $this->logoUrl($ministry),
            'coursesCount' => $courses->count(),
            'leadershipCourses' => $leadershipCourses,
            'implementationCourses' => $implementationCourses,
            'leadershipTeachersCount' => $this->countTeachersByRole($leadershipCourses, 'Teacher'),
            'implementationFacilitatorsCount' => $this->countTeachersByRole($implementationCourses, 'Facilitator'),
            'teachersCount' => $teachersCount,
        ]);
    }

    private function logoUrl(Ministry $ministry): ?string
    {
        $logoValue = trim((string) $ministry->logo);

        if ($logoValue === '') {
            return null;
        }

        if (str_starts_with($logoValue, 'http')) {
            return $logoValue;
        }

        $normalizedLogo = ltrim($logoValue, '/');

        if (! Storage::disk('public')->exists($normalizedLogo)) {
            return null;
        }

        return Storage::disk('public')->url($normalizedLogo);
    }

    private function countTeachersByRole(\Illuminate\Support\Collection $courses, string $roleName): int
    {
        return $courses
            ->flatMap(fn ($course) => $course->teachers)
            ->filter(fn ($teacher): bool => $teacher->hasRole($roleName))
            ->unique('id')
            ->count();
    }

    /**
     * @param  Collection<int, Course>  $courses
     */
    private function syncCourseOrder(Collection $courses): void
    {
        $courses->values()->each(function (Course $course, int $index): void {
            $nextOrder = $index + 1;

            if ((int) $course->order === $nextOrder) {
                return;
            }

            $course->forceFill(['order' => $nextOrder])->save();
        });
    }
}
