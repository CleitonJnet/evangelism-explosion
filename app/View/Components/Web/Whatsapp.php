<?php

namespace App\View\Components\Web;

use App\Helpers\PhoneHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Whatsapp extends Component
{
    public ?string $phone;

    public string $title;

    public string $uid;

    public array $ddis;

    public array $subjects = [
        'Adotar um projeto',
        'Esperança para Crianças',
        'Evangelismo Eficaz',
        'Inscrição em treinamentos',
        'Levar um treinamento à minha igreja',
        'Pedido de Materiais',
        'Treinamento de líderes',
        'Outros assuntos',
    ];

    public function __construct(
        ?string $phone = null,
        string $title = 'WhatsApp Widget'
    ) {
        $this->phone = PhoneHelper::normalize($phone);
        $this->title = $title;
        $this->uid = 'wa-'.uniqid();

        $this->ddis = [
            ['code' => '+27',  'flag' => '🇿🇦', 'name' => 'África do Sul', 'sample' => '0123456789'],
            ['code' => '+244', 'flag' => '🇦🇴', 'name' => 'Angola', 'sample' => '912345678'],
            ['code' => '+54',  'flag' => '🇦🇷', 'name' => 'Argentina', 'sample' => '1112345678'],
            ['code' => '+61',  'flag' => '🇦🇺', 'name' => 'Austrália', 'sample' => '012345678'],
            ['code' => '+591', 'flag' => '🇧🇴', 'name' => 'Bolívia', 'sample' => '721234567'],
            ['code' => '+55',  'flag' => '🇧🇷', 'name' => 'Brasil', 'locales' => ['pt-BR'], 'sample' => '11900000000'],
            ['code' => '+238', 'flag' => '🇨🇻', 'name' => 'Cabo Verde', 'sample' => '12345678'],
            ['code' => '+56',  'flag' => '🇨🇱', 'name' => 'Chile', 'sample' => '912345678'],
            ['code' => '+86',  'flag' => '🇨🇳', 'name' => 'China', 'sample' => '12345678901'],
            ['code' => '+57',  'flag' => '🇨🇴', 'name' => 'Colômbia', 'sample' => '3001234567'],
            ['code' => '+53',  'flag' => '🇨🇺', 'name' => 'Cuba', 'sample' => '512345678'],
            ['code' => '+593', 'flag' => '🇪🇨', 'name' => 'Equador', 'sample' => '991234567'],
            ['code' => '+34',  'flag' => '🇪🇸', 'name' => 'Espanha', 'sample' => '123456789'],
            ['code' => '+1',   'flag' => '🇺🇸', 'name' => 'EUA / Canadá', 'sample' => '5555555555'],
            ['code' => '+33',  'flag' => '🇫🇷', 'name' => 'França', 'sample' => '012345678'],
            ['code' => '+49',  'flag' => '🇩🇪', 'name' => 'Alemanha', 'sample' => '01234567890'],
            ['code' => '+245', 'flag' => '🇬🇼', 'name' => 'Guiné-Bissau', 'sample' => '12345678'],
            ['code' => '+91',  'flag' => '🇮🇳', 'name' => 'Índia', 'sample' => '12345678901'],
            ['code' => '+39',  'flag' => '🇮🇹', 'name' => 'Itália', 'sample' => '123456789'],
            ['code' => '+81',  'flag' => '🇯🇵', 'name' => 'Japão', 'sample' => '0312345678'],
            ['code' => '+52',  'flag' => '🇲🇽', 'name' => 'México', 'sample' => '5512345678'],
            ['code' => '+258', 'flag' => '🇲🇿', 'name' => 'Moçambique', 'sample' => '821234567'],
            ['code' => '+64',  'flag' => '🇳🇿', 'name' => 'Nova Zelândia', 'sample' => '021234567'],
            ['code' => '+595', 'flag' => '🇵🇾', 'name' => 'Paraguai', 'sample' => '991234567'],
            ['code' => '+51',  'flag' => '🇵🇪', 'name' => 'Peru', 'sample' => '011234567'],
            ['code' => '+351', 'flag' => '🇵🇹', 'name' => 'Portugal', 'locales' => ['pt-PT'], 'sample' => '912345678'],
            ['code' => '+239', 'flag' => '🇸🇹', 'name' => 'São Tomé e Príncipe', 'sample' => '12345678'],
            ['code' => '+598', 'flag' => '🇺🇾', 'name' => 'Uruguai', 'sample' => '991234567'],
            ['code' => '+58',  'flag' => '🇻🇪', 'name' => 'Venezuela', 'sample' => '412345678'],
            ['code' => '+44',  'flag' => '🇬🇧', 'name' => 'Reino Unido', 'sample' => '0123456789'],
        ];

        // ordenar alfabeticamente pelo nome
        usort($this->ddis, fn ($a, $b) => strcmp($a['name'], $b['name']));
    }

    public function render(): View|Closure|string
    {
        return view('components.web.whatsapp');
    }
}
