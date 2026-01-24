<?php

use App\Livewire\Pages\App\Student\Training\Show;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('student can upload payment receipt', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $training = Training::factory()->create(['price' => 100, 'price_church' => 0, 'discount' => 0]);

    $training->students()->attach($user->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Show::class, ['training' => $training])
        ->set('paymentReceipt', UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf'))
        ->call('uploadPaymentReceipt')
        ->assertHasNoErrors();

    $receiptPath = $training->students()->whereKey($user->id)->first()?->pivot?->payment_receipt;

    expect($receiptPath)->not->toBeNull();

    Storage::disk('public')->assertExists($receiptPath);
});

test('student sees payment confirmed message', function () {
    $user = User::factory()->create();
    $training = Training::factory()->create(['price' => 100, 'price_church' => 0, 'discount' => 0]);

    $training->students()->attach($user->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 1,
        'payment_receipt' => 'training-receipts/confirmed.pdf',
    ]);

    $this->actingAs($user);

    Livewire::test(Show::class, ['training' => $training])
        ->assertSee('Pagamento confirmado');
});
