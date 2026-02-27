<?php

use App\Livewire\Pages\App\Teacher\Training\EditEventBannerModal;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function createTeacherForEventBannerModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForEventBannerModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('updates event banner from schedule modal', function (): void {
    Storage::fake('public');

    $teacher = createTeacherForEventBannerModal();
    $training = createTrainingForEventBannerModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditEventBannerModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('bannerUpload', UploadedFile::fake()->image('banner-evento.webp')->size(10240))
        ->call('save')
        ->assertSet('showModal', false)
        ->assertDispatched('training-banner-updated', trainingId: $training->id);

    $training->refresh();

    expect($training->banner)->not->toBeNull()
        ->and((string) $training->banner)->toContain('training-banners/'.$training->id.'/');

    Storage::disk('public')->assertExists((string) $training->banner);
});

it('validates max size of ten megabytes for event banner upload', function (): void {
    Storage::fake('public');

    $teacher = createTeacherForEventBannerModal();
    $training = createTrainingForEventBannerModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditEventBannerModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('bannerUpload', UploadedFile::fake()->image('banner-evento.webp')->size(10241))
        ->call('save')
        ->assertHasErrors(['bannerUpload' => 'max']);
});

it('forbids teacher that does not own the training when editing event banner', function (): void {
    $ownerTeacher = createTeacherForEventBannerModal();
    $otherTeacher = createTeacherForEventBannerModal();
    $training = createTrainingForEventBannerModal($ownerTeacher);

    Livewire::actingAs($otherTeacher)
        ->test(EditEventBannerModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});

it('shows the banner upload button in teacher schedule toolbar', function (): void {
    $teacher = createTeacherForEventBannerModal();
    $training = createTrainingForEventBannerModal($teacher);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertSee('Banner do Evento');
});
