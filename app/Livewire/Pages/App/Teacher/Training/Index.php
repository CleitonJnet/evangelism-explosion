<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Services\Training\TrainingIndexService;
use App\TrainingStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $statusKey = 'scheduled';

    public function mount(?string $statusKey = null): void
    {
        $this->statusKey = $this->normalizeStatusKey($statusKey);
    }

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user?->can('access-teacher'), 403);

        return view('livewire.pages.app.teacher.training.index', [
            ...app(TrainingIndexService::class)->buildIndexData($user, $this->statusKey, null, [
                'planning' => 'app.teacher.trainings.planning',
                'scheduled' => 'app.teacher.trainings.scheduled',
                'canceled' => 'app.teacher.trainings.canceled',
                'completed' => 'app.teacher.trainings.completed',
            ], 'teacher'),
        ]);
    }

    private function normalizeStatusKey(?string $statusKey): string
    {
        $key = $statusKey ?? 'scheduled';

        foreach (TrainingStatus::cases() as $status) {
            if ($status->key() === $key) {
                return $key;
            }
        }

        return 'scheduled';
    }
}
