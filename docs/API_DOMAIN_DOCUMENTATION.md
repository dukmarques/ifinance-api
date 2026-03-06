# iFinance API - Documentacao por Dominio

## 1. Objetivo
Este documento descreve os contratos da API para implementacao de formularios e integracao frontend (Replit/Lovable), incluindo:
- endpoints por dominio
- validacoes de entrada
- formatos de response
- regras de negocio
- codigos HTTP
- observacoes importantes de comportamento real

Fonte: codigo atual do projeto (routes, controllers, requests, services, resources e testes).

## 2. Convencoes Gerais

### 2.1 Base URL
- Prefixo de API: `/api`

### 2.2 Autenticacao
- Endpoints protegidos usam `auth:sanctum`.
- Enviar token como Bearer:
  - `Authorization: Bearer <token>`

### 2.3 Content-Type
- Requests JSON: `Content-Type: application/json`

### 2.4 Tipos de ID
- UUID (string): `users`, `cards`, `categories`, `expenses`, `revenues`, `card_expenses`, `card_installments`, overrides.
- Inteiro (auto increment): `expense_assignees.id`.

### 2.5 Dinheiro
- Banco usa inteiro (centavos) para campos monetarios.
- Input esperado: inteiro (ex.: `12345` = R$123.45).
- Response: string decimal formatada com 2 casas (ex.: `"123.45"`).
- Campos afetados:
  - `amount`
  - `total_amount`
  - `limit`

### 2.6 Datas
- Formato geral esperado: `Y-m-d` (ex.: `2026-02-28`).
- Alguns endpoints aceitam `date` como referencia de mes para regras de recorrencia.

### 2.7 Estrutura de resposta
- Listas com Resource Collection normalmente retornam:
  - `{ "data": [...] }`
- Listas com `?paginate=true` (nos endpoints baseados em BaseService) retornam `data`, `links`, `meta`.
- Item unico geralmente retorna objeto direto (sem wrapper `data`).

### 2.8 Erros comuns
- `401`: `{ "message": "Unauthenticated." }`
- `404`: mensagens variam por dominio (`Resource not found`, `Expense not found`, `Revenue not found`).
- `422` (FormRequest):
  - `{ "message": "...", "errors": { "campo": ["..."] } }`
- `400` (exceptions tratadas em controller/service):
  - `{ "message": "..." }`

## 3. Dominios

## 3.1 Health

### GET `/api/ping`
- Auth: nao
- Response `200`:
```json
{ "message": "pong" }
```

### GET `/api/unauthenticated`
- Auth: nao
- Response `200`:
```json
{ "message": "unauthenticated user" }
```

## 3.2 Auth

### POST `/api/auth/login`
- Auth: nao
- Body:
  - `email`: required, email
  - `password`: required
  - `device_name`: required
- Success `200`:
```json
{
  "id": "uuid",
  "email": "user@email.com",
  "name": "User",
  "token": "plain_text_token",
  "image": "https://..."
}
```
- Regra de negocio:
  - credenciais invalidas geram erro
  - ao logar, a API remove tokens antigos do usuario e gera novo token
- Erros:
  - `400` com `{ "message": "The provided credentials are incorrect" }`

### POST `/api/auth/logout`
- Auth: sim
- Body: vazio
- Success `200` (json vazio)
- Regra de negocio:
  - remove todos os tokens do usuario autenticado
- Erro possivel:
  - `500` com `{ "message": "An error occurred while logging out user" }`

## 3.3 Users

### POST `/api/users` (registro)
- Auth: nao
- Body:
  - `name`: required, min:3
  - `email`: required, email, unique:users,email
  - `password`: required, min 8, precisa conter letras, maiuscula/minuscula, numero e simbolo
  - `image`: opcional (`filled`)
- Success `201`: retorna `UserResource`
```json
{
  "id": "uuid",
  "name": "Nome",
  "email": "email@dominio.com",
  "image": "https://...",
  "created_at": "...",
  "updated_at": "...",
  "cards_count": 0,
  "categories_count": 0,
  "revenues_count": 0,
  "expenses_count": 0,
  "card_expenses_count": 0
}
```
- Regra de negocio:
  - senha e armazenada com hash
- Erros:
  - `400` com `{ "message": "..." }`

### GET `/api/users/profile`
- Auth: sim
- Success `200`: retorna usuario autenticado (`UserResource`)
- Erro:
  - `404` `{ "message": "User not found" }`

