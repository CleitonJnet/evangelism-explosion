<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Training $training;

    /**
     * @var array<int, int>
     */
    public array $extraCourseIds = [2];

    public ?int $course_id = null;

    public ?int $teacher_id = null;

    public ?int $church_id = null;

    /**
     * @var array<int, array{date: string, start_time: string, end_time: string}>
     */
    public array $eventDates = [];

    public mixed $bannerUpload = null;

    public ?string $banner = null;

    public ?string $coordinator = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $url = null;

    public ?string $gpwhatsapp = null;

    public ?string $price = null;

    public ?string $price_church = null;

    public ?string $discount = null;

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

    public ?int $status = null;

    public ?int $welcome_duration_minutes = 30;

    public string $churchSearch = '';

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

    public function mount(Training $training): void
    {
        $this->training = $training->load('eventDates');
        $this->course_id = $training->course_id;
        $this->teacher_id = $training->teacher_id;
        $this->church_id = $training->church_id;
        $this->banner = $training->banner;
        $this->coordinator = $training->coordinator;
        $this->phone = $training->phone;
        $this->email = $training->email;
        $this->url = $training->url;
        $this->gpwhatsapp = $training->gpwhatsapp;
        $this->price = $training->price;
        $this->price_church = $training->price_church;
        $this->discount = $training->discount;
        $this->kits = $training->kits;
        $this->totStudents = $training->totStudents;
        $this->totChurches = $training->totChurches;
        $this->totNewChurches = $training->totNewChurches;
        $this->totPastors = $training->totPastors;
        $this->totKitsUsed = $training->totKitsUsed;
        $this->totListeners = $training->totListeners;
        $this->totKitsReceived = $training->totKitsReceived;
        $this->totApproaches = $training->totApproaches;
        $this->totDecisions = $training->totDecisions;
        $this->notes = $training->notes;
        $this->welcome_duration_minutes = $training->welcome_duration_minutes ?? 30;

        $status = $training->status instanceof TrainingStatus
            ? $training->status->value
            : (int) $training->status;
        $this->status = $status;

        $this->address = [
            'postal_code' => $training->postal_code ?? '',
            'street' => $training->street ?? '',
            'number' => $training->number ?? '',
            'complement' => $training->complement ?? '',
            'district' => $training->district ?? '',
            'city' => $training->city ?? '',
            'state' => $training->state ?? '',
        ];

        $this->eventDates = $training->eventDates
            ->sortBy(fn ($eventDate) => sprintf('%s %s', $eventDate->date, $eventDate->start_time))
            ->map(fn ($eventDate): array => [
                'date' => $eventDate->date,
                'start_time' => substr($eventDate->start_time ?? '', 0, 5),
                'end_time' => substr($eventDate->end_time ?? '', 0, 5),
            ])
            ->values()
            ->all();

        if ($this->eventDates === []) {
            $this->eventDates = [['date' => '', 'start_time' => '', 'end_time' => '']];
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::exists('course_user', 'user_id')->where('course_id', $this->course_id),
            ],
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

        $church = Church::query()->find($value);

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

    public function updatedChurchSearch(string $value): void
    {
        $churches = $this->loadChurches();
        $firstId = $churches->first()?->id;

        if (! $firstId) {
            $this->church_id = null;

            return;
        }

        if ($this->church_id !== $firstId) {
            $this->church_id = $firstId;
        }
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

    public function submit(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated): void {
            $this->training->update([
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'] ?? null,
                'church_id' => $validated['church_id'] ?? null,
                'banner' => $validated['banner'] ?? $this->training->banner,
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

            $this->training->eventDates()->delete();
            $this->training->eventDates()->createMany($eventDates);
        });

        if ($this->bannerUpload) {
            $path = $this->bannerUpload->store("training-banners/{$this->training->id}", 'public');
            $this->training->update(['banner' => $path]);
            $this->banner = $path;
        }

        $this->redirectRoute('app.director.training.show', ['training' => $this->training->id]);
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.training.edit', [
            'courses' => $this->loadCourses(),
            'teachers' => $this->loadTeachers(),
            'churches' => $this->loadChurches(),
            'statusOptions' => TrainingStatus::labels(),
        ]);
    }

    /**
     * @return Collection<int, Course>
     */
    private function loadCourses(): Collection
    {
        return Course::query()
            ->where(function ($query): void {
                $query->where('execution', 0)
                    ->orWhereIn('id', $this->extraCourseIds);
            })
            ->when($this->course_id, function ($query): void {
                $query->orWhere('id', $this->course_id);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private function loadTeachers(): Collection
    {
        if (! $this->course_id) {
            return collect();
        }

        return Course::query()
            ->with('teachers')
            ->find($this->course_id)
            ?->teachers
            ?? collect();
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
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search);
            })
            ->when($this->church_id, function ($query): void {
                $query->orWhere('id', $this->church_id);
            })
            ->orderBy('name')
            ->limit(25)
            ->get();
    }
}
