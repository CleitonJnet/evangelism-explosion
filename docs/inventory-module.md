# Módulo de Estoque Enxuto

## Resumo da arquitetura

- `inventories` representa o estoque central e os estoques locais dos professores.
- `inventory_material` guarda o saldo operacional consolidado por estoque e material.
- `stock_movements` é o histórico oficial auditável.
- `materials` concentra itens simples e materiais compostos.
- `material_components` descreve a composição dos materiais do tipo `composite`.
- `course_material` vincula materiais aos cursos já existentes.

## Fluxo de saldo

1. A operação entra pelos componentes Livewire do diretor ou pelo fluxo de treinamento.
2. O serviço `App\Services\Inventory\StockMovementService` valida quantidade e impede saldo negativo.
3. O pivot `inventory_material` é atualizado como saldo atual consolidado.
4. O movimento correspondente é gravado em `stock_movements`.

## Fluxo de kit composto

1. O kit é um `material` com `type = composite`.
2. A composição do kit é definida em `material_components`.
3. Na saída do kit, o sistema registra a saída do composto e a baixa dos componentes no mesmo estoque.
4. Todos os movimentos relacionados compartilham o mesmo `batch_uuid`.

## Integração com ministries, courses e trainings

- Os materiais recomendados para um treinamento vêm do `course_material` do curso associado ao `training`.
- Ministérios continuam organizando os cursos; o estoque não usa categoria para substituir curso.
- Entregas físicas em treinamento usam `training_id` em `stock_movements`.
- `training_user.payment` continua sendo financeiro.
- `training_user.kit` indica entrega física quando aplicável.
