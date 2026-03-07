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

function totalTrainingItems(\Illuminate\Support\Collection $groups): int
{
    return $groups->sum(fn (array $group): int => $group['courses']->sum(fn (array $courseGroup): int => $courseGroup['items']->count()));
}

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
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'Jonas')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'Sao')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'SP')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);
});

it('filters director trainings index with a single search input', function (): void {
    $actingDirector = User::factory()->create();
    $matchingTeacher = User::factory()->create(['name' => 'Professora Zuleica']);
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
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'Zulei')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'Curi')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);

    $groups = $component->set('searchTerm', 'AC')->viewData('groups');
    expect(totalTrainingItems($groups))->toBe(1);
});

it('shows only leadership trainings on the teacher index', function (): void {
    $actingTeacher = User::factory()->create();
    $leadershipCourse = Course::factory()->create([
        'execution' => 0,
        'name' => 'Clínica de Liderança',
    ]);
    $membersCourse = Course::factory()->create([
        'execution' => 1,
        'name' => 'Treinamento para Membros',
    ]);

    Training::factory()->create([
        'course_id' => $leadershipCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    Training::factory()->create([
        'course_id' => $membersCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $groups = Livewire::actingAs($actingTeacher)
        ->test(TeacherTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->viewData('groups');

    expect($groups->flatMap(fn (array $group) => $group['courses']->pluck('course.name'))->filter()->values()->all())->toBe(['Clínica de Liderança']);
});

it('shows only leadership trainings on the director index', function (): void {
    $actingDirector = User::factory()->create();
    $leadershipCourse = Course::factory()->create([
        'execution' => 0,
        'name' => 'Clínica de Liderança',
    ]);
    $membersCourse = Course::factory()->create([
        'execution' => 1,
        'name' => 'Treinamento para Membros',
    ]);

    Training::factory()->create([
        'course_id' => $leadershipCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    Training::factory()->create([
        'course_id' => $membersCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $groups = Livewire::actingAs($actingDirector)
        ->test(DirectorTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->viewData('groups');

    expect($groups->flatMap(fn (array $group) => $group['courses']->pluck('course.name'))->filter()->values()->all())->toBe(['Clínica de Liderança']);
});

it('groups director trainings by ministry', function (): void {
    $actingDirector = User::factory()->create();
    $ministryAlpha = \App\Models\Ministry::query()->create(['initials' => 'ALP', 'name' => 'Ministerio Alpha']);
    $ministryBeta = \App\Models\Ministry::query()->create(['initials' => 'BET', 'name' => 'Ministerio Beta']);

    $courseOne = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso A',
        'ministry_id' => $ministryAlpha->id,
    ]);
    $courseTwo = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso B',
        'ministry_id' => $ministryAlpha->id,
    ]);
    $courseThree = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso C',
        'ministry_id' => $ministryBeta->id,
    ]);

    Training::factory()->create(['course_id' => $courseOne->id, 'status' => TrainingStatus::Scheduled]);
    Training::factory()->create(['course_id' => $courseTwo->id, 'status' => TrainingStatus::Scheduled]);
    Training::factory()->create(['course_id' => $courseThree->id, 'status' => TrainingStatus::Scheduled]);

    $groups = Livewire::actingAs($actingDirector)
        ->test(DirectorTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->viewData('groups');

    expect($groups)->toHaveCount(2)
        ->and($groups->first()['ministry']?->name)->toBe('Ministerio Alpha')
        ->and($groups->first()['courses'])->toHaveCount(2)
        ->and($groups->last()['ministry']?->name)->toBe('Ministerio Beta')
        ->and($groups->last()['courses'])->toHaveCount(1);
});

it('groups teacher trainings by ministry', function (): void {
    $actingTeacher = User::factory()->create();
    $ministryAlpha = \App\Models\Ministry::query()->create(['initials' => 'ALP', 'name' => 'Ministerio Alpha']);
    $ministryBeta = \App\Models\Ministry::query()->create(['initials' => 'BET', 'name' => 'Ministerio Beta']);

    $courseOne = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso A',
        'ministry_id' => $ministryAlpha->id,
    ]);
    $courseTwo = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso B',
        'ministry_id' => $ministryAlpha->id,
    ]);
    $courseThree = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso C',
        'ministry_id' => $ministryBeta->id,
    ]);

    Training::factory()->create(['course_id' => $courseOne->id, 'status' => TrainingStatus::Scheduled]);
    Training::factory()->create(['course_id' => $courseTwo->id, 'status' => TrainingStatus::Scheduled]);
    Training::factory()->create(['course_id' => $courseThree->id, 'status' => TrainingStatus::Scheduled]);

    $groups = Livewire::actingAs($actingTeacher)
        ->test(TeacherTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->viewData('groups');

    expect($groups)->toHaveCount(2)
        ->and($groups->first()['ministry']?->name)->toBe('Ministerio Alpha')
        ->and($groups->first()['courses'])->toHaveCount(2)
        ->and($groups->last()['ministry']?->name)->toBe('Ministerio Beta')
        ->and($groups->last()['courses'])->toHaveCount(1);
});
