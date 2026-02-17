<?php

namespace App\Livewire\Shared;

use App\Models\ChurchTemp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateChurchTempModal extends Component
{
    public bool $showModal = false;

    public string $churchTempName = '';

    public string $churchTempPastor = '';

    public string $churchTempPostalCode = '';

    public string $churchTempStreet = '';

    public string $churchTempNumber = '';

    public string $churchTempDistrict = '';

    public string $churchTempCity = '';

    public string $churchTempState = '';

    public ?string $churchTempPhone = null;

    public ?string $churchTempEmail = null;

    #[On('open-create-church-temp-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());
        $user = Auth::user();

        if (! $user) {
            $this->addError('churchTempName', 'Nao foi possivel salvar a igreja.');

            return;
        }

        $normalizedName = $this->normalizeName($validated['churchTempName']);

        $churchTemp = ChurchTemp::query()
            ->where('status', 'pending')
            ->where('normalized_name', $normalizedName)
            ->first();

        if (! $churchTemp) {
            $churchTemp = ChurchTemp::query()->create([
                'name' => $validated['churchTempName'],
                'pastor' => $validated['churchTempPastor'],
                'email' => $validated['churchTempEmail'] ?: null,
                'phone' => $validated['churchTempPhone'] ?: null,
                'street' => $validated['churchTempStreet'],
                'number' => $validated['churchTempNumber'],
                'district' => $validated['churchTempDistrict'],
                'city' => $validated['churchTempCity'],
                'state' => strtoupper($validated['churchTempState']),
                'postal_code' => $validated['churchTempPostalCode'],
                'status' => 'pending',
                'normalized_name' => $normalizedName,
            ]);
        }

        $user->forceFill([
            'church_id' => null,
            'church_temp_id' => $churchTemp->id,
        ])->save();

        session()->forget(['church_modal_open', 'church_modal_prompted']);

        $this->dispatch('church-temp-linked', churchTempId: $churchTemp->id);
        $this->closeModal();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'churchTempName' => ['required', 'string', 'min:3', 'max:255'],
            'churchTempPastor' => ['required', 'string', 'max:255'],
            'churchTempPostalCode' => ['required', 'string', 'max:20'],
            'churchTempStreet' => ['required', 'string', 'max:255'],
            'churchTempNumber' => ['required', 'string', 'max:50'],
            'churchTempDistrict' => ['required', 'string', 'max:255'],
            'churchTempCity' => ['required', 'string', 'max:255'],
            'churchTempState' => ['required', 'string', 'size:2'],
            'churchTempPhone' => ['nullable', 'string', 'max:20'],
            'churchTempEmail' => ['nullable', 'email', 'max:255'],
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
            'churchTempName' => 'nome completo da igreja',
            'churchTempPastor' => 'nome do pastor titular',
            'churchTempPostalCode' => 'CEP',
            'churchTempStreet' => 'logradouro',
            'churchTempNumber' => 'número',
            'churchTempDistrict' => 'bairro',
            'churchTempCity' => 'cidade',
            'churchTempState' => 'UF',
            'churchTempPhone' => 'telefone',
            'churchTempEmail' => 'e-mail',
        ];
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->squish()->lower()->ascii()->value();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->churchTempName = '';
        $this->churchTempPastor = '';
        $this->churchTempPostalCode = '';
        $this->churchTempStreet = '';
        $this->churchTempNumber = '';
        $this->churchTempDistrict = '';
        $this->churchTempCity = '';
        $this->churchTempState = '';
        $this->churchTempPhone = null;
        $this->churchTempEmail = null;
    }

    public function render(): View
    {
        return view('livewire.shared.create-church-temp-modal');
    }
}
