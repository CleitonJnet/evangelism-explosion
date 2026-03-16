# Portal Base e Treinamentos

## Blocos escolhidos

- Minha Base
  - igreja-base vinculada ao usuario;
  - leitura de base anfitria ja configurada;
  - alertas de acervo/estoque com impacto local.
- Treinamentos em que Sirvo
  - proximos treinamentos com atuacao direta;
  - frentes em andamento;
  - pendencias de relato.
- Eventos da Base
  - agenda sediada pela igreja-base;
  - eventos concluidos recentes;
  - pendencias de programacao.
- Faixa de operacao do portal
  - pendencias operacionais agrupadas por contexto;
  - atalhos rapidos por frente de atuacao;
  - snapshots das fontes reaproveitadas.

## Fontes de dados reaproveitadas

- `App\Services\Dashboard\TeacherDashboardService`
  - snapshot de treinamentos futuros, pagantes e pendencias de programacao.
- `App\Services\Dashboard\DirectorDashboardService`
  - snapshot de alcance de igrejas, novas igrejas e gargalos financeiros.
- `App\Services\Training\MentorTrainingOverviewService`
  - resumo de mentoria, times e sessoes concluidas.
- `App\Support\TrainingAccess\TrainingVisibilityScope`
  - visibilidade automatica de treinamentos sem replicar regra de acesso.
- `App\Support\TrainingAccess\TrainingCapabilityResolver`
  - roteamento contextual para detalhes, programacao e relato.
- Modulo `Church`
  - igreja-base do usuario e eventos sediados por `church_id`.
- Modulo `Inventory`
  - estoques sob responsabilidade do usuario ou visao ampliada do diretor;
  - contagem de itens abaixo do estoque minimo.

## Decisoes de UX e reaproveitamento

- O portal foi orientado por frentes de atuacao, nao por cards isolados de role.
- O dashboard inicial comunica tres eixos fixos: base local, servico em treinamentos e eventos sediados.
- A navegacao do sidebar foi reorganizada para refletir o portal, mantendo links para fluxos legados quando necessario.
- As acoes detalhadas continuam apontando para modulos existentes de treinamento, igreja e estoque, evitando reescrita da operacao atual.
- Breadcrumbs e shell visual usam os componentes de portal ja introduzidos no Portal do Aluno para manter consistencia.

## Gaps para proximas etapas

- Fieldworker e facilitador ainda entram mais como contexto de acesso do que como fontes de dados dedicadas.
- Eventos da base usam `church_id` como referencia de sede; ainda nao ha uma camada especifica para operacao de anfitriao separada da igreja-base.
- Alertas de acervo consideram principalmente estoque minimo e status do estoque; podem evoluir para consumo por evento e reserva de materiais.
- Pendencias de relatorio usam a ausencia de `notes/testimony` como sinal inicial; podem amadurecer para um fluxo mais explicito de fechamento operacional.
- Vale evoluir as paginas de `Minha Base`, `Treinamentos em que Sirvo` e `Eventos da Base` com filtros e indicadores mais profundos, mas sem quebrar os modulos antigos.
