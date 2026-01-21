# Evangelismo Explosivo Internacional (EEI)

Plataforma web para comunicacao publica e operacao administrativa do Evangelismo Explosivo Internacional.

## Visao geral

O sistema e dividido em duas frentes:

1) Pagina Web Publica: informacoes institucionais, eventos e inscricoes.
2) Parte Administrativa: area autenticada com funcoes por role (Board, Director, Teacher, Facilitator, FieldWorker, Mentor, Student).

Esta documentacao foi escrita para crescer junto com o projeto. Novas rotas, roles, modelos e processos devem ser adicionados nas secoes correspondentes.

## Stack e bibliotecas

Backend
- Laravel 12
- PHP 8.4
- Fortify (autenticacao)
- Livewire 4 + Volt
- Flux UI (componentes de UI)

Frontend
- Tailwind CSS 4
- Vite
- Axios
- SwiperJS (carousels)

Dev/Qualidade
- Pest
- Laravel Pint
- Laravel Boost
- Sail

## Arquitetura do sistema

Camadas principais
- Rotas: `routes/web.php` (publico) e `routes/app/*.php` (admin).
- Controllers: `app/Http/Controllers/Web` e `app/Http/Controllers/System`.
- Views/Livewire: `resources/views` e componentes Livewire/Volt.
- Dominio: `app/Models` com Eloquent e relacionamentos.
- Autorizacao: gates em `app/Providers/AppServiceProvider.php` + policy em `app/Policies/RoleAccessPolicy.php`.

Fluxo de acesso (admin)
1) Usuario autenticado entra em `/start`.
2) O role e verificado; se tiver apenas um role, redireciona para o dashboard correspondente.
3) Cada area e protegida por gate: `access-board`, `access-director`, etc.

## Parte 1: Pagina Web Publica

Rotas e telas principais (routes/web.php + SiteController)

| Rota | Controller | View | Descricao |
| --- | --- | --- | --- |
| `/` | `SiteController@home` | `pages.web.home` | Home institucional |
| `/donate` | `SiteController@donate` | `pages.web.donate` | Doacoes |
| `/ministry/everyday-evangelism` | `SiteController@everyday_evangelism` | `pages.web.ministry.everyday-evangelism` | Ministerio |
| `/ministry/kids-ee` | `SiteController@kids_ee` | `pages.web.ministry.kids-ee` | Ministerio kids |
| `/ministry/kids-ee2` | `SiteController@kids_ee2` | `pages.web.ministry.kids-ee2` | Ministerio kids (variante) |
| `/about-ee/history` | `SiteController@history` | `pages.web.about.history` | Historia |
| `/about-ee/faith` | `SiteController@faith` | `pages.web.about.faith` | Fe |
| `/about-ee/vision-mission` | `SiteController@vision_mission` | `pages.web.about.vision-mission` | Visao e missao |
| `/event/schedule` | `SiteController@schedule` | `pages.web.events.schedule` | Calendario |
| `/event/list` | `SiteController@events` | `pages.web.events.index` | Lista de eventos |
| `/event/{id}/details` | `SiteController@details` | `pages.web.events.details` | Detalhes do treinamento |
| `/event/{id}/register` | `SiteController@register` | `pages.web.events.register` | Inscricao em evento |
| `/event/{id}/login` | `SiteController@login` | `pages.web.events.login` | Acesso vinculado ao evento |
| `/event/training-host-church` | `SiteController@clinic_base` | `pages.web.events.clinic-base` | Clinica base |

Principais recursos publicos
- Eventos e treinamentos carregados de `Training` com `EventDate`, `Course`, `Church`, `Teacher`.
- Paginas institucionais segmentadas por ministerio e sobre o EEI.
- Componentes web reutilizaveis em `resources/views/components/web`.

## Parte 2: Area Administrativa (roles)

Entrada e navegacao
- `/start`: seleciona o painel quando o usuario possui multiplos roles.
- Cada role possui um namespace e middleware de acesso (gates).

Roles e acessos (rotas atuais)

| Role | Gate | Rota base | Tela |
| --- | --- | --- | --- |
| Board | `access-board` | `/board` | `pages.app.roles.board.dashboard` |
| Director | `access-director` | `/director` | `pages.app.roles.director.dashboard` |
| Teacher | `access-teacher` | `/teacher` | `pages.app.roles.teacher.dashboard` |
| Facilitator | `access-facilitator` | `/facilitator` | `pages.app.roles.facilitator.dashboard` |
| FieldWorker | `access-fieldworker` | `/fieldworker` | `pages.app.roles.fieldworker.dashboard` |
| Mentor | `access-mentor` | `/mentor` | `pages.app.roles.mentor.dashboard` |
| Student | `access-student` | `/student` | `pages.app.roles.student.dashboard` |

