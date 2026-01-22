<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use Livewire\Volt\Volt;

test('student sees only enrolled trainings on the list', function () {
    $student = User::factory()->create();
    $teacher = User::factory()->create();

    $church = Church::create([
        'name' => 'Igreja Central',
        'pastor' => 'Pr. Lucas',
    ]);

    $course = Course::create([
        'name' => 'Treinamento Basico',
        'type' => 'Treinamento',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $training->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
    ]);

    $otherCourse = Course::create([
        'name' => 'Treinamento Avancado',
        'type' => 'Treinamento',
    ]);

    Training::factory()->create([
        'course_id' => $otherCourse->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $this->actingAs($student);

    Volt::test('pages.app.student.training.index')
        ->assertSee($course->name)
        ->assertSee($church->name)
        ->assertDontSee($otherCourse->name);

    $this->actingAs($student)
        ->get(route('app.student.trainings.index'))
        ->assertSuccessful()
        ->assertSee('Meus treinamentos');
});

test('student sees empty state when there are no trainings', function () {
    $student = User::factory()->create();

    $this->actingAs($student);

    Volt::test('pages.app.student.training.index')
        ->assertSee('Voce ainda nao se inscreveu em nenhum treinamento.');
});
