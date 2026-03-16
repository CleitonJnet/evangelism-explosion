<?php

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\Services\EventReports\EventReportService;
use App\Services\EventReports\EventReportWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps empty draft reports to not started and submitted reports to sent status', function (): void {
    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create(['church_id' => $church->id]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $actor = User::factory()->create(['church_id' => $church->id]);
    $reportService = app(EventReportService::class);
    $workflow = app(EventReportWorkflowService::class);

    $draft = $reportService->ensureReport($training, EventReportType::Church, $actor)->load('sections');

    expect($workflow->presentationStatus($draft)['label'])->toBe('Nao iniciado')
        ->and($workflow->isEditable($draft))->toBeTrue();

    $submitted = $reportService->submit($draft, [
        'summary' => 'Resumo final do evento.',
        'sections' => [
            [
                'key' => 'attendance',
                'position' => 1,
                'content' => ['present' => 24],
            ],
        ],
    ], $actor);

    expect($workflow->presentationStatus($submitted)['label'])->toBe('Enviado')
        ->and($workflow->isEditable($submitted))->toBeFalse()
        ->and($submitted->status)->toBe(EventReportStatus::Submitted);
});
