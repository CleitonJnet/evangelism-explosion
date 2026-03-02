<?php

use App\Livewire\Pages\App\Teacher\Church\EditModal;
use App\Livewire\Pages\App\Teacher\Church\View as ChurchDetailsView;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForChurchDetails(?int $churchId = null): User
{
    $teacher = User::factory()->create([
        'church_id' => $churchId,
    ]);

    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows complete church details to a teacher with access', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Memorial da Paz',
        'pastor' => 'Pr. Elias Costa',
        'email' => 'igreja@example.org',
        'contact' => 'Ana Lima',
        'contact_email' => 'ana@example.org',
        'street' => 'Rua Central',
        'number' => '150',
        'district' => 'Centro',
        'city' => 'Curitiba',
        'state' => 'PR',
    ]);

    $teacher = createTeacherForChurchDetails();

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => 1,
    ]);

    $member = User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Carlos Membro',
        'email' => 'carlos@example.org',
    ]);

    $course = Course::factory()->create([
        'name' => 'Curso Base de Evangelismo',
    ]);

    $trainingWithCourse = Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
        'status' => 1,
    ]);

    $trainingWithCourse->eventDates()->create([
        'date' => now()->addDays(10)->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.churches.show', $church));

    $response->assertOk();
    $response->assertSeeText('Igreja Memorial da Paz');
    $response->assertSeeText('Pr. Elias Costa');
    $response->assertSeeText('igreja@example.org');
    $response->assertSeeText('Ana Lima');
    $response->assertSeeText('ana@example.org');
    $response->assertSeeText('Rua Central');
    $response->assertSeeText('Carlos Membro');
    $response->assertSeeText('Curso Base de Evangelismo');
    $response->assertSee('data-row-link="'.route('app.teacher.trainings.show', $trainingWithCourse).'"', false);
    $response->assertSeeText($teacher->name);
    $response->assertSeeText('Membros totais');
    $response->assertSeeText('Treinamentos na igreja');

    expect($member->church_id)->toBe($church->id);
});

it('forbids teacher from viewing church details without relation', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();

    $response = $this->actingAs($teacher)->get(route('app.teacher.churches.show', $church));

    $response->assertForbidden();
});

it('enables training row redirect only for trainings owned by logged teacher', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();
    $otherTeacher = createTeacherForChurchDetails();

    $ownCourse = Course::factory()->create(['name' => 'Curso do Professor']);
    $otherCourse = Course::factory()->create(['name' => 'Curso de Outro Professor']);

    $ownTraining = Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $ownCourse->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    $otherTraining = Training::query()->create([
        'teacher_id' => $otherTeacher->id,
        'church_id' => $church->id,
        'course_id' => $otherCourse->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    $ownTraining->eventDates()->create([
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $otherTraining->eventDates()->create([
        'date' => now()->addDays(2)->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.churches.show', $church));

    $response->assertOk();
    $response->assertSeeText('Curso do Professor');
    $response->assertSeeText('Curso de Outro Professor');
    $response->assertSee('data-row-link="'.route('app.teacher.trainings.show', $ownTraining).'"', false);
    $response->assertDontSee('data-row-link="'.route('app.teacher.trainings.show', $otherTraining).'"', false);
});

it('updates church details from edit modal', function (): void {
    Storage::fake('public');

    $church = Church::factory()->create([
        'name' => 'Igreja Original',
        'pastor' => 'Pr. Original',
        'city' => 'Cidade Original',
        'state' => 'SP',
    ]);

    $teacher = createTeacherForChurchDetails();

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => 1,
    ]);

    $newLogo = UploadedFile::fake()->image('new-logo.png', 180, 180);

    Livewire::actingAs($teacher)
        ->test(EditModal::class, ['churchId' => $church->id])
        ->call('openModal', $church->id)
        ->set('logoUpload', $newLogo)
        ->set('church_name', 'Igreja Atualizada')
        ->set('pastor_name', 'Pr. Atualizado')
        ->set('phone_church', '11999998888')
        ->set('church_email', 'atualizada@example.org')
        ->set('church_contact', 'Contato Atualizado')
        ->set('church_contact_phone', '11988887777')
        ->set('church_contact_email', 'contato@example.org')
        ->set('church_notes', 'Observação atualizada')
        ->set('churchAddress.postal_code', '70000000')
        ->set('churchAddress.street', 'Rua Nova')
        ->set('churchAddress.number', '200')
        ->set('churchAddress.complement', 'Sala 1')
        ->set('churchAddress.district', 'Centro Novo')
        ->set('churchAddress.city', 'Brasilia')
        ->set('churchAddress.state', 'df')
        ->call('save')
        ->assertDispatched('teacher-church-updated', churchId: $church->id)
        ->assertSet('showModal', false);

    $updatedChurch = $church->fresh();

    expect($updatedChurch->name)->toBe('Igreja Atualizada');
    expect($updatedChurch->pastor)->toBe('Pr. Atualizado');
    expect($updatedChurch->city)->toBe('Brasilia');
    expect($updatedChurch->getRawOriginal('state'))->toBe('DF');
    expect($updatedChurch->logo)->not->toBeNull();

    Storage::disk('public')->assertExists((string) $updatedChurch->logo);
});

it('paginates related trainings in church details with livewire', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();

    foreach (range(1, 9) as $index) {
        $course = Course::factory()->create([
            'name' => sprintf('Curso relacionado %02d', $index),
        ]);

        $training = Training::query()->create([
            'teacher_id' => $teacher->id,
            'church_id' => $church->id,
            'course_id' => $course->id,
            'status' => 1,
        ]);

        $training->eventDates()->create([
            'date' => now()->addDays($index)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
        ]);
    }

    Livewire::actingAs($teacher)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertSee('Curso relacionado 09')
        ->assertSee('Curso relacionado 02')
        ->assertDontSee('Curso relacionado 01')
        ->call('setPage', 2, 'trainingsPage')
        ->assertSee('Curso relacionado 01')
        ->assertDontSee('Curso relacionado 09');
});

it('filters linked members by name or email in church details', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => 1,
    ]);

    User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Joao Batista',
        'email' => 'joao.batista@example.org',
    ]);

    User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Maria Fernandes',
        'email' => 'maria.fernandes@example.org',
    ]);

    Livewire::actingAs($teacher)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertSee('Joao Batista')
        ->assertSee('Maria Fernandes')
        ->set('memberSearch', 'joao')
        ->assertSee('Joao Batista')
        ->assertDontSee('Maria Fernandes')
        ->set('memberSearch', 'maria.fernandes@example.org')
        ->assertSee('Maria Fernandes')
        ->assertDontSee('Joao Batista');
});

it('counts only scheduled and completed trainings in church indicators', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();
    $otherTeacher = createTeacherForChurchDetails();

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Completed->value,
    ]);

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Planning->value,
    ]);

    Training::query()->create([
        'teacher_id' => $otherTeacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Canceled->value,
    ]);

    Training::query()->create([
        'teacher_id' => $otherTeacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    Livewire::actingAs($teacher)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertViewHas('churchTrainingsCount', 3)
        ->assertViewHas('teacherTrainingsCount', 2);
});

it('counts total linked members correctly in church indicators', function (): void {
    $church = Church::factory()->create();
    $teacher = createTeacherForChurchDetails();

    Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Membro Um',
        'email' => 'membro1@example.org',
    ]);

    User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Membro Dois',
        'email' => 'membro2@example.org',
    ]);

    Livewire::actingAs($teacher)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertViewHas('totalMembersCount', 2)
        ->set('memberSearch', 'membro1@example.org')
        ->assertViewHas('totalMembersCount', 2);
});
