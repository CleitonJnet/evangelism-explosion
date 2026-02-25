<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Helpers\MoneyHelper;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View as ViewResponse;
use Livewire\Attributes\On;
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

    public int $totalNewChurches = 0;

    public int $totalDecisions = 0;

    /**
     * @var array{
     *     sessoes_concluidas: int,
     *     sessoes_previstas: int,
     *     evangelho_explicado: int,
     *     pessoas_ouviram: int,
     *     decisao: int,
     *     sem_decisao_interessado: int,
     *     rejeicao: int,
     *     para_seguranca_ja_e_crente: int,
     *     visita_agendada: int
     * }
     */
    public array $resumoStp = [
        'sessoes_concluidas' => 0,
        'sessoes_previstas' => 0,
        'evangelho_explicado' => 0,
        'pessoas_ouviram' => 0,
        'decisao' => 0,
        'sem_decisao_interessado' => 0,
        'rejeicao' => 0,
        'para_seguranca_ja_e_crente' => 0,
        'visita_agendada' => 0,
    ];

    public ?string $eeMinistryBalance = null;

    public ?string $hostChurchExpenseBalance = null;

    public ?string $totalReceivedFromRegistrations = null;

    public function mount(Training $training): void
    {
        $this->loadTrainingData($training->id);
    }

    #[On('training-finance-updated')]
    public function handleFinanceUpdated(?int $trainingId = null): void
    {
        if ($trainingId !== null && $trainingId !== $this->training->id) {
            return;
        }

        $this->loadTrainingData($this->training->id);
    }

    public function render(): ViewResponse
    {
        return view('livewire.pages.app.teacher.training.view');
    }

    private function loadTrainingData(int $trainingId): void
    {
        $this->training = Training::query()->with([
            'course.ministry',
            'teacher',
            'church',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            'students' => fn ($query) => $query->orderBy('name'),
        ])->findOrFail($trainingId)->loadCount('scheduleItems');

        $this->eventDates = $this->training->eventDates;
        $this->students = $this->training->students;
        $this->totalRegistrations = $this->students->count();
        $this->totalParticipatingChurches = $this->students
            ->pluck('church_id')
            ->filter()
            ->unique()
            ->count();
        $this->totalPastors = $this->students
            ->filter(fn (User $student): bool => filled($student->pastor))
            ->count();
        $this->totalUsedKits = $this->students
            ->filter(fn (User $student): bool => (bool) $student->pivot?->kit)
            ->count();
        $this->totalNewChurches = $this->training->newChurches()->count();
        $this->resumoStp = $this->buildOjtSummary();
        $this->totalDecisions = $this->resumoStp['decisao'];
        $this->paidStudentsCount = $this->training->students()
            ->wherePivot('payment', true)
            ->count();
        $this->totalReceivedFromRegistrations = $this->calculateTotalReceivedFromRegistrations();
        $this->eeMinistryBalance = $this->calculateEeMinistryBalance();
        $this->hostChurchExpenseBalance = $this->calculateHostChurchExpenseBalance();
    }

    /**
     * @return array{
     *     sessoes_concluidas: int,
     *     sessoes_previstas: int,
     *     evangelho_explicado: int,
     *     pessoas_ouviram: int,
     *     decisao: int,
     *     sem_decisao_interessado: int,
     *     rejeicao: int,
     *     para_seguranca_ja_e_crente: int,
     *     visita_agendada: int
     * }
     */
    private function buildOjtSummary(): array
    {
        $summary = [
            'sessoes_concluidas' => 0,
            'sessoes_previstas' => 0,
            'evangelho_explicado' => 0,
            'pessoas_ouviram' => 0,
            'decisao' => 0,
            'sem_decisao_interessado' => 0,
            'rejeicao' => 0,
            'para_seguranca_ja_e_crente' => 0,
            'visita_agendada' => 0,
        ];

        $summary['sessoes_previstas'] = StpSession::query()
            ->where('training_id', $this->training->id)
            ->count();

        $summary['sessoes_concluidas'] = StpSession::query()
            ->where('training_id', $this->training->id)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('stp_approaches')
                    ->whereColumn('stp_approaches.stp_session_id', 'stp_sessions.id')
                    ->whereIn('stp_approaches.status', [
                        StpApproachStatus::Done->value,
                        StpApproachStatus::Reviewed->value,
                    ]);
            })
            ->count();

        $approaches = StpApproach::query()
            ->where('training_id', $this->training->id)
            ->whereIn('status', [
                StpApproachStatus::Done->value,
                StpApproachStatus::Reviewed->value,
            ])
            ->get();

        foreach ($approaches as $approach) {
            $summary['evangelho_explicado'] += (int) ($approach->gospel_explained_times ?? 0);

            $listeners = collect(data_get($approach->payload, 'listeners', []))
                ->filter(fn (mixed $listener): bool => is_array($listener))
                ->values();

            if ($listeners->isNotEmpty()) {
                $summary['pessoas_ouviram'] += $listeners->count();

                foreach ($listeners as $listener) {
                    $resultKey = data_get($listener, 'result');
                    $this->incrementOjtResultTotals($summary, is_string($resultKey) ? $resultKey : null);
                }
            } else {
                $summary['pessoas_ouviram'] += (int) ($approach->people_count ?? 0);

                $result = $approach->result;
                $resultKey = $result instanceof StpApproachResult ? $result->value : null;
                $this->incrementOjtResultTotals($summary, $resultKey);
            }

            if ($approach->follow_up_scheduled_at !== null) {
                $summary['visita_agendada']++;
            }
        }

        return $summary;
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function incrementOjtResultTotals(array &$summary, ?string $resultKey): void
    {
        if ($resultKey === null) {
            return;
        }

        if ($resultKey === StpApproachResult::Decision->value) {
            $summary['decisao']++;
        }

        if ($resultKey === StpApproachResult::NoDecisionInterested->value) {
            $summary['sem_decisao_interessado']++;
        }

        if ($resultKey === StpApproachResult::Rejection->value) {
            $summary['rejeicao']++;
        }

        if ($resultKey === StpApproachResult::AlreadyChristian->value) {
            $summary['para_seguranca_ja_e_crente']++;
        }
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
