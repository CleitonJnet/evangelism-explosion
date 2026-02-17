<?php

use App\Livewire\Pages\App\Teacher\Training\ChurchTempReviewModal;
use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForChurchReview(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForChurchReview(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('opens approval review flow without creating official church immediately', function () {
    $teacher = createTeacherForChurchReview();
    $training = createTrainingForChurchReview($teacher);
    $churchesBefore = Church::query()->count();

    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja da Esperanca',
        'city' => 'Goiania',
        'state' => 'GO',
        'status' => 'pending',
        'normalized_name' => 'igreja da esperanca',
    ]);

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->call('openApproveReview', $temp->id)
        ->assertDispatched('open-approve-church-temp-modal', trainingId: $training->id, churchTempId: $temp->id);

    $student->refresh();
    $temp->refresh();

    expect(Church::query()->count())->toBe($churchesBefore);
    expect($student->church_id)->toBeNull();
    expect($student->church_temp_id)->toBe($temp->id);
    expect($temp->status)->toBe('pending');

    $this->assertDatabaseMissing('training_new_churches', [
        'training_id' => $training->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $teacher->id,
    ]);
});

it('merges pending church temp into selected official church from review modal', function () {
    $teacher = createTeacherForChurchReview();
    $training = createTrainingForChurchReview($teacher);
    $officialChurch = Church::factory()->create();

    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Fonte de Vida',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja fonte de vida',
    ]);

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->set("mergeTargets.$temp->id", $officialChurch->id)
        ->call('mergeTemp', $temp->id)
        ->assertDispatched('church-temp-reviewed');

    $student->refresh();
    $temp->refresh();

    expect($student->church_id)->toBe($officialChurch->id);
    expect($student->church_temp_id)->toBeNull();
    expect($temp->status)->toBe('merged');

    $this->assertDatabaseMissing('training_new_churches', [
        'training_id' => $training->id,
        'church_id' => $officialChurch->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $teacher->id,
    ]);
});

it('shows possible match and quick merge button when normalized name matches', function (): void {
    $teacher = createTeacherForChurchReview();
    $training = createTrainingForChurchReview($teacher);
    $officialChurch = Church::factory()->create([
        'name' => 'Igreja Caminho da Paz',
        'city' => 'Recife',
        'state' => 'PE',
    ]);

    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja  Caminho da Paz',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja caminho da paz',
    ]);

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->call('openModal')
        ->assertSee('Possible match')
        ->assertSee('Merge into')
        ->assertSee($officialChurch->name)
        ->call('quickMergeTemp', $temp->id)
        ->assertDispatched('church-temp-reviewed');

    $student->refresh();
    $temp->refresh();

    expect($student->church_id)->toBe($officialChurch->id);
    expect($student->church_temp_id)->toBeNull();
    expect($temp->status)->toBe('merged');

    $this->assertDatabaseMissing('training_new_churches', [
        'training_id' => $training->id,
        'church_id' => $officialChurch->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $teacher->id,
    ]);
});

it('filters pending temps by search fields in review modal', function (): void {
    $teacher = createTeacherForChurchReview();
    $training = createTrainingForChurchReview($teacher);

    $matchingTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Monte Sião',
        'pastor' => 'Pr. Elias',
        'city' => 'Goiania',
        'state' => 'GO',
        'street' => 'Rua das Flores',
        'district' => 'Setor Sul',
        'status' => 'pending',
        'normalized_name' => 'igreja monte siao',
    ]);

    $otherTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Luz da Vida',
        'pastor' => 'Pr. Carlos',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja luz da vida',
    ]);

    $studentA = User::factory()->create(['church_id' => null, 'church_temp_id' => $matchingTemp->id]);
    $studentB = User::factory()->create(['church_id' => null, 'church_temp_id' => $otherTemp->id]);
    $training->students()->attach($studentA->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $training->students()->attach($studentB->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->set('pendingSearch', 'flores')
        ->assertCount('pendingTemps', 1)
        ->assertSet('pendingTemps.0.id', $matchingTemp->id)
        ->set('pendingSearch', 'pr. carlos')
        ->assertCount('pendingTemps', 1)
        ->assertSet('pendingTemps.0.id', $otherTemp->id);
});

it('filters merge targets using search and allows click to select result', function (): void {
    $teacher = createTeacherForChurchReview();
    $training = createTrainingForChurchReview($teacher);
    $firstChurch = Church::factory()->create(['name' => 'Igreja Palavra Viva', 'city' => 'Santos', 'state' => 'SP']);
    Church::factory()->create(['name' => 'Igreja Outro Nome', 'city' => 'Santos', 'state' => 'SP']);

    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Temp',
        'city' => 'Santos',
        'state' => 'SP',
        'status' => 'pending',
        'normalized_name' => 'igreja temp',
    ]);

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training])
        ->set("mergeChurchSearch.$temp->id", 'palavra')
        ->assertSee('Igreja Palavra Viva')
        ->assertDontSee('Igreja Outro Nome')
        ->call('selectMergeTarget', $temp->id, $firstChurch->id)
        ->assertSet("mergeTargets.$temp->id", $firstChurch->id);
});
