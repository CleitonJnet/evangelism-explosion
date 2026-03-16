# Inventario Tecnico para Refatoracao Arquitetural Incremental

## Objetivo

Este documento registra o estado real do repositorio para preparar a migracao incremental da experiencia atual, hoje centrada em roles, para tres portais:

1. Base e Treinamentos
2. Staff / Governanca
3. Aluno

Escopo desta analise:

- nao altera comportamento;
- nao remove funcionalidades;
- nao cria migrations;
- nao redefine autorizacao por role;
- identifica o que pode ser reaproveitado, encapsulado ou reconstruido em camadas de portal.

## Snapshot do Sistema Atual

- Stack principal: Laravel 12, Livewire 4, Volt, TailwindCSS 4, Flux UI.
- Estrutura de navegacao atual: area publica + area autenticada separada por prefixos de role.
- Entrada da area autenticada: `routes/app/start.php`.
- Base de autorizacao: `Gate::define(...)` + policies em `app/Policies/*`.
- Dominios com maior maturidade tecnica: treinamento, STP/OJT, metricas, agenda, estoque.
- Dominios com maior acoplamento de UX a role: menus, layouts, rotas e views `pages/app/roles/*`.

Leituras principais desta inspecao:

- `routes/web.php`
- `routes/app/start.php`
- `routes/app/director.php`
- `routes/app/teacher.php`
- `routes/app/student.php`
- `routes/app/mentor.php`
- `routes/app/fieldworker.php`
- `app/Providers/AppServiceProvider.php`
- `app/Policies/*`
- `app/Support/TrainingAccess/*`
- `app/Services/*`
- `app/Livewire/Shared/Training/*`
- `app/Livewire/Pages/App/{Director,Teacher,Student,Mentor}/*`
- `resources/views/components/app/layouts/app/sidebar.blade.php`
- `resources/views/components/app/desktop-roles-menu.blade.php`

## Arquitetura Atual em Uma Frase

O sistema ja tem boa parte da regra de negocio centralizada em services, metrics e resolvers compartilhados, mas a experiencia autenticada ainda esta fortemente acoplada a prefixos de role, controllers paralelos e views duplicadas por area.

## Mapa de Rotas Atuais e Destino Provavel por Portal

### 1. Site publico

Arquivo base: `routes/web.php`

| Area atual | Rotas principais | Estado atual | Portal provavel |
| --- | --- | --- | --- |
| Home e institucional | `/`, `/donate`, `/about-ee/*` | site publico | fora dos 3 portais |
| Ministerios publicos | `/ministry/everyday-evangelism`, `/ministry/kids-ee` | site publico | fora dos 3 portais |
| Eventos publicos | `/event/list`, `/event/{id}/details`, `/event/{id}/register`, `/event/{id}/login`, `/event/{training}/programacao` | onboarding publico de treinamentos | Base e Treinamentos |

Observacao:

- a agenda publica ja e um subdominio funcional do portal Base e Treinamentos, mas hoje ainda vive no site publico.

### 2. Entrada autenticada

Arquivo base: `routes/app/start.php`

Comportamento atual:

- usuarios com uma role sao redirecionados automaticamente para o dashboard da role;
- usuarios com varias roles vao para `app.start`;
- o conceito de "contexto" visivel ao usuario e role, nao portal.

Destino provavel:

- manter roles como autorizacao;
- trocar a decisao de navegacao de "qual role abrir" para "qual portal abrir";
- preservar a tela `app.start` como camada de triagem/mapeamento durante a transicao.

### 3. Area Diretor

Arquivo base: `routes/app/director.php`

| Modulo atual | Rotas | Destino provavel |
| --- | --- | --- |
| Dashboard | `director/`, `director/dashboard/infrastructure` | Staff / Governanca |
| Igrejas e perfis | `director/church*`, `director/church/{church}/profile*` | Staff / Governanca |
| Ministerios e cursos | `director/ministry*`, `director/ministry/{ministry}/course*` | Base e Treinamentos |
| Treinamentos | `director/training*` e `director/trainings*` | Base e Treinamentos |
| Agenda operacional | `director/trainings/{training}/schedule*` | Base e Treinamentos |
| STP / OJT | `director/*/statistics`, `director/*/stp/approaches` | Base e Treinamentos |
| Inventario | `director/inventory*` | Staff / Governanca |
| Testemunhos do site | `director/testimonials` | Staff / Governanca |

