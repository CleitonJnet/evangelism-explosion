<?php

use App\Models\Course;
use App\Models\Church;
use App\Models\EventDate;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('it passes workload duration to event show view', function () {
    $compiledPath = storage_path('framework/views-test');
    File::ensureDirectoryExists($compiledPath);
    config(['view.compiled' => $compiledPath]);
    app()->forgetInstance('blade.compiler');
    app()->forgetInstance('view.engine.resolver');
    app()->forgetInstance('view');

    $course = Course::query()->create([
        'type' => 'Workshop',
        'name' => 'Evangelismo Explosivo',
    ]);

    $church = Church::query()->create([
        'name' => 'Igreja Central',
        'contact_phone' => '11999999999',
        'street' => 'Rua Dr. Paulo Alves',
        'number' => '125',
        'district' => 'Ingá',
        'city' => 'Niterói',
        'state' => 'RJ',
    ]);

    $training = Training::query()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'price' => '0,00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-06',
        'start_time' => '18:00:00',
        'end_time' => '21:30:00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-07',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->get(route('web.event.show', ['id' => $training->id]));

    $response->assertSuccessful();
    $response->assertSee(urlencode('Rua Dr. Paulo Alves, 125, Ingá, Niterói, RJ'));
    $response->assertSee('Sexta-feira');
    $response->assertViewHas('workloadDuration', '06h30');
});
