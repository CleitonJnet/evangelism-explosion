<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use App\Services\Stp\StpApproachReportService;
use App\Services\Stp\StpBoardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class StpApproachesBoard extends Component
{
    use AuthorizesRequests;

    public Training $training;

    public ?int $activeSessionId = null;

    /**
     * @var array<int, array{id: int, label: string}>
     */
    public array $sessions = [];

    /**
     * @var array<int, array{
     *     id: int,
     *     name: string,
     *     mentor: array{id: int, name: string},
     *     students: array<int, array{id: int, name: string}>,
     *     students_label: string,
     *     approaches: array<int, array{id: int, person_name: string, type: string, status: string}>
     * }>
     */
    public array $teams = [];

    /**
     * @var array<int, array{id: int, person_name: string, type: string, status: string}>
     */
    public array $queue = [];

    public ?int $editingApproachId = null;

    public bool $showModal = false;

    /**
     * @var array{id: int, person_name: string, type: string, status: string}|null
     */
    public ?array $editingApproach = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    public bool $canReview = false;

    public ?string $editingApproachTypeLabel = null;

    public function mount(Training $training): void
    {
        $this->authorize('view', $training);
        $this->training = $training;

        $this->refreshSessions();
        $this->loadBoard();
    }

    public function selectSession(int|string $sessionId): void
    {
        $sessionId = (int) $sessionId;

        $exists = collect($this->sessions)
            ->contains(fn (array $session): bool => $session['id'] === $sessionId);

        if (! $exists) {
            return;
        }

        $this->activeSessionId = $sessionId;
        $this->closeModal();
        $this->loadBoard();
    }

    public function moveApproach(int $approachId, string $toContainer, int $toIndex, string $fromContainer): void
    {
        $this->authorize('view', $this->training);

        $session = $this->activeSession();

        if (! $session) {
            return;
        }

        $toTeamId = $this->parseContainerTeamId($toContainer);
        $fromTeamId = $this->parseContainerTeamId($fromContainer);

        app(StpBoardService::class)->moveApproach(
            $approachId,
            $session->id,
            $toTeamId,
            $toIndex,
            $fromTeamId,
        );

        $this->loadBoard();
    }

    public function openApproachModal(int $approachId): void
    {
        $session = $this->activeSession();

        if (! $session) {
            return;
        }

        $approach = StpApproach::query()
            ->with(['training', 'team'])
            ->where('stp_session_id', $session->id)
            ->find($approachId);

        if (! $approach) {
            return;
        }

        $this->authorize('view', $approach);
        $this->authorize('update', $approach);

        $this->editingApproachId = $approachId;
        $this->editingApproach = $this->mapApproach($approach);
        $this->form = $this->buildForm($approach);
        $this->canReview = $this->isOwnerTeacher();
        $this->editingApproachTypeLabel = $this->translateApproachType($approach->type->value);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->editingApproachId = null;
        $this->editingApproach = null;
        $this->form = [];
        $this->canReview = false;
        $this->editingApproachTypeLabel = null;
        $this->showModal = false;
        $this->resetValidation();
    }

    public function addListener(): void
    {
        $listeners = data_get($this->form, 'payload.listeners', []);

        if (! is_array($listeners)) {
            $listeners = [];
        }

        $listeners[] = $this->emptyListenerRow();
        data_set($this->form, 'payload.listeners', array_values($listeners));
    }

    public function removeListener(int $index): void
    {
        $listeners = data_get($this->form, 'payload.listeners', []);

        if (! is_array($listeners)) {
            return;
        }

        unset($listeners[$index]);
        $listeners = array_values($listeners);

        if ($listeners === []) {
            $listeners[] = $this->emptyListenerRow();
        }

        data_set($this->form, 'payload.listeners', $listeners);
    }

    public function saveApproachDraft(StpApproachReportService $reportService): void
    {
        $approach = $this->editableApproach();

        if (! $approach) {
            return;
        }

        $actor = Auth::user();

        if (! $actor instanceof User) {
            abort(403);
        }

        $validated = $this->validateApproachForm(false);

        $reportService->updateDraft($approach, $validated, $actor);

        $this->openApproachModal($approach->id);
        $this->loadBoard();
    }

    public function markAsDone(StpApproachReportService $reportService): void
    {
        $approach = $this->editableApproach();

        if (! $approach) {
            return;
        }

        $actor = Auth::user();

        if (! $actor instanceof User) {
            abort(403);
        }

        $validated = $this->validateApproachForm(true);

        $reportService->finalize($approach, $validated, $actor);

        $this->closeModal();
        $this->loadBoard();
    }

    public function markAsReviewed(StpApproachReportService $reportService): void
    {
        $approach = $this->editableApproach();

        if (! $approach) {
            return;
        }

        $actor = Auth::user();

        if (! $actor instanceof User || ! $this->isOwnerTeacher()) {
            abort(403);
        }

        $reportService->review($approach, $actor);

        $this->closeModal();
        $this->loadBoard();
    }

    public function createPlannedApproach(string $type): void
    {
        $this->authorize('view', $this->training);

        $session = $this->activeSession();

        if (! $session) {
            return;
        }

        $supportedTypes = array_map(
            fn (StpApproachType $case): string => $case->value,
            StpApproachType::cases(),
        );

        if (! in_array($type, $supportedTypes, true)) {
            return;
        }

        $lastPosition = (int) StpApproach::query()
            ->where('stp_session_id', $session->id)
            ->whereNull('stp_team_id')
            ->max('position');

        $creatorId = Auth::id();

        if (! $creatorId) {
            return;
        }

        StpApproach::query()->create([
            'training_id' => $this->training->id,
            'stp_session_id' => $session->id,
            'stp_team_id' => null,
            'type' => $type,
            'status' => StpApproachStatus::Planned->value,
            'position' => $lastPosition + 1,
            'person_name' => 'Nova visita planejada',
            'created_by_user_id' => $creatorId,
        ]);

        $this->loadBoard();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.stp-approaches-board');
    }

    private function refreshSessions(): void
    {
        $training = Training::query()
            ->with(['stpSessions' => fn ($query) => $query->orderBy('sequence')->orderBy('id')])
            ->findOrFail($this->training->id);

        $this->sessions = $training->stpSessions
            ->map(function (StpSession $session): array {
                return [
                    'id' => $session->id,
                    'label' => $session->label
                        ? sprintf('Sessão %d: %s', $session->sequence, $session->label)
                        : sprintf('Sessão %d', $session->sequence),
                ];
            })
            ->values()
            ->all();

        if ($this->activeSessionId === null) {
            $this->activeSessionId = $training->stpSessions->last()?->id;

            return;
        }

        $hasActiveSession = collect($this->sessions)
            ->contains(fn (array $session): bool => $session['id'] === $this->activeSessionId);

        if (! $hasActiveSession) {
            $this->activeSessionId = $training->stpSessions->last()?->id;
        }
    }

    private function loadBoard(): void
    {
        $this->refreshSessions();

        $session = $this->activeSession();

        if (! $session) {
            $this->queue = [];
            $this->teams = [];

            return;
        }

        $session = StpSession::query()
            ->with([
                'teams' => fn ($query) => $query
                    ->with([
                        'mentor',
                        'students' => fn ($studentsQuery) => $studentsQuery
                            ->orderBy('stp_team_students.position')
                            ->orderBy('name'),
                        'approaches' => fn ($approachesQuery) => $approachesQuery
                            ->orderBy('position')
                            ->orderBy('id'),
                    ])
                    ->orderBy('position')
                    ->orderBy('id'),
            ])
            ->findOrFail($session->id);

        $this->queue = StpApproach::query()
            ->where('stp_session_id', $session->id)
            ->whereNull('stp_team_id')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (StpApproach $approach): array => $this->mapApproach($approach))
            ->values()
            ->all();

        $this->teams = $session->teams
            ->map(function ($team): array {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'mentor' => [
                        'id' => (int) $team->mentor_user_id,
                        'name' => $team->mentor?->name ?? 'Mentor não definido',
                    ],
                    'students' => $team->students
                        ->map(fn ($student): array => [
                            'id' => $student->id,
                            'name' => $student->name,
                        ])
                        ->values()
                        ->all(),
                    'students_label' => $team->students->pluck('name')->join(', '),
                    'approaches' => $team->approaches
                        ->map(fn (StpApproach $approach): array => $this->mapApproach($approach))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, person_name: string, type: string, status: string}
     */
    private function mapApproach(StpApproach $approach): array
    {
        return [
            'id' => $approach->id,
            'person_name' => $approach->person_name,
            'type' => $approach->type->value,
            'status' => $approach->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildForm(StpApproach $approach): array
    {
        $payload = is_array($approach->payload) ? $approach->payload : [];

        return [
            'person_name' => $approach->person_name,
            'type' => $approach->type->value,
            'status' => $approach->status->value,
            'stp_team_id' => $approach->stp_team_id,
            'approach_date' => $this->resolveApproachDateValue($payload, $approach),
            'phone' => $approach->phone,
            'email' => $approach->email,
            'address' => [
                'street' => $approach->street,
                'number' => $approach->number,
                'complement' => $approach->complement,
                'district' => $approach->district,
                'city' => $approach->city,
                'state' => $approach->state,
                'postal_code' => $approach->postal_code,
            ],
            'reference_point' => $approach->reference_point,
            'result' => $approach->result?->value,
            'means_growth' => (bool) $approach->means_growth,
            'follow_up_scheduled_at' => $approach->follow_up_scheduled_at?->format('Y-m-d\TH:i'),
            'payload' => [
                'listeners' => $this->normalizeListenersForForm($payload),
                'notes' => data_get($payload, 'notes'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateApproachForm(bool $finalizing): array
    {
        $validated = $this->validate([
            'form.person_name' => ['required', 'string', 'min:3', 'max:255'],
            'form.approach_date' => ['required', 'date'],
            'form.phone' => ['nullable', 'string', 'max:255'],
            'form.email' => ['nullable', 'string', 'max:255'],
            'form.address.street' => ['nullable', 'string', 'max:255'],
            'form.address.number' => ['nullable', 'string', 'max:255'],
            'form.address.complement' => ['nullable', 'string', 'max:255'],
            'form.address.district' => ['nullable', 'string', 'max:255'],
            'form.address.city' => ['nullable', 'string', 'max:255'],
            'form.address.state' => ['nullable', 'string', 'max:255'],
            'form.address.postal_code' => ['nullable', 'string', 'max:255'],
            'form.reference_point' => ['nullable', 'string', 'max:255'],
            'form.means_growth' => ['boolean'],
            'form.follow_up_scheduled_at' => ['nullable', 'date'],
            'form.payload.listeners' => ['array', 'min:1'],
            'form.payload.listeners.*.name' => ['required', 'string', 'min:3', 'max:255'],
            'form.payload.listeners.*.diagnostic_answer' => ['required', 'string', 'in:christ,works'],
            'form.payload.listeners.*.result' => ['required', 'string', 'in:decision,no_decision_interested,rejection,already_christian'],
            'form.payload.notes' => ['nullable', 'string'],
        ], [
            'form.person_name.required' => 'Informe o nome da pessoa visitada.',
            'form.person_name.min' => 'O nome da pessoa visitada deve ter ao menos 3 caracteres.',
            'form.approach_date.required' => 'Informe a data da abordagem.',
            'form.payload.listeners.min' => 'Informe ao menos um ouvinte nesta abordagem.',
            'form.payload.listeners.*.name.required' => 'Informe o nome de cada ouvinte.',
            'form.payload.listeners.*.diagnostic_answer.required' => 'Selecione a resposta diagnóstica de cada ouvinte.',
            'form.payload.listeners.*.result.required' => 'Selecione o resultado de cada ouvinte.',
        ]);

        $extraErrors = [];

        if ($finalizing && ! filled($this->form['stp_team_id'] ?? null)) {
            $extraErrors['form.stp_team_id'] = 'A visita precisa estar atribuída a uma equipe para concluir.';
        }

        if ($extraErrors !== []) {
            throw ValidationException::withMessages($extraErrors);
        }

        $validated['form']['payload']['approach_date'] = $validated['form']['approach_date'];

        return $validated['form'];
    }

    private function editableApproach(): ?StpApproach
    {
        if ($this->editingApproachId === null) {
            return null;
        }

        $approach = StpApproach::query()
            ->with(['training', 'team'])
            ->find($this->editingApproachId);

        if (! $approach) {
            return null;
        }

        $this->authorize('update', $approach);

        return $approach;
    }

    private function activeSession(): ?StpSession
    {
        if ($this->activeSessionId === null) {
            return null;
        }

        return StpSession::query()
            ->where('training_id', $this->training->id)
            ->find($this->activeSessionId);
    }

    private function parseContainerTeamId(string $container): ?int
    {
        if ($container === 'queue') {
            return null;
        }

        if (! str_starts_with($container, 'team:')) {
            return null;
        }

        $teamId = (int) str_replace('team:', '', $container);

        return $teamId > 0 ? $teamId : null;
    }

    private function isOwnerTeacher(): bool
    {
        return (int) Auth::id() === (int) $this->training->teacher_id;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{name: ?string, diagnostic_answer: ?string, result: ?string}>
     */
    private function normalizeListenersForForm(array $payload): array
    {
        $listeners = data_get($payload, 'listeners', []);

        if (! is_array($listeners) || $listeners === []) {
            return [$this->emptyListenerRow()];
        }

        return collect($listeners)
            ->map(function (mixed $row): array {
                if (! is_array($row)) {
                    return $this->emptyListenerRow();
                }

                return [
                    'name' => data_get($row, 'name'),
                    'diagnostic_answer' => data_get($row, 'diagnostic_answer'),
                    'result' => data_get($row, 'result'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveApproachDateValue(array $payload, StpApproach $approach): string
    {
        $storedDate = data_get($payload, 'approach_date');

        if (is_string($storedDate) && trim($storedDate) !== '') {
            return $storedDate;
        }

        return $approach->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');
    }

    /**
     * @return array{name: ?string, diagnostic_answer: ?string, result: ?string}
     */
    private function emptyListenerRow(): array
    {
        return [
            'name' => null,
            'diagnostic_answer' => null,
            'result' => null,
        ];
    }

    private function translateApproachType(string $type): string
    {
        return match ($type) {
            StpApproachType::Visitor->value => 'Visitante',
            StpApproachType::SecurityQuestionnaire->value => 'Questionário de Segurança',
            StpApproachType::Indication->value => 'Indicação',
            StpApproachType::Lifestyle->value => 'Estilo de Vida',
            default => 'Visita',
        };
    }
}
