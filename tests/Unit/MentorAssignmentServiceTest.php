<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\MentorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(MentorAssignmentService::class);
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

it('keeps a single mentor pivot record per training and user', function (): void {
    $training = ($this->createTraining)();
    $mentorUser = User::factory()->create();
    $actor = User::factory()->create();

    $this->service->addMentor($training, $mentorUser, $actor);
    $this->service->addMentor($training, $mentorUser, $actor);

    $this->assertDatabaseCount('mentors', 1);
    $this->assertDatabaseHas('mentors', [
        'training_id' => $training->id,
        'user_id' => $mentorUser->id,
        'created_by' => $actor->id,
    ]);
});

it('assigns mentor role when adding mentor without role', function (): void {
    $training = ($this->createTraining)();
    $mentorUser = User::factory()->create();
    $actor = User::factory()->create();

    expect($mentorUser->roles()->exists())->toBeFalse();

    $this->service->addMentor($training, $mentorUser, $actor);

    $mentorRole = Role::query()
        ->whereRaw('LOWER(name) = ?', ['mentor'])
        ->first();

    expect($mentorRole)->not->toBeNull();
    expect($mentorUser->roles()->whereKey($mentorRole->id)->exists())->toBeTrue();
});

it('detaches mentor from training when removing mentor', function (): void {
    $training = ($this->createTraining)();
    $mentorUser = User::factory()->create();
    $actor = User::factory()->create();

    $this->service->addMentor($training, $mentorUser, $actor);
    $this->service->removeMentor($training, $mentorUser, $actor);

    $this->assertDatabaseMissing('mentors', [
        'training_id' => $training->id,
        'user_id' => $mentorUser->id,
    ]);
});
