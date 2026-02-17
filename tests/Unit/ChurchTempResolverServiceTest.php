<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\Services\ChurchTempResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(ChurchTempResolverService::class);
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

it('approves temp church as new official and migrates users with audit fields', function (): void {
    $training = ($this->createTraining)();
    $actor = User::factory()->create();
    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Esperanca',
        'city' => 'Recife',
        'state' => 'PE',
        'email' => 'contato@temp.org',
    ]);

    $firstUser = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);
    $secondUser = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);

    $official = $this->service->approveAsNewOfficial(
        $training,
        $temp,
        [
            'name' => 'Igreja Esperanca Oficial',
            'city' => 'Olinda',
            'state' => 'PE',
        ],
        $actor,
    );

    expect($official->name)->toBe('Igreja Esperanca Oficial');
    expect($official->city)->toBe('Olinda');

    $firstUser->refresh();
    $secondUser->refresh();
    $temp->refresh();

    expect($firstUser->church_id)->toBe($official->id);
    expect($firstUser->church_temp_id)->toBeNull();
    expect($secondUser->church_id)->toBe($official->id);
    expect($secondUser->church_temp_id)->toBeNull();
    expect($temp->status)->toBe('approved_new');
    expect($temp->resolved_church_id)->toBe($official->id);
    expect($temp->resolved_by)->toBe($actor->id);
    expect($temp->resolved_at)->not->toBeNull();
    expect($temp->normalized_name)->toBe('igreja esperanca');

    $this->assertDatabaseHas('training_new_churches', [
        'training_id' => $training->id,
        'church_id' => $official->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $actor->id,
    ]);
});

it('merges temp church into an official church and records triage resolution', function (): void {
    $training = ($this->createTraining)();
    $actor = User::factory()->create();
    $official = Church::factory()->create();
    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Fonte de Vida',
        'city' => 'Goiania',
        'state' => 'GO',
    ]);

    $user = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $temp->id,
    ]);

    $this->service->mergeIntoOfficial($training, $temp, $official, $actor);

    $user->refresh();
    $temp->refresh();

    expect($user->church_id)->toBe($official->id);
    expect($user->church_temp_id)->toBeNull();
    expect($temp->status)->toBe('merged');
    expect($temp->resolved_church_id)->toBe($official->id);
    expect($temp->resolved_by)->toBe($actor->id);
    expect($temp->resolved_at)->not->toBeNull();
    expect($temp->normalized_name)->toBe('igreja fonte de vida');

    $this->assertDatabaseMissing('training_new_churches', [
        'training_id' => $training->id,
        'church_id' => $official->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $actor->id,
    ]);
});

it('approves church temp with unsaved training context without creating training_new_church row', function (): void {
    $actor = User::factory()->create();
    $temp = ChurchTemp::query()->create([
        'name' => 'Igreja Contexto Rascunho',
        'city' => 'Brasilia',
        'state' => 'DF',
    ]);
    $unsavedTraining = new Training([
        'course_id' => null,
        'teacher_id' => $actor->id,
    ]);

    $official = $this->service->approveAsNewOfficial($unsavedTraining, $temp, [], $actor);
    $temp->refresh();

    expect($official->id)->not->toBeNull();
    expect($temp->status)->toBe('approved_new');

    $this->assertDatabaseMissing('training_new_churches', [
        'church_id' => $official->id,
        'source_church_temp_id' => $temp->id,
        'created_by' => $actor->id,
    ]);
});