Observacoes reais do arquivo:

- existem duas arvores paralelas para treinamento: `training/*` e `trainings/*`;
- ambas expoem essencialmente os mesmos fluxos;
- as rotas de agenda detalhada (`schedule-items`, `regenerate`) estao concentradas em `trainings/{training}`.

### 4. Area Teacher

Arquivo base: `routes/app/teacher.php`

| Modulo atual | Rotas | Destino provavel |
| --- | --- | --- |
| Dashboard | `teacher/`, `teacher/dashboard/infrastructure` | Base e Treinamentos |
| Igrejas | `teacher/churches*` | Staff / Governanca ou Base e Treinamentos, dependendo da experiencia final |
| Inventario | `teacher/inventory*` | Staff / Governanca |
| Treinamentos | `teacher/trainings*` | Base e Treinamentos |
| Agenda | `teacher/trainings/{training}/schedule` | Base e Treinamentos |
| STP / OJT | `teacher/trainings/{training}/statistics`, `stp/approaches` | Base e Treinamentos |

Observacoes:

- e a area com maior paralelismo em relacao a Diretor;
- muda menos a regra de negocio e mais o contexto de autorizacao e a superficie de UI.

### 5. Area Student

Arquivo base: `routes/app/student.php`

| Modulo atual | Rotas | Destino provavel |
| --- | --- | --- |
| Dashboard | `student/` | Aluno |
| Treinamentos do aluno | `student/training/training`, `student/training/training/{training}` | Aluno |

Observacoes:

- ha um indicio de naming inconsistente: `student/training/training`;
- o modulo atual e pequeno, mas o componente Livewire de visualizacao ja tem responsabilidade relevante: comprovante, estado de pagamento e dados do treinamento.

### 6. Areas laterais que afetam a refatoracao

| Area | Arquivo | Impacto |
| --- | --- | --- |
| Mentor | `routes/app/mentor.php` | depende do mesmo dominio de treinamento e STP; precisara encaixar em Base e Treinamentos |
| FieldWorker | `routes/app/fieldworker.php` | hoje quase vazio em rota, mas ja existe menu, views e dependencias historicas |
| Settings | `routes/app/settings.php` | atravessa todos os futuros portais |

## Controllers Atuais

### Controllers publicos

- `app/Http/Controllers/Web/SiteController.php`
- `app/Http/Controllers/Web/PublicEventScheduleController.php`

Papel:

- site institucional, lista/detalhe de eventos, login/cadastro publico e agenda publica em PDF/HTML.

### Controllers autenticados por role

Diretor:

- `System/Director/DashboardController`
- `ChurchController`
- `ProfileController`
- `MinistryController`
- `CourseController`
- `TrainingController`
- `OjtController`
- `StpApproachController`
- `InventoryController`
- `SiteController`

Teacher:

- `System/Teacher/DashboardController`
- `ChurchController`
- `TrainingController`
- `OjtController`
- `StpApproachController`
- `InventoryController`
- `TrainingScheduleController`
- controllers legados de curso/ministry/profile ainda existem, mas nao aparecem nas rotas atuais principais

Student:

- `System/Student/TrainingController`

Mentor:

- `System/Mentor/DashboardController`
- `TrainingController`
- `OjtController`
- `OjtSessionController`
- `OjtTeamReportController`

### Leitura arquitetural

Reaproveitamento parcial forte:

- `System/Director/TrainingController.php`
- `System/Teacher/TrainingController.php`

Motivo:

- ambos usam `TrainingIndexService`;
- ambos usam `UpdateTrainingTestimonyRequest` + `TestimonySanitizer`;
- a principal diferenca e a ability usada na policy (`view/update/delete` vs `viewTeacherContext/updateTeacherContext/deleteTeacherContext`) e a view retornada.

Mesmo padrao aparece, em menor escala, em:

- `Director/InventoryController.php` vs `Teacher/InventoryController.php`
- `Director/ChurchController.php` vs `Teacher/ChurchController.php`

Diagnostico:

- controllers sao candidatos fortes a wrapper por portal ou controller base compartilhado;
- nao sao ainda reaproveitamento integral porque a navegacao, o nome das rotas e parte das views continuam role-centric.

## Mapa de Componentes Livewire e Papel Atual

### Nucleo compartilhado de treinamento

Arquivos-base:

