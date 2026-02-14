<?php

use App\Livewire\Pages\App\Teacher\Training\Create;
use App\Models\Church;
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
        ->assertSeeHtml('wire:click="submit"')
        ->assertSeeHtml('x-show="step === totalSteps"');
});

it('selects a newly created church when child modal updates the model', function () {
    $teacher = User::factory()->create();
    $church = Church::factory()->create(['name' => 'Igreja Nova Base']);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->set('newChurchSelection', [
            'id' => $church->id,
            'name' => $church->name,
        ])
        ->assertSet('churchSearch', 'Igreja Nova Base')
        ->assertSet('church_id', $church->id);
});

it('keeps the church selected from modal when search would otherwise auto-select another church', function () {
    $teacher = User::factory()->create();

    Church::factory()->create(['name' => 'Igreja Alpha']);
    Church::factory()->create(['name' => 'Igreja Beta']);
    $newChurch = Church::factory()->create(['name' => 'Igreja']);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->set('newChurchSelection', [
            'id' => $newChurch->id,
            'name' => $newChurch->name,
        ])
        ->set('churchSearch', 'Igreja')
        ->assertSet('church_id', $newChurch->id);
});

it('hydrates event address fields when church comes from modal selection', function () {
    $teacher = User::factory()->create();
    $church = Church::factory()->create([
        'name' => 'Igreja Base Modal',
        'street' => 'Rua Transparente',
        'number' => '45',
        'district' => 'Centro',
        'city' => 'Niterói',
        'state' => 'RJ',
        'postal_code' => '24000000',
        'phone' => '21999990000',
        'email' => 'base@igreja.com',
        'contact' => 'Ana Responsável',
        'contact_phone' => '21988887777',
    ]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->set('newChurchSelection', [
            'id' => $church->id,
            'name' => $church->name,
        ])
        ->assertSet('church_id', $church->id)
        ->assertSet('address.street', 'Rua Transparente')
        ->assertSet('address.number', '45')
        ->assertSet('address.district', 'Centro')
        ->assertSet('address.city', 'Niterói')
        ->assertSet('address.state', 'RJ')
        ->assertSet('address.postal_code', '24.000-000')
        ->assertSet('phone', '(21) 99999-0000')
        ->assertSet('email', 'base@igreja.com')
        ->assertSet('coordinator', 'Ana Responsável')
        ->assertSet('gpwhatsapp', '(21) 98888-7777');
});
