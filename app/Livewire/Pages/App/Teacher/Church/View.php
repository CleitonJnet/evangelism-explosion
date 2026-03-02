<?php

namespace App\Livewire\Pages\App\Teacher\Church;

use App\Models\Church;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View as ViewContract;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Church $church;

    public int $membersPerPage = 10;

    public int $trainingsPerPage = 8;

    public string $memberSearch = '';

    public function mount(Church $church): void
    {
        $this->church = $church;
        $this->authorize('view', $church);
    }

    #[On('teacher-church-updated')]
    public function handleChurchUpdated(?int $churchId = null): void
    {
        if ($churchId !== null && $churchId !== $this->church->id) {
            return;
        }

        $this->church = $this->church->fresh();
    }

    public function updatedMemberSearch(): void
    {
        $this->resetPage('membersPage');
    }

    public function render(): ViewContract
    {
        $teacherId = Auth::id();

        $church = Church::query()
            ->withCount(['missionaries'])
            ->findOrFail($this->church->id);

        $totalMembersCount = (clone $church->members()->getQuery())->count();

        $members = $this->membersQuery($church)
            ->orderBy('name')
            ->paginate($this->membersPerPage, pageName: 'membersPage');

        $trainings = $this->trainingsQuery($church)
            ->orderByDesc('id')
            ->paginate($this->trainingsPerPage, pageName: 'trainingsPage');

        $churchTrainingsCount = (clone $this->indicatorTrainingsQuery($church))->count();
        $teacherTrainingsCount = (clone $this->indicatorTrainingsQuery($church))
            ->where('teacher_id', $teacherId)
            ->count();

        $pastorMembersCount = (clone $church->members()->getQuery())
            ->where('is_pastor', 1)
            ->count();

        return view('livewire.pages.app.teacher.church.view', [
            'church' => $church,
            'totalMembersCount' => $totalMembersCount,
            'members' => $members,
            'trainings' => $trainings,
            'churchTrainingsCount' => $churchTrainingsCount,
            'teacherTrainingsCount' => $teacherTrainingsCount,
            'pastorMembersCount' => $pastorMembersCount,
            'logoUrl' => $this->logoUrl($church),
        ]);
    }

    private function membersQuery(Church $church): Builder
    {
        $query = $church->members();

        if ($this->memberSearch !== '') {
            $query->where(function (Builder $query): void {
                $query
                    ->where('name', 'like', '%'.$this->memberSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->memberSearch.'%');
            });
        }

        return $query->getQuery();
    }

    private function trainingsQuery(Church $church): Builder
    {
        return Training::query()
            ->with([
                'course',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->where('church_id', $church->id);
    }

    private function indicatorTrainingsQuery(Church $church): Builder
    {
        return Training::query()
            ->where('church_id', $church->id)
            ->whereIn('status', [
                TrainingStatus::Scheduled->value,
                TrainingStatus::Completed->value,
            ]);
    }

    private function logoUrl(Church $church): string
    {
        $logoPath = trim((string) $church->getRawOriginal('logo'));

        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->url($logoPath);
        }

        return asset('images/svg/church.svg');
    }
}
