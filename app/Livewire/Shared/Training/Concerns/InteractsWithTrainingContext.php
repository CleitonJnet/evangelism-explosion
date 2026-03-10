<?php

namespace App\Livewire\Shared\Training\Concerns;

use App\Models\Training;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;
use Illuminate\Support\Facades\Auth;

trait InteractsWithTrainingContext
{
    public array $capabilities = [];

    public array $contextConfig = [];

    protected string $trainingContext = 'teacher';

    protected function initializeTrainingContext(?Training $training = null): void
    {
        $this->contextConfig = $this->buildContextConfig();
        $this->capabilities = $training ? $this->resolveCapabilities($training) : $this->defaultCapabilities();
    }

    public function contextRoute(string $key): string
    {
        return $this->contextConfig['routes'][$key];
    }

    public function contextComponent(string $key): ?string
    {
        return $this->contextConfig['components'][$key] ?? null;
    }

    protected function isDirectorContext(): bool
    {
        return $this->trainingContext === 'director';
    }

    public function usesManualMaterialDelivery(): bool
    {
        return $this->isDirectorContext();
    }

    public function canToggleRegistrationKit(): bool
    {
        return ! $this->usesManualMaterialDelivery() && ($this->capabilities['canEdit'] ?? false);
    }

    protected function canReviewStpApproaches(Training $training): bool
    {
        if ($this->isDirectorContext()) {
            return true;
        }

        return (int) Auth::id() === (int) $training->teacher_id;
    }

    /**
     * @return array{
     *     canView: bool,
     *     canEdit: bool,
     *     canDelete: bool,
     *     canManageSchedule: bool,
     *     canSeeFinance: bool,
     *     canSeeSensitiveData: bool,
     *     canManageMentors: bool,
     *     canSeeDiscipleship: bool
     * }
     */
    protected function resolveCapabilities(Training $training): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return $this->defaultCapabilities();
        }

        $summary = app(TrainingCapabilityResolver::class)->summary($user, $training);

        return [
            'canView' => (bool) ($summary['can_view'] ?? false),
            'canEdit' => (bool) ($summary['can_edit'] ?? false),
            'canDelete' => (bool) ($summary['can_delete'] ?? false),
            'canManageSchedule' => (bool) ($summary['can_manage_schedule'] ?? false),
            'canSeeFinance' => (bool) ($summary['can_view_finance'] ?? false),
            'canSeeSensitiveData' => (bool) ($summary['can_view_sensitive_data'] ?? false),
            'canManageMentors' => (bool) ($summary['can_manage_mentors'] ?? false),
            'canSeeDiscipleship' => (bool) ($summary['can_see_discipleship'] ?? false),
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function defaultCapabilities(): array
    {
        return [
            'canView' => false,
            'canEdit' => false,
            'canDelete' => false,
            'canManageSchedule' => false,
            'canSeeFinance' => false,
            'canSeeSensitiveData' => false,
            'canManageMentors' => false,
            'canSeeDiscipleship' => false,
        ];
    }

    /**
     * @return array{
     *     routes: array<string, string>,
     *     components: array<string, ?string>
     * }
     */
    private function buildContextConfig(): array
    {
        return match ($this->trainingContext) {
            'director' => [
                'routes' => [
                    'show' => 'app.director.training.show',
                    'schedule' => 'app.director.training.schedule',
                    'statistics' => 'app.director.training.statistics',
                    'stpBoard' => 'app.director.training.stp.approaches',
                    'registrations' => 'app.director.training.registrations',
                ],
                'components' => [
                    'editEventDatesModal' => 'pages.app.director.training.edit-event-dates-modal',
                    'editEventBannerModal' => 'pages.app.director.training.edit-event-banner-modal',
                    'manageMentorsModal' => 'pages.app.director.training.manage-mentors-modal',
                    'eventTeachers' => 'pages.app.director.training.event-teachers',
                    'churchTempReviewModal' => 'pages.app.director.training.church-temp-review-modal',
                    'approveChurchTempModal' => 'pages.app.director.training.approve-church-temp-modal',
                    'deliverMaterialModal' => 'pages.app.director.training.deliver-material-modal',
                ],
            ],
            default => [
                'routes' => [
                    'show' => 'app.teacher.trainings.show',
                    'schedule' => 'app.teacher.trainings.schedule',
                    'statistics' => 'app.teacher.trainings.statistics',
                    'stpBoard' => 'app.teacher.trainings.stp.approaches',
                    'registrations' => 'app.teacher.trainings.registrations',
                ],
                'components' => [
                    'editEventDatesModal' => 'pages.app.teacher.training.edit-event-dates-modal',
                    'editEventBannerModal' => 'pages.app.teacher.training.edit-event-banner-modal',
                    'manageMentorsModal' => 'pages.app.teacher.training.manage-mentors-modal',
                    'eventTeachers' => 'pages.app.teacher.training.event-teachers',
                    'churchTempReviewModal' => 'pages.app.teacher.training.church-temp-review-modal',
                    'approveChurchTempModal' => 'pages.app.teacher.training.approve-church-temp-modal',
                    'deliverMaterialModal' => null,
                ],
            ],
        };
    }
}