- `app/Livewire/Shared/Training/ViewPage.php`
- `app/Livewire/Shared/Training/SchedulePage.php`
- `app/Livewire/Shared/Training/RegistrationsPage.php`
- `app/Livewire/Shared/Training/StatisticsPage.php`
- `app/Livewire/Shared/Training/StpBoardPage.php`
- `app/Livewire/Shared/Training/ApproveChurchTempModal.php`
- `app/Livewire/Shared/Training/ChurchTempReviewModal.php`
- `app/Livewire/Shared/Training/Concerns/InteractsWithTrainingContext.php`

Papel atual:

- encapsular a parte mais reaproveitavel da experiencia de treinamento;
- separar capabilities, rotas de contexto e componentes dependentes de diretor/professor;
- permitir que a mesma pagina base rode em contexto `director` ou `teacher`.

Sinal importante:

- `InteractsWithTrainingContext` ja introduz um eixo conceitual de "contexto";
- hoje esse contexto ainda e `director` ou `teacher`;
- esse mesmo ponto pode evoluir depois para "portal" sem trocar a base de autorizacao.

### Diretor

Pastas principais:

- `app/Livewire/Pages/App/Director/Training/*`
- `app/Livewire/Pages/App/Director/Church/*`
- `app/Livewire/Pages/App/Director/Inventory/*`
- `app/Livewire/Pages/App/Director/Ministry/*`
- `app/Livewire/Pages/App/Director/Course/*`
- `app/Livewire/Pages/App/Director/Profile/*`
- `app/Livewire/Pages/App/Director/Website/Testimonials/*`

Leitura:

- treinamento e o modulo mais evoluido;
- inventario possui mais responsabilidade operacional do lado Diretor, incluindo:
- `DeliverMaterialModal`
- `MaterialCreateModal`
- `MaterialEditModal`
- `TransferModal`
- a tela `Director/Training/View.php` ja agrega materiais de curso e movimentos de estoque ligados ao treinamento.

### Teacher

Pastas principais:

- `app/Livewire/Pages/App/Teacher/Training/*`
- `app/Livewire/Pages/App/Teacher/Church/*`
- `app/Livewire/Pages/App/Teacher/Inventory/*`

Leitura:

- espelha grande parte da experiencia de Diretor;
- usa os componentes compartilhados de treinamento para view, agenda, inscricoes, estatisticas e board STP;
- difere mais por permissao e superficie funcional do que por dominio.

### Student

Pastas principais:

- `app/Livewire/Pages/App/Student/Training/Show.php`

Papel atual:

- exibe dados do treinamento do aluno;
- controla upload/remocao de comprovante de pagamento;
- calcula carga horaria a partir de `eventDates`;
- garante que o usuario autenticado esteja inscrito no treinamento.

Leitura:

- o portal Aluno ainda esta pequeno;
- ha dominio real para expandir sem reescrever a base: inscricao, pagamento, cronograma, trilhas futuras.

### Mentor

Pastas principais:

- `app/Livewire/Pages/App/Mentor/Training/*`
- `app/Livewire/Pages/App/Mentor/Ojt/*`
- `app/Livewire/Pages/App/Mentor/Dashboard.php`

Leitura:

- nao esta entre os tres portais alvo, mas depende diretamente do dominio Base e Treinamentos;
- deve ser tratado como contexto lateral do mesmo portal, nao como dominio isolado.

## Layouts, Views e Navegacao Atual

Arquivos chave:

- `resources/views/components/app/layouts/app/sidebar.blade.php`
- `resources/views/components/app/desktop-roles-menu.blade.php`
- `resources/views/components/app/menu-roles/*.blade.php`
- `resources/views/pages/app/roles/*`
- `resources/views/livewire/pages/app/*`

Diagnostico:

- o layout principal decide o menu a partir do prefixo de rota atual:
- `app.board.*`
- `app.director.*`
- `app.teacher.*`
- `app.facilitator.*`
- `app.fieldworker.*`
- `app.mentor.*`
- `app.student.*`
- isso acopla layout + navegacao + contexto visual a role, nao a dominio;
- `desktop-roles-menu.blade.php` reforca o mesmo modelo ao alternar entre roles.

Conclusao:

- a mudanca para portais vai passar primeiro por navegacao/layout;
- a base de policy pode permanecer;
- o layout e hoje um candidato claro a wrapper novo.

