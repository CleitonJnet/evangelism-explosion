<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public int $perPage = 9;

    #[On('director-ministry-created')]
    public function handleMinistryCreated(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $ministries = Ministry::query()
            ->with([
                'courses' => fn ($query) => $query
                    ->select(['id', 'ministry_id', 'name', 'execution', 'order'])
                    ->orderBy('execution')
                    ->orderBy('order')
                    ->orderBy('name'),
            ])
            ->withCount([
                'courses',
                'courses as launcher_courses_count' => fn ($query) => $query->where('execution', 0),
                'courses as implementation_courses_count' => fn ($query) => $query->where('execution', 1),
            ])
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.pages.app.director.ministry.index', compact('ministries'));
    }
}
