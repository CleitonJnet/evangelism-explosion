<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherForDashboard(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('renders operational teacher dashboard with pendencies and quick actions', function (): void {
    $teacher = createTeacherForDashboard();
    $church = Church::factory()->create();
    $pendingTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Pendente Dashboard',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente dashboard',
    ]);

    $trainingWithIssue = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $studentWithPendingChurch = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $pendingTemp->id,
    ]);
    $trainingWithIssue->students()->attach($studentWithPendingChurch->id);

    $trainingWithoutIssue = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Planning,
    ]);
    $studentWithChurch = User::factory()->create([
        'church_id' => $church->id,
        'church_temp_id' => null,
    ]);
    $trainingWithoutIssue->students()->attach($studentWithChurch->id);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Próximos Treinamentos');
    $response->assertSee('Pendências');
    $response->assertSee('Ações rápidas');
    $response->assertSee((string) $trainingWithIssue->course?->name);
    $response->assertSee('Programação pendente');
    $response->assertSee('Inscrições com igreja ausente/pendente');
});
