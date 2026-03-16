# Arquitetura do Modulo de Relatorios do Evento

## Objetivo

Introduzir uma infraestrutura persistente e extensivel para o fluxo de relatorios do evento sem alterar tabelas centrais de forma destrutiva e sem quebrar o legado.

O modulo cobre tres atores principais do dominio:

- igreja anfitria ou base local responsavel pela execucao do evento;
- professor que orienta a execucao segundo o padrao ministerial;
- staff que recebe, comenta e revisa os relatorios para governanca.

## Decisao de modelagem

A modelagem foi estruturada em tres tabelas novas:

### `event_reports`

Agregado principal do modulo. Cada linha representa um relatorio contextualizado por `training` e por papel emissor.

Campos centrais:

- `training_id`: vinculo ao evento existente;
- `church_id`: vinculo opcional com a igreja anfitria, preservando o contexto da base local;
- `type`: tipo do relatorio, atualmente `church` ou `teacher`;
- `status`: estado do fluxo, atualmente `draft`, `submitted`, `needs_revision` e `reviewed`;
- `schema_version`: versao do contrato do relatorio para futuras evolucoes;
- `title`, `summary`, `context`, `meta`: envelope flexivel para metadados, resumo e contexto operacional;
- `created_by_user_id`, `updated_by_user_id`, `submitted_by_user_id`, `last_reviewed_by_user_id`: trilha principal de autoria e governanca;
- `submitted_at`, `review_requested_at`, `reviewed_at`: marcos do ciclo de vida.

Restricoes:

- `unique(training_id, type)` garante um relatorio canonico por tipo dentro de cada evento;
- a relacao com `training` usa cascade delete porque o relatorio nao faz sentido sem o evento;
- relacoes com usuarios usam `nullOnDelete` para preservar historico de governanca.

### `event_report_sections`

Tabela de secoes estruturadas do relatorio.

Motivacao:

- evitar acoplamento prematuro do formulario inteiro ao schema relacional;
- permitir que blocos do relatorio evoluam de forma incremental;
- suportar layouts diferentes para relatorio da igreja e do professor sem nova tabela por formulario.

Campos centrais:

- `event_report_id`;
- `key`: identificador funcional da secao, como `attendance`, `finance`, `testimonies`;
- `title`;
- `position`;
- `content` em JSON;
- `meta` em JSON.

Restricao:

- `unique(event_report_id, key)` garante uma secao canonica por chave dentro do relatorio.

### `event_report_reviews`

Trilha aditiva de revisao e comentarios do staff.

Campos centrais:

- `event_report_id`;
- `reviewer_user_id`;
- `outcome`: `commented`, `changes_requested` ou `approved`;
- `comment`;
- `payload` em JSON;
- `reviewed_at`.

Essa tabela permite historico de governanca sem sobrescrever observacoes anteriores.

## Enums do modulo

Foram introduzidos enums para explicitar o contrato do dominio:

- `App\Enums\EventReportType`
- `App\Enums\EventReportStatus`
- `App\Enums\EventReportReviewOutcome`

Isso reduz string solta no codigo e facilita futuras extensoes, como novos tipos de relatorio ou novos desfechos de revisao.

## Models e relacionamentos

### Novos models

- `App\Models\EventReport`
- `App\Models\EventReportSection`
- `App\Models\EventReportReview`

### Relacionamentos adicionados ao legado

- `Training::eventReports()`
- `Training::churchEventReport()`
- `Training::teacherEventReport()`
- `Church::eventReports()`
- `User::createdEventReports()`
- `User::updatedEventReports()`
- `User::submittedEventReports()`
- `User::reviewedEventReports()`
- `User::eventReportReviews()`

Esses relacionamentos sao apenas aditivos e nao alteram o comportamento atual das areas existentes.

## Politica de autorizacao

Policy nova: `App\Policies\EventReportPolicy`

Regras adotadas:

- relatorio da igreja segue a capability existente `submitChurchEventReport`;
- relatorio do professor segue a capability existente `submitTeacherEventReport`;
- revisao e comentario ficam reservados a usuarios com acesso ao `Portal::Staff`.

Com isso, o modulo reaproveita a matriz de capabilities ja prevista no portal base em vez de abrir uma nova trilha paralela de autorizacao.

## Services do modulo

### `App\Services\EventReports\EventReportService`

Responsabilidades:

- garantir a existencia do relatorio canonico por evento e tipo;
- salvar rascunho;
- submeter relatorio;
- sincronizar secoes estruturadas em tabela propria.

Escolhas importantes:

- o relatorio nasce em `draft`;
- reenvio apos ajustes volta para `submitted`;
- a sincronizacao de secoes remove chaves antigas que nao vierem mais no payload, preservando a coerencia do agregado.

### `App\Services\EventReports\EventReportReviewService`

Responsabilidades:

- registrar comentarios do staff;
- solicitar ajustes;
- aprovar ou marcar como revisado.

Efeito sobre o agregado:

- `changes_requested` move o relatorio para `needs_revision`;
- `approved` move o relatorio para `reviewed`;
- `commented` preserva o estado atual e adiciona historico.

## Compatibilidade com o legado

A implementacao foi desenhada para nao quebrar o sistema atual:

- nenhuma tabela existente foi removida ou alterada de forma destrutiva;
- as capabilities ja existentes de relatorio foram reaproveitadas;
- o modulo pode ser acoplado gradualmente a Livewire, controllers, abas e menus sem refazer a persistencia.

## Extensoes futuras facilitadas por este desenho

- novos tipos de relatorio alem de igreja e professor;
- templates diferentes por curso ou ministerio via `schema_version` e `meta`;
- checklist de revisao por staff em `payload`;
- dashboards e filas de governanca filtrando por `status`, `type` e timestamps;
- auditoria adicional por versao de secao, se o fluxo passar a exigir trilha editorial completa.
