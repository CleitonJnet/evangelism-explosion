<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Helpers\MoneyHelper;
use App\Models\Church;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\Services\Schedule\TrainingScheduleGenerator;
use App\Services\Training\TeacherTrainingCreateService;
use App\TrainingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    private const MAX_STEP = 6;

    public ?int $course_id = null;

    public ?int $teacher_id = null;

    public string $teacherSearch = '';

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

    public ?string $price_church = null;

    public ?string $discount = null;

    public mixed $pixQrCodeUpload = null;

    public ?string $pix_key = null;

    public ?int $kits = null;

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

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::in($this->loadTeachers()->pluck('id')->all()),
            ],
            'church_id' => ['nullable', 'integer', 'exists:churches,id'],
            'banner' => ['nullable', 'string', 'max:255'],
            'coordinator' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'gpwhatsapp' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'price_church' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'string', 'max:50'],
            'pixQrCodeUpload' => ['nullable', 'image', 'max:5120'],
            'pix_key' => ['nullable', 'string', 'max:255', 'required_with:pixQrCodeUpload'],
            'bannerUpload' => ['nullable', 'image', 'max:10240'],
            'kits' => ['nullable', 'integer', 'min:0'],
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
            'teacher_id.required' => 'Selecione um professor.',
            'teacher_id.exists' => 'O professor selecionado é inválido.',
            'teacher_id.in' => 'O professor selecionado não pertence à lista do curso.',
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
            'pix_key.required_with' => 'Informe a chave PIX da igreja sede ao enviar o QR Code PIX.',
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
            'bannerUpload' => 'arquivo de divulgação',
            'pixQrCodeUpload' => 'QR Code PIX da igreja sede',
            'pix_key' => 'chave PIX da igreja sede',
            'welcome_duration_minutes' => 'boas-vindas',
        ];
    }

    public function mount(): void
    {
        $this->price_church = MoneyHelper::formatInput(0, '0');
        $this->discount = MoneyHelper::formatInput(0, '0');
    }

    public function updatedCourseId(?string $value): void
    {
        $this->step = 1;
        $this->teacher_id = null;
        $this->teacherSearch = '';

        if (! $value) {
            $this->price = null;

            return;
        }

        $this->price = $this->createService()->resolveCoursePrice($value);
    }

    public function selectTeacher(int $teacherId): void
    {
        if (! $this->loadTeachers()->contains('id', $teacherId)) {
            return;
        }

        $this->teacher_id = $teacherId;
    }

    public function updatedTeacherSearch(string $value): void
    {
        $teachers = $this->loadTeachers();
        $firstId = $teachers->first()?->id;

        if (! $firstId) {
            $this->teacher_id = null;

            return;
        }

        if ($this->teacher_id !== $firstId) {
            $this->teacher_id = $firstId;
        }
    }

    public function updatedStep(mixed $value): void
    {
        $normalizedStep = $this->createService()->normalizeStep((int) $value, self::MAX_STEP);

        if ($this->step !== $normalizedStep) {
            $this->step = $normalizedStep;
        }
    }

    public function updatedChurchId(?string $value): void
    {
        if (! $value) {
            return;
        }

        $this->applySelectedChurchData((int) $value);
    }

    public function selectChurch(int $churchId): void
    {
        $this->preserveNewChurchSelection = false;
        $this->church_id = $churchId;
        $this->applySelectedChurchData($churchId);
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

        if (! $this->createService()->churchExists($churchId)) {
            return;
        }

        $this->preserveNewChurchSelection = true;
        $this->churchSearch = $churchName;
        $this->church_id = $churchId;
        $this->applySelectedChurchData($churchId);
        $this->dispatch('step-validity-updated');
    }

    #[On('church-created')]
    public function handleChurchCreated(int $churchId, string $churchName): void
    {
        $this->updatedNewChurchSelection([
            'id' => $churchId,
            'name' => $churchName,
        ]);
    }

    public function addEventDate(): void
    {
        $lastDate = collect($this->eventDates)
            ->pluck('date')
            ->filter(fn (mixed $date): bool => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->map(function (string $date): ?Carbon {
                try {
                    return Carbon::createFromFormat('Y-m-d', $date);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->sort()
            ->last();

        $nextDate = $lastDate instanceof Carbon
            ? $lastDate->copy()->addDay()->format('Y-m-d')
            : '';

        $this->eventDates[] = ['date' => $nextDate, 'start_time' => '', 'end_time' => ''];
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
                'teacher_id' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id'),
                    Rule::in($this->loadTeachers()->pluck('id')->all()),
                ],
            ],
            3 => [
                'eventDates' => ['required', 'array', 'min:1'],
                'eventDates.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
                'eventDates.*.start_time' => ['required', 'date_format:H:i'],
                'eventDates.*.end_time' => ['required', 'date_format:H:i', 'after:eventDates.*.start_time'],
            ],
            4 => [
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
                'teacher_id' => $this->teacher_id,
                'church_id' => $this->church_id,
                'eventDates' => $this->eventDates,
            ],
            $rules,
            $this->messages(),
            $this->validationAttributes(),
        )->fails();
    }

    public function nextStep(): void
    {
        if (! $this->canProceedStep($this->step)) {
            return;
        }

        $this->step = $this->createService()->normalizeStep($this->step + 1, self::MAX_STEP);
    }

    public function previousStep(): void
    {
        $this->step = $this->createService()->normalizeStep($this->step - 1, self::MAX_STEP);
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

        return MoneyHelper::formatInput($total, '0') ?? '0';
    }

    public function submit(TrainingScheduleGenerator $generator): void
    {
        $validated = $this->validate();

        $training = DB::transaction(function () use ($validated): Training {
            $training = Training::create([
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
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
                'pix_key' => filled($validated['pix_key'] ?? null) ? trim((string) $validated['pix_key']) : null,
                'kits' => $validated['kits'] ?? null,
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

        if ($this->pixQrCodeUpload) {
            $path = $this->pixQrCodeUpload->store("training-pix-qrcodes/{$training->id}", 'public');
            $training->update(['pix_qr_code' => $path]);
        }

        $generator->generate($training);
        $generator->normalizeGeneratedDurationsToFive($training->fresh());

        $this->redirectRoute('app.director.training.show', ['training' => $training->id]);
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.training.create', [
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
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return Course::query()
            ->with('ministry')
            ->leftJoin('ministries', 'ministries.id', '=', 'courses.ministry_id')
            ->select('courses.*')
            ->when(! $user->hasRole('Director'), function ($query) use ($user): void {
                $query->whereHas('teachers', function ($teacherQuery) use ($user): void {
                    $teacherQuery->whereKey($user->id);
                });
            })
            ->where('courses.execution', 0)
            ->orderBy('ministries.name')
            ->orderBy('courses.order')
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

        $search = trim($this->teacherSearch);

        return Course::query()
            ->find($this->course_id)
            ?->teachers()
            ->wherePivot('status', 1)
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'Teacher');
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($teacherQuery) use ($search): void {
                    $teacherQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit(5)
            ->get(['users.id', 'users.name', 'users.email', 'users.profile_photo_path'])
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
        $churchSelectionData = $this->createService()->resolveChurchSelectionData($churchId);

        if (! $churchSelectionData) {
            return;
        }

        $this->address = $churchSelectionData['address'];
        $this->phone = $churchSelectionData['phone'] ?? $this->phone;
        $this->email = $churchSelectionData['email'] ?? $this->email;
        $this->gpwhatsapp = $churchSelectionData['gpwhatsapp'] ?? $this->gpwhatsapp;
        $this->coordinator = $churchSelectionData['coordinator'] ?? $this->coordinator;
    }

    private function createService(): TeacherTrainingCreateService
    {
        return app(TeacherTrainingCreateService::class);
    }
}
