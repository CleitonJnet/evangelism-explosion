# Dashboard Metrics Catalog

## Servicos de metricas

- `App\Services\Metrics\TrainingOverviewMetricsService`
  Reune a visao geral do treinamento usada nas telas de resumo, combinando inscricoes, STP, novas igrejas e financeiro.
- `App\Services\Metrics\TrainingRegistrationMetricsService`
  Calcula agrupamentos por igreja, totais de inscricoes e indicadores de comprovante, kit, credenciamento e pendencias.
- `App\Services\Metrics\TrainingFinanceMetricsService`
  Centraliza saldo do ministerio, repasse da igreja base e total recebido nas inscricoes pagas.
- `App\Services\Metrics\TrainingStpMetricsService`
  Resume sessoes STP, decisoes, acompanhamentos e contadores reaproveitados por mentorias e dashboards.
- `App\Services\Metrics\TrainingDiscipleshipMetricsService`
  Monta o quadro de sessoes/equipes, totais por coluna, alunos pendentes e regras para liberar novas sessoes.

## Reaproveitamento atual

- `teacher/director training view`
  Usa `TrainingOverviewMetricsService`.
- `teacher/director training registrations`
  Usa `TrainingRegistrationMetricsService`.
- `teacher/director training statistics`
  Usa `TrainingDiscipleshipMetricsService`.
- `mentor training overview/dashboard support`
  `MentorTrainingOverviewService` passou a reutilizar `TrainingStpMetricsService` para manter a mesma inteligencia de STP.
- `teacher dashboard`
  `TeacherDashboardService` reutiliza `TrainingRegistrationMetricsService`, `TrainingFinanceMetricsService`, `TrainingStpMetricsService` e `TrainingDiscipleshipMetricsService`.
- `director dashboard`
  `DirectorDashboardService` reutiliza a mesma base de metricas para consolidacao nacional.

## Uso futuro em dashboards

- Os dashboards agregados podem reutilizar os mesmos servicos para evitar divergencia entre paginas operacionais e cards consolidados.
- Quando houver apenas um treinamento no recorte, os dashboards devem refletir os mesmos totais produzidos por `TrainingOverviewMetricsService`.

## Regra de consistencia adotada

- inscricoes e igrejas: `TrainingRegistrationMetricsService`
- financeiro: `TrainingFinanceMetricsService`
- STP, decisoes e visitas agendadas: `TrainingStpMetricsService`
- discipulado paralelo: `TrainingDiscipleshipMetricsService`
- overview de pagina por treinamento: `TrainingOverviewMetricsService`

Essa divisao evita duplicacao de regra de negocio entre pagina operacional e dashboard agregado.
