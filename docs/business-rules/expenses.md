# Expenses Domain Business Rules

## Scope
This document describes the current business rules implemented for the `Expenses` domain in this API, based on tests, controller, requests, service, model, and routes.

## Main Entities
- `expenses`: base expense record (single or recurrent).
- `expenses_overrides`: month-level override for recurrent expenses.

## Authentication and Ownership
- All expenses routes require `auth:sanctum`.
- Expense records are scoped by authenticated user via `AuthScope` (`user_id = Auth::id()`).
- On create, `user_id` is always set from authenticated user in `BaseService::store`.

## Input Validation Rules
### Create (`POST /api/expenses`)
- Required:
  - `title` (string, max 100)
  - `recurrent` (boolean)
  - `amount` (numeric, min 1)
  - `is_owner` (boolean)
  - `payment_month` (date)
- Conditional:
  - `assignee_id` is required when `is_owner=false` and must belong to authenticated user.
  - `owner` is required when `is_owner=false`.
- Optional:
  - `paid` (boolean)
  - `deprecated_date` (date)
  - `description` (string, max 300)
  - `category_id` (must exist)
- Date consistency:
  - `deprecated_date` cannot be in same month/year as `payment_month`.
  - `payment_month` cannot be greater than `deprecated_date`.

### Update (`PUT/PATCH /api/expenses/{id}`)
- User must own the expense.
- Most create fields are optional (`filled`).
- If `assignee_id` is informed, it must belong to authenticated user.
- `update_type` allowed values (for recurrent):
  - `only_month`
  - `current_and_future`
  - `all`

### Delete (`DELETE /api/expenses/{id}`)
- `delete_type` is mandatory in controller for all expenses:
  - `only_month`
  - `current_and_future`
  - `all`

### Update Payment Status (`POST /api/expenses/{id}/update-expense-payment-status`)
- Required: `paid` (boolean)
- Optional: `date` (`Y-m-d`)
  - If omitted, current date is used.

## Monetary Rule
- Amount is stored as integer cents in DB.
- API response uses decimal string formatted via `currency_format` (value / 100).

## Listing Rule (`GET /api/expenses?date=YYYY-MM-DD`)
Given a month `date`, response includes:
- Recurrent expenses active in that month:
  - `payment_month <= month end`
  - and (`deprecated_date >= month start` or `deprecated_date is null`)
- Plus non-recurrent expenses with `payment_month` in that month.
- Includes relations:
  - `category`
  - `assignee`
  - `overrides` filtered to requested month

## Recurrence Update Rules
For recurrent expenses:
- `update_type=all`:
  - Updates the base recurring record directly.
- `update_type=current_and_future`:
  - Closes old recurrence by setting `deprecated_date` to month before new `payment_month`.
  - Creates a new recurring base expense starting at new `payment_month`.
- `update_type=only_month`:
  - Creates/uses an override for that specific month in `expenses_overrides`.
  - There is only one override per (`expense_id`, `payment_month`).

For non-recurrent expenses:
- Updates base expense directly.

## Delete Rules
For recurrent expenses:
- `delete_type=only_month`:
  - Marks month override as `is_deleted=true`.
  - If no override exists, creates one with `is_deleted=true`.
- `delete_type=current_and_future`:
  - Sets `deprecated_date` to previous month of informed `date`.
- `delete_type=all` (or fallback path):
  - Deletes base expense and all overrides.

For non-recurrent expenses:
- Deletes base expense.

## Payment Status Rules
For recurrent expenses:
- Payment is month-specific and stored in override (`expenses_overrides.paid`).
- If override for target month exists, update it.
- If not, create override with `paid` and `payment_month`.
- If `date` is not provided, current month is used.

For non-recurrent expenses:
- Updates `expenses.paid` directly.

## Current Gaps and Risks (Implementation Not Fully Complete)
- No open gaps from the initial assessment points (1 to 5).

## Test Coverage Snapshot (Current)
`tests/Feature/Api/Expenses/ExpensesTest.php` validates:
- create (simple and recurrent)
- create as non-owner with persisted `owner`
- update (simple and recurrent with all update types)
- recurrent update validation requiring `update_type`
- list (`GET /expenses`) and show (`GET /expenses/{id}`)
- delete (simple and recurrent scenarios, including recurrence options)
- update payment status (simple and recurrent, with and without `date`)
- selected validation errors

## Guia Rapido (Nao Tecnico) - O Que Voce Pode Fazer com Expenses
Esta secao explica, em linguagem simples, as acoes de negocio disponiveis para despesas.

### 1) Cadastrar uma despesa avulsa
Voce pode criar uma despesa que acontece apenas uma vez, informando valor, data do mes de pagamento, categoria e descricao (quando quiser).

### 2) Cadastrar uma despesa recorrente
Voce pode criar uma despesa mensal recorrente, para contas que se repetem (ex.: aluguel, internet, assinatura).
Se quiser, voce tambem pode definir ate quando essa recorrencia vale.

### 3) Definir responsavel da despesa
Voce pode informar se a despesa e sua (`is_owner=true`) ou de outra pessoa (`is_owner=false`).
Quando for de outra pessoa, pode informar quem e o responsavel (`owner`) e o vinculo com um assignee (`assignee_id`).

### 4) Listar despesas do mes
Voce pode consultar as despesas de um mes especifico e ver:
- despesas avulsas daquele mes
- despesas recorrentes que estao ativas naquele periodo
- ajustes pontuais daquele mes (overrides), quando existirem
- quais despesas nao sao suas (`is_owner=false`), para exibir indicador "Nao e minha" no front.

### 5) Marcar despesa como paga ou nao paga
Voce pode atualizar o status de pagamento:
- em despesa avulsa: altera o proprio registro da despesa
- em despesa recorrente: altera o mes especifico (comportamento mensal)

### 6) Editar despesa recorrente com 3 opcoes
Ao editar uma despesa recorrente, voce escolhe o alcance da alteracao:
- `only_month`: muda somente o mes selecionado
- `current_and_future`: muda o mes atual e os proximos
- `all`: muda toda a recorrencia

### 7) Excluir despesa recorrente com 3 opcoes
Ao excluir uma despesa recorrente, voce escolhe o alcance da exclusao:
- `only_month`: exclui so o mes selecionado
- `current_and_future`: exclui do mes selecionado em diante
- `all`: exclui toda a recorrencia

### 8) Consultar detalhe de uma despesa especifica
Voce pode abrir uma despesa pelo ID para ver seus dados completos.

## Melhorias Futuras (Roadmap)
Esta secao descreve melhorias planejadas, ainda nao implementadas.

### Compartilhar despesas com outro usuario (planejado)
- Manter `owner` como texto de exibicao para casos manuais.
- Adicionar um campo relacional (ex.: `shared_with_user_id`) para vincular a despesa a outro usuario.
- Adicionar fluxo de convite/status (ex.: `pending`, `accepted`, `rejected`) antes da despesa aparecer para o outro usuario.
- Na listagem, exibir:
  - despesas proprias (`user_id = usuario autenticado`)
  - despesas compartilhadas aceitas (`shared_with_user_id = usuario autenticado` e status `accepted`)
- Definir permissoes para despesas compartilhadas (quem pode visualizar/editar/excluir).
