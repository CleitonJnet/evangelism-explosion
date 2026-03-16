<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createStudentPortalUser(): User
{
    $user = User::factory()->create();
    $roleId = Role::query()->firstOrCreate(['name' => 'Student'])->id;
    $user->roles()->syncWithoutDetaching([$roleId]);

    return $user;
}

function createTrainingWithStudent(
    User $student,
    Church $church,
    Course $course,
    string $date,
    array $pivot = [],
    array $trainingAttributes = [],
): Training {
    $training = Training::factory()->create([
        'church_id' => $church->id,
        'course_id' => $course->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
        ...$trainingAttributes,
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => $date,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $training->students()->attach($student->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
        'payment_receipt' => null,
        ...$pivot,
    ]);

    return $training;
}

it('shows a unified student portal dashboard with journey sections', function () {
    Carbon::setTestNow('2026-03-13 10:00:00');

    $student = createStudentPortalUser();
    $church = Church::factory()->create();
    $course = Course::factory()->create([
        'name' => 'Clinica 1',
        'type' => 'EE',
        'certificate' => 'Certificado de participacao',
    ]);

    createTrainingWithStudent($student, $church, $course, '2026-03-20');
    createTrainingWithStudent($student, $church, $course, '2026-03-13', ['payment_receipt' => 'training-receipts/pending.webp']);
    createTrainingWithStudent($student, $church, $course, '2026-03-01', ['payment' => 1, 'accredited' => 1, 'kit' => 1]);
    $pendingReceipt = createTrainingWithStudent($student, $church, $course, '2026-03-25');

    $response = $this
        ->actingAs($student)
        ->get(route('app.portal.student.dashboard'));

    $response->assertOk();
    $response->assertSee('Portal do aluno');
    $response->assertSee('Proximos treinamentos');
    $response->assertSee('Treinamentos em andamento');
    $response->assertSee('Pendencias de comprovante');
    $response->assertSee('Historico resumido');
    $response->assertSee('Atalhos uteis');
    $response->assertSee(route('app.portal.student.receipts'), false);
    $response->assertSee(route('app.portal.student.trainings.show', $pendingReceipt), false);
});

it('exposes student portal navigation pages with consistent links', function () {
    $student = createStudentPortalUser();

    $response = $this
        ->actingAs($student)
        ->get(route('app.portal.student.receipts'));

    $response->assertOk();
    $response->assertSee('Comprovantes');
    $response->assertSee(route('app.portal.student.dashboard'), false);
    $response->assertSee(route('app.portal.student.history'), false);
    $response->assertSee(route('app.portal.student.certificates'), false);
});
