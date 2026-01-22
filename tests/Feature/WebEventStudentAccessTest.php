<?php

use App\Livewire\Web\Event\Login;
use App\Livewire\Web\Event\Register;
use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

test('event registration validates required fields', function () {
    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    Livewire::test(Register::class, ['event' => $training])
        ->call('registerEvent')
        ->assertHasErrors([
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'password' => 'required',
            'gender' => 'required',
        ]);
});

test('student can register and is redirected to the training page', function () {
    Role::create(['name' => 'Student']);

    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '90,00',
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-01-10',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    Livewire::test(Register::class, ['event' => $training])
        ->set('ispastor', 'N')
        ->set('name', 'Aluno Teste')
        ->set('mobile', '11999999999')
        ->set('email', 'aluno@example.com')
        ->set('password', 'secret1234')
        ->set('password_confirmation', 'secret1234')
        ->set('birth_date', '2000-01-01')
        ->set('gender', 'M')
        ->call('registerEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id], absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'email' => 'aluno@example.com',
        'name' => 'Aluno Teste',
        'pastor' => 'N',
    ]);

    $user = User::where('email', 'aluno@example.com')->firstOrFail();

    $this->assertDatabaseHas('role_user', [
        'user_id' => $user->id,
        'role_id' => Role::where('name', 'Student')->value('id'),
    ]);

    $this->assertDatabaseHas('training_user', [
        'user_id' => $user->id,
        'training_id' => $training->id,
    ]);
});

test('existing user can register for a training with the same email', function () {
    Role::create(['name' => 'Student']);

    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $user = User::factory()->create([
        'email' => 'aluno@example.com',
        'password' => 'secret1234',
    ]);

    Livewire::test(Register::class, ['event' => $training])
        ->set('ispastor', 'N')
        ->set('name', 'Aluno Atualizado')
        ->set('mobile', '11999999999')
        ->set('email', 'aluno@example.com')
        ->set('password', 'secret1234')
        ->set('password_confirmation', 'secret1234')
        ->set('birth_date', '2000-01-01')
        ->set('gender', 'M')
        ->call('registerEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id], absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('training_user', [
        'user_id' => $user->id,
        'training_id' => $training->id,
    ]);
});

test('registration is blocked when the student is already enrolled in the training', function () {
    Role::create(['name' => 'Student']);

    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $user = User::factory()->create([
        'email' => 'aluno@example.com',
        'password' => 'secret1234',
    ]);

    $training->students()->syncWithoutDetaching([$user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0]]);

    Livewire::test(Register::class, ['event' => $training])
        ->set('ispastor', 'N')
        ->set('name', 'Aluno Atualizado')
        ->set('mobile', '11999999999')
        ->set('email', 'aluno@example.com')
        ->set('password', 'secret1234')
        ->set('password_confirmation', 'secret1234')
        ->set('birth_date', '2000-01-01')
        ->set('gender', 'M')
        ->call('registerEvent')
        ->assertHasErrors(['email']);

    $this->assertGuest();
    $this->assertDatabaseCount('training_user', 1);
});

test('student can log in after registering for the training', function () {
    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $user = User::factory()->create([
        'email' => 'aluno@example.com',
        'password' => 'secret1234',
    ]);

    $training->students()->syncWithoutDetaching([$user->id => ['accredited' => 0, 'kit' => 0, 'payment' => 0]]);

    Livewire::test(Login::class, ['event' => $training])
        ->set('email', 'aluno@example.com')
        ->set('password', 'secret1234')
        ->call('loginEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id], absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('role_user', [
        'user_id' => $user->id,
        'role_id' => Role::firstOrCreate(['name' => 'Student'])->id,
    ]);
});

test('student can log in and is enrolled when not registered for the training', function () {
    $course = Course::create([
        'name' => 'Treinamento Teste',
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $teacher = User::factory()->create();

    $training = Training::create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $user = User::factory()->create([
        'email' => 'aluno2@example.com',
        'password' => 'secret1234',
    ]);

    Livewire::test(Login::class, ['event' => $training])
        ->set('email', 'aluno2@example.com')
        ->set('password', 'secret1234')
        ->call('loginEvent')
        ->assertHasNoErrors()
        ->assertRedirect(route('app.student.training.show', ['training' => $training->id], absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('training_user', [
        'user_id' => $user->id,
        'training_id' => $training->id,
    ]);
});
