<?php

namespace App;

enum TrainingStatus: int
{
    case Planning = 0;
    case Scheduled = 1;
    case Canceled = 2;
    case Completed = 3;

    public function key(): string
    {
        return match ($this) {
            self::Planning => 'planning',
            self::Scheduled => 'scheduled',
            self::Canceled => 'canceled',
            self::Completed => 'completed',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planejamento',
            self::Scheduled => 'Agendado',
            self::Canceled => 'Cancelado',
            self::Completed => 'Concluído',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