### PUT `/api/users/profile`
- Auth: sim
- Body:
  - `name`: opcional, min:3
  - `email`: opcional, email
- Success `200`: usuario atualizado (`UserResource`)
- Erro:
  - `400` `{ "message": "..." }`

## 3.4 Categories

### Endpoints
- `GET /api/categories`
- `POST /api/categories`
- `GET /api/categories/{id}`
- `PUT/PATCH /api/categories/{id}`
- `DELETE /api/categories/{id}`

### Validacoes
- Create:
  - `name`: required, min:2
- Update:
  - `name`: optional (`filled`), min:2

### Regras de negocio
- `user_id` e preenchido automaticamente com usuario autenticado ao criar.
- Modelo possui escopo por usuario (`AuthScope`): consultas retornam apenas registros do usuario logado.
- `GET /api/categories` suporta `?paginate=true`.

### Success responses
- `GET list`: `200` com `{ "data": [CategoryResource...] }`
- `POST`: `201` com `CategoryResource`
- `GET item`: `200` com `CategoryResource`
- `PUT/PATCH`: `200` com `CategoryResource`
- `DELETE`: `204` sem corpo

### CategoryResource
```json
{
  "id": "uuid",
  "name": "Categoria",
  "created_at": "...",
  "updated_at": "...",
  "user_id": "uuid",
  "revenues_count": 0,
  "expenses_count": 0,
  "card_expenses_count": 0
}
```

### Erros
- `401` nao autenticado
- `404` `{ "message": "Resource not found" }`
- `422` validacao FormRequest
- `400` excecao de regra/DB

## 3.5 Cards (cartoes)

### Endpoints
- `GET /api/cards`
- `POST /api/cards`
- `GET /api/cards/{id}`
- `PUT/PATCH /api/cards/{id}`
- `DELETE /api/cards/{id}`

### Validacoes
- Create:
  - `name`: required, min:2
  - `closing_day`: required, integer, 1..31
  - `due_day`: required, integer, 1..31
  - `limit`: required, integer, min:0, max:1000000000
  - `background_color`: optional (`filled`), min:2
  - `card_flag`: optional (`filled`), min:2
- Update:
  - mesmos campos, todos opcionais (`filled`)

### Regras de negocio
- `user_id` preenchido automaticamente no create.
- Modelo possui `AuthScope` (usuario so enxerga os proprios cartoes).
- `GET /api/cards` suporta `?paginate=true`.

### CardResource
```json
{
  "id": "uuid",
  "name": "Cartao",
  "closing_day": 10,
  "due_day": 20,
  "limit": "5000.00",
  "background_color": "#123ABC",
  "card_flag": "Visa",
  "user_id": "uuid",
  "created_at": "...",
  "updated_at": "...",
  "card_expenses_count": 0
}
```

### Success/erros
- Mesmo padrao de Categories (`200/201/204/404/422/400`).

## 3.6 Expense Assignees (responsaveis)

### Endpoints
- `GET /api/expense-assignees`
- `POST /api/expense-assignees`
- `GET /api/expense-assignees/{id}`
- `PUT/PATCH /api/expense-assignees/{id}`
- `DELETE /api/expense-assignees/{id}`

### Validacoes
- Create:
  - `name`: required, string, min:2, max:50
  - `description`: nullable, string, max:300
- Update:
  - `name`: optional (`filled`), string, max:50
  - `description`: nullable, string, max:300

### Regras de negocio
- `user_id` preenchido automaticamente no create.
- `GET /api/expense-assignees` suporta `?paginate=true`.
- `GET/PUT/DELETE` so acessam registros do usuario autenticado.
- `DELETE` retorna `422` quando o responsavel possui vinculo com `expenses` ou `card_expenses`.
- Resource calcula contadores:
  - `expense_count`
  - `card_expense_count`

### ExpenseAssigneeResource
```json
{
  "id": 1,
  "name": "Nome",
  "description": "...",
  "user_id": "uuid",
  "expense_count": 0,
  "card_expense_count": 0
}
```

### Success/erros
- `200/201/204/404/422/400`

## 3.7 Expenses

### Endpoints
- `GET /api/expenses?date=YYYY-MM-DD`
- `POST /api/expenses`
- `GET /api/expenses/{id}`
- `PUT/PATCH /api/expenses/{id}`
- `DELETE /api/expenses/{id}`
- `POST /api/expenses/{id}/update-expense-payment-status`

