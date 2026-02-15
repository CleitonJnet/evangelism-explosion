<?php

use App\Livewire\Pages\App\Teacher\Training\Create;
use App\Models\Church;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows save button only on last step', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertSee('Próximo')
        ->assertDontSee('Salvar evento')
        ->set('step', 5)
        ->assertSee('Salvar evento');
});

it('resets step to one when course changes', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->set('step', 5)
        ->set('course_id', $course->id)
        ->assertSet('step', 1);
});

it('accepts the newly created church id and unlocks step 3 progression', function () {
    $teacher = User::factory()->create();
    $church = Church::factory()->create(['name' => 'Igreja Nova Fluxo']);

    $this->actingAs($teacher);

    $component = Livewire::test(Create::class)
        ->set('step', 3)
        ->set('newChurchSelection', [
            'id' => $church->id,
            'name' => $church->name,
        ])
        ->assertSet('church_id', $church->id);

    expect($component->instance()->canProceedStep(3))->toBeTrue();
});

it('advances only when current step is valid', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->call('nextStep')
        ->assertSet('step', 1)
        ->set('course_id', $course->id)
        ->call('nextStep')
        ->assertSet('step', 2);
});
