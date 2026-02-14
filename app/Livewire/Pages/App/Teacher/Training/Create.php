<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Helpers\MoneyHelper;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Services\Schedule\TrainingScheduleGenerator;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    /**
     * @var array<int, int>
     */
    public array $extraCourseIds = [2];

    public ?int $course_id = null;

    public ?int $teacher_id = null;

    public ?int $church_id = null;

    public int $step = 1;

    /**
     * @var array<int, array{date: string, start_time: string, end_time: string}>
     */
    public array $eventDates = [
        ['date' => '', 'start_time' => '', 'end_time' => ''],
    ];

    public mixed $bannerUpload = null;

    public ?string $banner = null;

    public ?string $coordinator = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $url = null;

    public ?string $gpwhatsapp = null;

    public ?string $price = null;

    public ?string $price_church = '0,00';

    public ?string $discount = '0,00';

    public ?int $kits = null;

    public ?int $totStudents = null;

    public ?int $totChurches = null;

    public ?int $totNewChurches = null;

    public ?int $totPastors = null;

    public ?int $totKitsUsed = null;

    public ?int $totListeners = null;

    public ?int $totKitsReceived = null;

    public ?int $totApproaches = null;

    public ?int $totDecisions = null;

    public ?string $notes = null;

    public ?int $status = TrainingStatus::Scheduled->value;

    public ?int $welcome_duration_minutes = 30;

    public string $churchSearch = '';

    public bool $preserveNewChurchSelection = false;

    /**
     * @var array{id: int|null, name: string}
     */
    public array $newChurchSelection = [
        'id' => null,
        'name' => '',
    ];

    /**
     * @var array{postal_code: string, street: string, number: string, complement: string, district: string, city: string, state: string}
     */
    public array $address = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public function mount(): void
    {
        $this->teacher_id = Auth::id();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'church_id' => ['nullable', 'integer', 'exists:churches,id'],
            'bannerUpload' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'string', 'max:255'],
            'coordinator' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'gpwhatsapp' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'price_church' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'string', 'max:50'],
            'kits' => ['nullable', 'integer', 'min:0'],
            'totStudents' => ['nullable', 'integer', 'min:0'],
            'totChurches' => ['nullable', 'integer', 'min:0'],
            'totNewChurches' => ['nullable', 'integer', 'min:0'],
            'totPastors' => ['nullable', 'integer', 'min:0'],
            'totKitsUsed' => ['nullable', 'integer', 'min:0'],
            'totListeners' => ['nullable', 'integer', 'min:0'],
            'totKitsReceived' => ['nullable', 'integer', 'min:0'],
            'totApproaches' => ['nullable', 'integer', 'min:0'],
            'totDecisions' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'integer', 'in:0,1,2,3'],
            'welcome_duration_minutes' => ['nullable', 'integer', 'min:30', 'max:60'],
            'eventDates' => ['required', 'array', 'min:1'],
            'eventDates.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
            'eventDates.*.start_time' => ['required', 'date_format:H:i'],
            'eventDates.*.end_time' => ['required', 'date_format:H:i', 'after:eventDates.*.start_time'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:50'],
            'address.complement' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'course_id.required' => 'Selecione um curso.',
            'course_id.exists' => 'O curso selecionado é inválido.',
            'teacher_id.exists' => 'O professor selecionado é inválido.',
            'church_id.exists' => 'A igreja selecionada é inválida.',
            'eventDates.required' => 'Adicione ao menos um dia.',
            'eventDates.min' => 'Adicione ao menos um dia.',
            'eventDates.*.date.required' => 'Informe a data.',
            'eventDates.*.date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
            'eventDates.*.date.distinct' => 'As datas não podem se repetir.',
            'eventDates.*.start_time.required' => 'Informe o horário inicial.',
            'eventDates.*.start_time.date_format' => 'O horário inicial deve estar no formato HH:MM.',
            'eventDates.*.end_time.required' => 'Informe o horário final.',
            'eventDates.*.end_time.date_format' => 'O horário final deve estar no formato HH:MM.',
            'eventDates.*.end_time.after' => 'O horário final deve ser maior que o horário inicial.',
            'welcome_duration_minutes.min' => 'O período de boas-vindas deve ter no mínimo 30 minutos.',
            'welcome_duration_minutes.max' => 'O período de boas-vindas deve ter no máximo 60 minutos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'course_id' => 'curso',
            'teacher_id' => 'professor',
            'church_id' => 'igreja',
            'welcome_duration_minutes' => 'boas-vindas',
        ];
    }

    public function updatedCourseId(?string $value): void
    {
        if (! $value) {
            $this->price = null;
            $this->teacher_id = null;

            return;
        }

        $course = Course::query()->find($value);

        $this->price = $course?->price;
        $this->teacher_id = null;
    }

    public function updatedChurchId(?string $value): void
    {
        if (! $value) {
            return;
        }

        $this->applySelectedChurchData((int) $value);
    }

    public function updatedChurchSearch(string $value): void
    {
        $selectedNewChurchId = isset($this->newChurchSelection['id']) ? (int) $this->newChurchSelection['id'] : null;
        $selectedNewChurchName = trim((string) ($this->newChurchSelection['name'] ?? ''));

        if (
            $this->preserveNewChurchSelection
            && $selectedNewChurchId
            && $this->church_id === $selectedNewChurchId
            && trim($value) === $selectedNewChurchName
        ) {
            return;
        }

        $this->preserveNewChurchSelection = false;

        $churches = $this->loadChurches();
        $firstId = $churches->first()?->id;

        if (! $firstId) {
            $this->church_id = null;

            return;
        }

        if ($this->church_id !== $firstId) {
            $this->church_id = $firstId;
            $this->applySelectedChurchData($firstId);
        }
    }

    /**
     * @param  array{id?: int|string|null, name?: string|null}  $value
     */
    public function updatedNewChurchSelection(array $value): void
    {
        $churchId = isset($value['id']) ? (int) $value['id'] : null;
        $churchName = trim((string) ($value['name'] ?? ''));

        if (! $churchId || $churchName === '') {
            return;
        }

        $this->preserveNewChurchSelection = true;
        $this->churchSearch = $churchName;
        $this->church_id = $churchId;
        $this->applySelectedChurchData($churchId);
    }

    public function addEventDate(): void
    {
        $this->eventDates[] = ['date' => '', 'start_time' => '', 'end_time' => ''];
    }

    public function removeEventDate(int $index): void
    {
        if (count($this->eventDates) === 1) {
            return;
        }

        unset($this->eventDates[$index]);
        $this->eventDates = array_values($this->eventDates);
    }

    public function updated(string $property): void
    {
        foreach (array_keys($this->rules()) as $ruleKey) {
            if (Str::is($ruleKey, $property)) {
                $this->validateOnly($property);
                break;
            }
        }
    }

    public function canProceedStep(int $step): bool
    {
        $rules = match ($step) {
            1 => [
                'course_id' => ['required', 'integer', 'exists:courses,id'],
            ],
            2 => [
                'eventDates' => ['required', 'array', 'min:1'],
                'eventDates.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
                'eventDates.*.start_time' => ['required', 'date_format:H:i'],
                'eventDates.*.end_time' => ['required', 'date_format:H:i', 'after:eventDates.*.start_time'],
            ],
            3 => [
                'church_id' => ['required', 'integer', 'exists:churches,id'],
            ],
            default => [],
        };

        if ($rules === []) {
            return true;
        }

        return ! Validator::make(
            [
                'course_id' => $this->course_id,
                'church_id' => $this->church_id,
                'eventDates' => $this->eventDates,
            ],
            $rules,
            $this->messages(),
            $this->validationAttributes(),
        )->fails();
    }

    public function getCanProceedToNextStepProperty(): bool
    {
        return $this->canProceedStep($this->step);
    }

    public function getFinalPricePerRegistrationProperty(): string
    {
        $price = MoneyHelper::toFloat($this->price) ?? 0.0;
        $priceChurch = MoneyHelper::toFloat($this->price_church) ?? 0.0;
        $discount = MoneyHelper::toFloat($this->discount) ?? 0.0;

        $total = $price + $priceChurch - $discount;

        return number_format($total, 2, ',', '.');
    }

    public function submit(TrainingScheduleGenerator $generator): void
    {
        $validated = $this->validate();

        $training = DB::transaction(function () use ($validated): Training {
            $training = Training::create([
                'course_id' => $validated['course_id'],
                'teacher_id' => $this->teacher_id ?? Auth::id(),
                'church_id' => $validated['church_id'] ?? null,
                'banner' => $validated['banner'] ?? null,
                'coordinator' => $validated['coordinator'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'url' => $validated['url'] ?? null,
                'gpwhatsapp' => $validated['gpwhatsapp'] ?? null,
                'price' => $validated['price'] ?? null,
                'price_church' => $validated['price_church'] ?? null,
                'discount' => $validated['discount'] ?? null,
                'kits' => $validated['kits'] ?? null,
                'totStudents' => $validated['totStudents'] ?? 0,
                'totChurches' => $validated['totChurches'] ?? 0,
                'totNewChurches' => $validated['totNewChurches'] ?? 0,
                'totPastors' => $validated['totPastors'] ?? 0,
                'totKitsUsed' => $validated['totKitsUsed'] ?? 0,
                'totListeners' => $validated['totListeners'] ?? 0,
                'totKitsReceived' => $validated['totKitsReceived'] ?? 0,
                'totApproaches' => $validated['totApproaches'] ?? 0,
                'totDecisions' => $validated['totDecisions'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? TrainingStatus::Scheduled,
                'welcome_duration_minutes' => $validated['welcome_duration_minutes'] ?? 30,
                'street' => $validated['address']['street'] ?? null,
                'number' => $validated['address']['number'] ?? null,
                'complement' => $validated['address']['complement'] ?? null,
                'district' => $validated['address']['district'] ?? null,
                'city' => $validated['address']['city'] ?? null,
                'state' => $validated['address']['state'] ?? null,
                'postal_code' => $validated['address']['postal_code'] ?? null,
            ]);

            $eventDates = collect($validated['eventDates'])
                ->map(fn (array $date): array => [
                    'date' => $date['date'],
                    'start_time' => $date['start_time'].':00',
                    'end_time' => $date['end_time'].':00',
                ])
                ->values()
                ->all();

            $training->eventDates()->createMany($eventDates);

            return $training;
        });

        if ($this->bannerUpload) {
            $path = $this->bannerUpload->store("training-banners/{$training->id}", 'public');
            $training->update(['banner' => $path]);
            $this->banner = $path;
        }

        $generator->generate($training);
        $generator->normalizeGeneratedDurationsToFive($training->fresh());

        $this->redirectRoute('app.teacher.trainings.show', ['training' => $training->id]);
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.create', [
            'courses' => $this->loadCourses(),
            'churches' => $this->loadChurches(),
            'statusOptions' => TrainingStatus::labels(),
        ]);
    }

    /**
     * @return Collection<int, Course>
     */
    private function loadCourses(): Collection
    {
        $teacherId = $this->teacher_id ?? Auth::id();

        if (! $teacherId) {
            return collect();
        }

        return Course::query()
            ->with('ministry')
            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
            ->select('courses.*')
            ->whereHas('teachers', function ($query) use ($teacherId): void {
                $query->whereKey($teacherId);
            })
            ->where(function ($query): void {
                $query->where('courses.execution', 0)
                    ->orWhereIn('courses.id', $this->extraCourseIds);
            })
            ->orderBy('ministries.name')
            ->orderBy('courses.order')
            ->get();
    }

    /**
     * @return EloquentCollection<int, Church>
     */
    private function loadChurches(): EloquentCollection
    {
        return Church::query()
            ->when($this->churchSearch !== '', function ($query): void {
                $search = '%'.$this->churchSearch.'%';
                $query->where('name', 'like', $search)
                    ->orWhere('pastor', 'like', $search)
                    ->orWhere('district', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search);
            })
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    private function applySelectedChurchData(int $churchId): void
    {
        $church = Church::query()->find($churchId);

        if (! $church) {
            return;
        }

        $this->address = [
            'postal_code' => $church->postal_code ?? '',
            'street' => $church->street ?? '',
            'number' => $church->number ?? '',
            'complement' => $church->complement ?? '',
            'district' => $church->district ?? '',
            'city' => $church->city ?? '',
            'state' => $church->state ?? '',
        ];

        $this->phone = $church->phone ?? $this->phone;
        $this->email = $church->email ?? $this->email;
        $this->gpwhatsapp = $church->contact_phone ?? $this->gpwhatsapp;
        $this->coordinator = $church->contact ?? $this->coordinator;
    }
}
