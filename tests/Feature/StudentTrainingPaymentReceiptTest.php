<?php

use App\Livewire\Pages\App\Student\Training\Show as StudentTrainingShow;
use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createStudentWithRoleForTrainingReceipt(): User
{
    $student = User::factory()->create();
    $studentRole = Role::query()->firstOrCreate(['name' => 'Student']);
    $student->roles()->syncWithoutDetaching([$studentRole->id]);

    return $student;
}

it('allows student to upload payment receipt image', function () {
    Storage::fake('public');

    $church = Church::factory()->create();
    $student = createStudentWithRoleForTrainingReceipt();
    $student->update(['church_id' => $church->id]);
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
        ->set('paymentReceipt', UploadedFile::fake()->image('receipt.webp'))
        ->call('uploadPaymentReceipt')
        ->assertHasNoErrors()
        ->assertSet('paymentReceiptIsImage', true)
        ->assertSet('paymentReceiptIsPdf', false);

    $enrollment = $training->students()
        ->where('users.id', $student->id)
        ->firstOrFail();

    $receiptPath = $enrollment->pivot?->payment_receipt;

    expect($receiptPath)->not->toBeNull();
    expect(str_ends_with((string) $receiptPath, '.webp'))->toBeTrue();
    Storage::disk('public')->assertExists((string) $receiptPath);
});

it('allows student to upload payment receipt pdf', function () {
    Storage::fake('public');

    $church = Church::factory()->create();
    $student = createStudentWithRoleForTrainingReceipt();
    $student->update(['church_id' => $church->id]);
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

it('shows payment status on student training index cards', function () {
    $church = Church::factory()->create();
    $student = createStudentWithRoleForTrainingReceipt();
    $student->update(['church_id' => $church->id]);

    $confirmedTraining = Training::factory()->create([
        'church_id' => $church->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);
    $analysisTraining = Training::factory()->create([
        'church_id' => $church->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);
    $pendingReceiptTraining = Training::factory()->create([
        'church_id' => $church->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);

    $confirmedTraining->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 1,
        'payment_receipt' => 'training-receipts/confirmed.webp',
    ]);
    $analysisTraining->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => 'training-receipts/pending.webp',
    ]);
    $pendingReceiptTraining->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => null,
    ]);

    $response = $this
        ->actingAs($student)
        ->get(route('app.student.training.index'));

    $response->assertOk();
    $response->assertSee('Pagamento confirmado');
    $response->assertSee('Pagamento em analise');
    $response->assertSee('Aguardando comprovante');
});
