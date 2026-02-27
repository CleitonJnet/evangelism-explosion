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
        ->set('ispastor', '0')
        ->set('name', '  jOAO   dA  sILVA  e  souza  ')
        ->set('mobile', '(11) 9998-8776')
        ->set('email', '  JOAO.SILVA@EXAMPLE.COM  ')
        ->set('password', 'Secret@123')
        ->set('password_confirmation', 'Secret@123')
        ->set('birth_date', '1990-10-10')
        ->set('gender', '1')
        ->call('registerEvent')
        ->assertHasNoErrors();

    $user = User::query()
        ->where('email', 'joao.silva@example.com')
        ->firstOrFail();

    expect($user->name)->toBe('Joao da Silva e Souza');
    expect((string) $user->getRawOriginal('phone'))->toBe('1199988776');
    expect((int) $user->getRawOriginal('is_pastor'))->toBe(0);
    expect((int) $user->getRawOriginal('gender'))->toBe(User::GENDER_MALE);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $user->id,
    ]);
});
