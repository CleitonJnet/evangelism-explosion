# Matriz de Capabilities do Portal Base e Treinamentos

## Objetivo

Concentrar a autorizacao do `Portal Base e Treinamentos` em uma matriz explicita de capabilities, sem substituir os roles existentes.

Os roles continuam sendo a base de autorizacao primaria. A matriz adiciona contexto de dominio para responder perguntas como:

- o usuario esta atuando como igreja responsavel local pela base?
- o usuario esta servindo no treinamento como professor ou mentor?
- o usuario esta no papel institucional da base ou apenas em uma frente ministerial do evento?

## Servico central

Servico implementado: `App\Services\Portals\PortalBaseCapabilityService`

Responsabilidades:

- calcular capabilities institucionais da base sem depender de Blade;
- calcular capabilities contextuais de um evento hospedado ou servido;
- expor a mesma logica para gates, policies, controllers, menus e abas;
- fornecer adaptacao legada para o `TrainingCapabilityResolver` enquanto o portal ainda reutiliza componentes existentes.

## Capabilities explicitas

### Base institucional

- `viewBaseOverview`
- `manageBaseMembers`
- `viewBaseParticipants`
- `viewBaseInventory`

### Contexto de evento e treinamento

- `viewServedTrainings`
- `manageTrainingRegistrations`
- `manageEventSchedule`
- `manageMentors`
- `manageFacilitators`
- `submitChurchEventReport`
- `submitTeacherEventReport`
- `viewEventMaterials`

## Leitura de dominio

### Gestor da base

Hoje corresponde ao papel contextual de quem pode gerir a frente institucional da base dentro do portal.

Regra atual:

- `Director`
- `FieldWorker` com igreja-base vinculada

Capabilities principais:

- `viewBaseOverview`
- `manageBaseMembers`
- `viewBaseParticipants`
- `viewBaseInventory`

### Anfitriao

Usuario vinculado a uma igreja que sedia o evento atual.

Regra atual:

- usuario com `church_id` igual ao `church_id` do treinamento
- com role relevante no Portal Base (`Teacher`, `Mentor`, `Facilitator`, `FieldWorker`, `Director`)

Capabilities principais:

- `viewBaseOverview` no evento sediado
- `viewEventMaterials`

### Coordenador local

Faixa operacional da igreja anfitria para responder pela frente local.

Regra atual:

- `FieldWorker` ou `Facilitator` na igreja anfitria

Capabilities principais:

- `submitChurchEventReport`
- `viewEventMaterials`
- `viewBaseInventory` quando aplicavel

### Professor

Orientador do evento e guardiao do padrao ministerial. Nao recebe por tabela dominio institucional da base.

Regra atual:

- `Teacher` titular ou auxiliar do treinamento

Capabilities principais:

- `viewServedTrainings`
- `manageTrainingRegistrations`
- `manageEventSchedule`
- `manageMentors`
- `manageFacilitators`
- `submitTeacherEventReport`
- `viewEventMaterials`

Capabilities explicitamente negadas por tabela:

- `manageBaseMembers`
- `viewBaseParticipants`
- `viewBaseInventory`

### Mentor

Atua no treinamento servido, mas nao assume automaticamente a operacao institucional da base.

Regra atual:

- `Mentor` vinculado ao treinamento

Capabilities principais:

- `viewServedTrainings`
- `viewEventMaterials`

Capabilities negadas:

- `manageTrainingRegistrations`
- `manageEventSchedule`
- `manageMentors`
- `manageBaseMembers`

### Facilitador

Faixa local da igreja anfitria, sem herdar a guardia ministerial do professor.

Regra atual:

- `Facilitator` da igreja anfitria

Capabilities principais:

- `viewBaseOverview` no evento sediado
- `viewBaseInventory`
- `viewEventMaterials`
- `submitChurchEventReport`

Capabilities negadas:

- `manageTrainingRegistrations`
- `manageEventSchedule`
- `manageMentors`

