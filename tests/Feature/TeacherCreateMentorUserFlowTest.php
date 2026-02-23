<?php

use App\Livewire\Pages\App\Teacher\Training\CreateMentorUserModal;
use App\Livewire\Pages\App\Teacher\Training\ManageMentorsModal;
use App\Livewire\Shared\CreateChurchModal;
use App\Mail\MentorEventConfirmationMail;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForMentorFlow(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForMentorFlow(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('creates mentor user with church and attaches mentor role and pivot', function (): void {
    Mail::fake();

    $teacher = createTeacherForMentorFlow();
    $training = createTrainingForMentorFlow($teacher);
    $church = Church::factory()->create(['name' => 'Igreja Oficial Mentor']);

    Livewire::actingAs($teacher)
        ->test(CreateMentorUserModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('name', 'Mentor Novo')
        ->set('email', 'mentor.novo@example.test')
        ->set('phone', '11999999999')
        ->set('selectedChurchId', $church->id)
        ->call('save')
        ->assertDispatched('mentor-user-created', trainingId: $training->id);

    $mentorUser = User::query()->where('email', 'mentor.novo@example.test')->firstOrFail();
    $mentorRole = Role::query()->firstWhere('name', 'Mentor');

    expect($mentorUser->church_id)->toBe($church->id);
    expect($mentorUser->must_change_password)->toBeTrue();
    expect(Hash::check('Mentor_01', (string) $mentorUser->password))->toBeTrue();
    expect($mentorRole)->not->toBeNull();
    expect($mentorUser->roles()->whereKey($mentorRole->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('mentors', [
        'training_id' => $training->id,
        'user_id' => $mentorUser->id,
        'created_by' => $teacher->id,
    ]);

    Mail::assertSent(MentorEventConfirmationMail::class, function (MentorEventConfirmationMail $mail) use ($mentorUser): bool {
        return $mail->hasTo($mentorUser->email)
            && $mail->mentorUser->is($mentorUser)
            && str_contains($mail->passwordResetUrl, 'reset-password')
            && str_contains($mail->passwordResetUrl, 'email='.urlencode($mentorUser->email));
    });
});

it('creates official church and returns selection to create mentor user modal', function (): void {
    $teacher = createTeacherForMentorFlow();
    $training = createTrainingForMentorFlow($teacher);

    Livewire::actingAs($teacher)
        ->test(CreateChurchModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('churchName', 'Igreja Criada no Fluxo Mentor')
        ->set('pastorName', 'Pr. Lucas Souza')
        ->set('postalCode', '01001000')
        ->set('street', 'Rua Nova')
        ->set('number', '123')
        ->set('district', 'Centro')
        ->set('city', 'Sao Paulo')
        ->set('state', 'SP')
        ->set('phone', '1133333333')
        ->set('email', 'igreja.mentor@example.test')
        ->call('save')
        ->assertDispatched('mentor-church-created', trainingId: $training->id);

    $createdChurch = Church::query()
        ->where('name', 'Igreja Criada no Fluxo Mentor')
        ->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(CreateMentorUserModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->call('handleChurchCreated', $training->id, $createdChurch->id, $createdChurch->name)
        ->assertSet('selectedChurchId', $createdChurch->id)
        ->assertSet('churchSearch', $createdChurch->name);
});

it('still removes mentor through manage modal after mentor user is created', function (): void {
    $teacher = createTeacherForMentorFlow();
    $training = createTrainingForMentorFlow($teacher);
    $church = Church::factory()->create();

    Livewire::actingAs($teacher)
        ->test(CreateMentorUserModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('name', 'Mentor Remove')
        ->set('email', 'mentor.remove@example.test')
        ->set('selectedChurchId', $church->id)
        ->call('save');

    $mentorUser = User::query()->where('email', 'mentor.remove@example.test')->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageMentorsModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->call('removeMentor', $mentorUser->id);

    $this->assertDatabaseMissing('mentors', [
        'training_id' => $training->id,
        'user_id' => $mentorUser->id,
    ]);
});

it('blocks non-teachers from create mentor user and create church flow', function (): void {
    $teacher = createTeacherForMentorFlow();
    $training = createTrainingForMentorFlow($teacher);
    $nonTeacher = User::factory()->create();

    Livewire::actingAs($nonTeacher)
        ->test(CreateMentorUserModal::class, ['trainingId' => $training->id])
        ->assertForbidden();

    Livewire::actingAs($nonTeacher)
        ->test(CreateChurchModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});
