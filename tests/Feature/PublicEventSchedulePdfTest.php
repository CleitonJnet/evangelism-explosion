<?php

use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('downloads the public event schedule as a pdf with safe headers', function (): void {
    $training = Training::factory()->create();
    Storage::disk('public')->delete('cache/programacao-evento-'.$training->id.'.pdf');

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

it('downloads the public event schedule pdf even when the logo cache path is not writable as a file', function (): void {
    $training = Training::factory()->create();
    Storage::disk('public')->delete('cache/programacao-evento-'.$training->id.'.pdf');
    $cacheFilePath = storage_path('app/public/cache/ee-gold.png');
    $originalCacheContents = is_file($cacheFilePath) ? file_get_contents($cacheFilePath) : null;
    $hadOriginalCacheFile = is_file($cacheFilePath);

    if ($hadOriginalCacheFile) {
        unlink($cacheFilePath);
    }

    mkdir($cacheFilePath, 0755, true);

    try {
        $response = $this->get(route('web.event.schedule.pdf', $training));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    } finally {
        rmdir($cacheFilePath);

        if ($hadOriginalCacheFile && is_string($originalCacheContents)) {
            file_put_contents($cacheFilePath, $originalCacheContents);
        }
    }
});

it('stores the generated event schedule pdf in cache', function (): void {
    $training = Training::factory()->create();
    $cachedPdfRelativePath = 'cache/programacao-evento-'.$training->id.'.pdf';

    Storage::disk('public')->delete($cachedPdfRelativePath);

    $response = $this->get(route('web.event.schedule.pdf', $training));

    $response->assertOk();
    expect(Storage::disk('public')->exists($cachedPdfRelativePath))->toBeTrue();
});

it('returns the cached event schedule pdf when file already exists', function (): void {
    $training = Training::factory()->create();
    $cachedPdfRelativePath = 'cache/programacao-evento-'.$training->id.'.pdf';
    $cachedPdfContent = 'cached-pdf-binary-content';

    Storage::disk('public')->put($cachedPdfRelativePath, $cachedPdfContent);

    try {
        $response = $this->get(route('web.event.schedule.pdf', $training));

        $response->assertOk();
        expect($response->baseResponse->getContent())->toBe($cachedPdfContent);
    } finally {
        Storage::disk('public')->delete($cachedPdfRelativePath);
    }
});
