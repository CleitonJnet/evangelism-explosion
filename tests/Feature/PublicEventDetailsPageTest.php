<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows banner download button in details body and fixed bar when banner image is available', function () {
    Storage::fake('public');

    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $bannerPath = 'training-banners/banner.jpg';
    Storage::disk('public')->put($bannerPath, 'fake-image-content');

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
        'banner' => $bannerPath,
    ]);

    $response = $this->get(route('web.event.details', ['id' => $training->id]));
    $downloadRoute = route('web.event.banner.download', ['id' => $training->id]);

    $response
        ->assertSuccessful()
        ->assertSee('Baixar cartaz')
        ->assertSee($downloadRoute, false);

    expect(substr_count($response->getContent(), $downloadRoute))->toBe(2);
});

it('hides banner download button when banner is not an image', function () {
    Storage::fake('public');

    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $bannerPath = 'training-banners/banner.pdf';
    Storage::disk('public')->put($bannerPath, 'fake-pdf-content');

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
        'banner' => $bannerPath,
    ]);

    $this->get(route('web.event.details', ['id' => $training->id]))
        ->assertSuccessful()
        ->assertDontSee('Baixar cartaz');
});

it('downloads the event banner image file', function () {
    Storage::fake('public');

    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $bannerPath = 'training-banners/banner.jpg';
    Storage::disk('public')->put($bannerPath, 'fake-image-content');

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
        'banner' => $bannerPath,
    ]);

    $response = $this->get(route('web.event.banner.download', ['id' => $training->id]));

    $response->assertSuccessful();

    expect($response->streamedContent())->toBe('fake-image-content');
    expect((string) $response->headers->get('content-disposition'))->toContain('attachment;');
    expect((string) $response->headers->get('content-disposition'))->toContain('banner.jpg');
});
