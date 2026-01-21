<?php

use App\Livewire\Pages\App\Director\Training\Index;
use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Livewire\Livewire;

test('training events are grouped by status with details', function () {
    $church = Church::create([
        'name' => 'Igreja Central',
        'pastor' => 'Pr. Paulo',
    ]);

    $teacher = User::factory()->create(['name' => 'Professor Lucas']);

    $course = Course::create([
        'name' => 'Curso Intensivo',
    ]);

    $course->forceFill(['execution' => 0])->save();

    $hiddenCourse = Course::create([
        'name' => 'Curso Bloqueado',
    ]);

    $hiddenCourse->forceFill(['execution' => 1])->save();

    $training = Training::create([
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled,
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
    ]);

    $hiddenChurch = Church::create([
        'name' => 'Igreja Oculta',
        'pastor' => 'Pr. Joao',
    ]);

    $hiddenTeacher = User::factory()->create(['name' => 'Professor Oculto']);

    Training::create([
        'church_id' => $hiddenChurch->id,
        'teacher_id' => $hiddenTeacher->id,
        'course_id' => $hiddenCourse->id,
        'status' => TrainingStatus::Scheduled,
        'street' => 'Rua Oculta',
        'number' => '99',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'postal_code' => '01001-000',
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-02-06',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-02-07',
        'start_time' => '13:00:00',
        'end_time' => '17:00:00',
    ]);

    Livewire::test(Index::class)
        ->assertSee('Agendado')
        ->assertSee('Planejamento')
        ->assertSee('Cancelado')
        ->assertSee('Concluido')
        ->assertSee('Professor Lucas')
        ->assertSee('Igreja Central')
        ->assertSee('Pr. Paulo')
        ->assertSee('Rua A, 10, Centro, Sao Paulo, SP, 01.001-000')
        ->assertSee('07/02/2026')
        ->assertSee('13:00 - 17:00')
        ->assertDontSee('Curso Bloqueado');
});