## Policies, Gates e Resolvers de Capability

### Gates registrados

Arquivo: `app/Providers/AppServiceProvider.php`

Gates declarados:

- `access-board`
- `access-director`
- `access-teacher`
- `access-facilitator`
- `access-fieldworker`
- `access-mentor`
- `access-student`
- `manageChurches`

Policies registradas:

- `UserPolicy`
- `TrainingPolicy`
- `StpApproachPolicy`
- `StpSessionPolicy`
- `StpTeamPolicy`
- `ChurchPolicy`
- `InventoryPolicy`

### Ponto mais importante para a refatoracao

Arquivos:

- `app/Support/TrainingAccess/TrainingCapabilityResolver.php`
- `app/Support/TrainingAccess/TrainingVisibilityScope.php`

O que eles fazem hoje:

- separam visibilidade de treinamentos por contexto `director`, `teacher`, `mentor` ou `auto`;
- resolvem capacidade de ver, editar, excluir, gerir agenda, ver financeiro, gerir mentores e ver discipulado;
- suportam abilities especiais de contexto professor via `summaryForTeacherContext()`.

Leitura arquitetural:

- esta e a melhor base do sistema para manter roles como autorizacao e migrar a UX para portais;
- a refatoracao deve se apoiar aqui, nao reescrever regras em controllers ou views.

### Policies relevantes

| Arquivo | Papel atual | Leitura para refatoracao |
| --- | --- | --- |
| `TrainingPolicy.php` | centraliza abilities gerais e contexto professor | reaproveitamento integral |
| `ChurchPolicy.php` | combina role com escopo de igrejas acessiveis | reaproveitamento integral |
| `InventoryPolicy.php` | separa Diretor de Professor por ownership | reaproveitamento integral |
| `RoleAccessPolicy.php` | controla entrada por role | reaproveitamento parcial; precisara conviver com portal selector |
| `Stp*Policy.php` | dependem de `TrainingCapabilityResolver` | reaproveitamento integral |

## Services por Dominio

### Dashboard e metricas

Arquivos:

- `app/Services/Dashboard/DirectorDashboardService.php`
- `app/Services/Dashboard/TeacherDashboardService.php`
- `app/Services/Metrics/TrainingOverviewMetricsService.php`
- `TrainingRegistrationMetricsService.php`
- `TrainingFinanceMetricsService.php`
- `TrainingStpMetricsService.php`
- `TrainingDiscipleshipMetricsService.php`
- `app/Support/Dashboard/*`

Leitura:

- as metricas sao compartilhaveis;
- os dashboards finais ainda sao separados por role;
- `DirectorDashboardService` e `TeacherDashboardService` sao wrappers de composicao sobre metricas comuns.

Classificacao:

- metricas e DTOs: reaproveitamento integral;
- services finais de dashboard: reaproveitamento parcial / wrapper novo.

### Treinamentos

Arquivos:

- `app/Services/Training/TrainingIndexService.php`
- `TrainingCreateStateService.php`
- `TeacherTrainingCreateService.php`
- `TeacherParticipantRegistrationProcessor.php`
- `TrainingMaterialDeliveryService.php`
- `MentorAssignmentService.php`
- `MentorTrainingOverviewService.php`
- `TestimonySanitizer.php`

Leitura:

- `TrainingIndexService` ja abstrai lista e visibilidade por contexto;
- criacao ainda tem servicos com naming fortemente ligado a Teacher;
- entrega de material e atribuicao de mentor ja estao em services de dominio, bons candidatos a reuse em portal.

Classificacao:

- `TrainingIndexService`, `TrainingMaterialDeliveryService`, `MentorAssignmentService`, `TestimonySanitizer`: reaproveitamento integral;
- `TeacherTrainingCreateService`, `TeacherParticipantRegistrationProcessor`: reaproveitamento parcial pelo naming e pelo contexto atual.

### Agenda / programacao

Arquivos:

- `app/Services/Schedule/TrainingScheduleGenerator.php`
- `TrainingScheduleTimelineService.php`
- `TrainingScheduleDayBlocksApplier.php`
- `TrainingScheduleBreakPolicy.php`
- `TrainingDayBlocksService.php`
- `TrainingScheduleResetService.php`
- `app/Http/Controllers/TrainingScheduleController.php`

Leitura:

