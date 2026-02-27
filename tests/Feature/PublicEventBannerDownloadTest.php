<?php

use App\Models\Course;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('downloads the public event banner using event name and date in filename', function (): void {
    Storage::fake('public');

    $course = Course::factory()->create([
        'type' => 'Treinamento Base',
        'name' => 'Evangelismo Explosivo',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'banner' => 'training-banners/'.$course->id.'/banner-evento.webp',
    ]);

    $training->eventDates()
        ->orderBy('id')
        ->get()
        ->values()
        ->each(function ($eventDate, int $index): void {
            $eventDate->update([
                'date' => now()->setDate(2026, 11, 21)->addDays($index)->format('Y-m-d'),
            ]);
        });

    Storage::disk('public')->put((string) $training->banner, 'fake-image-content');

    $response = $this->get(route('web.event.banner.download', ['id' => $training->id]));

    $response->assertOk();
    $response->assertHeader(
        'content-disposition',
        'attachment; filename=treinamento-base-evangelismo-explosivo_21-11-2026.webp',
    );
});
