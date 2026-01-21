<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\Church;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public ?string $church_logo = null;
    public string $church_name = '';
    public string $pastor_name = '';
    public string $phone_church = '';
    public ?string $church_email = null;
    public ?string $church_contact = null;
    public ?string $church_contact_phone = null;
    public ?string $church_contact_email = null;
    public ?string $church_notes = null;

    public array $churchAddress = [
        'postal_code' => '',
        'street'      => '',
        'number'      => '',
        'complement'  => '',
        'district'    => '',
        'city'        => '',
        'state'       => '',
    ];

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'church_logo' => ['nullable', 'string', 'max:255'],
            'church_name' => ['required', 'string', 'max:255'],
            'pastor_name' => ['required', 'string', 'max:255'],
            'phone_church' => ['required', 'string', 'max:255'],
            'church_email' => ['nullable', 'email', 'max:255'],
            'church_contact' => ['required', 'string', 'max:255'],
            'church_contact_phone' => ['required', 'string', 'max:255'],
            'church_contact_email' => ['required', 'email', 'max:255'],
            'church_notes' => ['required', 'string'],
            'churchAddress.postal_code' => ['required', 'string', 'max:20'],
            'churchAddress.street' => ['required', 'string', 'max:255'],
            'churchAddress.number' => ['required', 'string', 'max:20'],
            'churchAddress.complement' => ['nullable', 'string', 'max:255'],
            'churchAddress.district' => ['required', 'string', 'max:255'],
            'churchAddress.city' => ['required', 'string', 'max:255'],
            'churchAddress.state' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve ser um e-mail válido.',
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
            'pastor_name' => 'nome do pastor',
            'phone_church' => 'telefone da igreja',
            'church_email' => 'e-mail da igreja',
            'church_contact' => 'nome do contato',
            'church_contact_phone' => 'telefone do contato',
            'church_contact_email' => 'e-mail do contato',
            'church_notes' => 'comentários sobre a igreja',
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
        if (!array_key_exists($property, $this->rules())) {
            return;
        }

        $this->validateOnly($property);
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Church::create([
            'logo' => $validated['church_logo'],
            'name' => $validated['church_name'],
            'pastor' => $validated['pastor_name'],
            'email' => $validated['church_email'],
            'phone' => $validated['phone_church'],
            'contact' => $validated['church_contact'],
            'contact_phone' => $validated['church_contact_phone'],
            'contact_email' => $validated['church_contact_email'],
            'notes' => $validated['church_notes'],
            'postal_code' => $validated['churchAddress']['postal_code'],
            'street' => $validated['churchAddress']['street'],
            'number' => $validated['churchAddress']['number'],
            'complement' => $validated['churchAddress']['complement'],
            'district' => $validated['churchAddress']['district'],
            'city' => $validated['churchAddress']['city'],
            'state' => $validated['churchAddress']['state'],
        ]);

        session()->flash('success', 'Igreja cadastrada com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.church.create');
    }
}
