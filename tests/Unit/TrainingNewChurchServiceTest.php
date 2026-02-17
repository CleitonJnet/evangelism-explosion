<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\Services\TrainingNewChurchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(TrainingNewChurchService::class);
    $this->createTraining = function (): Training {
        $course = Course::factory()->create();
        $hostChurch = Church::factory()->create();
        $teacher = User::factory()->create(['church_id' => $hostChurch->id]);

        return Training::factory()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'church_id' => $hostChurch->id,
        ]);
    };
});

it('marks new church once per training and church pair', function (): void {
    $training = ($this->createTraining)();
    $church = Church::factory()->create();
    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Renascer',
        'city' => 'Curitiba',
        'state' => 'PR',
    ]);
    $actor = User::factory()->create();

    $first = $this->service->markNewChurch($training, $church, $temp, $actor);
    $second = $this->service->markNewChurch($training, $church, $temp, $actor);

    expect($first->id)->toBe($second->id);
    $this->assertDatabaseCount('training_new_churches', 1);
});

it('counts only new churches for the given training', function (): void {
    $trainingA = ($this->createTraining)();
    $trainingB = ($this->createTraining)();
    $actor = User::factory()->create();

    $this->service->markNewChurch($trainingA, Church::factory()->create(), null, $actor);
    $this->service->markNewChurch($trainingA, Church::factory()->create(), null, $actor);
    $this->service->markNewChurch($trainingB, Church::factory()->create(), null, $actor);

    expect($this->service->countNewChurches($trainingA))->toBe(2);
    expect($this->service->countNewChurches($trainingB))->toBe(1);
});

it('returns trainings with new church counts ordered by highest count', function (): void {
    $trainingA = ($this->createTraining)();
    $trainingB = ($this->createTraining)();
    $actor = User::factory()->create();

    $this->service->markNewChurch($trainingA, Church::factory()->create(), null, $actor);
    $this->service->markNewChurch($trainingA, Church::factory()->create(), null, $actor);
    $this->service->markNewChurch($trainingB, Church::factory()->create(), null, $actor);

    $trainings = $this->service->getTrainingsWithNewChurchCounts();

    expect($trainings->first()->id)->toBe($trainingA->id);
    expect($trainings->first()->new_churches_count)->toBe(2);
    expect($trainings->firstWhere('id', $trainingB->id)?->new_churches_count)->toBe(1);
});
