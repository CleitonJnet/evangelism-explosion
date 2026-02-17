<?php

use App\Livewire\Pages\App\Teacher\Training\ApproveChurchTempModal;
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

function createTeacherForApproveChurchTempModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForApproveChurchTempModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('creates official church and resolves temp only after confirm approve', function (): void {
    $teacher = createTeacherForApproveChurchTempModal();
    $training = createTrainingForApproveChurchTempModal($teacher);

    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Boas Novas',
        'pastor' => 'Pr. Temp Pastor',
        'city' => 'Fortaleza',
        'state' => 'CE',
        'street' => 'Rua 1',
        'number' => '100',
        'district' => 'Centro',
        'postal_code' => '60000000',
        'status' => 'pending',
        'normalized_name' => 'igreja boas novas',
    ]);

    $student = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(ApproveChurchTempModal::class, ['training' => $training])
        ->call('openModal', $training->id, $temp->id)
        ->assertSet('trainingId', $training->id)
        ->assertSet('churchTempId', $temp->id)
        ->assertSet('church_name', 'Igreja Boas Novas')
        ->set('church_name', 'Igreja Boas Novas Oficial')
        ->call('confirmApprove')
        ->assertDispatched('church-temp-approved')
        ->assertDispatched('church-temp-reviewed');

    $student->refresh();
    $temp->refresh();

    $official = Church::query()->where('name', 'Igreja Boas Novas Oficial')->first();
    expect($official)->not->toBeNull();

    $officialId = $official->id;

    expect($student->church_id)->toBe($officialId);
    expect($student->church_temp_id)->toBeNull();
    expect($temp->status)->toBe('approved_new');

    $this->assertDatabaseHas('training_new_churches', [
        'training_id' => $training->id,
        'church_id' => $officialId,
        'source_church_temp_id' => $temp->id,
        'created_by' => $teacher->id,
    ]);
});

it('refreshes triage merge options after approval so new church can be used immediately', function (): void {
    $teacher = createTeacherForApproveChurchTempModal();
    $training = createTrainingForApproveChurchTempModal($teacher);

    $tempToApprove = ChurchTemp::query()->create([
        'name' => 'Igreja Alfa Nova',
        'pastor' => 'Pr. Alfa',
        'city' => 'Goiania',
        'state' => 'GO',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'postal_code' => '74000000',
        'status' => 'pending',
        'normalized_name' => 'igreja alfa nova',
    ]);

    $anotherTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Beta',
        'city' => 'Goiania',
        'state' => 'GO',
        'status' => 'pending',
        'normalized_name' => 'igreja beta',
    ]);

    $studentA = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $tempToApprove->id,
    ]);
    $studentB = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $anotherTemp->id,
    ]);
    $training->students()->attach($studentA->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $training->students()->attach($studentB->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    $reviewModal = Livewire::actingAs($teacher)
        ->test(ChurchTempReviewModal::class, ['training' => $training]);

    expect(collect($reviewModal->get('churchOptions'))
        ->contains(fn (array $option): bool => str_contains($option['label'], 'Igreja Alfa Nova')))->toBeFalse();

    Livewire::actingAs($teacher)
        ->test(ApproveChurchTempModal::class, ['training' => $training])
        ->call('openModal', $training->id, $tempToApprove->id)
        ->call('confirmApprove')
        ->assertDispatched('church-temp-approved');

    $reviewModal->call('handleChurchTempApproved');

    expect(collect($reviewModal->get('churchOptions'))
        ->contains(fn (array $option): bool => str_contains($option['label'], 'Igreja Alfa Nova')))->toBeTrue();

    expect(collect($reviewModal->get('pendingTemps'))
        ->contains(fn (array $pendingTemp): bool => (int) $pendingTemp['id'] === $anotherTemp->id))->toBeTrue();
});
