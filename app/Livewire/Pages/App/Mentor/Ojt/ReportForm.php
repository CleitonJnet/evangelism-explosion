<?php

namespace App\Livewire\Pages\App\Mentor\Ojt;

use App\Models\OjtReport;
use App\Models\OjtTeam;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class ReportForm extends Component
{
    use AuthorizesRequests;

    public OjtTeam $team;

    public ?OjtReport $report = null;

    public string $contact_type = '';

    /**
     * @var array<int, array{type: string|null, count: int|null}>
     */
    public array $contactTypeCounts = [];

    public int $gospel_presentations = 0;

    public int $listeners_count = 0;

    public int $results_decisions = 0;

    public int $results_interested = 0;

    public int $results_rejection = 0;

    public int $results_assurance = 0;

    public bool $follow_up_scheduled = false;

    /**
     * @var array<int, array<string, array{enabled: bool, type: string|null, description: string}>>
     */
    public array $outline = [];

    public string $lesson_learned = '';

    public bool $isLocked = false;

    public ?string $submittedAt = null;

    /**
     * @var array<string, string>
     */
    public array $outlinePoints = [];

    public function mount(OjtTeam $team): void
    {
        $this->authorize('update', $team);

        $this->team = $team->load(['session', 'mentor', 'trainees.trainee', 'report']);
        $this->report = $team->report;

        $this->outlinePoints = $this->defaultOutlinePoints();
        $this->hydrateFromReport();
    }

    public function render(): View
    {
        return view('livewire.pages.app.mentor.ojt.report-form');
    }

    public function addContactTypeRow(): void
    {
        $this->contactTypeCounts[] = [
            'type' => null,
            'count' => null,
        ];
    }

    public function removeContactTypeRow(int $index): void
    {
        if (! isset($this->contactTypeCounts[$index])) {
            return;
        }

        unset($this->contactTypeCounts[$index]);
        $this->contactTypeCounts = array_values($this->contactTypeCounts);
    }

    public function saveDraft(): void
    {
        $this->ensureEditable();

        $validated = $this->validateForm();

        $this->persist($validated, false);
    }

    public function submitReport(): void
    {
        $this->ensureEditable();

        $validated = $this->validateForm();

        $this->persist($validated, true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    private function validateForm(): array
    {
        $data = $this->validationData();

        $validator = Validator::make($data, $this->rules(), $this->messages());

        $validator->after(function ($validator) use ($data): void {
            $this->validateContactTypeCounts($validator, $data['contactTypeCounts'] ?? []);
            $this->validateOutlineParticipation($validator, $data['outline'] ?? []);
        });

        return $validator->validate();
    }

    /**
     * @return array<string, mixed>
     */
    private function validationData(): array
    {
        return [
            'contact_type' => $this->contact_type,
            'contactTypeCounts' => $this->contactTypeCounts,
            'gospel_presentations' => $this->gospel_presentations,
            'listeners_count' => $this->listeners_count,
            'results_decisions' => $this->results_decisions,
            'results_interested' => $this->results_interested,
            'results_rejection' => $this->results_rejection,
            'results_assurance' => $this->results_assurance,
            'follow_up_scheduled' => $this->follow_up_scheduled,
            'outline' => $this->outline,
            'lesson_learned' => $this->lesson_learned,
        ];
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    private function rules(): array
    {
        return [
            'contact_type' => ['nullable', 'string', 'max:50'],
            'contactTypeCounts' => ['array'],
            'contactTypeCounts.*.type' => ['nullable', 'string', 'max:50'],
            'contactTypeCounts.*.count' => ['nullable', 'integer', 'min:0'],
            'gospel_presentations' => ['required', 'integer', 'min:0'],
            'listeners_count' => ['required', 'integer', 'min:0'],
            'results_decisions' => ['required', 'integer', 'min:0'],
            'results_interested' => ['required', 'integer', 'min:0'],
            'results_rejection' => ['required', 'integer', 'min:0'],
            'results_assurance' => ['required', 'integer', 'min:0'],
            'follow_up_scheduled' => ['boolean'],
            'outline' => ['array'],
            'outline.*.*.enabled' => ['boolean'],
            'outline.*.*.type' => ['nullable', 'in:testimony,illustration'],
            'outline.*.*.description' => ['nullable', 'string', 'max:255'],
            'lesson_learned' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'gospel_presentations.required' => __('Gospel presentations are required.'),
            'listeners_count.required' => __('Listeners count is required.'),
            'results_decisions.required' => __('Decisions count is required.'),
            'results_interested.required' => __('Interested count is required.'),
            'results_rejection.required' => __('Rejection count is required.'),
            'results_assurance.required' => __('Assurance count is required.'),
            'outline.*.*.type.in' => __('Select testimony or illustration.'),
            'outline.*.*.description.max' => __('Descriptions should be under 255 characters.'),
        ];
    }

    /**
     * @param  array<int, array{type: string|null, count: int|null}>  $rows
     */
    private function validateContactTypeCounts($validator, array $rows): void
    {
        foreach ($rows as $index => $row) {
            $type = trim((string) ($row['type'] ?? ''));
            $count = $row['count'] ?? null;

            if ($type === '' && $count === null) {
                continue;
            }

            if ($type === '') {
                $validator->errors()->add("contactTypeCounts.{$index}.type", __('Contact type is required.'));
            }

            if ($count === null) {
                $validator->errors()->add("contactTypeCounts.{$index}.count", __('Contact count is required.'));
            }
        }
    }

    /**
     * @param  array<int, array<string, array{enabled: bool, type: string|null, description: string}>>  $outline
     */
    private function validateOutlineParticipation($validator, array $outline): void
    {
        foreach ($outline as $traineeId => $points) {
            foreach ($points as $pointKey => $point) {
                $enabled = (bool) ($point['enabled'] ?? false);

                if (! $enabled) {
                    continue;
                }

                if (! Arr::get($point, 'type')) {
                    $validator->errors()->add("outline.{$traineeId}.{$pointKey}.type", __('Type is required.'));
                }

                if (! trim((string) Arr::get($point, 'description'))) {
                    $validator->errors()->add("outline.{$traineeId}.{$pointKey}.description", __('Description is required.'));
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function persist(array $validated, bool $submit): void
    {
        $report = $this->report ?? new OjtReport(['ojt_team_id' => $this->team->id]);
        $contactTypeCounts = $this->normalizedContactTypeCounts();

        $report->fill([
            'contact_type' => $validated['contact_type'] ?: null,
            'contact_type_counts' => $contactTypeCounts === [] ? null : $contactTypeCounts,
            'gospel_presentations' => $validated['gospel_presentations'],
            'listeners_count' => $validated['listeners_count'],
            'results_decisions' => $validated['results_decisions'],
            'results_interested' => $validated['results_interested'],
            'results_rejection' => $validated['results_rejection'],
            'results_assurance' => $validated['results_assurance'],
            'follow_up_scheduled' => (bool) $validated['follow_up_scheduled'],
            'outline_participation' => $this->normalizedOutlineParticipation(),
            'lesson_learned' => $validated['lesson_learned'] ?: null,
            'public_report' => $validated['lesson_learned'] ? ['lesson_learned' => $validated['lesson_learned']] : null,
        ]);

        if ($submit) {
            $report->submitted_at = now();
            $report->is_locked = true;
        } else {
            if (! $report->submitted_at) {
                $report->submitted_at = null;
            }
        }

        if (! $report->exists) {
            $report->is_locked = $submit;
        }

        $report->save();

        $this->report = $report->fresh();
        $this->hydrateFromReport();
    }

    private function ensureEditable(): void
    {
        $this->report = $this->team->report()->first();

        if (! $this->report) {
            return;
        }

        if ($this->report->is_locked) {
            abort(403);
        }
    }

    private function hydrateFromReport(): void
    {
        $report = $this->report;

        if (! $report) {
            $this->contactTypeCounts = [[
                'type' => null,
                'count' => null,
            ]];
            $this->isLocked = false;
            $this->submittedAt = null;
            $this->buildOutline();

            return;
        }

        $this->contact_type = (string) ($report->contact_type ?? '');
        $this->contactTypeCounts = $report->contact_type_counts ?: [[
            'type' => null,
            'count' => null,
        ]];
        $this->gospel_presentations = (int) $report->gospel_presentations;
        $this->listeners_count = (int) $report->listeners_count;
        $this->results_decisions = (int) $report->results_decisions;
        $this->results_interested = (int) $report->results_interested;
        $this->results_rejection = (int) $report->results_rejection;
        $this->results_assurance = (int) $report->results_assurance;
        $this->follow_up_scheduled = (bool) $report->follow_up_scheduled;
        $this->lesson_learned = (string) ($report->lesson_learned ?? '');
        $this->isLocked = (bool) $report->is_locked;
        $this->submittedAt = $report->submitted_at?->format('Y-m-d H:i');

        $this->buildOutline($report->outline_participation ?? []);
    }

    /**
     * @param  array<int, array<string, array{type: string, description: string}>>  $stored
     */
    private function buildOutline(array $stored = []): void
    {
        $this->outline = [];

        foreach ($this->team->trainees as $trainee) {
            $traineeId = (int) $trainee->trainee_id;
            $this->outline[$traineeId] = [];

            foreach ($this->outlinePoints as $key => $label) {
                $this->outline[$traineeId][$key] = [
                    'enabled' => false,
                    'type' => null,
                    'description' => '',
                ];
            }
        }

        foreach ($stored as $traineeId => $points) {
            foreach ($points as $label => $data) {
                $key = $this->pointKey($label);

                if (! isset($this->outline[$traineeId][$key])) {
                    continue;
                }

                $this->outline[$traineeId][$key]['enabled'] = true;
                $this->outline[$traineeId][$key]['type'] = $data['type'] ?? null;
                $this->outline[$traineeId][$key]['description'] = $data['description'] ?? '';
            }
        }
    }

    /**
     * @return array<int, array{type: string, count: int}>
     */
    private function normalizedContactTypeCounts(): array
    {
        return collect($this->contactTypeCounts)
            ->filter(function (array $row): bool {
                return trim((string) ($row['type'] ?? '')) !== '';
            })
            ->map(function (array $row): array {
                return [
                    'type' => trim((string) $row['type']),
                    'count' => (int) ($row['count'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, array{type: string|null, description: string}>>|null
     */
    private function normalizedOutlineParticipation(): ?array
    {
        $payload = [];

        foreach ($this->outline as $traineeId => $points) {
            foreach ($points as $key => $data) {
                if (! ($data['enabled'] ?? false)) {
                    continue;
                }

                $payload[$traineeId][$this->outlinePoints[$key]] = [
                    'type' => $data['type'] ?? null,
                    'description' => $data['description'] ?? '',
                ];
            }
        }

        return $payload === [] ? null : $payload;
    }

    /**
     * @return array<string, string>
     */
    private function defaultOutlinePoints(): array
    {
        return [
            'introduction' => 'Introduction',
            'grace' => 'Grace',
            'man' => 'Man',
            'god' => 'God',
            'christ' => 'Christ',
            'faith' => 'Faith',
            'decision' => 'Decision',
            'follow_up' => 'Follow-Up',
        ];
    }

    private function pointKey(string $label): string
    {
        return Str::slug($label, '_');
    }
}
