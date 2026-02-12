<?php

use App\Livewire\Pages\App\Director\Training\Create;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('prefills price and filters teachers when course changes', function () {
    $course = Course::factory()->create(['price' => '123,00']);
    $teacher = User::factory()->create();
    $course->teachers()->attach($teacher->id, ['status' => 1]);

    Livewire::test(Create::class)
        ->set('course_id', $course->id)
        ->assertSet('price', '123,00')
        ->assertViewHas('teachers', function ($teachers) use ($teacher): bool {
            return $teachers->pluck('id')->contains($teacher->id);
        });
});

it('lists execution courses plus extra ids', function () {
    $executionCourse = Course::factory()->create(['execution' => 1]);
    $extraCourse = Course::factory()->create(['execution' => 0]);
    $otherCourse = Course::factory()->create(['execution' => 0]);

    Livewire::test(Create::class)
        ->set('extraCourseIds', [$extraCourse->id])
        ->assertViewHas('courses', function ($courses) use ($executionCourse, $extraCourse, $otherCourse): bool {
            return $courses->pluck('id')->contains($executionCourse->id)
                && $courses->pluck('id')->contains($extraCourse->id)
                && ! $courses->pluck('id')->contains($otherCourse->id);
        });
});

it('defaults status to scheduled', function () {
    Livewire::test(Create::class)
        ->assertSet('status', \App\TrainingStatus::Scheduled->value);
});

it('stores the uploaded banner', function () {
    Storage::fake('public');

    $course = Course::factory()->create(['execution' => 1]);

    Livewire::test(Create::class)
        ->set('course_id', $course->id)
        ->set('eventDates', [
            ['date' => '2026-02-10', 'start_time' => '08:00', 'end_time' => '12:00'],
        ])
        ->set('bannerUpload', UploadedFile::fake()->image('banner.jpg'))
        ->call('submit');

    $training = Training::query()->first();

    expect($training)->not->toBeNull();
    expect($training->banner)->not->toBeNull();
    Storage::disk('public')->assertExists($training->banner);
});

it('loads address from selected church', function () {
    $church = Church::factory()->create([
        'street' => 'Rua Central',
        'number' => '123',
        'district' => 'Centro',
        'city' => 'Niterói',
        'state' => 'RJ',
        'postal_code' => '24000000',
        'phone' => '21999998888',
        'email' => 'igreja@exemplo.com',
        'contact' => 'Maria Coordenadora',
        'contact_phone' => '21988887777',
    ]);

    Livewire::test(Create::class)
        ->set('church_id', $church->id)
        ->assertSet('address.street', 'Rua Central')
        ->assertSet('address.number', '123')
        ->assertSet('address.district', 'Centro')
        ->assertSet('address.city', 'Niterói')
        ->assertSet('address.state', 'RJ')
        ->assertSet('address.postal_code', '24.000-000')
        ->assertSet('phone', '(21) 99999-8888')
        ->assertSet('email', 'igreja@exemplo.com')
        ->assertSet('coordinator', 'Maria Coordenadora')
        ->assertSet('gpwhatsapp', '(21) 98888-7777');
});

it('auto selects the first church from the search results', function () {
    $first = Church::factory()->create(['name' => 'Igreja Alfa', 'city' => 'Niterói']);
    $second = Church::factory()->create(['name' => 'Igreja Beta', 'city' => 'Niterói']);

    Livewire::test(Create::class)
        ->set('churchSearch', 'Igreja')
        ->assertSet('church_id', $first->id);
});
