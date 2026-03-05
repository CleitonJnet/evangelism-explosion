<?php

use App\Livewire\Pages\App\Director\Training\Index as DirectorTrainingIndex;
use App\Livewire\Pages\App\Teacher\Training\Index as TeacherTrainingIndex;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters teacher trainings index with a single search input', function (): void {
    $actingTeacher = User::factory()->create();
    $matchingTeacher = User::factory()->create(['name' => 'Professor Jonas']);
    $otherTeacher = User::factory()->create(['name' => 'Professor Lucas']);

    $matchingChurch = Church::factory()->create(['name' => 'Igreja Central Esperanca']);
    $otherChurch = Church::factory()->create(['name' => 'Igreja Bairro Novo']);

    $course = Course::factory()->create(['execution' => 0]);

    Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $matchingTeacher->id,
        'church_id' => $matchingChurch->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'status' => TrainingStatus::Scheduled,
    ]);

    Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $otherTeacher->id,
        'church_id' => $otherChurch->id,
        'city' => 'Rio de Janeiro',
        'state' => 'RJ',
        'status' => TrainingStatus::Scheduled,
    ]);

    $component = Livewire::actingAs($actingTeacher)
        ->test(TeacherTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->set('searchTerm', 'Central');

    $groups = $component->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'Jonas')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'Sao')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'SP')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);
});

it('filters director trainings index with a single search input', function (): void {
    $actingDirector = User::factory()->create();
    $matchingTeacher = User::factory()->create(['name' => 'Professora Ana']);
    $otherTeacher = User::factory()->create(['name' => 'Professora Bia']);

    $matchingChurch = Church::factory()->create(['name' => 'Igreja Vida Plena']);
    $otherChurch = Church::factory()->create(['name' => 'Igreja Vida Nova']);

    $course = Course::factory()->create(['execution' => 0]);

    Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $matchingTeacher->id,
        'church_id' => $matchingChurch->id,
        'city' => 'Curitiba',
        'state' => 'AC',
        'status' => TrainingStatus::Scheduled,
    ]);

    Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $otherTeacher->id,
        'church_id' => $otherChurch->id,
        'city' => 'Recife',
        'state' => 'PE',
        'status' => TrainingStatus::Scheduled,
    ]);

    $component = Livewire::actingAs($actingDirector)
        ->test(DirectorTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->set('searchTerm', 'Plena');

    $groups = $component->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'Ana')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'Curi')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);

    $groups = $component->set('searchTerm', 'AC')->viewData('groups');
    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);
});
