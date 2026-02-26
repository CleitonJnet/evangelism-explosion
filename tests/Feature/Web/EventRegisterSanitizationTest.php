<?php

use App\Livewire\Web\Event\Register;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('sanitizes name, email and mobile before saving event registration', function (): void {
    $training = Training::factory()->create();

    Livewire::test(Register::class, ['event' => $training])
        ->set('ispastor', 'N')
        ->set('name', '  jOAO   dA  sILVA  e  souza  ')
        ->set('mobile', '(21) 9 9988-7766')
        ->set('email', '  JOAO.SILVA@EXAMPLE.COM  ')
        ->set('password', 'Secret@123')
        ->set('password_confirmation', 'Secret@123')
        ->set('birth_date', '1990-10-10')
        ->set('gender', 'M')
        ->call('registerEvent')
        ->assertHasNoErrors();

    $user = User::query()
        ->where('email', 'joao.silva@example.com')
        ->firstOrFail();

    expect($user->name)->toBe('Joao da Silva e Souza');
    expect($user->getRawOriginal('phone'))->toBe('21999887766');

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $user->id,
    ]);
});
