<?php

namespace App\Support\Portals\Enums;

enum Portal: string
{
    case Base = 'base';
    case Staff = 'staff';
    case Student = 'student';

    public function key(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Base => 'Base e Treinamentos',
            self::Staff => 'Staff / Governanca',
            self::Student => 'Aluno',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Base => 'Experiencia operacional para base ministerial, treinamentos e acompanhamento.',
            self::Staff => 'Experiencia de governanca, staff e coordenacao institucional.',
            self::Student => 'Experiencia do aluno, trilha de participacao e materiais de estudo.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Base => 'squares-2x2',
            self::Staff => 'building-office-2',
            self::Student => 'academic-cap',
        };
    }

    public function entryRoute(): string
    {
        return match ($this) {
            self::Base => 'app.portal.base.dashboard',
            self::Staff => 'app.portal.staff.dashboard',
            self::Student => 'app.portal.student.dashboard',
        };
    }

    public static function defaultOrder(): array
    {
        return [
            self::Base,
            self::Staff,
            self::Student,
        ];
    }
}
