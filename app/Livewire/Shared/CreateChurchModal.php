<?php

namespace App\Livewire\Shared;

use App\Models\Church;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateChurchModal extends Component
{
    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $churchName = '';

    public string $pastorName = '';

    public string $postalCode = '';

    public string $street = '';

    public string $number = '';

    public string $district = '';

    public string $city = '';

    public string $state = '';

    public ?string $phone = null;

    public ?string $email = null;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->authorizeTraining();
    }

    #[On('open-create-mentor-church-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->trainingId) {
            return;
        }

        $this->authorizeTraining();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining();
        $validated = $this->validate();
        $this->busy = true;

        try {
            $church = DB::transaction(function () use ($validated): Church {
                return Church::query()->create([
                    'name' => $validated['churchName'],
                    'pastor' => $validated['pastorName'],
                    'postal_code' => $validated['postalCode'],
                    'street' => $validated['street'],
                    'number' => $validated['number'],
                    'district' => $validated['district'],
                    'city' => $validated['city'],
                    'state' => strtoupper($validated['state']),
                    'phone' => $validated['phone'] ?: null,
                    'email' => $validated['email'] ?: null,
                ]);
            });

            $this->dispatch(
                'mentor-church-created',
                trainingId: $this->trainingId,
                churchId: $church->id,
                churchName: $church->name,
            );

            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.shared.create-church-modal');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'churchName' => ['required', 'string', 'min:3', 'max:255'],
            'pastorName' => ['required', 'string', 'min:3', 'max:255'],
            'postalCode' => ['required', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
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
            'min' => 'O campo :attribute deve ter no mínimo :min caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'churchName' => 'nome completo da igreja',
            'pastorName' => 'nome do pastor titular',
            'postalCode' => 'CEP',
            'street' => 'logradouro',
            'number' => 'número',
            'district' => 'bairro',
            'city' => 'cidade',
            'state' => 'UF',
            'phone' => 'telefone',
            'email' => 'e-mail',
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->churchName = '';
        $this->pastorName = '';
        $this->postalCode = '';
        $this->street = '';
        $this->number = '';
        $this->district = '';
        $this->city = '';
        $this->state = '';
        $this->phone = null;
        $this->email = null;
    }

    private function authorizeTraining(): void
    {
        Gate::authorize('access-teacher');

        $teacherId = Auth::id();

        $training = Training::query()->findOrFail($this->trainingId);

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }
}