### Validacoes - create (`POST /expenses`)
- `title`: required, string, max:100
- `recurrent`: required, boolean
- `amount`: required, numeric, min:1
- `is_owner`: required, boolean
- `assignee_id`: required_if `is_owner=false`, exists:expense_assignees,id (do usuario autenticado)
- `owner`: required_if `is_owner=false`, string, max:50
- `paid`: optional (`filled`), boolean
- `payment_month`: required, date
- `deprecated_date`: optional (`filled`), date
- `description`: optional (`filled`), string, max:300
- `category_id`: optional (`filled`), exists:categories,id
- Validacao adicional:
  - `deprecated_date` nao pode estar no mesmo mes de `payment_month`
  - `payment_month` nao pode ser maior que `deprecated_date`

### Validacoes - update (`PUT/PATCH /expenses/{id}`)
- Mesmos campos, opcionais (`filled`), mais:
- `update_type`: obrigatorio para despesa recorrente, in: `only_month,current_and_future,all`
- `assignee_id` (quando enviado): deve existir em `expense_assignees` do usuario autenticado
- Autorizacao:
  - usuario deve estar autenticado
  - `expense.user_id` deve ser do usuario logado

### Validacoes - delete
- Body obrigatorio:
  - `delete_type`: required, in: `only_month,current_and_future,all`
- `date`: nao obrigatorio, mas necessario para cenarios de recorrencia

### Validacoes - update payment status
- `paid`: required, boolean
- `date`: opcional (`filled`), formato `Y-m-d`

### Regras de negocio - listagem (`GET /expenses`)
- A lista e mensal e usa `date` como referencia (quando ausente, backend usa data atual).
- Inclui:
  - despesas recorrentes ativas no mes
  - despesas nao recorrentes cujo `payment_month` pertence ao mes
- Carrega override do mes em `override` (singular).

### Regras de negocio - update recorrente
- `update_type=all`:
  - atualiza o registro recorrente base
- `update_type=current_and_future`:
  - cria novo registro recorrente com novos dados a partir de `payment_month` informado
  - marca o registro antigo com `deprecated_date` no mes anterior
- `update_type=only_month`:
  - cria/atualiza um override para o mes informado

### Regras de negocio - delete recorrente
- `delete_type=only_month`:
  - cria/atualiza override com `is_deleted=true` no mes informado
- `delete_type=current_and_future`:
  - se `date` for diferente do mes de inicio da recorrencia, define `deprecated_date` no mes anterior
- demais casos:
  - remove registro base e overrides

### Regras de negocio - pagamento
- Despesa nao recorrente:
  - atualiza `paid` direto na despesa
- Despesa recorrente:
  - atualiza/cria override do mes com `paid`

### ExpenseResource
```json
{
  "id": "uuid",
  "title": "Aluguel",
  "recurrent": true,
  "amount": "1500.00",
  "is_owner": true,
  "paid": false,
  "payment_month": "2026-02-01",
  "deprecated_date": null,
  "description": "...",
  "category_id": "uuid",
  "user_id": "uuid",
  "override": {
    "id": "uuid",
    "title": null,
    "amount": "",
    "paid": true,
    "is_deleted": false,
    "payment_month": "2026-02-01",
    "description": null
  }
}
```

### Success/erros principais
- `GET list/show`: `200`
- `POST`: `201`
- `PUT/PATCH`: `200`
- `DELETE`: `204`
- `POST update-expense-payment-status`: `200`
- `404`: `{ "message": "Expense not found" }` (em rotas custom) ou `Resource not found` (base)
- `422`: validacao
- `400`: erros de regra/exception

## 3.8 Card Expenses (fatura parcelada)

### Endpoints registrados
- `GET /api/card-expenses`
- `POST /api/card-expenses`
- `GET /api/card-expenses/{id}`
- `PUT/PATCH /api/card-expenses/{id}`
- `DELETE /api/card-expenses/{id}`

### Estado atual de implementacao
- Implementado: apenas `POST /api/card-expenses`.
- Nao implementados (metodos vazios): `GET list`, `GET item`, `PUT/PATCH`, `DELETE`.

### Validacoes do create
- `title`: required, string, max:100
- `total_amount`: required, numeric, min:1
- `is_owner`: required, boolean
- `assignee_id`: required_if `is_owner=false`, exists:expense_assignees,id (do usuario autenticado)
- `card_id`: required, exists:cards,id
- `category_id`: required, exists:categories,id
- `date`: required, date
- `initial_installment`: required, integer, min:1
- `final_installment`: required, integer, gte:initial_installment

