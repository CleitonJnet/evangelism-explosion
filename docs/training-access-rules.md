# Training Access Rules

## Objetivo

Centralizar regras de acesso a treinamentos para evitar filtros e autorizacoes divergentes entre policy, controller, query e componentes.

## Regras aplicadas

1. `Director` sempre pode ver, editar, excluir e acessar STP/OJT, dados sensiveis e financas.
2. `Teacher` so pode acessar treinamentos em que seja `teacher_id` ou esteja em `training_assistant_teacher`.
3. `Mentor` so pode acessar treinamentos em que esteja em `mentors`.
4. `Mentor` nunca pode editar ou excluir treinamento nesta fase.
5. `Mentor` pode ver STP/OJT do treinamento vinculado, mas nao ve financas nem dados sensiveis.

## Pontos de uso consolidados

- `TrainingVisibilityScope::apply()` filtra listagens.
- `TrainingCapabilityResolver` responde:
  - `canView()`
  - `canEdit()`
  - `canDelete()`
  - `canViewStpOjt()`
  - `canViewSensitiveData()`
  - `canViewFinance()`
- `TrainingPolicy` usa a capacidade central para `view`, `update` e `delete`.
- `StpApproachPolicy` usa a capacidade central para leitura e mutacao de STP.

## Proximos encaixes naturais

- Reaplicar o mesmo resolver em componentes Livewire que ainda consultam `Training::query()` diretamente.
- Expor rotas/paginas de mentor consumindo a mesma matriz central, sem duplicar ifs de perfil.
