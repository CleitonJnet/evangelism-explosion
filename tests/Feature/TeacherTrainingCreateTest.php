<?php

use App\Livewire\Pages\App\Teacher\Training\Create;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists only courses assigned to the logged in teacher', function () {
    $teacher = User::factory()->create();
    $allowedCourse = Course::factory()->create(['execution' => 0]);
    $otherCourse = Course::factory()->create(['execution' => 0]);

    $allowedCourse->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertViewHas('courses', function ($courses) use ($allowedCourse, $otherCourse): bool {
            return $courses->pluck('id')->contains($allowedCourse->id)
                && ! $courses->pluck('id')->contains($otherCourse->id);
        });
});

it('orders assigned courses by ministry name, type and course name', function () {
    $teacher = User::factory()->create();

    $betaMinistry = Ministry::query()->create([
        'initials' => 'BET',
        'name' => 'Beta',
    ]);

    $alphaMinistry = Ministry::query()->create([
        'initials' => 'ALP',
        'name' => 'Alpha',
    ]);

    $courseOne = Course::factory()->create([
        'execution' => 0,
        'ministry_id' => $betaMinistry->id,
        'type' => 'Basico',
        'name' => 'Curso A',
    ]);

    $courseTwo = Course::factory()->create([
        'execution' => 0,
        'ministry_id' => $alphaMinistry->id,
        'type' => 'Especial',
        'name' => 'Curso Z',
    ]);

    $courseThree = Course::factory()->create([
        'execution' => 0,
        'ministry_id' => $alphaMinistry->id,
        'type' => 'Especial',
        'name' => 'Curso A',
    ]);

    $courseFour = Course::factory()->create([
        'execution' => 0,
        'ministry_id' => $alphaMinistry->id,
        'type' => 'Basico',
        'name' => 'Curso C',
    ]);

    collect([$courseOne, $courseTwo, $courseThree, $courseFour])
        ->each(fn (Course $course) => $course->teachers()->attach($teacher->id, ['status' => 1]));

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertViewHas('courses', function ($courses) use ($courseOne, $courseTwo, $courseThree, $courseFour): bool {
            return $courses->pluck('id')->values()->all() === [
                $courseFour->id,
                $courseThree->id,
                $courseTwo->id,
                $courseOne->id,
            ];
        });
});

it('renders step navigation with alpine controls', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertSeeHtml('x-data="{')
        ->assertSeeHtml("entangle('step').live")
        ->assertSeeHtml('canProceedStep(Number(this.step))')
        ->assertSeeHtml('x-show="step === 1"')
        ->assertSeeHtml('x-show="step === 2"')
        ->assertSeeHtml('wire:model.live="eventDates.')
        ->assertSeeHtml('x-on:click="nextStep"')
        ->assertSeeHtml('x-on:click="previousStep"')
        ->assertSeeHtml('disabled:cursor-not-allowed');
});

it('shows the final amount per registration using course price, extra expenses and discount', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create([
        'execution' => 0,
        'price' => '100.00',
    ]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->set('course_id', $course->id)
        ->set('price_church', '15,50')
        ->set('discount', '5,25')
        ->assertSee('110,25');
});

it('prevents native submit and only allows saving through the save button click', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $course->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertSeeHtml('x-on:submit.prevent')
        ->assertSeeHtml('type="button" wire:click="submit" x-show="step === totalSteps"');
});