### Regras de negocio
- Cria 1 registro em `card_expenses`.
- Gera parcelas em `card_installments` de `initial_installment` ate `final_installment`.
- `payment_month` de cada parcela = `date + (numero_da_parcela - 1) meses`.
- `amount` de cada parcela = `total_amount / final_installment`.
- `paid` inicia como `false`.

### Response create (`201`)
```json
{
  "id": "uuid",
  "total_amount": "1200.00",
  "is_owner": true,
  "assignee": {
    "id": 1,
    "name": "Nome",
    "description": "...",
    "user_id": "uuid",
    "expense_count": 0,
    "card_expense_count": 0
  },
  "card_id": "uuid",
  "category_id": "uuid",
  "installments": [
    {
      "id": "uuid",
      "title": "Compra",
      "amount": "100.00",
      "paid": false,
      "installment_number": 1,
      "payment_month": "2026-02-01",
      "notes": null,
      "card_expenses_id": "uuid"
    }
  ]
}
```

### Observacao importante
- No `CardExpensesResource`, o campo `card_id` esta mapeado para `user_id` (inconsistencia atual de implementacao).

## 3.9 Card Installments

### Endpoints registrados
- `GET /api/card-expenses/{card_expense}/installments`
- `POST /api/card-expenses/{card_expense}/installments`
- `GET /api/card-expenses/{card_expense}/installments/{installment}`
- `PUT/PATCH /api/card-expenses/{card_expense}/installments/{installment}`
- `DELETE /api/card-expenses/{card_expense}/installments/{installment}`

### Estado atual de implementacao
- Implementados: `PUT/PATCH`, `DELETE`.
- Nao implementados: `GET list`, `POST`, `GET item`.

### Validacoes - update
- `title`: optional (`filled`), string, max:100
- `amount`: optional (`filled`), numeric, min:0.01
- `paid`: optional (`filled`), boolean
- `notes`: optional (`filled`), string, max:300
- `update_type`: required, in: `only_month,current_and_future,all`

### Autorizacao - update
- usuario autenticado
- parcela deve pertencer ao `card_expense` da rota
- `card_expense.user_id` deve ser do usuario logado

### Regras de negocio - update
- `update_type=all`:
  - atualiza todas as parcelas da despesa
- `update_type=current_and_future`:
  - atualiza parcela atual e futuras (`installment_number >= atual`)
- `update_type=only_month`:
  - atualiza apenas a parcela alvo

### Response update (`200`)
- Retorna `CardInstallmentsResource` da parcela alvo (apos refresh).

### Validacoes - delete
- `delete_type`: required, in: `only_month,current_and_future,all`

### Regras de negocio - delete
- `delete_type=all`:
  - remove o `card_expense` inteiro (cascade remove parcelas)
- `delete_type=only_month`:
  - remove apenas a parcela alvo (soft delete)
- `delete_type=current_and_future`:
  - remove parcela alvo e futuras

### Response delete (`200`)
```json
{ "message": "Expense successfully removed" }
```
ou
```json
{ "message": "Expense installment successfully removed" }
```
ou
```json
{ "message": "Current and upcoming installments successfully deleted" }
```

## 3.10 Revenues

### Endpoints
- `GET /api/revenues?date=YYYY-MM-DD`
- `POST /api/revenues`
- `GET /api/revenues/{id}?date=YYYY-MM-DD`
- `PUT/PATCH /api/revenues/{id}`
- `DELETE /api/revenues/{id}`

### Validacoes - create
- `title`: required, min:2, max:100
- `amount`: required, numeric
- `receiving_date`: required, date
- `recurrent`: required, boolean
- `description`: optional (`sometimes|nullable`), string, max:300
- `category_id`: optional (`sometimes|nullable`), uuid

### Validacoes - update (atencao)
- O request exige `id` no payload:
  - `id`: required, uuid
- Campos opcionais:
  - `title`: filled, min:2, max:100
  - `amount`: filled, numeric
  - `receiving_date`: filled, date
  - `description`: filled, string, max:300
  - `category_id`: filled, uuid, nullable
  - `date`: filled, date
  - `update_type`: filled, in: `only_month,current_month_and_followers,all_month`

