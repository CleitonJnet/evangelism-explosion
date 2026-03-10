# Dashboard Infrastructure

Base técnica criada para futuros dashboards de Professor e Diretor.

## Dependências

- `chart.js`
- `chartjs-adapter-date-fns`
- `date-fns`

## Filtro temporal

- Enum central: `App\Support\Dashboard\Enums\DashboardPeriod`
- Opções disponíveis:
  - `quarter`
  - `semester`
  - `year`
- Padrão: `year`
- Persistência: query string `?period=...`
- Validação HTTP:
  - `App\Http\Requests\TeacherDashboardFilterRequest`
  - `App\Http\Requests\DirectorDashboardFilterRequest`
- Consumo:
  - `App\Http\Controllers\System\Teacher\DashboardController`
  - `App\Http\Controllers\System\Director\DashboardController`

## Estrutura PHP

- DTOs em `app/Support/Dashboard/Data`
  - `KpiData`
  - `ChartDatasetData`
  - `ChartData`
  - `TimeSeriesPointData`
  - `RankingRowData`
  - `RankingTableData`
  - `DashboardPayload`
- Builder de gráficos:
  - `App\Support\Dashboard\Builders\ChartPayloadBuilder`
- Builder demo:
  - `App\Services\Dashboard\DashboardDemoBuilder`

## Estrutura front-end

- Registro Chart.js:
  - `resources/js/charts/chart-registry.js`
- Builders JS:
  - `resources/js/charts/builders/base-config.js`
  - `resources/js/charts/builders/category-chart.js`
  - `resources/js/charts/builders/time-series-chart.js`
  - `resources/js/charts/builders/doughnut-chart.js`
- Blade components:
  - `resources/views/components/dashboard/chart.blade.php`
  - `resources/views/components/dashboard/period-filter.blade.php`

## Demo técnica

- Componente compartilhado:
  - `App\Livewire\Shared\Dashboard\InfrastructureDemoPage`
- Rotas internas:
  - `app.teacher.dashboard.infrastructure`
  - `app.director.dashboard.infrastructure`

## Reuso esperado

- KPIs e métricas consolidadas das páginas operacionais poderão ser transformados em `DashboardPayload`
- séries temporais, barras, roscas e rankings já têm formato padrão
- a camada JS só conhece o payload, não regras de negócio específicas de Professor ou Diretor
- `resources/js/charts/chart-registry.js` mantém uma única instância de listeners/observer para evitar duplicação e vazamento de instâncias em re-render Livewire

## Dashboards reais

- Professor:
  - serviço: `App\Services\Dashboard\TeacherDashboardService`
  - rota: `app.teacher.dashboard`
- Diretor:
  - serviço: `App\Services\Dashboard\DirectorDashboardService`
  - rota: `app.director.dashboard`
