# Host Church Decision

## Resumo executivo

No estado atual do código, `HostChurch` não é apenas uma leitura derivada de `churches + trainings`. A estrutura persistida representa uma entidade institucional própria, ainda que com uso operacional limitado e UI parcialmente incompleta. A recomendação conservadora é manter a entidade e evoluir a camada de leitura antes de qualquer tentativa de substituição.

## Usos reais encontrados

### Models e migrations

- [app/Models/HostChurch.php](/home/cleiton/ee/app/Models/HostChurch.php)
- [app/Models/HostChurchAdmin.php](/home/cleiton/ee/app/Models/HostChurchAdmin.php)
- [app/Models/Church.php](/home/cleiton/ee/app/Models/Church.php)
- [app/Models/User.php](/home/cleiton/ee/app/Models/User.php)
- [database/migrations/2026_01_15_023852_create_host_churches_table.php](/home/cleiton/ee/database/migrations/2026_01_15_023852_create_host_churches_table.php)
- [database/migrations/2026_01_15_023907_create_host_church_admins_table.php](/home/cleiton/ee/database/migrations/2026_01_15_023907_create_host_church_admins_table.php)

### Controllers, Livewire e rotas

- [app/Http/Controllers/System/Director/ChurchController.php](/home/cleiton/ee/app/Http/Controllers/System/Director/ChurchController.php)
- [app/Http/Controllers/System/Teacher/ChurchController.php](/home/cleiton/ee/app/Http/Controllers/System/Teacher/ChurchController.php)
- [app/Livewire/Pages/App/Director/Church/Hosts.php](/home/cleiton/ee/app/Livewire/Pages/App/Director/Church/Hosts.php)
- [app/Livewire/Pages/App/Director/Church/MakeHost.php](/home/cleiton/ee/app/Livewire/Pages/App/Director/Church/MakeHost.php)
- [app/Livewire/Pages/App/Director/Church/ViewHost.php](/home/cleiton/ee/app/Livewire/Pages/App/Director/Church/ViewHost.php)
- [routes/app/director.php](/home/cleiton/ee/routes/app/director.php)
- [routes/app/teacher.php](/home/cleiton/ee/routes/app/teacher.php)

### Views

- [resources/views/pages/app/roles/director/churches/make_host.blade.php](/home/cleiton/ee/resources/views/pages/app/roles/director/churches/make_host.blade.php)
- [resources/views/pages/app/roles/director/churches/view_host.blade.php](/home/cleiton/ee/resources/views/pages/app/roles/director/churches/view_host.blade.php)
- [resources/views/pages/app/roles/director/churches/edit_host.blade.php](/home/cleiton/ee/resources/views/pages/app/roles/director/churches/edit_host.blade.php)
- [resources/views/livewire/pages/app/director/church/hosts.blade.php](/home/cleiton/ee/resources/views/livewire/pages/app/director/church/hosts.blade.php)
- [resources/views/livewire/pages/app/director/church/view-host.blade.php](/home/cleiton/ee/resources/views/livewire/pages/app/director/church/view-host.blade.php)

### Policies

- Nenhuma policy específica de `HostChurch` foi encontrada.
- O acesso atual passa indiretamente por `ChurchPolicy` e pelo middleware `can:manageChurches`.

## Campos e dependências relevantes

### Tabela `host_churches`

- `church_id`: relação 1:1 com `churches`
- `since_date`: data de reconhecimento da base
- `notes`: contexto institucional e observações

### Tabela `host_church_admins`

- `host_church_id`
- `user_id`
- `certified_at`: certificação do administrador
- `status`: estado operacional do vínculo

### Dependências cruzadas

- `Church::hostChurch()` expõe a relação institucional.
- `User::hostChurches()` mantém a noção de administradores próprios da base.
- `Training` continua apontando diretamente para `church_id`; não existe `host_church_id` em `trainings`.
- Os cálculos financeiros nomeados como `hostChurchExpenseBalance` em views de treinamento referem-se ao valor destinado à igreja anfitriã do evento, não à tabela `host_churches`.

