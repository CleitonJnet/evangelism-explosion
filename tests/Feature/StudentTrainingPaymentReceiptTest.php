<?php

use App\Livewire\Pages\App\Student\Training\Show as StudentTrainingShow;
use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows student to upload payment receipt image', function () {
    Storage::fake('public');

    $church = Church::factory()->create();
    $student = User::factory()->create(['church_id' => $church->id]);
    $training = Training::factory()->create([
        'church_id' => $church->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);

    $training->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => null,
    ]);

    Livewire::actingAs($student)
        ->test(StudentTrainingShow::class, ['training' => $training])
        ->set('paymentReceipt', UploadedFile::fake()->image('receipt.png'))
        ->call('uploadPaymentReceipt')
        ->assertHasNoErrors()
        ->assertSet('paymentReceiptIsImage', true)
        ->assertSet('paymentReceiptIsPdf', false);

    $enrollment = $training->students()
        ->where('users.id', $student->id)
        ->firstOrFail();

    $receiptPath = $enrollment->pivot?->payment_receipt;

    expect($receiptPath)->not->toBeNull();
    expect(str_ends_with((string) $receiptPath, '.png'))->toBeTrue();
    Storage::disk('public')->assertExists((string) $receiptPath);
});

it('allows student to upload payment receipt pdf', function () {
    Storage::fake('public');

    $church = Church::factory()->create();
    $student = User::factory()->create(['church_id' => $church->id]);
    $training = Training::factory()->create([
        'church_id' => $church->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);

    $training->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => null,
    ]);

    Livewire::actingAs($student)
        ->test(StudentTrainingShow::class, ['training' => $training])
        ->set('paymentReceipt', UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf'))
        ->call('uploadPaymentReceipt')
        ->assertHasNoErrors()
        ->assertSet('paymentReceiptIsImage', false)
        ->assertSet('paymentReceiptIsPdf', true);

    $enrollment = $training->students()
        ->where('users.id', $student->id)
        ->firstOrFail();

    $receiptPath = $enrollment->pivot?->payment_receipt;

    expect($receiptPath)->not->toBeNull();
    expect(str_ends_with((string) $receiptPath, '.pdf'))->toBeTrue();
    Storage::disk('public')->assertExists((string) $receiptPath);
});
