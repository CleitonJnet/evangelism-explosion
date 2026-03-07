<?php

use App\Livewire\Pages\App\Director\Course\Sections as CourseSections;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForCourseSectionsManagement(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('creates a new section at the end of the course list and stores its uploaded banner', function (): void {
    Storage::fake('public');

    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'name' => 'Clínica de Líderes',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 1,
        'name' => 'Unidade 1',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 2,
        'name' => 'Unidade 2',
    ]);

    $director = createDirectorForCourseSectionsManagement();

    Livewire::actingAs($director)
        ->test(CourseSections::class, ['course' => $course])
        ->call('openCreateSectionModal')
        ->set('sectionForm.name', 'Unidade 3')
        ->set('sectionForm.duration', 45)
        ->set('sectionForm.devotional', 'João 3')
        ->set('sectionForm.description', 'Descrição da unidade')
        ->set('sectionForm.knowhow', 'Conhecimento da unidade')
        ->set('bannerUpload', UploadedFile::fake()->image('section-banner.webp', 500, 500)->size(500))
        ->call('saveSection')
        ->assertSet('showSectionModal', false);

    $section = Section::query()->where('course_id', $course->id)->where('name', 'Unidade 3')->first();

    expect($section)->not->toBeNull()
        ->and((int) $section->order)->toBe(3)
        ->and($section->banner)->not->toBeNull();

    Storage::disk('public')->assertExists($section->banner);
});

it('validates section data on the backend while the modal form is updated', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Escola de líderes',
        'initials' => 'EL',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
    ]);

    $section = Section::factory()->create([
        'course_id' => $course->id,
        'name' => 'Unidade válida',
        'duration' => '45 min',
    ]);

    $director = createDirectorForCourseSectionsManagement();

    Livewire::actingAs($director)
        ->test(CourseSections::class, ['course' => $course])
        ->call('openEditSectionModal', $section->id)
        ->set('sectionForm.name', '')
        ->assertHasErrors(['sectionForm.name' => ['required']])
        ->set('sectionForm.duration', '45 min')
        ->assertHasErrors(['sectionForm.duration' => ['integer']])
        ->set('sectionForm.duration', 43)
        ->assertHasErrors(['sectionForm.duration' => ['multiple_of']])
        ->set('sectionForm.devotional', str_repeat('b', 256))
        ->assertHasErrors(['sectionForm.devotional' => ['max']]);
});

it('normalizes legacy section duration to minutes when opening the edit modal', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Capacitação',
        'initials' => 'CP',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
    ]);

    $section = Section::factory()->create([
        'course_id' => $course->id,
        'duration' => '45 min',
    ]);

    $director = createDirectorForCourseSectionsManagement();

    Livewire::actingAs($director)
        ->test(CourseSections::class, ['course' => $course])
        ->call('openEditSectionModal', $section->id)
        ->assertSet('sectionForm.duration', 45);
});
