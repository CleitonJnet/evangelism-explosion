<?php

namespace App\Services\Metrics;

use App\Helpers\MoneyHelper;
use App\Models\Training;

class TrainingFinanceMetricsService
{
    /**
     * @return array{
     *     paidStudentsCount: int,
     *     totalReceivedFromRegistrations: ?string,
     *     eeMinistryBalance: ?string,
     *     hostChurchExpenseBalance: ?string
     * }
     */
    public function build(Training $training): array
    {
        $paidStudentsCount = (int) $training->students()
            ->wherePivot('payment', true)
            ->count();

        $price = MoneyHelper::toFloat($training->getRawOriginal('price'));
        $discount = MoneyHelper::toFloat($training->getRawOriginal('discount')) ?? 0.0;
        $priceChurch = MoneyHelper::toFloat($training->getRawOriginal('price_church'));

        return [
            'paidStudentsCount' => $paidStudentsCount,
            'totalReceivedFromRegistrations' => $this->formatTotalReceived($price, $discount, $priceChurch, $paidStudentsCount),
            'eeMinistryBalance' => $this->formatEeMinistryBalance($price, $discount, $paidStudentsCount),
            'hostChurchExpenseBalance' => $this->formatHostChurchExpenseBalance($priceChurch, $paidStudentsCount),
        ];
    }

    private function formatEeMinistryBalance(?float $price, float $discount, int $paidStudentsCount): ?string
    {
        if ($price === null) {
            return null;
        }

        return MoneyHelper::format_money(($price - $discount) * $paidStudentsCount);
    }

    private function formatHostChurchExpenseBalance(?float $priceChurch, int $paidStudentsCount): ?string
    {
        if ($priceChurch === null) {
            return null;
        }

        return MoneyHelper::format_money($priceChurch * $paidStudentsCount);
    }

    private function formatTotalReceived(?float $price, float $discount, ?float $priceChurch, int $paidStudentsCount): ?string
    {
        if ($price === null) {
            return null;
        }

        return MoneyHelper::format_money(($price - $discount + ($priceChurch ?? 0.0)) * $paidStudentsCount);
    }
}
