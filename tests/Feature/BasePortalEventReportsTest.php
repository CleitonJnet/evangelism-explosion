<?php

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Livewire\Pages\App\Portal\Base\Training\Reports;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createBasePortalActor(array $roles): User
{
    $church = Church::factory()->create();
    $user = User::factory()->create(['church_id' => $church->id]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user->fresh();
}

function createBasePortalTraining(User $teacher, ?int $churchId = null): Training
{
    return Training::factory()->create([
        'course_id' => Course::factory()->create()->id,
        'teacher_id' => $teacher->id,
        'church_id' => $churchId ?? $teacher->church_id,
    ]);
}

it('allows the hosted church to save draft and submit the church report', function (): void {
    $hostUser = createBasePortalActor(['Facilitator']);
    $teacher = createBasePortalActor(['Teacher']);
    $training = createBasePortalTraining($teacher, $hostUser->church_id);

    Livewire::actingAs($hostUser)
        ->test(Reports::class, ['training' => $training])
        ->set('churchForm.summary', 'Resumo local do evento.')
        ->set('churchForm.local_highlights', 'Recepcao alinhada, salas prontas e equipe de apoio ativa.')
        ->set('churchForm.attendance_present', 38)
        ->call('saveChurchDraft')
        ->assertSee('Rascunho do relatorio da igreja salvo com sucesso.');

    $this->assertDatabaseHas('event_reports', [
        'training_id' => $training->id,
        'type' => EventReportType::Church->value,
        'status' => EventReportStatus::Draft->value,
    ]);

    Livewire::actingAs($hostUser)
        ->test(Reports::class, ['training' => $training->fresh()])
        ->set('churchForm.summary', 'Resumo local do evento.')
        ->set('churchForm.local_highlights', 'Recepcao alinhada, salas prontas e equipe de apoio ativa.')
        ->set('churchForm.attendance_present', 38)
        ->call('submitChurchReport')
        ->assertSee('Relatorio da igreja enviado com sucesso.')
        ->assertSee('Enviado');

    $this->assertDatabaseHas('event_reports', [
        'training_id' => $training->id,
        'type' => EventReportType::Church->value,
        'status' => EventReportStatus::Submitted->value,
        'submitted_by_user_id' => $hostUser->id,
    ]);

    $this->actingAs($hostUser)
        ->get(route('app.portal.base.trainings.show', $training))
        ->assertOk()
        ->assertSee('Relatorio da igreja-base')
        ->assertSee('Enviado');
});

it('allows the teacher to submit the teacher report and locks edits after submission', function (): void {
    $teacher = createBasePortalActor(['Teacher']);
    $training = createBasePortalTraining($teacher);

    Livewire::actingAs($teacher)
        ->test(Reports::class, ['training' => $training])
        ->set('teacherForm.summary', 'Execucao concluida conforme o planejado.')
        ->set('teacherForm.ministry_highlights', 'As saidas praticas geraram bons contatos e follow-up.')
        ->set('teacherForm.sessions_completed', 6)
        ->call('submitTeacherReport')
        ->assertSee('Relatorio do professor enviado com sucesso.');

    $this->assertDatabaseHas('event_reports', [
        'training_id' => $training->id,
        'type' => EventReportType::Teacher->value,
        'status' => EventReportStatus::Submitted->value,
        'submitted_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(Reports::class, ['training' => $training->fresh()])
        ->set('teacherForm.summary', 'Tentativa de alterar apos envio.')
        ->call('saveTeacherDraft')
        ->assertHasErrors(['teacherReportLock']);
});

it('restricts each report workflow to the correct actor and training context', function (): void {
    $hostUser = createBasePortalActor(['Facilitator']);
    $teacher = createBasePortalActor(['Teacher']);
    $outsideTeacher = createBasePortalActor(['Teacher']);
    $training = createBasePortalTraining($teacher, $hostUser->church_id);
    $outsideTraining = createBasePortalTraining($outsideTeacher);

    Livewire::actingAs($hostUser)
        ->test(Reports::class, ['training' => $training])
        ->call('submitTeacherReport')
        ->assertForbidden();

    Livewire::actingAs($teacher)
        ->test(Reports::class, ['training' => $training])
        ->call('submitChurchReport')
        ->assertForbidden();

    Livewire::actingAs($teacher)
        ->test(Reports::class, ['training' => $outsideTraining])
        ->assertForbidden();
});