## O que `HostChurch` representa hoje

Conclusão baseada no código real: `HostChurch` hoje representa uma entidade institucional própria, mas com baixo acoplamento ao fluxo principal de treinamentos.

Sinais de entidade própria:

- existe tabela dedicada;
- existe histórico institucional (`since_date`, `notes`);
- existe governança própria de administradores (`host_church_admins`);
- existem campos de certificação e status que não são deriváveis apenas de `churches` e `trainings`.

Sinais de uso operacional fraco:

- o fluxo principal de treinamentos usa `trainings.church_id`, sem depender de `host_churches`;
- não há policy própria;
- a tela `ViewHost` está vazia;
- existem rotas de professor para `make_host/view_host/edit_host`, mas as páginas correspondentes não estão completas no mesmo nível do diretor;
- a listagem de hosts existia, mas quase sem exploração dos metadados institucionais.

## Riscos de remoção

- perda do conceito institucional de “igreja base homologada”, que hoje é mais do que simplesmente “igreja com eventos”.
- perda de `since_date`, `notes`, `certified_at` e `status`, que não podem ser reconstruídos de forma confiável só com `trainings`.
- quebra do vínculo administrativo `User <-> HostChurch`, hoje exposto em perfil e relacionamento de modelo.
- risco de regressão em rotas e telas administrativas já existentes, ainda que incompletas.
- risco semântico: “igreja que já sediou treinamento” não é necessariamente igual a “base institucional homologada”.

## Cenário A: manter como entidade própria

### Vantagens

- preserva a semântica institucional já embutida no schema;
- mantém espaço para certificação, governança e notas próprias;
- evita migração de dados ou colapso prematuro de conceitos distintos;
- permite evoluir o módulo gradualmente com UI e policies específicas.

### Custos

- exige completar a camada de leitura e a UI;
- hoje há sobreposição conceitual com `churches` e com o uso de `trainings.church_id`;
- aumenta o custo cognitivo se o domínio não for melhor explicitado.

## Cenário B: substituir por leitura derivada de `churches + trainings`

### Vantagens

- simplifica o modelo operacional de relatório;
- evita uma entidade separada quando o objetivo for apenas listar igrejas que já sediaram eventos;
- reduz duplicação para consultas analíticas.

### Limitações e riscos

- não cobre `since_date`, `notes`, `certified_at`, `status` nem administradores próprios sem criar novos campos em outro lugar;
- troca uma entidade institucional por uma projeção operacional;
- pode induzir regra errada: uma igreja com treinamento passado passaria a parecer automaticamente “host church” homologada.

## Preparação adicionada nesta etapa

Foi criada uma camada de leitura não destrutiva:

- [app/Services/HostChurchReadModel.php](/home/cleiton/ee/app/Services/HostChurchReadModel.php)

Essa camada oferece:

- consulta de hosts registrados com métricas derivadas de treinamentos;
- consulta de igrejas candidatas derivadas por atividade real de treinamento, mas ainda não registradas como `HostChurch`.

Essa decisao permanece compativel com os dashboards:

- dashboard do professor usa `church_id` e pendencias de validacao de igreja no nivel operacional;
- dashboard do diretor consolida novas igrejas por `training_new_churches`, sem colapsar `HostChurch` em mera projeção.

Ela foi conectada à listagem do diretor em:

- [app/Livewire/Pages/App/Director/Church/Hosts.php](/home/cleiton/ee/app/Livewire/Pages/App/Director/Church/Hosts.php)

## Recomendação final conservadora

Recomendação: **manter e evoluir**.

Decisão prática sugerida:

- manter `host_churches` e `host_church_admins` como entidade institucional própria;
- usar leitura derivada apenas para relatórios, comparação e auditoria de sobreposição com `churches + trainings`;
- adiar qualquer substituição estrutural até que o negócio confirme que certificação, notas e administradores próprios deixaram de ser necessários.

Em outras palavras: a leitura derivada já pode substituir relatórios simples, mas ainda não substitui o significado institucional de `HostChurch`.
