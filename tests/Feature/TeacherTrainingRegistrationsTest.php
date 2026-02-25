<?php

use App\Livewire\Pages\App\Teacher\Training\Registrations;
use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacher(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('renders the registrations page grouped by church', function () {
    $teacher = createTeacher();
    $churchA = Church::factory()->create(['name' => 'Igreja Alfa']);
    $churchB = Church::factory()->create(['name' => 'Igreja Beta']);
    $churchTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Pendente',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente',
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $churchA->id,
    ]);

    $studentA = User::factory()->create([
        'name' => 'Aluno Um',
        'church_id' => $churchA->id,
        'pastor' => 'Pr. Aluno Um',
    ]);
    $studentB = User::factory()->create([
        'name' => 'Aluno Dois',
        'church_id' => $churchB->id,
        'pastor' => null,
    ]);
    $studentPending = User::factory()->create([
        'name' => 'Aluno Pendente',
        'church_id' => null,
        'church_temp_id' => $churchTemp->id,
        'pastor' => null,
    ]);
    $studentNoChurch = User::factory()->create([
        'name' => 'Aluno Sem Igreja',
        'church_id' => null,
        'church_temp_id' => null,
        'pastor' => null,
    ]);

    $training->students()->attach($studentA->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $training->students()->attach($studentB->id, ['accredited' => 1, 'kit' => 1, 'payment' => 1]);
    $training->students()->attach($studentPending->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $training->students()->attach($studentNoChurch->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.registrations', $training));

    $response->assertOk();
    $response->assertSeeText('Gerenciamento de inscrições');
    $response->assertSeeText('Igreja Alfa');
    $response->assertSeeText('Igreja Alfa');
    $response->assertSeeText('Igreja Beta');
    $response->assertSeeText('(PENDING) Igreja Pendente');
    $response->assertSeeText('No church');
    $response->assertSeeText('Validate Churches');
    $response->assertSeeText('Pastor');
    $response->assertSeeText('Aluno Um');
    $response->assertSeeText('Aluno Dois');
    $response->assertSeeText('Aluno Pendente');
    $response->assertSeeText('Aluno Sem Igreja');
});

it('updates participant statuses on the training pivot', function () {
    $teacher = createTeacher();
    $church = Church::factory()->create();
    Storage::fake('public');

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $student = User::factory()->create(['church_id' => $church->id]);
    Storage::disk('public')->put('training-receipts/123/comprovante.png', 'fake-image-content');

    $training->students()->attach($student->id, [
        'payment_receipt' => 'training-receipts/123/comprovante.png',
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training])
        ->call('togglePayment', $student->id, true)
        ->call('toggleAccredited', $student->id, true)
        ->call('toggleKit', $student->id, true);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $student->id,
        'payment_receipt' => 'training-receipts/123/comprovante.png',
        'payment' => 1,
        'accredited' => 1,
        'kit' => 1,
    ]);
});

it('does not confirm payment when receipt file is missing', function () {
    $teacher = createTeacher();
    $church = Church::factory()->create();
    Storage::fake('public');

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $student = User::factory()->create(['church_id' => $church->id]);

    $training->students()->attach($student->id, [
        'payment_receipt' => 'training-receipts/999/arquivo-ausente.png',
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training])
        ->call('togglePayment', $student->id, true)
        ->assertHasErrors(['paymentConfirmation']);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $student->id,
        'payment' => 0,
    ]);
});

it('removes a participant from the training registrations', function () {
    $teacher = createTeacher();
    $church = Church::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $student = User::factory()->create(['church_id' => $church->id]);

    $training->students()->attach($student->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training])
        ->call('removeRegistration', $student->id);

    $this->assertDatabaseMissing('training_user', [
        'training_id' => $training->id,
        'user_id' => $student->id,
    ]);
});

it('marks registration groups with pending or missing church as issues', function () {
    $teacher = createTeacher();
    $hostChurch = Church::factory()->create();
    $officialChurch = Church::factory()->create(['name' => 'Igreja Oficial']);
    $pendingChurchTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Pendente',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente',
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);

    $officialStudent = User::factory()->create([
        'church_id' => $officialChurch->id,
        'church_temp_id' => null,
    ]);
    $pendingStudent = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $pendingChurchTemp->id,
    ]);
    $noChurchStudent = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $training->students()->attach($officialStudent->id);
    $training->students()->attach($pendingStudent->id);
    $training->students()->attach($noChurchStudent->id);

    $component = Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training]);

    $churchGroups = collect($component->get('churchGroups'));

    expect($churchGroups->firstWhere('church_name', 'Igreja Oficial')['has_church_issue'])->toBeFalse();
    expect($churchGroups->firstWhere('church_name', '(PENDING) Igreja Pendente')['has_church_issue'])->toBeTrue();
    expect($churchGroups->firstWhere('church_name', 'No church')['has_church_issue'])->toBeTrue();
});

it('sorts pending church issues before validated registrations', function () {
    $teacher = createTeacher();
    $hostChurch = Church::factory()->create();
    $officialChurch = Church::factory()->create(['name' => 'Igreja Oficial']);
    $pendingChurchTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Pendente',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente',
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);

    $validatedStudent = User::factory()->create(['church_id' => $officialChurch->id, 'church_temp_id' => null]);
    $pendingStudent = User::factory()->create(['church_id' => null, 'church_temp_id' => $pendingChurchTemp->id]);
    $noChurchStudent = User::factory()->create(['church_id' => null, 'church_temp_id' => null]);

    $training->students()->attach($validatedStudent->id);
    $training->students()->attach($pendingStudent->id);
    $training->students()->attach($noChurchStudent->id);

    $component = Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training]);

    $groupNames = collect($component->get('churchGroups'))
        ->pluck('church_name')
        ->values()
        ->all();

    expect($groupNames)->toBe([
        '(PENDING) Igreja Pendente',
        'No church',
        'Igreja Oficial',
    ]);
});