- modulo robusto e claramente de dominio;
- baixo acoplamento com role;
- excelente base para Base e Treinamentos.

Classificacao:

- reaproveitamento integral.

### STP / OJT / discipulado

Arquivos:

- `app/Services/Stp/StpSessionService.php`
- `StpBoardService.php`
- `StpStatisticsService.php`
- `StpTeamFormationService.php`
- `StpApproachReportService.php`

Leitura:

- dominio bem encapsulado;
- dependente de policies e capability resolver;
- reaproveitavel no portal Base e Treinamentos e nos fluxos de Mentor.

Classificacao:

- reaproveitamento integral.

### Igrejas e governanca

Arquivos:

- `app/Services/Church/ChurchParticipantRegistrationProcessor.php`
- `app/Services/ChurchTempResolverService.php`
- `app/Services/TrainingNewChurchService.php`
- `app/Services/HostChurchReadModel.php`

Leitura:

- parte importante de governanca e triagem ja esta em services;
- fluxo de validacao de igreja temporaria e novo registro de igreja merece ficar no portal Staff / Governanca, mesmo quando disparado por treinamento.

Classificacao:

- reaproveitamento integral, com wrapper novo de UX.

### Estoque

Arquivos:

- `app/Services/Inventory/StockMovementService.php`
- `app/Models/Inventory.php`
- `app/Models/Material.php`
- `app/Models/MaterialComponent.php`
- `app/Models/StockMovement.php`

Leitura:

- dominio forte, transacional e reutilizavel;
- modelo suporta materiais simples, compostos, transferencias e consumo ligado a treinamento;
- parte mais acoplada ainda esta na UX de Diretor/Professor.

Classificacao:

- dominio de estoque: reaproveitamento integral;
- paginas e modais atuais: reaproveitamento parcial.

## Modulos Alvo e Base da Refatoracao

### Portal Base e Treinamentos

Base real ja existente:

- `routes/web.php` em `event/*`
- `routes/app/director.php` em `training*`, `trainings*`, `ministry*`, `course*`
- `routes/app/teacher.php` em `trainings*`
- `routes/app/mentor.php`
- `app/Support/TrainingAccess/*`
- `app/Services/Training/*`
- `app/Services/Schedule/*`
- `app/Services/Stp/*`
- `app/Services/Metrics/*`
- `app/Livewire/Shared/Training/*`

Leitura:

- este e o portal com melhor base reaproveitavel;
- a estrategia ideal e montar wrappers de portal sobre esse nucleo.

### Portal Staff / Governanca

Base real ja existente:

- `director/church*`
- `teacher/churches*`
- `director/inventory*`
- `teacher/inventory*`
- `director/testimonials`
- `app/Policies/ChurchPolicy.php`
- `app/Policies/InventoryPolicy.php`
- `app/Services/Church*`
- `app/Services/Inventory/StockMovementService.php`
- `app/Services/HostChurchReadModel.php`
- `app/Services/TrainingNewChurchService.php`

Leitura:

- a regra de negocio ja existe;
- a reorganizacao principal sera de navegacao, ownership de tela e vocabulario de portal.

### Portal Aluno

Base real ja existente:

- `routes/app/student.php`
- `app/Http/Controllers/System/Student/TrainingController.php`
- `app/Livewire/Pages/App/Student/Training/Show.php`
- `resources/views/pages/app/roles/student/*`
- `resources/views/livewire/pages/app/student/*`

Leitura:

- dominio menor, mas com baixo legado complexo;
- bom candidato a consolidacao cedo, desde que sem mexer em autorizacao nem persistencia.

## Classificacao dos Artefatos

### Reaproveitamento integral

- `app/Support/TrainingAccess/TrainingCapabilityResolver.php`
- `app/Support/TrainingAccess/TrainingVisibilityScope.php`
- `app/Policies/TrainingPolicy.php`
- `app/Policies/ChurchPolicy.php`
- `app/Policies/InventoryPolicy.php`
- `app/Policies/StpApproachPolicy.php`
- `app/Policies/StpSessionPolicy.php`
- `app/Policies/StpTeamPolicy.php`
- `app/Services/Training/TrainingIndexService.php`
- `app/Services/Training/TrainingMaterialDeliveryService.php`
- `app/Services/Training/MentorAssignmentService.php`
- `app/Services/Training/TestimonySanitizer.php`
- `app/Services/Schedule/*`
- `app/Services/Stp/*`
- `app/Services/Metrics/*`
- `app/Services/Inventory/StockMovementService.php`
- `app/Services/ChurchTempResolverService.php`
- `app/Services/TrainingNewChurchService.php`
- `app/Services/HostChurchReadModel.php`
- `app/Models/*`
- `app/Livewire/Shared/Training/*`

