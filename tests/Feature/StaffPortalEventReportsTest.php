<?php

use App\Enums\EventReportReviewOutcome;
use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Livewire\Pages\App\Portal\Staff\Reports\Show;
use App\Models\Church;
use App\Models\Course;
use App\Models\EventReportReview;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\EventReports\EventReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createStaffPortalActor(array $roles, ?int $churchId = null): User
{
    $user = User::factory()->create(['church_id' => $churchId]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user->fresh();
}

function createGovernanceTraining(User $teacher, int $churchId): Training
{
    $training = Training::factory()->create([
        'course_id' => Course::factory()->create(['name' => 'Clinica de Governanca'])->id,
        'teacher_id' => $teacher->id,
        'church_id' => $churchId,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->subDays(4)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    return $training->fresh();
}

function submitGovernanceReport(Training $training, EventReportType $type, User $actor, array $payload): void
{
    $service = app(EventReportService::class);
    $report = $service->ensureReport($training, $type, $actor);

    $service->submit($report, $payload, $actor);
}

it('lists staff governance reports and opens the comparative view', function (): void {
    $hostChurch = Church::factory()->create(['name' => 'Igreja Central']);
    $staff = createStaffPortalActor(['Director']);
    $teacher = createStaffPortalActor(['Teacher'], $hostChurch->id);
    $host = createStaffPortalActor(['Facilitator'], $hostChurch->id);
    $training = createGovernanceTraining($teacher, $hostChurch->id);

    submitGovernanceReport($training, EventReportType::Church, $host, [
        'summary' => 'A base concluiu a consolidacao local.',
        'sections' => [
            ['key' => 'attendance', 'position' => 1, 'content' => ['registered' => 40, 'present' => 36, 'decisions' => 4]],
        ],
    ]);

    submitGovernanceReport($training, EventReportType::Teacher, $teacher, [
        'summary' => 'O professor concluiu a execucao ministerial.',
        'sections' => [
            ['key' => 'execution', 'position' => 1, 'content' => ['sessions_completed' => 6, 'people_trained' => 36, 'practical_contacts' => 12]],
        ],
    ]);

    $this->actingAs($staff)
        ->get(route('app.portal.staff.reports.index'))
        ->assertSuccessful()
        ->assertSee('Relatorios recebidos')
        ->assertSee('Clinica de Governanca')
        ->assertSee('Abrir comparacao');

    $this->actingAs($staff)
        ->get(route('app.portal.staff.trainings.reports', $training))
        ->assertSuccessful()
        ->assertSee('Relatorio da igreja-base')
        ->assertSee('Relatorio do professor')
        ->assertSee('Registrar leitura do Staff');
});

it('shows concluded events with missing evidence in the pending submissions queue', function (): void {
    $hostChurch = Church::factory()->create(['name' => 'Base de Apoio']);
    $staff = createStaffPortalActor(['Board']);
    $teacher = createStaffPortalActor(['Teacher'], $hostChurch->id);
    createGovernanceTraining($teacher, $hostChurch->id);

    $this->actingAs($staff)
        ->get(route('app.portal.staff.reports.pending'))
        ->assertSuccessful()
        ->assertSee('Pendentes de envio')
        ->assertSee('Clinica de Governanca')
        ->assertSee('Igreja-base')
        ->assertSee('Professor');
});

it('limits fieldworker governance report access to contextual bases and keeps review actions read only', function (): void {
    $accessibleChurch = Church::factory()->create(['name' => 'Base Acompanhada']);
    $hiddenChurch = Church::factory()->create(['name' => 'Base Fora do Escopo']);
    $fieldworker = createStaffPortalActor(['FieldWorker'], $accessibleChurch->id);
    $accessibleChurch->missionaries()->attach($fieldworker->id);
    $teacherA = createStaffPortalActor(['Teacher'], $accessibleChurch->id);
    $teacherB = createStaffPortalActor(['Teacher'], $hiddenChurch->id);
    $host = createStaffPortalActor(['Facilitator'], $accessibleChurch->id);
    $accessibleTraining = createGovernanceTraining($teacherA, $accessibleChurch->id);
    $hiddenTraining = createGovernanceTraining($teacherB, $hiddenChurch->id);

    submitGovernanceReport($accessibleTraining, EventReportType::Church, $host, [
        'summary' => 'Relato da base acompanhada.',
        'sections' => [
            ['key' => 'attendance', 'position' => 1, 'content' => ['registered' => 22, 'present' => 20, 'decisions' => 2]],
        ],
    ]);

    submitGovernanceReport($accessibleTraining, EventReportType::Teacher, $teacherA, [
        'summary' => 'Relato do professor da base acompanhada.',
        'sections' => [
            ['key' => 'execution', 'position' => 1, 'content' => ['sessions_completed' => 4, 'people_trained' => 20, 'practical_contacts' => 7]],
        ],
    ]);

    submitGovernanceReport($hiddenTraining, EventReportType::Teacher, $teacherB, [
        'summary' => 'Relato de uma base fora do escopo contextual.',
        'sections' => [
            ['key' => 'execution', 'position' => 1, 'content' => ['sessions_completed' => 3, 'people_trained' => 14, 'practical_contacts' => 4]],
        ],
    ]);

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.reports.index'))
        ->assertSuccessful()
        ->assertSee('Base Acompanhada')
        ->assertDontSee('Base Fora do Escopo');

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.trainings.reports', $accessibleTraining))
        ->assertSuccessful()
        ->assertSee('Leitura contextual do fieldworker')
        ->assertDontSee('Registrar leitura do Staff');

    $this->actingAs($fieldworker)
        ->get(route('app.portal.staff.trainings.reports', $hiddenTraining))
        ->assertNotFound();

    Livewire::actingAs($fieldworker)
        ->test(Show::class, ['training' => $accessibleTraining])
        ->call('saveReview')
        ->assertForbidden();
});

it('records a staff review with classification and follow-up on the comparative page', function (): void {
    $hostChurch = Church::factory()->create();
    $staff = createStaffPortalActor(['Director']);
    $teacher = createStaffPortalActor(['Teacher'], $hostChurch->id);
    $host = createStaffPortalActor(['Facilitator'], $hostChurch->id);
    $training = createGovernanceTraining($teacher, $hostChurch->id);

    submitGovernanceReport($training, EventReportType::Church, $host, [
        'summary' => 'Consolidacao da base pronta.',
        'sections' => [
            ['key' => 'attendance', 'position' => 1, 'content' => ['registered' => 35, 'present' => 30, 'decisions' => 3]],
        ],
    ]);

    submitGovernanceReport($training, EventReportType::Teacher, $teacher, [
        'summary' => 'Execucao ministerial concluida.',
        'sections' => [
            ['key' => 'execution', 'position' => 1, 'content' => ['sessions_completed' => 5, 'people_trained' => 30, 'practical_contacts' => 8]],
        ],
    ]);

    Livewire::actingAs($staff)
        ->test(Show::class, ['training' => $training])
        ->set('reviewForm.action', EventReportReviewOutcome::Approved->value)
        ->set('reviewForm.classification', 'attention')
        ->set('reviewForm.follow_up_required', true)
        ->set('reviewForm.comment', 'Manter acompanhamento institucional do proximo ciclo.')
        ->call('saveReview')
        ->assertSee('Leitura do Staff registrada');

    expect(EventReportReview::query()->count())->toBe(2);

    expect(EventReportReview::query()->latest('id')->first())
        ->outcome->toBe(EventReportReviewOutcome::Approved)
        ->payload->toMatchArray([
            'scope' => 'staff_governance',
            'classification' => 'attention',
            'follow_up_required' => true,
        ]);

    $this->assertDatabaseHas('event_reports', [
        'training_id' => $training->id,
        'type' => EventReportType::Church->value,
        'status' => EventReportStatus::Reviewed->value,
        'last_reviewed_by_user_id' => $staff->id,
    ]);

    $this->assertDatabaseHas('event_reports', [
        'training_id' => $training->id,
        'type' => EventReportType::Teacher->value,
        'status' => EventReportStatus::Reviewed->value,
        'last_reviewed_by_user_id' => $staff->id,
    ]);
});
