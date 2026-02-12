<?php

namespace App\Livewire\Web\Event;

use App\Models\Training;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Registration extends Component
{
    #[Locked]
    public Training $event;

    /** Wizard step (entangled com Alpine) */
    public int $step = 1;

    /** Derivados do evento */
    public bool $isPaid = false;

    public float $eventPrice = 0.0;

    public string $eventPriceFormatted;

    public ?string $whatsappGroupUrl = null;

    /** PIX (apenas para eventos pagos) */
    public array $pix = [];

    /** Fase 1 */
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** Fase 2 */
    public string $ispastor = 'N';

    public string $mobile = '';

    public ?string $birth_date = null;

    public ?string $gender = null;

    /** Fase 3 */
    public string $church_name = '';

    public string $pastor_name = '';

    public string $phone_church = '';

    public ?string $church_email = null;

    /**
     * Endereços (agora ficam em ARRAYS para o componente filho AddressFields)
     * - O componente filho é #[Modelable] e faz wire:model direto aqui.
     */
    public array $participantAddress = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public array $churchAddress = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    /** Confirmações */
    public bool $agree_terms = false;

    public bool $agree_faith = false;

    /** Pagamento */
    public bool $payment_confirmed = false;

    /** Opções */
    public array $yesNoOptions = [
        ['value' => 'Y', 'label' => 'Sim'],
        ['value' => 'N', 'label' => 'Não'],
    ];

    public array $genderOptions = [
        ['value' => 'M', 'label' => 'Masculino'],
        ['value' => 'F', 'label' => 'Feminino'],
    ];

    public function mount(Training $event): void
    {
        $this->event = $event;

        // ====== Flags do evento (pago x gratuito) ======
        $rawPrice = $event->price ?? ($event->value ?? ($event->amount ?? ($event->fee ?? null)));
        $paidFlag = (bool) ($event->is_paid ?? ($event->paid ?? false));

        $this->eventPrice = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;
        $this->isPaid = $paidFlag || ($this->eventPrice > 0);
        $this->eventPriceFormatted = $this->eventPrice;

        // Link do grupo (coluna WhatsApp existe na migration: gpwhatsapp)
        $this->whatsappGroupUrl =
            $event->gpwhatsapp ??
            ($event->whatsapp_group_link ??
                ($event->whatsapp_link ?? ($event->whatsapp_group ?? ($event->whatsapp ?? null))));

        // evento grátis nunca vai para fase 4
        if (! $this->isPaid && $this->step > 3) {
            $this->step = 3;
        }
    }

    public function updatedStep($value): void
    {
        // Se o usuário navegar para a fase 4 em evento pago, garanta que o PIX esteja preparado
        if ($this->isPaid && (int) $value === 4 && empty($this->pix)) {
            $this->pix = $this->buildPixPayload();
        }

        // Evento gratuito nunca deve passar da fase 3
        if (! $this->isPaid && (int) $value > 3) {
            $this->step = 3;
        }
    }

    /** Regras por etapa (para o wizard) */
    public function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                // Login + participante
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'max:80'],
                'password_confirmation' => ['required', 'same:password'],

                'ispastor' => ['required', 'in:Y,N'],
                'mobile' => ['required', 'string', 'min:7', 'max:20'],
                'birth_date' => ['nullable', 'date'],
                'gender' => ['required', 'in:M,F'],

                // Se quiser exigir endereço do PARTICIPANTE, mova estas regras para "required"
                'participantAddress.postal_code' => ['nullable', 'string', 'max:12'],
                'participantAddress.street' => ['nullable', 'string', 'max:255'],
                'participantAddress.number' => ['nullable', 'string', 'max:30'],
                'participantAddress.complement' => ['nullable', 'string', 'max:255'],
                'participantAddress.district' => ['nullable', 'string', 'max:255'],
                'participantAddress.city' => ['nullable', 'string', 'max:255'],
                'participantAddress.state' => ['nullable', 'string', 'size:2'],
            ],
            2 => [
                // Dados da igreja
                'church_name' => ['required', 'string', 'min:2', 'max:255'],
                'pastor_name' => ['required', 'string', 'min:2', 'max:255'],
                'phone_church' => ['required', 'string', 'min:8', 'max:30'],
                'church_email' => ['nullable', 'email:rfc', 'max:255'],

                // Endereço da igreja (este você já exigia)
                'churchAddress.postal_code' => ['nullable', 'string', 'max:12'],
                'churchAddress.street' => ['nullable', 'string', 'max:255'],
                'churchAddress.number' => ['nullable', 'string', 'max:30'],
                'churchAddress.complement' => ['nullable', 'string', 'max:255'],
                'churchAddress.district' => ['required', 'string', 'min:2', 'max:255'],
                'churchAddress.city' => ['required', 'string', 'min:2', 'max:255'],
                'churchAddress.state' => ['required', 'string', 'size:2'],
            ],
            3 => [
                'agree_terms' => ['accepted'],
                'agree_faith' => ['accepted'],
            ],
            default => [],
        };
    }

    /** Regras finais (submit) */
    public function rules(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
        );
    }

    public function messages(): array
    {
        return [
            'agree_terms.accepted' => 'É necessário aceitar os termos e condições.',
            'agree_faith.accepted' => 'É necessário concordar com a declaração de fé.',
            'password_confirmation.same' => 'A confirmação de senha não confere.',
        ];
    }

    public function validateStep(int $step): bool
    {
        $this->resetErrorBag();

        try {
            $this->validate($this->rulesForStep($step));

            return true;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return false;
        }
    }

    public function submit(): void
    {
        $this->validate();

        if ($this->isPaid) {
            if ($this->step < 4) {
                $this->step = 4;

                if (empty($this->pix)) {
                    $this->pix = $this->buildPixPayload();
                }

                return;
            }

            // fase 4: valida confirmação
            $this->validate([
                'payment_confirmed' => ['accepted'],
            ], [
                'payment_confirmed.accepted' => 'Confirme o pagamento para concluir a inscrição.',
            ]);

            $this->dispatch('toast', type: 'success', message: 'Pagamento confirmado. Inscrição finalizada com sucesso!');

            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Inscrição finalizada com sucesso!');
    }

    /** Payload PIX modelo básico */
    protected function buildPixPayload(): array
    {
        return [
            'key' => '',
            'emv' => null,
            'qr_svg' => null,
            'qr_base64' => null,
        ];
    }

    public function validationAttributes(): array
    {
        return [
            // Step 1
            'name' => 'Nome Completo',
            'email' => 'E-mail',
            'password' => 'Senha',
            'password_confirmation' => 'Confirmação de senha',
            'ispastor' => 'É pastor?',
            'mobile' => 'Celular',
            'birth_date' => 'Data de Nascimento',
            'gender' => 'Gênero',

            // Endereço participante
            'participantAddress.postal_code' => 'CEP do Participante',
            'participantAddress.street' => 'Logradouro do Participante',
            'participantAddress.number' => 'Número do Participante',
            'participantAddress.complement' => 'Complemento do Participante',
            'participantAddress.district' => 'Bairro do Participante',
            'participantAddress.city' => 'Cidade do Participante',
            'participantAddress.state' => 'UF do Participante',

            // Step 2
            'church_name' => 'Nome completo da Igreja',
            'pastor_name' => 'Nome do pastor titular',
            'phone_church' => 'Telefone WhatsApp',
            'church_email' => 'E-mail da Igreja',

            // Endereço igreja
            'churchAddress.postal_code' => 'CEP da Igreja',
            'churchAddress.street' => 'Logradouro',
            'churchAddress.number' => 'Número',
            'churchAddress.complement' => 'Complemento',
            'churchAddress.district' => 'Bairro',
            'churchAddress.city' => 'Cidade',
            'churchAddress.state' => 'UF',

            // Step 3
            'agree_terms' => 'Termos e Condições',
            'agree_faith' => 'Declaração de Fé',

            // Step 4
            'payment_confirmed' => 'Confirmação de Pagamento',
        ];
    }

    public function render()
    {
        return view('livewire.web.event.registration');
    }
}
