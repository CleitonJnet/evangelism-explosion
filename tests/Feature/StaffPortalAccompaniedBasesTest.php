<?php

use App\Enums\EventReportType;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\EventReports\EventReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStaffBaseActor(array $roles, ?int $churchId = null): User
{
    $user = User::factory()->create(['church_id' => $churchId]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user->fresh();
}

function createAccompaniedBaseTraining(Church $church, User $teacher, string $courseName = 'Clinica Base Acompanhada'): Training
{
    $training = Training::factory()->create([
        'course_id' => Course::factory()->create(['name' => $courseName])->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->subDays(3)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    return $training->fresh();
}

function submitAccompaniedBaseReport(Training $training, EventReportType $type, User $actor, array $payload): void
{
    $service = app(EventReportService::class);
    $report = $service->ensureReport($training, $type, $actor);

    $service->submit($report, $payload, $actor);
}

it('shows accompanied bases in the staff portal with health and pending indicators', function (): void {
    $staff = createStaffBaseActor(['Director']);
    $church = Church::factory()->create([
        'name' => 'Base Acompanhada Central',
        'city' => 'Campinas',
        'state' => 'SP',
    ]);
    $teacher = createStaffBaseActor(['Teacher'], $church->id);
    $fieldworker = createStaffBaseActor(['FieldWorker']);
    $church->missionaries()->attach($fieldworker->id);
    $training = createAccompaniedBaseTraining($church, $teacher);

    submitAccompaniedBaseReport($training, EventReportType::Teacher, $teacher, [
        'summary' => 'Execucao ministerial concluida.',
        'sections' => [
            ['key' => 'execution', 'position' => 1, 'content' => ['sessions_completed' => 5, 'people_trained' => 28, 'practical_contacts' => 9]],
        ],
    ]);

    $this->actingAs($staff)
        ->get(route('app.portal.staff.bases.index'))
        ->assertSuccessful()
        ->assertSee('Bases acompanhadas')
        ->assertSee('Base Acompanhada Central')
        ->assertSee('Saudaveis')
        ->assertSee('Relatos pendentes');

    $this->actingAs($staff)
        ->get(route('app.portal.staff.bases.show', $church))
        ->assertSuccessful()
        ->assertSee('Resumo da base')
        ->assertSee('Pendencias e sinais')
        ->assertSee('Eventos realizados')
        ->assertSee('Indicadores e agenda')
        ->assertSee('Governanca institucional');
});

it('limits the fieldworker accompanied-bases view to its contextual churches', function (): void {
    $accessibleChurch = Church::factory()->create(['name' => 'Base do Campo']);
    $hiddenChurch = Church::factory()->create(['name' => 'Base Oculta']);
    $fieldworker = createStaffBaseActor(['FieldWorker'], $accessibleChurch->id);
    $accessibleChurch->missionaries()->attach($fieldworker->id);
    $teacherA = createStaffBaseActor(['Teacher'], $accessibleChurch->id);
    $teacherB = createStaffBaseActor(['Teacher'], $hiddenChurch->id);

    createAccompaniedBaseTraining($accessibleChurch, $teacherA, 'Treinamento Acessivel');
    createAccompaniedBaseTraining($hiddenChurch, $teacherB, 'Treinamento Oculto');

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.bases.index'))
        ->assertSuccessful()
        ->assertSee('Acompanhamento contextual do fieldworker')
        ->assertSee('Base do Campo')
        ->assertDontSee('Base Oculta');

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.bases.show', $accessibleChurch))
        ->assertSuccessful()
        ->assertSee('Como fieldworker, seu papel aqui e acompanhar a base');

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.bases.show', $hiddenChurch))
        ->assertNotFound();
});
