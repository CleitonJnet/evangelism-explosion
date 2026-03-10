<?php

namespace App\Livewire\Shared\Dashboard;

use App\Services\Dashboard\DashboardDemoBuilder;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class InfrastructureDemoPage extends Component
{
    #[Url(as: 'period')]
    public string $period = 'year';

    public string $context = 'teacher';

    public function mount(string $context = 'teacher'): void
    {
        $this->context = in_array($context, ['teacher', 'director'], true)
            ? $context
            : 'teacher';
        $this->period = DashboardPeriod::fromValue($this->period)->value;
    }

    public function updatedPeriod(string $value): void
    {
        $this->period = DashboardPeriod::fromValue($value)->value;
    }

    #[Computed]
    public function selectedPeriod(): DashboardPeriod
    {
        return DashboardPeriod::fromValue($this->period);
    }

    /**
     * @return array{
     *     period: string,
     *     kpis: array<int, array{key: string, label: string, value: string|int|float, description: ?string, trend: ?string}>,
     *     charts: array<int, array{
     *         id: string,
     *         title: string,
     *         type: string,
     *         labels: array<int, string>,
     *         datasets: array<int, array{
     *             label: string,
     *             data: array<int, string|int|float|array{x: string, y: int|float}>,
     *             backgroundColor: string|array<int, string>|null,
     *             borderColor: string|array<int, string>|null,
     *             type: ?string,
     *             fill: bool
     *         }>,
     *         seriesType: string,
     *         height: int,
     *         options: array<string, mixed>
     *     }>,
     *     tables: array<int, array{
     *         id: string,
     *         title: string,
     *         columns: array<int, string>,
     *         rows: array<int, array{position: int, label: string, value: string|int|float, context: ?string}>
     *     }>
     * }
     */
    #[Computed]
    public function dashboard(): array
    {
        return app(DashboardDemoBuilder::class)
            ->build($this->selectedPeriod())
            ->toArray();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function periodOptions(): array
    {
        return DashboardPeriod::options();
    }

    public function render(): View
    {
        return view('livewire.shared.dashboard.infrastructure-demo-page');
    }
}
