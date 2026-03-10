<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('filters the teacher trainings list by teacher church location course date mentor and assistant teacher', function (): void {
    $teacherUser = createUserWithRole('Teacher');
    $principalTeacher = $teacherUser;
    $principalTeacher->update(['name' => 'Professor Jonas']);
    $assistantTeacher = User::factory()->create(['name' => 'Auxiliar Marta']);
    $mentorUser = User::factory()->create(['name' => 'Mentor Elias']);
    $church = Church::factory()->create([
        'name' => 'Igreja Central Esperanca',
        'city' => 'Campinas',
        'state' => 'SP',
    ]);
    $course = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clinica',
        'name' => 'Evangelismo Essencial',
    ]);
    $otherCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Workshop',
        'name' => 'Treinamento Urbano',
    ]);

    $matchingTraining = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $principalTeacher->id,
        'church_id' => $church->id,
        'city' => 'Campinas',
        'state' => 'SP',
        'status' => TrainingStatus::Scheduled,
    ]);
    $matchingTraining->assistantTeachers()->attach($assistantTeacher->id);
    $matchingTraining->mentors()->attach($mentorUser->id, ['created_by' => $teacherUser->id]);
    $matchingTraining->eventDates()->delete();
    $matchingTraining->eventDates()->create([
        'date' => '2026-04-12',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $otherTraining = Training::factory()->create([
        'course_id' => $otherCourse->id,
        'teacher_id' => User::factory()->create(['name' => 'Professor Lucas'])->id,
        'church_id' => Church::factory()->create([
            'name' => 'Igreja Bairro Novo',
            'city' => 'Recife',
            'state' => 'PE',
        ])->id,
        'city' => 'Recife',
        'state' => 'PE',
        'status' => TrainingStatus::Scheduled,
    ]);
    $otherTraining->eventDates()->delete();
    $otherTraining->eventDates()->create([
        'date' => '2026-06-20',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $searches = [
        'Professor Jonas',
        'Central Esperanca',
        'Campinas',
        'SP',
        '12/04/2026',
        'Clinica',
        'Auxiliar Marta',
        'Mentor Elias',
    ];

    foreach ($searches as $search) {
        $response = $this->actingAs($teacherUser)
            ->get(route('app.teacher.trainings.scheduled', ['filter' => $search]));

        $response
            ->assertOk()
            ->assertSee('name="filter"', false)
            ->assertSee('value="'.$search.'"', false)
            ->assertSee('Evangelismo Essencial')
            ->assertDontSee('Treinamento Urbano')
            ->assertDontSee('Recife, PE');
    }
});

it('filters the director trainings list and preserves the filter in status links', function (): void {
    $directorUser = createUserWithRole('Director');
    $matchingTeacher = User::factory()->create(['name' => 'Professora Zuleica']);
    $church = Church::factory()->create(['name' => 'Igreja Vida Plena']);
    $course = Course::factory()->create([
        'execution' => 0,
        'type' => 'Workshop',
        'name' => 'Treinamento Regional',
    ]);
    $otherCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Seminario',
        'name' => 'Capacitacao Local',
    ]);

    Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $matchingTeacher->id,
        'church_id' => $church->id,
        'city' => 'Curitiba',
        'state' => 'PR',
        'status' => TrainingStatus::Scheduled,
    ]);

    Training::factory()->create([
        'course_id' => $otherCourse->id,
        'teacher_id' => User::factory()->create(['name' => 'Professora Bia'])->id,
        'church_id' => Church::factory()->create(['name' => 'Igreja Vida Nova'])->id,
        'city' => 'Recife',
        'state' => 'PE',
        'status' => TrainingStatus::Scheduled,
    ]);

    $response = $this->actingAs($directorUser)
        ->get(route('app.director.training.scheduled', ['filter' => 'Zuleica']));

    $response
        ->assertOk()
        ->assertSee('Treinamento Regional')
        ->assertDontSee('Capacitacao Local')
        ->assertDontSee('Recife, PE')
        ->assertSee(route('app.director.training.completed', ['filter' => 'Zuleica']), false);
});

it('shows only leadership trainings on the teacher index', function (): void {
    $teacherUser = createUserWithRole('Teacher');
    $otherTeacher = createUserWithRole('Teacher');
    $leadershipCourse = Course::factory()->create([
        'execution' => 0,
        'name' => 'Clinica de Lideranca',
    ]);
    $membersCourse = Course::factory()->create([
        'execution' => 1,
        'name' => 'Treinamento para Membros',
    ]);

    Training::factory()->create([
        'course_id' => $leadershipCourse->id,
        'teacher_id' => $teacherUser->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    Training::factory()->create([
        'course_id' => $leadershipCourse->id,
        'teacher_id' => $otherTeacher->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    Training::factory()->create([
        'course_id' => $membersCourse->id,
        'teacher_id' => $teacherUser->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $response = $this->actingAs($teacherUser)
        ->get(route('app.teacher.trainings.scheduled'));

    $response
        ->assertOk()
        ->assertSee('Clinica de Lideranca')
        ->assertDontSee($otherTeacher->name)
        ->assertDontSee('Treinamento para Membros');
});

it('groups director trainings by ministry on the page', function (): void {
    $directorUser = createUserWithRole('Director');
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

    $response = $this->actingAs($directorUser)
        ->get(route('app.director.training.scheduled'));

    $content = $response->getContent();

    $response
        ->assertOk()
        ->assertSee('Ministerio Alpha')
        ->assertSee('Ministerio Beta');

    expect(strpos($content, 'Ministerio Alpha'))->toBeInt()
        ->and(strpos($content, 'Ministerio Beta'))->toBeInt()
        ->and(strpos($content, 'id="course-'.$courseOne->id.'"'))->toBeInt()
        ->and(strpos($content, 'id="course-'.$courseTwo->id.'"'))->toBeInt()
        ->and(strpos($content, 'id="course-'.$courseThree->id.'"'))->toBeInt()
        ->and(strpos($content, 'Ministerio Alpha'))->toBeLessThan(strpos($content, 'Ministerio Beta'))
        ->and(strpos($content, 'Ministerio Alpha'))->toBeLessThan(strpos($content, 'id="course-'.$courseOne->id.'"'))
        ->and(strpos($content, 'Ministerio Beta'))->toBeLessThan(strpos($content, 'id="course-'.$courseThree->id.'"'));
});

it('renders the course index with valid courses only', function (): void {
    $view = $this->blade(
        <<<'BLADE'
        @php
            $groups = collect([
                [
                    'ministry' => (object) ['name' => 'Ministerio Alpha', 'color' => '#c9b457'],
                    'courses' => collect([
                        [
                            'course' => (object) ['id' => 15, 'name' => 'Curso Valido', 'initials' => 'CV', 'type' => 'Clinica'],
                            'items' => collect([]),
                        ],
                        [
                            'course' => null,
                            'items' => collect([]),
                        ],
                    ]),
                ],
            ]);

            $statuses = [
                ['key' => 'scheduled', 'label' => 'Agendados', 'route' => '/treinamentos/agendados'],
            ];
        @endphp

        <x-src.training-index
            role="director"
            create-route="/treinamentos/novo"
            status-key="scheduled"
            :statuses="$statuses"
            :groups="$groups"
        />
        BLADE,
    );

    $view
        ->assertSee('href="#course-15"', false)
        ->assertDontSee('href="#course-curso"', false)
        ->assertDontSee('Curso não informado');
});
