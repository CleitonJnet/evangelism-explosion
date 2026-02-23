<?php

use App\Livewire\Pages\App\Teacher\Training\Create as TeacherTrainingCreate;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('stores church pix key and qr code when creating a training', function () {
    Storage::fake('public');

    $teacher = User::factory()->create();
    $church = Church::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    Livewire::actingAs($teacher)
        ->test(TeacherTrainingCreate::class)
        ->set('course_id', $course->id)
        ->set('church_id', $church->id)
        ->set('eventDates.0.date', now()->addDays(10)->format('Y-m-d'))
        ->set('eventDates.0.start_time', '08:00')
        ->set('eventDates.0.end_time', '12:00')
        ->set('price', '100,00')
        ->set('price_church', '20,00')
        ->set('discount', '10,00')
        ->set('pix_key', '11.222.333/0001-44')
        ->set('pixQrCodeUpload', UploadedFile::fake()->image('pix-qr.png'))
        ->call('submit')
        ->assertHasNoErrors();

    $training = Training::query()->latest('id')->firstOrFail();

    expect($training->pix_key)->toBe('11.222.333/0001-44');
    expect($training->pix_qr_code)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $training->pix_qr_code);
});

it('blocks malicious teacher_id payload updates in teacher flow', function () {
    $teacher = User::factory()->create();
    $otherUser = User::factory()->create();

    expect(function () use ($teacher, $otherUser): void {
        Livewire::actingAs($teacher)
            ->test(TeacherTrainingCreate::class)
            ->set('teacher_id', $otherUser->id);
    })->toThrow(CannotUpdateLockedPropertyException::class);
});

it('returns default pix data when training does not have church pix settings', function () {
    $training = Training::factory()->create([
        'pix_key' => null,
        'pix_qr_code' => null,
    ]);

    expect($training->pixKeyForPayment())->toBe('eebrasil@eebrasil.org.br');
    expect($training->pixQrCodeUrlForPayment())->toContain('/images/qrcode-pix-ee.webp');
});

it('returns church pix data when training has custom settings', function () {
    Storage::fake('public');
    Storage::disk('public')->put('training-pix-qrcodes/123/custom-qr.png', 'fake-content');

    $training = Training::factory()->create([
        'pix_key' => 'chave-pix-igreja-sede',
        'pix_qr_code' => 'training-pix-qrcodes/123/custom-qr.png',
    ]);

    expect($training->pixKeyForPayment())->toBe('chave-pix-igreja-sede');
    expect($training->pixQrCodeUrlForPayment())->toBe(Storage::disk('public')->url('training-pix-qrcodes/123/custom-qr.png'));
});