### Fieldworker contextual

Faixa local mais ampla da base, com leitura institucional e operacao contextual.

Regra atual:

- `FieldWorker` com base vinculada
- no evento, quando a igreja do usuario e a anfitria

Capabilities principais:

- `viewBaseOverview`
- `manageBaseMembers`
- `viewBaseParticipants`
- `viewBaseInventory`
- `manageFacilitators`
- `submitChurchEventReport`
- `viewEventMaterials`

## Tabela resumida

| Capability | Director | FieldWorker contextual | Facilitator anfitriao | Professor vinculado | Mentor vinculado |
| --- | --- | --- | --- | --- | --- |
| `viewBaseOverview` | Sim | Sim | Sim no evento/base vinculada | Sim no evento ou base vinculada | Sim no evento servido; nao automaticamente na base |
| `manageBaseMembers` | Sim | Sim | Nao | Nao | Nao |
| `viewBaseParticipants` | Sim | Sim | Nao | Nao | Nao |
| `viewServedTrainings` | Sim quando no contexto do evento | Sim se tambem servir | Nao por tabela | Sim | Sim |
| `manageTrainingRegistrations` | Sim | Nao | Nao | Sim | Nao |
| `manageEventSchedule` | Sim | Nao | Nao | Sim | Nao |
| `manageMentors` | Sim | Nao | Nao | Sim | Nao |
| `manageFacilitators` | Sim | Sim no evento da base | Nao | Sim | Nao |
| `submitChurchEventReport` | Sim | Sim no evento da base | Sim no evento da base | Nao por tabela | Nao |
| `submitTeacherEventReport` | Sim | Nao | Nao | Sim | Nao |
| `viewBaseInventory` | Sim | Sim | Sim na base vinculada | Nao | Nao |
| `viewEventMaterials` | Sim | Sim no evento da base | Sim no evento da base | Sim | Sim |

## Integracoes implementadas

### Gates

Registrados em `App\Providers\AppServiceProvider`:

- `viewBaseOverview`
- `manageBaseMembers`
- `viewBaseParticipants`
- `viewBaseInventory`
- `viewServedTrainings`
- `manageTrainingRegistrations`
- `manageEventSchedule`
- `manageMentors`
- `manageFacilitators`
- `submitChurchEventReport`
- `submitTeacherEventReport`
- `viewEventMaterials`

### Policies

`App\Policies\TrainingPolicy` agora expõe metodos explicitos para o contexto base:

- `viewBaseOverview`
- `manageTrainingRegistrationsBaseContext`
- `manageEventScheduleBaseContext`
- `manageMentorsBaseContext`
- `manageFacilitatorsBaseContext`
- `submitChurchEventReportBaseContext`
- `submitTeacherEventReportBaseContext`
- `viewEventMaterialsBaseContext`

As policies antigas foram mantidas e passaram a delegar para a matriz quando o contexto e `Base`.

### Portal Base

O `BasePortalController` e o `PortalMenuBuilder` agora consomem a matriz explicitamente para:

- proteger paginas institucionais da base;
- filtrar abas e cards contextuais do evento;
- manter coerencia entre backend e UI.

### Compatibilidade com componentes compartilhados

`TrainingCapabilityResolver` continua existindo para os modulos legados e para os componentes compartilhados de treinamento.

No contexto `Base`, ele agora traduz a matriz explicita para o shape legado:

- `can_view`
- `can_edit`
- `can_manage_schedule`
- `can_view_sensitive_data`
- `can_manage_mentors`
- `can_see_discipleship`

Isso permite migracao gradual sem perder seguranca.

## Estado atual de seguranca

- seguranca de backend nao depende de Blade;
- a mesma matriz pode ser usada por menu, controller, policy, Gate e view;
- professor continua sem ganhar dominio institucional completo da base por tabela;
- a igreja anfitria ganha leitura local coerente com seu papel;
- o sistema fica preparado para a futura fase de relatorios da igreja e do professor.
