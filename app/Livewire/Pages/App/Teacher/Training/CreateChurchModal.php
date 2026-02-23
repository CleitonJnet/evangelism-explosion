<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Training;
use App\Services\ChurchTempResolverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class CreateChurchModal extends Component
{
    #[Modelable]
    public array $selectedChurch = [
        'id' => null,
        'name' => '',
    ];

    public bool $showModal = false;

    public ?int $trainingCourseId = null;

    public ?int $trainingTeacherId = null;

    public ?string $church_logo = null;

    public string $church_name = '';

    public string $pastor_name = '';

    public string $phone_church = '';

    public ?string $church_email = null;

    public string $church_contact = '';

    public string $church_contact_phone = '';

    public ?string $church_contact_email = null;

    public ?string $church_notes = null;

    /**
     * @var array{postal_code: string, street: string, number: string, complement: string, district: string, city: string, state: string}
     */
    public array $churchAddress = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'church_logo' => ['nullable', 'string', 'max:255'],
            'church_name' => ['required', 'string', 'max:255'],
            'pastor_name' => ['required', 'string', 'max:255'],
            'phone_church' => ['required', 'string', 'max:30'],
            'church_email' => ['nullable', 'email', 'max:255'],
            'church_contact' => ['required', 'string', 'max:255'],
            'church_contact_phone' => ['required', 'string', 'max:30'],
            'church_contact_email' => ['nullable', 'email', 'max:255'],
            'church_notes' => ['nullable', 'string', 'max:2000'],
            'churchAddress.postal_code' => ['required', 'string', 'max:20'],
            'churchAddress.street' => ['required', 'string', 'max:255'],
            'churchAddress.number' => ['required', 'string', 'max:20'],
            'churchAddress.complement' => ['nullable', 'string', 'max:255'],
            'churchAddress.district' => ['required', 'string', 'max:255'],
            'churchAddress.city' => ['required', 'string', 'max:255'],
            'churchAddress.state' => ['required', 'string', 'size:2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve conter um e-mail válido.',
            'size' => 'O campo :attribute deve conter exatamente :size caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'church_logo' => 'logo da igreja',
            'church_name' => 'nome da igreja',
            'pastor_name' => 'nome do pastor titular',
            'phone_church' => 'telefone da igreja',
            'church_email' => 'e-mail da igreja',
            'church_contact' => 'nome do contato',
            'church_contact_phone' => 'telefone do contato',
            'church_contact_email' => 'e-mail do contato',
            'church_notes' => 'observações',
            'churchAddress.postal_code' => 'CEP',
            'churchAddress.street' => 'logradouro',
            'churchAddress.number' => 'número',
            'churchAddress.complement' => 'complemento',
            'churchAddress.district' => 'bairro',
            'churchAddress.city' => 'cidade',
            'churchAddress.state' => 'UF',
        ];
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

        $church = Church::query()->create([
            'logo' => $validated['church_logo'] ?? null,
            'name' => $validated['church_name'],
            'pastor' => $validated['pastor_name'],
            'email' => $validated['church_email'] ?? null,
            'phone' => $validated['phone_church'],
            'contact' => $validated['church_contact'],
            'contact_phone' => $validated['church_contact_phone'],
            'contact_email' => $validated['church_contact_email'] ?? null,
            'notes' => $validated['church_notes'] ?? null,
            'postal_code' => $validated['churchAddress']['postal_code'],
            'street' => $validated['churchAddress']['street'],
            'number' => $validated['churchAddress']['number'],
            'complement' => $validated['churchAddress']['complement'] ?? null,
            'district' => $validated['churchAddress']['district'],
            'city' => $validated['churchAddress']['city'],
            'state' => strtoupper($validated['churchAddress']['state']),
        ]);

        $this->selectedChurch = [
            'id' => $church->id,
            'name' => $church->name,
        ];

        $this->dispatch('church-created', churchId: $church->id, churchName: $church->name);
        $this->showModal = false;
        $this->resetChurchForm();
    }

    public function approveAndUseNow(): void
    {
        $validated = $this->validate();
        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        $normalizedName = $this->normalizeName($validated['church_name']);

        $churchTemp = ChurchTemp::query()
            ->where('status', 'pending')
            ->where('normalized_name', $normalizedName)
            ->first();

        if (! $churchTemp) {
            $churchTemp = ChurchTemp::query()->create([
                'name' => $validated['church_name'],
                'pastor' => $validated['pastor_name'],
                'email' => $validated['church_email'] ?? null,
                'phone' => $validated['phone_church'],
                'street' => $validated['churchAddress']['street'],
                'number' => $validated['churchAddress']['number'],
                'complement' => $validated['churchAddress']['complement'] ?? null,
                'district' => $validated['churchAddress']['district'],
                'city' => $validated['churchAddress']['city'],
                'state' => strtoupper($validated['churchAddress']['state']),
                'postal_code' => $validated['churchAddress']['postal_code'],
                'contact' => $validated['church_contact'],
                'contact_phone' => $validated['church_contact_phone'],
                'contact_email' => $validated['church_contact_email'] ?? null,
                'notes' => $validated['church_notes'] ?? null,
                'logo' => $validated['church_logo'] ?? null,
                'status' => 'pending',
                'normalized_name' => $normalizedName,
            ]);
        }

        $trainingContext = new Training([
            'course_id' => $this->trainingCourseId,
            'teacher_id' => $actor->id,
        ]);

        $church = app(ChurchTempResolverService::class)->approveAsNewOfficial(
            $trainingContext,
            $churchTemp,
            [],
            $actor,
        );

        $this->selectedChurch = [
            'id' => $church->id,
            'name' => $church->name,
        ];

        $this->dispatch('church-created', churchId: $church->id, churchName: $church->name);
        $this->showModal = false;
        $this->resetChurchForm();
    }

    private function resetChurchForm(): void
    {
        $this->reset([
            'church_logo',
            'church_name',
            'pastor_name',
            'phone_church',
            'church_email',
            'church_contact',
            'church_contact_phone',
            'church_contact_email',
            'church_notes',
        ]);

        $this->churchAddress = [
            'postal_code' => '',
            'street' => '',
            'number' => '',
            'complement' => '',
            'district' => '',
            'city' => '',
            'state' => '',
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.create-church-modal');
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->squish()->lower()->ascii()->value();
    }
}
