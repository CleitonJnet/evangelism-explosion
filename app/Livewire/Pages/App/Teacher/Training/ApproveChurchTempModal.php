<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\ChurchTemp;
use App\Models\Training;
use App\Services\ChurchTempResolverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ApproveChurchTempModal extends Component
{
    public Training $training;

    public bool $showModal = false;

    public ?int $trainingId = null;

    public ?int $churchTempId = null;

    public string $church_name = '';

    public string $pastor_name = '';

    public string $phone_church = '';

    public ?string $church_email = null;

    /**
     * @var array{postal_code: string, street: string, number: string, district: string, city: string, state: string}
     */
    public array $churchAddress = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public function mount(Training $training): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
    }

    #[On('open-approve-church-temp-modal')]
    public function openModal(int $trainingId, int $churchTempId): void
    {
        $this->authorizeTraining($this->training);

        if ($trainingId !== $this->training->id) {
            abort(404);
        }

        $temp = ChurchTemp::query()
            ->where('status', 'pending')
            ->findOrFail($churchTempId);

        $this->trainingId = $trainingId;
        $this->churchTempId = $temp->id;
        $this->church_name = (string) $temp->name;
        $this->pastor_name = (string) ($temp->pastor ?? '');
        $this->phone_church = (string) ($temp->phone ?? '');
        $this->church_email = $temp->email;
        $this->churchAddress = [
            'postal_code' => (string) ($temp->postal_code ?? ''),
            'street' => (string) ($temp->street ?? ''),
            'number' => (string) ($temp->number ?? ''),
            'district' => (string) ($temp->district ?? ''),
            'city' => (string) ($temp->city ?? ''),
            'state' => strtoupper((string) ($temp->state ?? '')),
        ];

        $this->resetValidation();
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
            'church_name' => ['required', 'string', 'max:255'],
            'pastor_name' => ['required', 'string', 'max:255'],
            'phone_church' => ['nullable', 'string', 'max:30'],
            'church_email' => ['nullable', 'email', 'max:255'],
            'churchAddress.postal_code' => ['required', 'string', 'max:20'],
            'churchAddress.street' => ['required', 'string', 'max:255'],
            'churchAddress.number' => ['required', 'string', 'max:20'],
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
            'church_name' => 'nome completo da igreja',
            'pastor_name' => 'nome do pastor titular',
            'phone_church' => 'telefone da igreja',
            'church_email' => 'e-mail da igreja',
            'churchAddress.postal_code' => 'CEP',
            'churchAddress.street' => 'logradouro',
            'churchAddress.number' => 'número',
            'churchAddress.district' => 'bairro',
            'churchAddress.city' => 'cidade',
            'churchAddress.state' => 'UF',
        ];
    }

    public function confirmApprove(): void
    {
        $this->authorizeTraining($this->training);

        if ($this->trainingId !== $this->training->id || ! $this->churchTempId) {
            abort(404);
        }

        $temp = ChurchTemp::query()
            ->where('status', 'pending')
            ->findOrFail($this->churchTempId);

        $actor = Auth::user();

        if (! $actor) {
            abort(403);
        }

        $validated = $this->validate();

        app(ChurchTempResolverService::class)->approveAsNewOfficial(
            $this->training,
            $temp,
            [
                'name' => $validated['church_name'],
                'pastor' => $validated['pastor_name'],
                'phone' => $validated['phone_church'] !== '' ? $validated['phone_church'] : null,
                'email' => $validated['church_email'] ?: null,
                'postal_code' => $validated['churchAddress']['postal_code'],
                'street' => $validated['churchAddress']['street'],
                'number' => $validated['churchAddress']['number'],
                'district' => $validated['churchAddress']['district'],
                'city' => $validated['churchAddress']['city'],
                'state' => strtoupper($validated['churchAddress']['state']),
            ],
            $actor,
        );

        $this->showModal = false;
        $this->dispatch('church-temp-approved');
        $this->dispatch('church-temp-reviewed');
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.approve-church-temp-modal');
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('manageChurches');

        $teacherId = Auth::id();

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }
}
