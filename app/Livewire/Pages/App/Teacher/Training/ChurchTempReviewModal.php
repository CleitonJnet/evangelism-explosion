<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Training;
use App\Models\User;
use App\Services\ChurchTempResolverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ChurchTempReviewModal extends Component
{
    public Training $training;

    public bool $showModal = false;

    public bool $hasPendingTemps = false;

    public string $pendingSearch = '';

    /**
     * @var array<int, array{
     *     id: int,
     *     name: string,
     *     city: ?string,
     *     state: ?string,
     *     users_count: int,
     *     has_possible_match: bool,
     *     quick_merge_church_id: int|null,
     *     quick_merge_church_name: string|null
     * }>
     */
    public array $pendingTemps = [];

    /**
     * @var array<int, int|null>
     */
    public array $mergeTargets = [];

    /**
     * @var array<int, string>
     */
    public array $mergeChurchSearch = [];

    /**
     * @var array<int, array{id: int, label: string}>
     */
    public array $churchOptions = [];

    public function mount(Training $training): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
        $this->loadReviewData();
    }

    #[On('open-church-temp-review-modal')]
    public function openModal(): void
    {
        $this->authorizeTraining($this->training);
        $this->loadReviewData();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedPendingSearch(): void
    {
        $this->authorizeTraining($this->training);
        $this->loadReviewData();
    }

    public function openApproveReview(int $tempId): void
    {
        $this->authorizeTraining($this->training);

        $temp = $this->resolvePendingTempOrFail($tempId);
        $this->dispatch('open-approve-church-temp-modal', trainingId: $this->training->id, churchTempId: $temp->id);
    }

    public function mergeTemp(int $tempId): void
    {
        $this->authorizeTraining($this->training);

        $mergeTargetId = $this->mergeTargets[$tempId] ?? null;

        if (! $mergeTargetId) {
            $this->addError("mergeTargets.$tempId", __('Selecione uma igreja oficial para mesclar.'));

            return;
        }

        $temp = $this->resolvePendingTempOrFail($tempId);
        $official = Church::query()->findOrFail($mergeTargetId);
        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        app(ChurchTempResolverService::class)->mergeIntoOfficial(
            $this->training,
            $temp,
            $official,
            $actor,
        );

        $this->refreshReviewDataAfterAction();
        $this->dispatch('church-temp-reviewed');
    }

    public function quickMergeTemp(int $tempId): void
    {
        $this->authorizeTraining($this->training);

        $pendingTemp = collect($this->pendingTemps)
            ->first(fn (array $item): bool => (int) $item['id'] === $tempId);

        if (! $pendingTemp || ! $pendingTemp['quick_merge_church_id']) {
            abort(404);
        }

        $this->mergeTargets[$tempId] = (int) $pendingTemp['quick_merge_church_id'];
        $this->mergeTemp($tempId);
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.church-temp-review-modal');
    }

    #[On('church-temp-approved')]
    public function handleChurchTempApproved(): void
    {
        $this->authorizeTraining($this->training);
        $this->refreshReviewDataAfterAction();
    }

    private function loadReviewData(): void
    {
        $this->training = Training::query()
            ->with([
                'students' => fn ($query) => $query
                    ->with('church_temp')
                    ->orderBy('name'),
            ])
            ->findOrFail($this->training->id);

        $pendingGroups = $this->training->students
            ->filter(fn (User $student): bool => $student->church_temp?->status === 'pending')
            ->groupBy('church_temp_id');
        $this->hasPendingTemps = $pendingGroups->isNotEmpty();

        $basePendingTemps = $pendingGroups
            ->map(function ($group): array {
                $temp = $group->first()?->church_temp;

                return [
                    'id' => $temp->id,
                    'name' => $temp->name,
                    'pastor' => $temp->pastor,
                    'city' => $temp->city,
                    'state' => $temp->state,
                    'street' => $temp->street,
                    'number' => $temp->number,
                    'district' => $temp->district,
                    'postal_code' => $temp->postal_code,
                    'users_count' => $group->count(),
                ];
            })
            ->values()
            ->all();

        $search = $this->normalizeName($this->pendingSearch);
        if ($search !== '') {
            $basePendingTemps = collect($basePendingTemps)
                ->filter(function (array $pendingTemp) use ($search): bool {
                    $searchableText = implode(' ', [
                        (string) ($pendingTemp['name'] ?? ''),
                        (string) ($pendingTemp['pastor'] ?? ''),
                        (string) ($pendingTemp['city'] ?? ''),
                        (string) ($pendingTemp['state'] ?? ''),
                        (string) ($pendingTemp['street'] ?? ''),
                        (string) ($pendingTemp['number'] ?? ''),
                        (string) ($pendingTemp['district'] ?? ''),
                        (string) ($pendingTemp['postal_code'] ?? ''),
                    ]);

                    return str_contains($this->normalizeName($searchableText), $search);
                })
                ->values()
                ->all();
        }

        $officialChurches = Church::query()
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'state']);

        $this->churchOptions = $officialChurches
            ->map(fn (Church $church): array => [
                'id' => $church->id,
                'label' => trim($church->name.' - '.$church->city.'/'.$church->state, ' -/'),
            ])
            ->all();

        $officialChurchesByName = [];

        foreach ($officialChurches as $officialChurch) {
            $normalizedName = $this->normalizeName((string) $officialChurch->name);
            $officialChurchesByName[$normalizedName] = $officialChurchesByName[$normalizedName] ?? [];
            $officialChurchesByName[$normalizedName][] = [
                'id' => $officialChurch->id,
                'name' => (string) $officialChurch->name,
                'city' => $this->normalizeCity((string) $officialChurch->city),
                'state' => $this->normalizeState((string) $officialChurch->state),
            ];
        }

        $this->pendingTemps = collect($basePendingTemps)
            ->map(function (array $pendingTemp) use ($officialChurchesByName): array {
                $normalizedName = $this->normalizeName((string) $pendingTemp['name']);
                $nameMatches = $officialChurchesByName[$normalizedName] ?? [];
                $tempCity = $this->normalizeCity((string) ($pendingTemp['city'] ?? ''));
                $tempState = $this->normalizeState((string) ($pendingTemp['state'] ?? ''));

                $cityStateMatches = collect($nameMatches)
                    ->filter(fn (array $candidate): bool => $tempCity !== '' && $tempState !== ''
                        && $candidate['city'] === $tempCity
                        && $candidate['state'] === $tempState)
                    ->values();

                $highConfidenceMatches = $cityStateMatches->isNotEmpty()
                    ? $cityStateMatches
                    : (count($nameMatches) === 1 ? collect($nameMatches) : collect());

                $quickMergeCandidate = $highConfidenceMatches->count() === 1 ? $highConfidenceMatches->first() : null;

                return array_merge($pendingTemp, [
                    'has_possible_match' => count($nameMatches) > 0,
                    'quick_merge_church_id' => $quickMergeCandidate['id'] ?? null,
                    'quick_merge_church_name' => $quickMergeCandidate['name'] ?? null,
                ]);
            })
            ->values()
            ->all();

        foreach ($this->pendingTemps as $pendingTemp) {
            $tempId = (int) $pendingTemp['id'];
            $this->mergeTargets[$tempId] = $this->mergeTargets[$tempId] ?? null;
            $this->mergeChurchSearch[$tempId] = $this->mergeChurchSearch[$tempId] ?? '';
        }
    }

    private function refreshReviewDataAfterAction(): void
    {
        $this->resetValidation();
        $this->loadReviewData();

        if ($this->pendingTemps === []) {
            $this->showModal = false;
        }
    }

    private function resolvePendingTempOrFail(int $tempId): ChurchTemp
    {
        if (! collect($this->pendingTemps)->contains(fn (array $pendingTemp): bool => (int) $pendingTemp['id'] === $tempId)) {
            abort(404);
        }

        return ChurchTemp::query()
            ->where('status', 'pending')
            ->findOrFail($tempId);
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('manageChurches');

        $teacherId = Auth::id();

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->squish()->lower()->ascii()->value();
    }

    private function normalizeCity(string $city): string
    {
        return $this->normalizeName($city);
    }

    private function normalizeState(string $state): string
    {
        return Str::of($state)->squish()->upper()->value();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    public function mergeChurchSearchResults(int $tempId): array
    {
        $search = $this->normalizeName((string) ($this->mergeChurchSearch[$tempId] ?? ''));

        $results = collect($this->churchOptions);
        if ($search !== '') {
            $results = $results->filter(
                fn (array $churchOption): bool => str_contains($this->normalizeName($churchOption['label']), $search)
            );
        }

        return $results
            ->take(8)
            ->values()
            ->all();
    }

    public function selectMergeTarget(int $tempId, int $churchId): void
    {
        $churchOption = collect($this->churchOptions)
            ->first(fn (array $option): bool => (int) $option['id'] === $churchId);

        if (! $churchOption) {
            abort(404);
        }

        $this->mergeTargets[$tempId] = $churchId;
        $this->mergeChurchSearch[$tempId] = (string) $churchOption['label'];
        $this->resetErrorBag("mergeTargets.$tempId");
    }
}
