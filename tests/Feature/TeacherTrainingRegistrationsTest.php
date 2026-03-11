<?php

use App\Livewire\Pages\App\Teacher\Training\CreateParticipantRegistrationModal;
use App\Livewire\Pages\App\Teacher\Training\Registrations;
use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        'is_pastor' => 1,
    ]);
    $studentB = User::factory()->create([
        'name' => 'Aluno Dois',
        'church_id' => $churchB->id,
        'is_pastor' => 0,
    ]);
    $studentPending = User::factory()->create([
        'name' => 'Aluno Pendente',
        'church_id' => null,
        'church_temp_id' => $churchTemp->id,
        'is_pastor' => 0,
    ]);
    $studentNoChurch = User::factory()->create([
        'name' => 'Aluno Sem Igreja',
        'church_id' => null,
        'church_temp_id' => null,
        'is_pastor' => 0,
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
    $response->assertSeeText('Novo inscrito');
});

it('updates participant statuses on the training pivot', function () {
    $teacher = createTeacher();
    $church = Church::factory()->create();
    Storage::fake('public');
    $ministry = Ministry::query()->create([
        'initials' => 'EE',
        'name' => 'Evangelismo',
    ]);
    $leaderCourse = Course::factory()->create([
        'execution' => 0,
        'ministry_id' => $ministry->id,
    ]);
    $implementationCourseOne = Course::factory()->create([
        'execution' => 1,
        'ministry_id' => $ministry->id,
    ]);
    $implementationCourseTwo = Course::factory()->create([
        'execution' => 1,
        'ministry_id' => $ministry->id,
    ]);
    $otherMinistry = Ministry::query()->create([
        'initials' => 'KID',
        'name' => 'Kids',
    ]);
    $unrelatedImplementationCourse = Course::factory()->create([
        'execution' => 1,
        'ministry_id' => $otherMinistry->id,
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $leaderCourse->id,
    ]);
    $student = User::factory()->create(['church_id' => $church->id]);
    Storage::disk('public')->put('training-receipts/123/comprovante.webp', 'fake-image-content');

    $training->students()->attach($student->id, [
        'payment_receipt' => 'training-receipts/123/comprovante.webp',
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
        'payment_receipt' => 'training-receipts/123/comprovante.webp',
        'payment' => 1,
        'accredited' => 1,
        'kit' => 1,
    ]);

    $facilitatorRole = Role::query()->firstWhere('name', 'Facilitator');

    expect($facilitatorRole)->not->toBeNull();
    expect($student->fresh()->roles()->whereKey($facilitatorRole?->id)->exists())->toBeTrue();
    $this->assertDatabaseHas('course_user', [
        'course_id' => $implementationCourseOne->id,
        'user_id' => $student->id,
        'status' => 1,
    ]);
    $this->assertDatabaseHas('course_user', [
        'course_id' => $implementationCourseTwo->id,
        'user_id' => $student->id,
        'status' => 1,
    ]);
    $this->assertDatabaseMissing('course_user', [
        'course_id' => $unrelatedImplementationCourse->id,
        'user_id' => $student->id,
    ]);
});