### Validacoes - delete
- Sem FormRequest.
- Campos opcionais no body:
  - `exclusion_type`: `only_month` | `current_month_and_followers` | `all_month`
  - `date`: data de referencia para recorrencia

### Regras de negocio - listagem (`GET /revenues`)
- Filtra por mes de `date` (se ausente, backend usa data atual).
- Inclui:
  - receitas recorrentes ativas no mes
  - receitas nao recorrentes do mes
- Carrega override do mes em `override` (singular).

### Regras de negocio - show
- Busca receita por ID e carrega `category` + override do mes de `date`.

### Regras de negocio - update
- Receita nao recorrente:
  - atualiza diretamente
- Receita recorrente + `update_type=only_month`:
  - cria/atualiza override para o mes de `date`
- Receita recorrente + `update_type=current_month_and_followers`:
  - se `date` for diferente do mes de inicio da receita, cria nova receita a partir de `date` e deprecia a antiga
- Demais casos:
  - atualiza receita recorrente em todos os meses

### Regras de negocio - delete
- Receita recorrente + `exclusion_type=only_month`:
  - cria/atualiza override do mes com `is_deleted=true`
- Receita recorrente + `exclusion_type=current_month_and_followers`:
  - se `date` diferente do mes de inicio, ajusta `deprecated_date` para mes anterior
- Demais casos:
  - remove receita base (forceDelete) e overrides

### RevenuesResource
```json
{
  "id": "uuid",
  "title": "Salario",
  "amount": "5000.00",
  "receiving_date": "2026-02-01",
  "recurrent": true,
  "description": "...",
  "deprecated_date": null,
  "user_id": "uuid",
  "category_id": "uuid",
  "override": {
    "id": "uuid",
    "title": "Salario ajustado",
    "amount": "5200.00",
    "receiving_date": "2026-02-01",
    "description": "...",
    "revenues_id": "uuid",
    "is_deleted": false
  },
  "category": {
    "id": "uuid",
    "name": "Salario"
  }
}
```

### Status esperados
- `GET list/show`: `200`
- `POST`: `201`
- `PUT/PATCH`: `200` quando valido
- `DELETE`: `204`
- `404`: `{ "message": "Revenue not found" }`
- `422`: erros de FormRequest (create/update)
- `400`: excecoes de negocio/DB

## 4. Enumeracoes para Frontend

### Expenses e Card Installments
- `update_type`:
  - `only_month`
  - `current_and_future`
  - `all`
- `delete_type` (expenses/card installments):
  - `only_month`
  - `current_and_future`
  - `all`

### Revenues
- `update_type`:
  - `only_month`
  - `current_month_and_followers`
  - `all_month`
- `exclusion_type`:
  - `only_month`
  - `current_month_and_followers`
  - `all_month`

## 5. Observacoes Importantes para Integracao Frontend

- Em valores monetarios, enviar sempre inteiro em centavos e exibir string decimal da response.
- Em endpoints mensais (`expenses` e `revenues`), sempre enviar `date` para comportamento deterministico no frontend.
- Em update de revenues, enviar tambem `id` no body (alem do ID da rota), pois a validacao atual exige esse campo.
- Em `card-expenses`, apenas o endpoint de criacao esta implementado no backend atual.
- Em `card-expenses.installments`, apenas update/delete estao implementados.
- O campo `owner` de expenses e validado quando `is_owner=false`, mas nao e persistido no banco.
- No response de `card-expenses`, `card_id` esta inconsistente (mapeado para `user_id`).

## 6. Checklist rapido para formularios

- Login: `email`, `password`, `device_name`.
- Registro de usuario: `name`, `email`, `password`, `image?`.
- Cartao: `name`, `closing_day`, `due_day`, `limit`, `background_color?`, `card_flag?`.
- Categoria: `name`.
- Responsavel: `name`, `description?`.
- Despesa: `title`, `recurrent`, `amount`, `is_owner`, `assignee_id?`, `owner?`, `paid?`, `payment_month`, `deprecated_date?`, `description?`, `category_id?`, `update_type?`.
- Despesa de cartao: `title`, `total_amount`, `is_owner`, `card_id`, `category_id`, `date`, `initial_installment`, `final_installment`.
- Parcela de cartao (update): `title?`, `amount?`, `paid?`, `notes?`, `update_type`.
- Receita: `title`, `amount`, `receiving_date`, `recurrent`, `description?`, `category_id?`, e em update incluir `id` + `date?` + `update_type?`.
