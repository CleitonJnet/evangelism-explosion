<?php

namespace App\Support\Dashboard\Enums;

use Carbon\CarbonImmutable;

enum DashboardPeriod: string
{
    case Quarter = 'quarter';
    case Semester = 'semester';
    case Year = 'year';

    public static function default(): self
    {
        return self::Year;
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $period): string => $period->value,
            self::cases(),
        );
    }

    public static function fromValue(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }

    public function label(): string
    {
        return match ($this) {
            self::Quarter => 'Trimestral',
            self::Semester => 'Semestral',
            self::Year => 'Anual',
        };
    }

    public function months(): int
    {
        return match ($this) {
            self::Quarter => 3,
            self::Semester => 6,
            self::Year => 12,
        };
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function range(?CarbonImmutable $reference = null): array
    {
        $reference ??= CarbonImmutable::now();

        [$start, $end] = match ($this) {
            self::Quarter => $this->quarterRange($reference),
            self::Semester => $this->semesterRange($reference),
            self::Year => [$reference->startOfYear(), $reference->endOfYear()],
        };

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function quarterRange(CarbonImmutable $reference): array
    {
        $startMonth = ((int) floor(($reference->month - 1) / 3) * 3) + 1;
        $start = $reference->startOfYear()->month($startMonth)->startOfMonth();

        return [$start, $start->addMonths(2)->endOfMonth()];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function semesterRange(CarbonImmutable $reference): array
    {
        $startMonth = $reference->month <= 6 ? 1 : 7;
        $start = $reference->startOfYear()->month($startMonth)->startOfMonth();

        return [$start, $start->addMonths(5)->endOfMonth()];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $period): array => [
                'value' => $period->value,
                'label' => $period->label(),
            ],
            self::cases(),
        );
    }
}
