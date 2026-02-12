<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class WeekHelper
{
    /**
     * Retorna o nome do dia da semana com a primeira letra maiúscula.
     */
    public static function dayName(string|CarbonInterface $date, ?string $locale = null): string
    {
        $locale = $locale ?? config('app.locale');

        return Str::ucfirst(
            Carbon::parse($date)->locale($locale)->isoFormat('dddd'),
        );
    }
}
