<?php

use App\Enums\EventReportReviewOutcome;
use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\EventReports\EventReportReviewService;
use App\Services\EventReports\EventReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->reportService = app(EventReportService::class);
    $this->reviewService = app(EventReportReviewService::class);

    $this->createTraining = function (): Training {
        $course = Course::factory()->create();
        $church = Church::factory()->create();
        $teacher = User::factory()->create(['church_id' => $church->id]);

        return Training::factory()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'church_id' => $church->id,
        ]);
    };
});

it('creates one canonical report per event and type', function (): void {
    $training = ($this->createTraining)();
    $actor = User::factory()->create(['church_id' => $training->church_id]);

    $first = $this->reportService->ensureReport($training, EventReportType::Church, $actor);
    $second = $this->reportService->ensureReport($training, EventReportType::Church, $actor);
    $teacher = $this->reportService->ensureReport($training, EventReportType::Teacher, $actor);

    expect($first->id)->toBe($second->id)
        ->and($first->status)->toBe(EventReportStatus::Draft)
        ->and($teacher->type)->toBe(EventReportType::Teacher);

    $this->assertDatabaseCount('event_reports', 2);
});

it('saves draft data and synchronizes sections', function (): void {
    $training = ($this->createTraining)();
    $actor = User::factory()->create(['church_id' => $training->church_id]);
    $report = $this->reportService->ensureReport($training, EventReportType::Church, $actor);

    $draft = $this->reportService->saveDraft($report, [
        'title' => 'Relatorio local',
        'summary' => 'Resumo inicial',
        'sections' => [
            [
                'key' => 'attendance',
                'title' => 'Participacao',
                'position' => 1,
                'content' => ['present' => 42],
            ],
            [
                'key' => 'follow_up',
                'title' => 'Acompanhamento',
                'position' => 2,
                'content' => ['next_steps' => ['visita', 'contato']],
            ],
        ],
    ], $actor);

    expect($draft->status)->toBe(EventReportStatus::Draft)
        ->and($draft->sections)->toHaveCount(2)
        ->and($draft->sections->firstWhere('key', 'attendance')?->content)->toBe(['present' => 42]);
});

it('submits a report and records review workflow', function (): void {
    $training = ($this->createTraining)();
    $actor = User::factory()->create(['church_id' => $training->church_id]);
    $staff = User::factory()->create();
    $boardRole = Role::query()->create(['name' => 'Board']);
    $staff->roles()->attach($boardRole);

    $report = $this->reportService->ensureReport($training, EventReportType::Teacher, $actor);

    $submitted = $this->reportService->submit($report, [
        'summary' => 'Evento executado conforme orientacao.',
        'sections' => [
            [
                'key' => 'execution',
                'position' => 1,
                'content' => ['completed' => true],
            ],
        ],
    ], $actor);

    $review = $this->reviewService->requestChanges($submitted, $staff, 'Completar evidencias.');
    $approved = $this->reviewService->approve($submitted->refresh(), $staff, 'Governanca concluida.');

    expect($submitted->refresh()->status)->toBe(EventReportStatus::Reviewed)
        ->and($review->outcome)->toBe(EventReportReviewOutcome::ChangesRequested)
        ->and($approved->outcome)->toBe(EventReportReviewOutcome::Approved);

    $this->assertDatabaseCount('event_report_reviews', 2);
});
