<?php

use App\Livewire\Web\Event\Access;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the unified access component on register and login event pages', function (): void {
    $training = Training::factory()->create([
        'course_id' => Course::factory()->create()->id,
        'church_id' => Church::factory()->create()->id,
    ]);

    $this->get(route('web.event.register', $training->id))
        ->assertOk()
        ->assertSee('Acessar ou fazer inscricao no evento');

    $this->get(route('web.event.login', $training->id))
        ->assertOk()
        ->assertSee('Acessar ou fazer inscricao no evento');
});

it('routes email to login mode when account already exists', function (): void {
    $training = Training::factory()->create();
    $user = User::factory()->create(['email' => 'joao@example.com']);

    Livewire::test(Access::class, ['event' => $training])
        ->set('email', 'JOAO@EXAMPLE.COM')
        ->call('identifyByEmail')
        ->assertSet('mode', 'login')
        ->assertSet('email', 'joao@example.com')
        ->assertSet('name', $user->name);
});

it('registers a new user from the unified access flow and enrolls in training', function (): void {
    $training = Training::factory()->create();

    Livewire::test(Access::class, ['event' => $training])
        ->set('mode', 'register')
        ->set('ispastor', '0')
        ->set('name', '  jOAO   dA  sILVA  e  souza  ')
        ->set('mobile', '(11) 9998-8776')
        ->set('email', '  JOAO.SILVA@EXAMPLE.COM  ')
        ->set('password', 'Secret@123')
        ->set('password_confirmation', 'Secret@123')
        ->set('birth_date', '1990-10-10')
        ->set('gender', '1')
        ->call('registerEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id]));

    $user = User::query()
        ->where('email', 'joao.silva@example.com')
        ->firstOrFail();

    expect($user->name)->toBe('Joao da Silva e Souza');
    expect((string) $user->getRawOriginal('phone'))->toBe('1199988776');

    $studentRole = Role::query()->firstWhere('name', 'Student');
    expect($studentRole)->not->toBeNull();
    expect($user->roles()->whereKey($studentRole?->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $user->id,
    ]);
});

it('logs in an existing user from unified access flow and enrolls in training', function (): void {
    $training = Training::factory()->create();
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'password' => Hash::make('Secret@123'),
    ]);

    Livewire::test(Access::class, ['event' => $training])
        ->set('mode', 'login')
        ->set('email', 'existing@example.com')
        ->set('password', 'Secret@123')
        ->call('loginEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id]));

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseHas('role_user', [
        'user_id' => $user->id,
        'role_id' => Role::query()->firstWhere('name', 'Student')?->id,
    ]);
});
