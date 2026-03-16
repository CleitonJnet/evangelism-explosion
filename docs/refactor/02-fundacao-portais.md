# Fundacao de Portais

## Estrutura criada

A fundacao de portais foi adicionada como uma camada paralela a arquitetura atual, sem remover rotas legadas e sem alterar o banco de dados.

### Modelagem canonica

- `app/Support/Portals/Enums/Portal.php`: enum canonico com chave, label, icone, descricao e rota de entrada de cada portal.
- `app/Support/Portals/Data/*`: DTOs leves para transportar portal resolvido, contexto e secoes do menu.

### Servicos iniciais

- `app/Services/Portals/UserPortalResolver.php`: resolve os portais acessiveis a partir das roles existentes, define metadados do portal e sugere um portal padrao.
- `app/Services/Portals/PortalContextResolver.php`: entrega o contexto inicial de Base, Staff e Aluno para os placeholders atuais.
- `app/Services/Portals/PortalMenuBuilder.php`: monta a estrutura inicial do menu do portal, incluindo links para os paines legados quando o usuario tem permissao.

### Entradas HTTP

- `routes/portal/base.php`
- `routes/portal/staff.php`
- `routes/portal/student.php`
- Registro em `routes/app/start.php` sob o prefixo autenticado `app.portal.*`.
- Gates novas em `AppServiceProvider`: `access-portal-base`, `access-portal-staff` e `access-portal-student`.

### Dashboards placeholder

- `app/Http/Controllers/System/Portal/*DashboardController.php`
- `resources/views/pages/app/portal/dashboard.blade.php`

Os dashboards ainda sao placeholders, mas ja expõem:

- contexto do portal atual;
- roles detectadas para aquele portal;
- lista de portais disponiveis para o usuario;
- menu inicial com links para a nova entrada do portal e para o sistema legado.

## Como os portais foram modelados

Os portais nao substituem roles, gates nem policies. Eles funcionam como camada de experiencia e navegacao.

### Mapeamento inicial

- `Base e Treinamentos`: `Director`, `Teacher`, `Facilitator`, `Mentor`, `FieldWorker`
- `Staff / Governanca`: `Board`, `Director`, `FieldWorker`
- `Aluno`: `Student`

### Regra de portal padrao sugerido

A sugestao inicial prioriza:

1. `Staff` para perfis de governanca (`Board`, `Director`, `FieldWorker`)
2. `Base` para perfis operacionais (`Teacher`, `Facilitator`, `Mentor`)
3. `Aluno` para `Student`

Isso permite que um usuario tenha acesso a mais de um portal sem perder o sistema atual baseado em roles.

## Como evoluir nas proximas etapas

### Etapa seguinte recomendada

- introduzir um seletor de portal no fluxo `app/start`;
- mover grupos de navegacao do sidebar para `PortalMenuBuilder`;
- separar dashboards reais por portal, reaproveitando os modulos legados por role;
- centralizar breadcrumbs, header e contexto visual por portal;
- adicionar testes cobrindo usuarios multi-role e destaque do portal padrao;
- criar mapeamentos mais finos por modulo, sem acoplar a camada de portal ao banco.

### Limites desta etapa

- nenhuma migration foi criada;
- nenhuma rota legada foi removida;
- nenhuma policy ou gate existente foi substituida;
- o conteudo atual continua respondendo pelos caminhos antigos.