### Reaproveitamento parcial

- `app/Http/Controllers/System/Director/TrainingController.php`
- `app/Http/Controllers/System/Teacher/TrainingController.php`
- `app/Http/Controllers/System/Director/InventoryController.php`
- `app/Http/Controllers/System/Teacher/InventoryController.php`
- `app/Http/Controllers/System/Director/ChurchController.php`
- `app/Http/Controllers/System/Teacher/ChurchController.php`
- `app/Services/Dashboard/DirectorDashboardService.php`
- `app/Services/Dashboard/TeacherDashboardService.php`
- `app/Services/Training/TeacherTrainingCreateService.php`
- `app/Services/Training/TeacherParticipantRegistrationProcessor.php`
- `resources/views/pages/app/roles/director/*`
- `resources/views/pages/app/roles/teacher/*`
- `resources/views/livewire/pages/app/director/*`
- `resources/views/livewire/pages/app/teacher/*`

### Provavel wrapper novo

- layouts e menus de portal sobre `resources/views/components/app/layouts/app/sidebar.blade.php`
- seletor de contexto de entrada sobre `routes/app/start.php`
- controllers/paginas de portal que reaproveitem services/policies atuais
- dashboards agregadores por portal, compostos a partir de metricas e services existentes

### Provavel modulo novo

- camada de navegacao por portal
- mapa de landing/dashboard por portal
- contratos de contexto visual e breadcrumbs por portal
- possivel camada de "portal routing" ou nomes de rota orientados a portal

## Duplicacoes Relevantes Entre Diretor e Professor

### 1. Treinamento

Duplicacao de superficie:

- rotas paralelas de treinamento;
- controllers quase equivalentes;
- componentes Livewire com mesmos nomes de arquivo em:
- `Training/Index.php`
- `Training/Create.php`
- `Training/View.php`
- `Training/Schedule.php`
- `Training/Registrations.php`
- `Training/Statistics.php`
- `Training/StpApproachesBoard.php`
- diversos modais auxiliares

Sinal importante:

- as classes correspondentes entre Diretor e Professor nao sao identicas byte a byte;
- varias, porem, sao wrappers finos sobre as bases em `app/Livewire/Shared/Training/*`.

Duplicacoes de view com mesmo conteudo:

- `training/church-temp-review-modal.blade.php`
- `training/create-mentor-user-modal.blade.php`
- `training/event-teachers.blade.php`
- `training/manage-mentors-modal.blade.php`
- `training/edit-event-banner-modal.blade.php`
- `training/edit-finance-modal.blade.php`
- `training/create-church-modal.blade.php`
- `training/approve-church-temp-modal.blade.php`
- `training/edit-event-dates-modal.blade.php`

Conclusao:

- treinamento ja aponta para consolidacao por wrapper de contexto, nao por reescrita do dominio.

### 2. Igreja

Duplicacoes:

- `Church/Index.php`
- `Church/View.php`
- `CreateModal.php`
- `EditModal.php`
- `CreateParticipantModal.php`

Leitura:

- dominio similar;
- autorizacao e escopo diferem mais que o fluxo base.

### 3. Inventario

Duplicacoes:

- `Inventory/Index.php`
- `Inventory/View.php`
- `Inventory/EditInventoryModal.php`
- `Inventory/StockActionModal.php`

Diferenca funcional importante:

- Diretor possui operacoes adicionais de administracao de materiais e transferencias;
- Professor opera subconjunto mais restrito.

## Pontos que Ja Favorecem Reutilizacao

- policies e gates ja estao centralizados;
- `TrainingCapabilityResolver` e `TrainingVisibilityScope` ja separam autorizacao de superficie;
- `TrainingIndexService` ja conhece contexto e status tabs;
- modulo de agenda (`Schedule/*`) ja esta isolado do layout por role;
- metricas de treinamento, financeiro, inscricao e discipulado estao em services independentes;
- estoque ja tem service transacional forte (`StockMovementService`);
- `Livewire/Shared/Training/*` ja prova que o sistema suporta uma camada compartilhada acima do dominio.