Funcionalidades do Director (rotas ativas)
- Setup de roles (Volt): `/director/setup` -> `resources/views/livewire/director/setup.blade.php`.
- Igrejas: listagem, visualizacao e edicao basica (`ChurchController`).
- Perfis por igreja: criacao/visualizacao/edicao (`ProfileController`).
- Ministerios e cursos: criacao/visualizacao/edicao (`MinistryController`, `CourseController`).
- Treinamentos: criacao/visualizacao/edicao (`TrainingController`).
- Inventario: criacao/visualizacao/edicao (`InventoryController`).
- Fluxos especificos de "host church": `make_host`, `view_host`, `edit_host`.

Observacao sobre telas prontas
- Existem views para outras roles e modulos (ex: `resources/views/pages/app/roles/fieldworker/...`), mesmo que algumas rotas ainda nao estejam expostas no backend. Ao habilitar novas rotas, atualize esta secao.

## Autenticacao e seguranca

- Fortify: registro, reset de senha, verificacao de email e 2FA com confirmacao.
- Gates por role em `app/Providers/AppServiceProvider.php`.
- Policy de acesso por role em `app/Policies/RoleAccessPolicy.php`.
- Password policy dinamica: mais forte em producao.

## Dominio e modelos principais

Usuarios e acesso
- `User`, `Role`, relacao muitos-para-muitos.

Ministerio e treinamento
- `Ministry`, `Course`, `Training`, `EventDate`, `Schedule`.

Igrejas e perfis
- `Church`, `ChurchTemp`, `HostChurch`, `HostChurchAdmin` (views de perfis em `resources/views/pages/app/roles/*/profiles`).

Inventario e logistica
- `Inventory`, `Material`, `Supplier`, `Shipping`, `Voucher`, `Receipt`.

Conteudo
- `Category`, `Section`, `Lessonplan`, `Media`, `Help`.

## Estrutura de arquivos (mapa)

Observacao: `vendor/` e `node_modules/` sao gerados por dependencias e nao devem ser versionados manualmente.

```
/
├── AGENTS.md
├── README.md
├── artisan
├── boost.json
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── pint.json
├── vite.config.js
├── backup_eebra780_development.sql
├── app/
│   ├── Actions/
│   │   └── Fortify/
│   ├── Concerns/
│   ├── Helpers/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── System/
│   │       └── Web/
│   ├── Livewire/
│   │   ├── Actions/
│   │   ├── Pages/
│   │   │   └── App/
│   │   └── Web/
│   ├── Models/
│   ├── Policies/
│   ├── Providers/
│   └── View/
│       └── Components/
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   │   └── fonts/
│   ├── js/
│   │   └── modules/
│   └── views/
│       ├── components/
│       ├── flux/
│       ├── layouts/
│       ├── livewire/
│       └── pages/
│           ├── app/
│           │   └── roles/
│           └── web/
├── routes/
│   ├── app/
│   ├── console.php
│   └── web.php
├── storage/
├── tests/
│   ├── Feature/
│   └── Unit/
├── vendor/
└── node_modules/
```

Detalhamento de pastas chave
- `app/Http/Controllers/System`: controllers da area administrativa.
- `app/Http/Controllers/Web`: controllers da pagina publica.
- `app/Livewire` + `resources/views/livewire`: componentes Livewire/Volt.
- `resources/views/pages/web`: paginas publicas.
- `resources/views/pages/app/roles`: telas por role.
- `resources/views/components`: componentes Blade reutilizaveis.
- `routes/app`: rotas do painel administrativo por role.
- `database/migrations`: esquema do banco de dados.

## UI e experiencia

- Flux UI para componentes padronizados (`resources/views/flux`).
- Tailwind v4 para utilitarios de estilo (via Vite).
- Componentes web e app separados para manter consistencia visual.

## Configuracao local (dev)

Comandos principais
- `composer run setup`
- `composer run dev`
- `npm run dev` (quando precisar apenas do frontend)

## Testes e qualidade

- Formatter: `vendor/bin/pint --dirty`
- Testes: `php artisan test --compact`

## Crescimento desta documentacao

Atualize sempre que houver:
- Novas rotas publicas ou administrativas.
- Novos roles ou gates.
- Novos modelos, migracoes ou jobs.
- Mudancas em processos (ex: cadastro, eventos, inventario).

Sugestao de proximas secoes
- Roadmap por modulo
- Regras de negocio por role
- Diagrama de dados (ER)
- Politicas de acesso detalhadas
