<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Helpers\MoneyHelper;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View as ViewResponse;
use Livewire\Component;

class View extends Component
{
    public Training $training;

    /**
     * @var Collection<int, \App\Models\EventDate>
     */
    public Collection $eventDates;

    /**
     * @var Collection<int, \App\Models\User>
     */
    public Collection $students;

    public int $paidStudentsCount = 0;

    public int $totalRegistrations = 0;

    public int $totalParticipatingChurches = 0;

    public int $totalPastors = 0;

    public int $totalUsedKits = 0;

    public ?string $eeMinistryBalance = null;

    public ?string $hostChurchExpenseBalance = null;

    public ?string $totalReceivedFromRegistrations = null;

    public function mount(Training $training): void
    {
        $this->training = $training->load([
            'course.ministry',
            'teacher',
            'church',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            'students' => fn ($query) => $query->orderBy('name'),
        ])->loadCount('scheduleItems');

        $this->eventDates = $this->training->eventDates;
        $this->students = $this->training->students;
        $this->totalRegistrations = $this->students->count();
        $this->totalParticipatingChurches = $this->students
            ->pluck('church_id')
            ->filter()
            ->unique()
            ->count();
        $this->totalPastors = $this->students
            ->filter(fn (User $student): bool => $student->pastor === 'Y')
            ->count();
        $this->totalUsedKits = $this->students
            ->filter(fn (User $student): bool => (bool) $student->pivot?->kit)
            ->count();
        $this->paidStudentsCount = $this->training->students()
            ->wherePivot('payment', true)
            ->count();
        $this->totalReceivedFromRegistrations = $this->calculateTotalReceivedFromRegistrations();
        $this->eeMinistryBalance = $this->calculateEeMinistryBalance();
        $this->hostChurchExpenseBalance = $this->calculateHostChurchExpenseBalance();
    }

    public function render(): ViewResponse
    {
        return view('livewire.pages.app.teacher.training.view');
    }

    private function calculateEeMinistryBalance(): ?string
    {
        $price = MoneyHelper::toFloat($this->training->getRawOriginal('price'));
        $discount = MoneyHelper::toFloat($this->training->getRawOriginal('discount')) ?? 0.0;

        if ($price === null) {
            return null;
        }

        $balance = ($price - $discount) * $this->paidStudentsCount;

        return MoneyHelper::format_money($balance);
    }

    private function calculateHostChurchExpenseBalance(): ?string
    {
        $priceChurch = MoneyHelper::toFloat($this->training->getRawOriginal('price_church'));

        if ($priceChurch === null) {
            return null;
        }

        $balance = $priceChurch * $this->paidStudentsCount;

        return MoneyHelper::format_money($balance);
    }

    private function calculateTotalReceivedFromRegistrations(): ?string
    {
        $price = MoneyHelper::toFloat($this->training->getRawOriginal('price'));
        $discount = MoneyHelper::toFloat($this->training->getRawOriginal('discount')) ?? 0.0;
        $priceChurch = MoneyHelper::toFloat($this->training->getRawOriginal('price_church')) ?? 0.0;

        if ($price === null) {
            return null;
        }

        $totalPerRegistration = $price - $discount + $priceChurch;
        $total = $totalPerRegistration * $this->paidStudentsCount;

        return MoneyHelper::format_money($total);
    }
}