## Pontos que Exigirao Construcao Nova

- taxonomia e URL strategy por portal;
- layouts, sidebars e menus orientados a portal;
- criterio de landing inicial no login ou em `app.start`;
- composicao de dashboards por portal, sem perder recorte por role;
- padrao de wrappers para manter as rotas antigas funcionando durante a migracao;
- consolidacao da experiencia de aluno alem da tela unica atual.

## Riscos Tecnicos e Dependencias

### Riscos

- forte acoplamento de layout e menu ao prefixo de rota atual;
- coexistencia de rotas `training/*` e `trainings/*` aumenta custo de migracao;
- muita view duplicada por role pode espalhar divergencias silenciosas;
- parte da experiencia Teacher depende de abilities especificas (`*TeacherContext`);
- inventario mistura governanca com operacao de treinamento em algumas telas de Diretor;
- portal Aluno ainda tem naming e controller simples, com pouca padronizacao comparado ao resto.

### Dependencias a respeitar

- roles continuam sendo a base da autorizacao;
- gates e policies nao devem ser substituidos por regras no front;
- `start.php` continua sendo ponto central de entrada durante a transicao;
- modulo publico de eventos nao pode quebrar URLs existentes;
- models e tabelas atuais precisam permanecer estaveis nesta etapa.

## Arquivos e Modulos que Devem Ser Base da Refatoracao

### Base e Treinamentos

- `app/Support/TrainingAccess/TrainingCapabilityResolver.php`
- `app/Support/TrainingAccess/TrainingVisibilityScope.php`
- `app/Services/Training/TrainingIndexService.php`
- `app/Services/Training/TrainingMaterialDeliveryService.php`
- `app/Services/Training/MentorAssignmentService.php`
- `app/Services/Schedule/TrainingScheduleGenerator.php`
- `app/Services/Schedule/TrainingScheduleTimelineService.php`
- `app/Services/Stp/StpBoardService.php`
- `app/Services/Stp/StpStatisticsService.php`
- `app/Livewire/Shared/Training/*`
- `app/Http/Controllers/System/Director/TrainingController.php`
- `app/Http/Controllers/System/Teacher/TrainingController.php`
- `routes/app/director.php`
- `routes/app/teacher.php`
- `routes/app/mentor.php`

### Staff / Governanca

- `app/Policies/ChurchPolicy.php`
- `app/Policies/InventoryPolicy.php`
- `app/Services/ChurchTempResolverService.php`
- `app/Services/HostChurchReadModel.php`
- `app/Services/TrainingNewChurchService.php`
- `app/Services/Inventory/StockMovementService.php`
- `app/Http/Controllers/System/Director/ChurchController.php`
- `app/Http/Controllers/System/Teacher/ChurchController.php`
- `app/Http/Controllers/System/Director/InventoryController.php`
- `app/Http/Controllers/System/Teacher/InventoryController.php`
- `app/Livewire/Pages/App/Director/Inventory/*`
- `app/Livewire/Pages/App/Teacher/Inventory/*`

### Aluno

- `routes/app/student.php`
- `app/Http/Controllers/System/Student/TrainingController.php`
- `app/Livewire/Pages/App/Student/Training/Show.php`
- `resources/views/pages/app/roles/student/*`
- `resources/views/livewire/pages/app/student/*`

## Recomendacao de Proximos Passos Imediatos

1. Definir um mapa explicito de portal x role x modulo, sem mexer ainda em rotas antigas.
2. Extrair um contrato de "contexto de portal" para layouts e navegacao, inspirado no padrao ja usado em `InteractsWithTrainingContext`.
3. Escolher o primeiro piloto de migracao: treinamento e o melhor candidato porque ja possui shared components, resolver de capability e services maduros.
4. Congelar proliferacao de novas views duplicadas entre Diretor e Professor enquanto a camada de portal nao nasce.
5. Introduzir wrappers novos de portal mantendo URLs atuais vivas, para reduzir risco de regressao.

## Sintese

O repositorio nao precisa de reescrita total para chegar aos tres portais. A base de dominio ja esta razoavelmente pronta em treinamento, agenda, STP, metricas, igrejas e estoque. O principal trabalho arquitetural esta em desacoplar navegacao, layout e naming de rota da role atual, enquanto se preserva a autorizacao ja consolidada em gates, policies e resolvers.
