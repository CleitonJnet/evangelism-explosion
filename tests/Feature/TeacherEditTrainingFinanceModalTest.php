<?php

use App\Livewire\Pages\App\Teacher\Training\EditFinanceModal;
use App\Livewire\Pages\App\Teacher\Training\View as TrainingView;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\TrainingFinanceAudit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function createTeacherForFinanceModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForFinanceModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);
}

it('updates editable finance fields without changing base price', function (): void {
    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('price_church', '25,00')
        ->set('discount', '5,00')
        ->set('pix_key', 'church-pix-key@example.test')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertDispatched('training-finance-updated', trainingId: $training->id);

    $training->refresh();

    expect((float) $training->getRawOriginal('price'))->toBe(100.0)
        ->and((float) $training->getRawOriginal('price_church'))->toBe(25.0)
        ->and((float) $training->getRawOriginal('discount'))->toBe(5.0)
        ->and($training->pix_key)->toBe('church-pix-key@example.test');

    $audit = TrainingFinanceAudit::query()
        ->where('training_id', $training->id)
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->user_id)->toBe($teacher->id);
    expect((float) data_get($audit->changes, 'price_church.before'))->toBe(0.0)
        ->and((float) data_get($audit->changes, 'price_church.after'))->toBe(25.0)
        ->and((float) data_get($audit->changes, 'discount.before'))->toBe(0.0)
        ->and((float) data_get($audit->changes, 'discount.after'))->toBe(5.0)
        ->and(data_get($audit->changes, 'pix_key.before'))->toBeNull()
        ->and(data_get($audit->changes, 'pix_key.after'))->toBe('church-pix-key@example.test');
});

it('creates finance audit when qr code is updated', function (): void {
    Storage::fake('public');

    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('pix_key', 'pix-igreja@teste.org')
        ->set('pixQrCodeUpload', UploadedFile::fake()->image('novo-qr.webp'))
        ->call('save')
        ->assertSet('showModal', false);

    $training->refresh();

    expect($training->pix_qr_code)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $training->pix_qr_code);

    $audit = TrainingFinanceAudit::query()
        ->where('training_id', $training->id)
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->changes)->toHaveKey('pix_qr_code');
    expect($audit->changes['pix_qr_code']['before'])->toBeNull();
    expect((string) $audit->changes['pix_qr_code']['after'])->toContain('training-pix-qrcodes/'.$training->id.'/');
});

it('requires pix key when updating qr code in finance modal', function (): void {
    Storage::fake('public');

    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('pix_key', '')
        ->set('pixQrCodeUpload', UploadedFile::fake()->image('novo-qr.webp'))
        ->call('save')
        ->assertHasErrors(['pix_key' => 'required_with']);
});

it('clears current qr code when only pix key changes in finance modal', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('training-pix-qrcodes/999/original.webp', 'fake-content');

    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);
    $training->update([
        'pix_key' => 'pix-antiga@igreja.org',
        'pix_qr_code' => 'training-pix-qrcodes/999/original.webp',
    ]);

    Livewire::actingAs($teacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('pix_key', 'pix-nova@igreja.org')
        ->call('save')
        ->assertHasNoErrors();

    $training->refresh();

    expect($training->pix_key)->toBe('pix-nova@igreja.org');
    expect($training->pix_qr_code)->toBeNull();
});

it('forbids teacher that does not own the training', function (): void {
    $ownerTeacher = createTeacherForFinanceModal();
    $otherTeacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($ownerTeacher);

    Livewire::actingAs($otherTeacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});

it('renders updated training values in details view after finance changes', function (): void {
    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);
    $paidStudent = User::factory()->create();
    $training->students()->attach($paidStudent->id, ['kit' => 0, 'accredited' => 0, 'payment' => 1]);

    Livewire::test(TrainingView::class, ['training' => $training])
        ->assertSee('100')
        ->assertSee('0');

    $training->update([
        'price_church' => '12,00',
        'discount' => '2,00',
    ]);

    Livewire::test(TrainingView::class, ['training' => $training->fresh()])
        ->assertSee('12')
        ->assertSee('2');
});

it('uses default banner when no banner is uploaded or when stored path is invalid', function (): void {
    Storage::fake('public');

    $teacher = createTeacherForFinanceModal();
    $defaultBannerUrl = asset('images/banner-default.webp');

    $trainingWithoutBanner = createTrainingForFinanceModal($teacher);
    $trainingWithoutBanner->update(['banner' => null]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $trainingWithoutBanner->fresh()))
        ->assertOk()
        ->assertSee('Banner do treinamento')
        ->assertSee('Nenhum banner foi enviado para este evento')
        ->assertSee($defaultBannerUrl, false);

    $trainingWithInvalidBanner = createTrainingForFinanceModal($teacher);
    $trainingWithInvalidBanner->update(['banner' => 'training-banners/missing-banner.webp']);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $trainingWithInvalidBanner->fresh()))
        ->assertOk()
        ->assertSee('Banner do treinamento')
        ->assertSee('Nenhum banner foi enviado para este evento')
        ->assertSee($defaultBannerUrl, false);
});
