# Access Matrix

## Training roles in scope

| Perfil | Visibilidade de treinamento | Editar | Excluir | Ver STP/OJT | Ver dados sensíveis | Ver finanças |
| --- | --- | --- | --- | --- | --- | --- |
| Director | Todos os treinamentos | Sim | Sim | Sim | Sim | Sim |
| Teacher | Apenas quando for titular ou auxiliar | Sim | Sim | Sim | Sim | Sim |
| Mentor | Apenas quando estiver vinculado como mentor | Nao | Nao | Sim | Nao | Nao |
| FieldWorker | Fora de escopo nesta fase | Nao definido | Nao definido | Nao definido | Nao definido | Nao definido |

## Fonte central

- Visibilidade de queries: `App\Support\TrainingAccess\TrainingVisibilityScope`
- Capacidades por treinamento: `App\Support\TrainingAccess\TrainingCapabilityResolver`
- Policies adaptadas nesta etapa: `TrainingPolicy`, `StpApproachPolicy`, `StpSessionPolicy` e `StpTeamPolicy`
- Dashboard do professor: rota `app.teacher.dashboard`, com escopo aplicado por `TeacherDashboardService`
- Dashboard do diretor: rota `app.director.dashboard`, acesso total via middleware `can:access-director`

## Decisoes conservadoras desta etapa

- Professor auxiliar recebe a mesma capacidade operacional do professor titular para o treinamento vinculado.
- Mentor recebe somente leitura, com foco em STP/OJT e resumo basico do treinamento.
- Dados sensiveis e financas permanecem restritos a `Director` e `Teacher` vinculado.
- O dashboard do professor herda o mesmo recorte de titular/auxiliar usado nas queries operacionais.
- O dashboard do diretor nao aplica recorte de treinamento; ele consolida a base nacional inteira dentro da janela de periodo.
