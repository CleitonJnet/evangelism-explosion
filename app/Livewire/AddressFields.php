<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Modelable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class AddressFields extends Component
{
    #[Modelable]
    public array $address = [
        'postal_code' => '',
        'street'      => '',
        'number'      => '',
        'complement'  => '',
        'district'    => '',
        'city'        => '',
        'state'       => '',
    ];

    public string $title = 'Endereço';
    public bool $requireDistrictCityState = true;

    public array $stateOptions = [
        ['value' => 'AC', 'label' => 'Acre'],
        ['value' => 'AL', 'label' => 'Alagoas'],
        ['value' => 'AP', 'label' => 'Amapá'],
        ['value' => 'AM', 'label' => 'Amazonas'],
        ['value' => 'BA', 'label' => 'Bahia'],
        ['value' => 'CE', 'label' => 'Ceará'],
        ['value' => 'DF', 'label' => 'Distrito Federal'],
        ['value' => 'ES', 'label' => 'Espírito Santo'],
        ['value' => 'GO', 'label' => 'Goiás'],
        ['value' => 'MA', 'label' => 'Maranhão'],
        ['value' => 'MT', 'label' => 'Mato Grosso'],
        ['value' => 'MS', 'label' => 'Mato Grosso do Sul'],
        ['value' => 'MG', 'label' => 'Minas Gerais'],
        ['value' => 'PA', 'label' => 'Pará'],
        ['value' => 'PB', 'label' => 'Paraíba'],
        ['value' => 'PR', 'label' => 'Paraná'],
        ['value' => 'PE', 'label' => 'Pernambuco'],
        ['value' => 'PI', 'label' => 'Piauí'],
        ['value' => 'RJ', 'label' => 'Rio de Janeiro'],
        ['value' => 'RN', 'label' => 'Rio Grande do Norte'],
        ['value' => 'RS', 'label' => 'Rio Grande do Sul'],
        ['value' => 'RO', 'label' => 'Rondônia'],
        ['value' => 'RR', 'label' => 'Roraima'],
        ['value' => 'SC', 'label' => 'Santa Catarina'],
        ['value' => 'SP', 'label' => 'São Paulo'],
        ['value' => 'SE', 'label' => 'Sergipe'],
        ['value' => 'TO', 'label' => 'Tocantins'],
    ];

    /** UX */
    public array $note = [
        'street'   => null,
        'district' => null,
        'city'     => null,
        'state'    => null,
    ];

    public bool $cepLoading = false;
    public ?string $cepError = null;
    public ?string $lastCepLookup = null;

    public function mount(string $title = 'Endereço'): void
    {
        $this->title = $title;
    }

    public function updatedAddressPostalCode($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) < 8) {
            $this->cepError = null;
            $this->lastCepLookup = null;
            $this->setCepNotesLoading(false);
        }
    }

    public function lookupCep(string $cep): void
    {
        $digits = preg_replace('/\D+/', '', (string) $cep);
        $digits = substr($digits, 0, 8);

        if (strlen($digits) !== 8) return;

        // Evita consultar o mesmo CEP repetidamente
        if ($this->lastCepLookup === $digits) {
            return;
        }

        $this->cepLoading = true;
        $this->cepError = null;
        $this->lastCepLookup = $digits;
        $this->setCepNotesLoading(true);

        try {

            $response = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(10)
                ->retry(2, 100)
                ->get("https://viacep.com.br/ws/{$digits}/json/");

            /** @var \Illuminate\Http\Client\Response $response */
            $data = $response->json();

            if (!empty($data['erro'])) {
                $this->cepError = 'CEP não encontrado. Verifique e tente novamente.';
                return;
            }

            $this->address['street']   = $data['logradouro'] ?? '';
            $this->address['district'] = $data['bairro'] ?? '';
            $this->address['city']     = $data['localidade'] ?? '';
            $this->address['state']    = $data['uf'] ?? '';

            $this->dispatch('reapply-cep-mask');

        } catch (ConnectionException $e) {
            $this->cepError = 'Sem conexão para consultar o CEP. Verifique sua internet e tente novamente. Recarregue a página e tente novamente.';
        } catch (RequestException $e) {
            $this->cepError = 'Não foi possível consultar o CEP agora. Tente novamente.';
        } finally {
            $this->cepLoading = false;
            $this->setCepNotesLoading(false);
        }
    }

    private function setCepNotesLoading(bool $loading): void
    {
        $msg = $loading ? 'Carregando...' : null;

        $this->note['street']   = $msg;
        $this->note['district'] = $msg;
        $this->note['city']     = $msg;
        $this->note['state']    = $msg;
    }

    public function render()
    {
        return view('livewire.address-fields');
    }
}
