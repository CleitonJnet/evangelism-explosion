<?php

use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('downloads the public event schedule as a pdf with safe headers', function (): void {
    $training = Training::factory()->create();

    $response = $this->get(route('web.event.schedule.pdf', $training));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('x-content-type-options', 'nosniff');
    $response->assertHeader('content-disposition', 'attachment; filename="programacao-evento-'.$training->id.'.pdf"');
});

it('renders the public schedule page with a relative pdf download link', function (): void {
    $training = Training::factory()->create();

    $response = $this->get(route('web.event.schedule', $training));

    $response->assertOk();
    $response->assertSee('href="/event/'.$training->id.'/programacao/pdf"', false);
});
