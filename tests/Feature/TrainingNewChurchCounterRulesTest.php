<?php

use App\Livewire\Pages\App\Teacher\Training\ApproveChurchTempModal;
use App\Livewire\Pages\App\Teacher\Training\ChurchTempReviewModal;
use App\Livewire\Pages\App\Teacher\Training\Index as TrainingIndex;
use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForCounterRules(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForCounterRules(User $teacher): Training
{
    $course = Course::factory()->create(['execution' => 0]);
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
        'status' => TrainingStatus::Scheduled,
    ]);
}

function createPendingTempForCounterRules(string $name = 'Igreja Pendente Regras'): ChurchTemp
{
    return ChurchTemp::query()->create([
        'name' => $name,
        'pastor' => 'Pr. Regra',
        'city' => 'Recife',
        'state' => 'PE',
        'street' => 'Rua Regra',
        'number' => '12',
        'district' => 'Centro',
        'postal_code' => '50000000',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente regras',
    ]);
}

it('keeps new churches count at zero when there are only pending temps', function (): void {
    $teacher = createTeacherForCounterRules();
    $training = createTrainingForCounterRules($teacher);
    $temp = createPendingTempForCounterRules();

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    expect($training->newChurches()->count())->toBe(0);
    $this->assertDatabaseCount('training_new_churches', 0);

    Livewire::actingAs($teacher)
        ->test(TrainingIndex::class, ['statusKey' => 'scheduled']);
});

it('keeps new churches count at zero when review modal is opened and cancelled', function (): void {
    $teacher = createTeacherForCounterRules();
    $training = createTrainingForCounterRules($teacher);
    $temp = createPendingTempForCounterRules();

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->call('openModal')
        ->call('closeModal');

    expect($training->fresh()->newChurches()->count())->toBe(0);
    $this->assertDatabaseCount('training_new_churches', 0);
});

it('increments new churches count only after confirm approve', function (): void {
    $teacher = createTeacherForCounterRules();
    $training = createTrainingForCounterRules($teacher);
    $temp = createPendingTempForCounterRules('Igreja Confirmacao Contador');

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ApproveChurchTempModal::class, ['training' => $training])
        ->call('openModal', $training->id, $temp->id)
        ->call('confirmApprove');

    expect($training->fresh()->newChurches()->count())->toBe(1);

    Livewire::actingAs($teacher)
        ->test(TrainingIndex::class, ['statusKey' => 'scheduled']);
});

it('does not increment new churches count when merging into an existing church', function (): void {
    $teacher = createTeacherForCounterRules();
    $training = createTrainingForCounterRules($teacher);
    $officialChurch = Church::factory()->create(['name' => 'Igreja Oficial Existente']);
    $temp = createPendingTempForCounterRules('Igreja Para Mesclar');

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->set("mergeTargets.$temp->id", $officialChurch->id)
        ->call('mergeTemp', $temp->id);

    expect($training->fresh()->newChurches()->count())->toBe(0);
    $this->assertDatabaseCount('training_new_churches', 0);
});
