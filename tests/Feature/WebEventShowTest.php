<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Training;
use App\Models\User;

test('web event show uses training relationships', function () {
    $course = Course::create([
        'name' => 'O Evangelho em Sua Mao',
        'type' => 'Workshop',
        'description' => 'Treinamento pratico para evangelizacao pessoal.',
        'targetAudience' => 'Membros e lideres que desejam crescer em evangelizacao.',
        'price' => '65,00',
    ]);

    $church = Church::create([
        'name' => 'Primeira Igreja Batista do Inga',
        'street' => 'Rua Dr. Paulo Alves',
        'number' => '125',
        'district' => 'Inga',
        'city' => 'Niteroi',
        'state' => 'RJ',
        'postal_code' => '24210-445',
        'contact' => 'Equipe EE',
        'contact_phone' => '21 97276-5535',
        'contact_email' => 'contato@example.com',
    ]);

    $teacher = User::factory()->create(['name' => 'Maria Santos']);

    $training = Training::create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
        'street' => 'Rua Dr. Paulo Alves',
        'number' => '125',
        'district' => 'Inga',
        'city' => 'Niteroi',
        'state' => 'RJ',
        'postal_code' => '24210-445',
        'price' => '65,00',
        'phone' => '21 97276-5535',
        'email' => 'contato@example.com',
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-01-16',
        'start_time' => '19:00:00',
        'end_time' => '22:00:00',
        'status' => 1,
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-01-17',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'status' => 1,
    ]);

    $response = $this->get(route('web.event.show', ['id' => $training->id]));

    $response
        ->assertSuccessful()
        ->assertSee('Workshop')
        ->assertSee('O Evangelho em Sua Mao')
        ->assertSee('Treinamento pratico para evangelizacao pessoal.')
        ->assertSee('Membros e lideres que desejam crescer em evangelizacao.')
        ->assertSee('Maria Santos')
        ->assertSee('Rua Dr. Paulo Alves, 125, Inga, Niteroi, RJ, 24210-445')
        ->assertSee('19h00')
        ->assertSee('22h00')
        ->assertSee('17/01')
        ->assertSee('09h00')
        ->assertSee('12h00')
        ->assertSee('R$ 65,00');
});