it('does not assign facilitator role when accrediting in non-leader course', function (): void {
    $teacher = createTeacher();
    $church = Church::factory()->create();
    $ministry = Ministry::query()->create([
        'initials' => 'EE',
        'name' => 'Evangelismo',
    ]);
    $regularCourse = Course::factory()->create([
        'execution' => 1,
        'ministry_id' => $ministry->id,
    ]);
    $implementationCourse = Course::factory()->create([
        'execution' => 1,
        'ministry_id' => $ministry->id,
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $regularCourse->id,
    ]);

    $student = User::factory()->create(['church_id' => $church->id]);

    $training->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training])
        ->call('toggleAccredited', $student->id, true);

    expect($student->fresh()->hasRole('Facilitator'))->toBeFalse();
    $this->assertDatabaseMissing('course_user', [
        'course_id' => $implementationCourse->id,
        'user_id' => $student->id,
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
        'payment_receipt' => 'training-receipts/999/arquivo-ausente.webp',
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

it('filters registrations by participant name, church region and email', function () {
    $teacher = createTeacher();
    $hostChurch = Church::factory()->create();
    $churchNorth = Church::factory()->create([
        'name' => 'Igreja Esperança',
        'city' => 'Manaus',
        'state' => 'AM',
    ]);
    $churchSouth = Church::factory()->create([
        'name' => 'Igreja Paz',
        'city' => 'Porto Alegre',
        'state' => 'RS',
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);

    $ana = User::factory()->create([
        'name' => 'Ana Clara',
        'email' => 'ana@example.com',
        'church_id' => $churchNorth->id,
        'city' => 'Manaus',
        'state' => 'AM',
        'district' => 'Centro',
    ]);
    $bruno = User::factory()->create([
        'name' => 'Bruno Lima',
        'email' => 'bruno@example.com',
        'church_id' => $churchSouth->id,
        'city' => 'Porto Alegre',
        'state' => 'RS',
        'district' => 'Menino Deus',
    ]);
    $carol = User::factory()->create([
        'name' => 'Carol Souza',
        'email' => 'carol@example.com',
        'church_id' => null,
        'church_temp_id' => null,
        'city' => 'Recife',
        'state' => 'PE',
        'district' => 'Boa Viagem',
    ]);

    $training->students()->attach($ana->id);
    $training->students()->attach($bruno->id);
    $training->students()->attach($carol->id);

    $component = Livewire::actingAs($teacher)
        ->test(Registrations::class, ['training' => $training])
        ->assertSet('totalRegistrations', 3)
        ->set('search', 'ana')
        ->assertSet('totalRegistrations', 1);

    expect(collect($component->get('churchGroups'))->pluck('church_name')->all())->toBe(['Igreja Esperança']);

    $component
        ->set('search', 'porto alegre')
        ->assertSet('totalRegistrations', 1);

    expect(collect($component->get('churchGroups'))->pluck('church_name')->all())->toBe(['Igreja Paz']);

    $component
        ->set('search', 'carol@example.com')
        ->assertSet('totalRegistrations', 1);

    expect(collect($component->get('churchGroups'))->pluck('church_name')->all())->toBe(['No church']);
});

it('routes teacher event registration modal by email to login mode when account already exists', function (): void {
    $teacher = createTeacher();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);
    $user = User::factory()->create(['email' => 'joao@example.com']);

    Livewire::actingAs($teacher)
        ->test(CreateParticipantRegistrationModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('email', 'JOAO@EXAMPLE.COM')
        ->call('identifyByEmail')
        ->assertSet('mode', 'login')
        ->assertSet('email', 'joao@example.com')
        ->assertSet('name', $user->name);
});

it('allows a teacher to register a student manually for the event', function (): void {
    $teacher = createTeacher();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(CreateParticipantRegistrationModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('mode', 'register')
        ->set('ispastor', '0')
        ->set('name', 'Aluno Avulso')
        ->set('mobile', '11999999999')
        ->set('email', 'aluno.avulso@example.com')
        ->set('password', 'Secret@123')
        ->set('password_confirmation', 'Secret@123')
        ->set('birth_date', '2000-01-15')
        ->set('gender', '1')
        ->call('registerEvent')
        ->assertDispatched('training-participant-registration-created', trainingId: $training->id)
        ->assertSet('showModal', false);

    $participant = User::query()->where('email', 'aluno.avulso@example.com')->first();

    expect($participant)->not->toBeNull();
    expect($participant?->name)->toBe('Aluno Avulso');
    expect($participant?->is_pastor)->toBeFalse();
    expect($participant?->gender)->toBe(1);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $participant?->id,
        'payment' => 0,
        'kit' => 0,
        'accredited' => 0,
    ]);
});

it('allows a teacher to enroll an existing student through login mode', function (): void {
    $teacher = createTeacher();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'password' => Hash::make('Secret@123'),
    ]);

    Livewire::actingAs($teacher)
        ->test(CreateParticipantRegistrationModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('mode', 'login')
        ->set('email', 'existing@example.com')
        ->set('password', 'Secret@123')
        ->call('loginEvent')
        ->assertHasNoErrors()
        ->assertDispatched('training-participant-registration-created', trainingId: $training->id)
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $user->id,
    ]);
});
