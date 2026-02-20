<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Database\Seeders\ClinicEvangelismoEficazSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds clinic training with schedules mentors and pending students', function (): void {
    $church = Church::factory()->create();
    $teacher = User::factory()->create(['church_id' => $church->id]);
    $teacherRole = Role::query()->create(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    Course::factory()->create([
        'name' => 'Evangelismo Eficaz',
        'type' => 'Clínica',
        'execution' => 0,
    ]);

    User::factory()->count(20)->create();

    $this->seed(ClinicEvangelismoEficazSeeder::class);

    $training = Training::query()
        ->where('email', 'clinica.ee.seed@eebrasil.org.br')
        ->with(['eventDates', 'mentors.roles', 'students'])
        ->firstOrFail();

    expect($training->eventDates)->toHaveCount(3);
    expect($training->eventDates->pluck('start_time')->all())->toBe([
        '18:30:00',
        '08:00:00',
        '08:00:00',
    ]);
    expect($training->eventDates->pluck('end_time')->all())->toBe([
        '21:30:00',
        '21:30:00',
        '18:30:00',
    ]);

    expect($training->mentors)->toHaveCount(5);
    expect($training->students)->toHaveCount(10);

    foreach ($training->mentors as $mentor) {
        expect($mentor->hasRole('Mentor'))->toBeTrue();
    }

    foreach ($training->students as $student) {
        expect($student->church_id)->toBeNull();
        expect($student->church_temp_id)->toBeNull();
        expect($student->pivot->payment_receipt)->toBeNull();
        expect((int) $student->pivot->payment)->toBe(0);
    }
});
