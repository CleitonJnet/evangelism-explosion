<?php

namespace App\Livewire\Shared\Training;

use App\Livewire\Shared\Training\Concerns\InteractsWithTrainingContext;
use App\Models\Training;
use App\Services\Metrics\TrainingOverviewMetricsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\View\View as ViewResponse;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class ViewPage extends Component
{
    use AuthorizesRequests;
    use InteractsWithTrainingContext;

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
        $this->authorizeTrainingAbility('view', $training);
        $this->initializeTrainingContext($training);
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

    #[On('training-church-updated')]
    public function handleChurchUpdated(?int $trainingId = null): void
    {
        if ($trainingId !== null && $trainingId !== $this->training->id) {
            return;
        }

        $this->loadTrainingData($this->training->id);
    }

    public function render(): ViewResponse
    {
        return view($this->viewTemplate());
    }

    protected function loadTrainingData(int $trainingId): void
    {
        $this->training = Training::query()->with([
            'course.ministry',
            'teacher',
            'church',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            'students' => fn ($query) => $query->orderBy('name'),
        ])->findOrFail($trainingId)->loadCount('scheduleItems');

        $this->authorizeTrainingAbility('view', $this->training);

        $this->eventDates = $this->training->eventDates;
        $this->students = $this->training->students;
        $this->loadContextTrainingData();
        $metrics = app(TrainingOverviewMetricsService::class)->build($this->training);

        $this->totalRegistrations = $metrics['totalRegistrations'];
        $this->totalParticipatingChurches = $metrics['totalParticipatingChurches'];
        $this->totalPastors = $metrics['totalPastors'];
        $this->totalUsedKits = $metrics['totalUsedKits'];
        $this->totalNewChurches = $metrics['totalNewChurches'];
        $this->totalDecisions = $metrics['totalDecisions'];
        $this->paidStudentsCount = $metrics['paidStudentsCount'];
        $this->resumoStp = $metrics['resumoStp'];
        $this->totalReceivedFromRegistrations = $metrics['totalReceivedFromRegistrations'];
        $this->eeMinistryBalance = $metrics['eeMinistryBalance'];
        $this->hostChurchExpenseBalance = $metrics['hostChurchExpenseBalance'];
    }

    protected function loadContextTrainingData(): void
    {
        //
    }

    abstract protected function viewTemplate(): string;
}
