# Prosperify API — Frontend Contract

> Fonte: código-fonte do projeto (routes, controllers, form requests, resources, services).
> Objetivo: input direto para ferramentas de geração de frontend (Replit, Lovable, v0, etc.).

---

## Convenções Globais

### Base URL
```
/api
```

### Autenticação
Todos os endpoints protegidos exigem:
```
Authorization: Bearer <token>
Content-Type: application/json
```
O token é obtido via `POST /api/auth/login`.

### IDs
- **UUID (string)**: `users`, `cards`, `categories`, `expenses`, `revenues`, `card_expenses`, `card_installments` e seus overrides.
- **Integer**: `expense_assignees.id`.

### Valores Monetários
| Lado | Formato | Exemplo |
|------|---------|---------|
| Envio (request) | inteiro em centavos | `150000` = R$1.500,00 |
| Recebimento (response) | string decimal 2 casas | `"1500.00"` |

Campos afetados: `amount`, `total_amount`, `limit`.

### Datas
- Formato: `YYYY-MM-DD` (ex: `2026-02-01`).
- Campos mensais (`payment_month`, `receiving_date`, `date`) aceitam qualquer dia do mês; o backend trata como referência de mês.

### Estrutura de resposta
| Situação | Shape |
|----------|-------|
| Lista (collection) | `{ "data": [...] }` |
| Lista paginada (`?paginate=true`) | `{ "data": [...], "links": {...}, "meta": {...} }` |
| Item único | objeto direto (sem wrapper `data`) |
| Sem conteúdo | HTTP 204, sem body |

### Erros padrão
```jsonc
// 401 - não autenticado
{ "message": "Unauthenticated." }

// 404 - não encontrado
{ "message": "Resource not found" }   // domínios genéricos
{ "message": "Expense not found" }    // expenses (endpoints customizados)
{ "message": "Revenue not found" }    // revenues

// 422 - validação (FormRequest)
{ "message": "...", "errors": { "campo": ["mensagem"] } }

// 400 - erro de negócio / exceção tratada
{ "message": "..." }
```

---

## TypeScript Types

```typescript
type UUID = string;
type ISODate = string;           // "YYYY-MM-DD"
type MoneyString = string;       // "1500.00"
type MoneyCents = number;        // 150000

// Enums compartilhados por expenses e card_installments
type EditType   = "only_month" | "current_and_future" | "all";
type DeleteType = "only_month" | "current_and_future" | "all";

// Enums exclusivos de revenues (nomes diferentes!)
type RevenueUpdateType = "only_month" | "current_month_and_followers" | "all_month";
type RevenueDeleteType = "only_month" | "current_month_and_followers" | "all_month";

interface User {
  id: UUID;
  name: string;
  email: string;
  image: string | null;
  created_at: string;
  updated_at: string;
  cards_count: number;
  categories_count: number;
  revenues_count: number;
  expenses_count: number;
  card_expenses_count: number;
}

interface Category {
  id: UUID;
  name: string;
  user_id: UUID;
  created_at: string;
  updated_at: string;
  revenues_count: number;
  expenses_count: number;
  card_expenses_count: number;
}

interface Card {
  id: UUID;
  name: string;
  closing_day: number;
  due_day: number;
  limit: MoneyString;
  background_color: string | null;
  card_flag: string | null;
  user_id: UUID;
  created_at: string;
  updated_at: string;
  card_expenses_count: number;
}

interface ExpenseAssignee {
  id: number;              // integer, não UUID
  name: string;
  description: string | null;
  user_id: UUID;
  expense_count: number;
  card_expense_count: number;
}

interface ExpenseOverride {
  id: UUID;
  title: string | null;
  amount: MoneyString;
  paid: boolean;
  is_deleted: boolean;
  payment_month: ISODate;
  description: string | null;
}

interface Expense {
  id: UUID;
  title: string;
  recurrent: boolean;
  amount: MoneyString;
  is_owner: boolean;
  paid: boolean;
  assignee?: ExpenseAssignee;
  payment_month: ISODate;
  deprecated_date: ISODate | null;
  description: string | null;
  category_id: UUID | null;
  category?: Category;
  user_id: UUID;
  override?: ExpenseOverride | null;  // presente na listagem mensal
}

interface RevenueOverride {
  id: UUID;
  title: string | null;
  amount: MoneyString;
  receiving_date: ISODate | null;
  description: string | null;
  revenues_id: UUID;
  is_deleted: boolean;
}

interface Revenue {
  id: UUID;
  title: string;
  amount: MoneyString;
  receiving_date: ISODate;
  recurrent: boolean;
  description: string | null;
  deprecated_date: ISODate | null;
  user_id: UUID;
  category_id: UUID | null;
  category?: { id: UUID; name: string };
  override?: RevenueOverride | null;  // presente na listagem mensal
}

interface CardInstallment {
  id: UUID;
  title: string;
  amount: MoneyString;
  paid: boolean;
  installment_number: number;
  payment_month: ISODate;
  notes: string | null;
  card_expenses_id: UUID;
}

interface CardExpense {
  id: UUID;
  total_amount: MoneyString;
  is_owner: boolean;
  assignee?: ExpenseAssignee;
  card_id: UUID;
  category_id: UUID;
  installments?: CardInstallment[];
}
```

---

## Endpoints

### Health

#### `GET /api/ping`
Auth: não
Response `200`: `{ "message": "pong" }`

---

### Auth

#### `POST /api/auth/login`
Auth: não

**Request body:**
```json
{
  "email": "user@email.com",
  "password": "Senha@123",
  "device_name": "web"
}
```

**Response `200`:**
```json
{
  "id": "uuid",
  "name": "Nome",
  "email": "user@email.com",
  "token": "plain_text_token",
  "image": null
}
```

**Erros:**
- `400`: `{ "message": "The provided credentials are incorrect" }`

Comportamento: remove todos os tokens anteriores do usuário e gera um novo.

---

#### `POST /api/auth/logout`
Auth: **sim**
Body: vazio
Response `200`: `{}` (json vazio)
Comportamento: revoga todos os tokens do usuário autenticado.

---

### Users

#### `POST /api/users` — Registro
Auth: não

**Request body:**
```json
{
  "name": "Nome Sobrenome",
  "email": "user@email.com",
  "password": "Senha@123",
  "image": "https://..."
}
```
Regras de `password`: mínimo 8 caracteres, letras maiúsculas e minúsculas, números e símbolos.
`image` é opcional.

**Response `201`:** `User`

**Erros:** `400`

---

#### `GET /api/users/profile`
Auth: **sim**
Response `200`: `User`
Erro: `404`

---

#### `PUT /api/users/profile`
Auth: **sim**

**Request body (todos opcionais):**
```json
{
  "name": "Novo Nome",
  "email": "novo@email.com"
}
```
Response `200`: `User`
Erro: `400`

---

### Categories

Todos os endpoints exigem auth.
Listagem suporta `?paginate=true`.

#### `GET /api/categories`
Response `200`: `{ "data": Category[] }`

#### `POST /api/categories`
**Request body:**
```json
{ "name": "Alimentação" }
```
`name`: required, min:2
Response `201`: `Category`

#### `GET /api/categories/{id}`
Response `200`: `Category`

#### `PUT /api/categories/{id}`
**Request body:**
```json
{ "name": "Novo Nome" }
```
`name`: opcional (filled), min:2
Response `200`: `Category`

#### `DELETE /api/categories/{id}`
Response `204` (sem body)

**Erros comuns:** `401`, `404`, `422`, `400`

---

### Cards (Cartões)

Todos os endpoints exigem auth.
Listagem suporta `?paginate=true`.

#### `GET /api/cards`
Response `200`: `{ "data": Card[] }`

#### `POST /api/cards`
**Request body:**
```json
{
  "name": "Nubank",
  "closing_day": 10,
  "due_day": 17,
  "limit": 500000,
  "background_color": "#820AD1",
  "card_flag": "Mastercard"
}
```
| Campo | Regras |
|-------|--------|
| `name` | required, min:2 |
| `closing_day` | required, integer, 1..31 |
| `due_day` | required, integer, 1..31 |
| `limit` | required, integer, min:0, max:1000000000 |
| `background_color` | opcional (filled), min:2 |
| `card_flag` | opcional (filled), min:2 |

Response `201`: `Card`

#### `GET /api/cards/{id}`
Response `200`: `Card`

#### `PUT /api/cards/{id}`
Mesmos campos do create, todos opcionais (filled).
Response `200`: `Card`

#### `DELETE /api/cards/{id}`
Response `204`

---

### Expense Assignees (Responsáveis)

Todos os endpoints exigem auth.
Listagem suporta `?paginate=true`.

#### `GET /api/expense-assignees`
Response `200`: `{ "data": ExpenseAssignee[] }`

#### `POST /api/expense-assignees`
**Request body:**
```json
{
  "name": "João",
  "description": "Irmão"
}
```
| Campo | Regras |
|-------|--------|
| `name` | required, string, min:2, max:50 |
| `description` | nullable, string, max:300 |

Response `201`: `ExpenseAssignee`

#### `GET /api/expense-assignees/{id}`
Response `200`: `ExpenseAssignee`
> Escopo por usuário: registros de outro usuário retornam `404`.

#### `PUT /api/expense-assignees/{id}`
```json
{
  "name": "João Silva",
  "description": null
}
```
`name`: opcional (filled), max:50 | `description`: nullable, max:300
Response `200`: `ExpenseAssignee`
> Escopo por usuário: registros de outro usuário retornam `404`.

#### `DELETE /api/expense-assignees/{id}`
Response `204`
Se houver vínculos com `expenses` ou `card_expenses`, retorna `422`:
```json
{ "message": "Responsible cannot be removed because it is linked to expenses." }
```

---

### Expenses (Despesas)

Todos os endpoints exigem auth.

#### `GET /api/expenses?date=YYYY-MM-DD`
Filtragem mensal. Se `date` ausente, usa data atual.
Retorna despesas do mês informado, incluindo recorrentes ativas.
O campo `override` vem populado com o override do mês (ou `null`).
Para UI: quando `is_owner=false`, exibir indicador visual "Não é minha".

Response `200`: `{ "data": Expense[] }`

---

#### `POST /api/expenses`
**Request body:**
```json
{
  "title": "Aluguel",
  "recurrent": true,
  "amount": 150000,
  "is_owner": true,
  "assignee_id": null,
  "owner": null,
  "paid": false,
  "payment_month": "2026-02-01",
  "deprecated_date": null,
  "description": "Apartamento",
  "category_id": "uuid-da-categoria"
}
```
| Campo | Regras |
|-------|--------|
| `title` | required, string, max:100 |
| `recurrent` | required, boolean |
| `amount` | required, numeric, min:1 |
| `is_owner` | required, boolean |
| `assignee_id` | required_if:`is_owner=false`, exists:expense_assignees do usuário autenticado |
| `owner` | required_if:`is_owner=false`, string, max:50 |
| `paid` | opcional (filled), boolean |
| `payment_month` | required, date |
| `deprecated_date` | opcional (filled), date |
| `description` | opcional (filled), string, max:300 |
| `category_id` | opcional (filled), exists:categories |

Validações cruzadas em `deprecated_date`:
- Não pode ser o mesmo mês de `payment_month`.
- `payment_month` não pode ser maior que `deprecated_date`.

Response `201`: `Expense`

---

#### `GET /api/expenses/{id}`
Response `200`: `Expense`

---

#### `PUT /api/expenses/{id}`
Mesmos campos do create, todos opcionais (filled), mais:

| Campo extra | Regras |
|-------------|--------|
| `update_type` | obrigatório para despesa recorrente, enum: `only_month \| current_and_future \| all` |

Autorização: `expense.user_id` deve ser do usuário autenticado.

**Comportamento por `update_type`:**
| Valor | Efeito |
|-------|--------|
| `all` | Atualiza o registro recorrente base diretamente |
| `current_and_future` | Cria novo registro a partir de `payment_month`; deprecia o antigo |
| `only_month` | Cria ou atualiza um `ExpenseOverride` para o mês |

Response `200`: `Expense`

---

#### `DELETE /api/expenses/{id}`
**Request body:**
```json
{
  "delete_type": "only_month",
  "date": "2026-02-01"
}
```
| Campo | Regras |
|-------|--------|
| `delete_type` | required, enum: `only_month \| current_and_future \| all` |
| `date` | não obrigatório, mas necessário para recorrentes |

**Comportamento por `delete_type`:**
| Valor | Efeito |
|-------|--------|
| `only_month` | Cria override com `is_deleted=true` no mês |
| `current_and_future` | Define `deprecated_date` no mês anterior (se não for o mês inicial) |
| `all` | Remove registro base e todos os overrides |

Response `204`

---

#### `POST /api/expenses/{id}/update-expense-payment-status`
**Request body:**
```json
{
  "paid": true,
  "date": "2026-02-01"
}
```
| Campo | Regras |
|-------|--------|
| `paid` | required, boolean |
| `date` | opcional (filled), formato `YYYY-MM-DD` |

**Comportamento:**
- Despesa não recorrente: atualiza `paid` diretamente.
- Despesa recorrente: cria ou atualiza override do mês com `paid`.

Response `200`: `Expense`

---

### Revenues (Receitas)

Todos os endpoints exigem auth.

> **Atenção:** os enums de `update_type` e `exclusion_type` de revenues são **diferentes** dos de expenses.
> Expenses usa `current_and_future`; revenues usa `current_month_and_followers`.

#### `GET /api/revenues?date=YYYY-MM-DD`
Filtragem mensal. Se `date` ausente, usa data atual.
Response `200`: `{ "data": Revenue[] }`

---

#### `POST /api/revenues`
**Request body:**
```json
{
  "title": "Salário",
  "amount": 500000,
  "receiving_date": "2026-02-05",
  "recurrent": true,
  "description": "Salário mensal",
  "category_id": "uuid-da-categoria"
}
```
| Campo | Regras |
|-------|--------|
| `title` | required, min:2, max:100 |
| `amount` | required, numeric |
| `receiving_date` | required, date |
| `recurrent` | required, boolean |
| `description` | opcional (sometimes\|nullable), string, max:300 |
| `category_id` | opcional (sometimes\|nullable), uuid |

Response `201`: `Revenue`

---

#### `GET /api/revenues/{id}?date=YYYY-MM-DD`
Carrega a receita com category + override do mês informado.
Response `200`: `Revenue`

---

#### `PUT /api/revenues/{id}`
> **Quirk:** o body deve incluir `id` (UUID) além dos campos a atualizar, pois a validação exige.

**Request body:**
```json
{
  "id": "uuid-da-receita",
  "title": "Novo Título",
  "amount": 550000,
  "receiving_date": "2026-02-05",
  "description": "Atualizado",
  "category_id": "uuid",
  "date": "2026-02-01",
  "update_type": "only_month"
}
```
| Campo | Regras |
|-------|--------|
| `id` | **required**, uuid |
| `title` | opcional (filled), min:2, max:100 |
| `amount` | opcional (filled), numeric |
| `receiving_date` | opcional (filled), date |
| `description` | opcional (filled), string, max:300 |
| `category_id` | opcional (filled), uuid, nullable |
| `date` | opcional (filled), date (referência do mês para recorrentes) |
| `update_type` | opcional (filled), enum: `only_month \| current_month_and_followers \| all_month` |

**Comportamento por `update_type`:**
| Valor | Efeito |
|-------|--------|
| `all_month` | Atualiza a receita recorrente base |
| `current_month_and_followers` | Cria nova receita a partir de `date`; deprecia a antiga |
| `only_month` | Cria ou atualiza override para o mês de `date` |

Response `200`: `Revenue`

---

#### `DELETE /api/revenues/{id}`
Sem FormRequest. Body opcional:
```json
{
  "exclusion_type": "only_month",
  "date": "2026-02-01"
}
```
| Campo | Valores |
|-------|---------|
| `exclusion_type` | `only_month \| current_month_and_followers \| all_month` |
| `date` | data de referência para recorrentes |

**Comportamento:**
| Valor | Efeito |
|-------|--------|
| `only_month` | Cria override com `is_deleted=true` |
| `current_month_and_followers` | Define `deprecated_date` no mês anterior |
| outro / omitido | Remove receita base (forceDelete) e overrides |

Response `204`

---

### Card Expenses (Despesas de Cartão)

> Estado de implementação: apenas `POST` está implementado no backend. Os demais métodos existem nas rotas mas retornam vazio.

#### `POST /api/card-expenses`
Auth: **sim**

**Request body:**
```json
{
  "title": "Notebook",
  "total_amount": 450000,
  "is_owner": false,
  "assignee_id": 1,
  "card_id": "uuid-do-cartao",
  "category_id": "uuid-da-categoria",
  "date": "2026-02-01",
  "initial_installment": 1,
  "final_installment": 12
}
```
| Campo | Regras |
|-------|--------|
| `title` | required, string, max:100 |
| `total_amount` | required, numeric, min:1 |
| `is_owner` | required, boolean |
| `assignee_id` | required_if:`is_owner=false`, exists:expense_assignees do usuário autenticado |
| `card_id` | required, exists:cards |
| `category_id` | required, exists:categories |
| `date` | required, date (mês da 1ª parcela) |
| `initial_installment` | required, integer, min:1 |
| `final_installment` | required, integer, >= initial_installment |

Geração automática de parcelas:
- Cria 1 `card_expense` e N parcelas (`initial_installment` até `final_installment`).
- `payment_month` de cada parcela = `date + (número_parcela - 1) meses`.
- `amount` de cada parcela = `total_amount / final_installment`.
- `paid` inicia como `false`.

**Response `201`:** `CardExpense` (com `installments` carregados)
Quando enviado no create, `assignee` também retorna carregado.

---

### Card Installments (Parcelas)

> Estado de implementação: apenas `PUT` e `DELETE` estão implementados.

#### `PUT /api/card-expenses/{card_expense}/installments/{installment}`
Auth: **sim**
Autorização: a parcela deve pertencer ao `card_expense` da rota, e o `card_expense.user_id` deve ser do usuário autenticado.

**Request body:**
```json
{
  "title": "Notebook 2/12",
  "amount": 37500,
  "paid": true,
  "notes": "Pago no débito",
  "update_type": "only_month"
}
```
| Campo | Regras |
|-------|--------|
| `update_type` | **required**, enum: `only_month \| current_and_future \| all` |
| `title` | opcional (filled), string, max:100 |
| `amount` | opcional (filled), numeric, min:0.01 |
| `paid` | opcional (filled), boolean |
| `notes` | opcional (filled), string, max:300 |

**Comportamento por `update_type`:**
| Valor | Efeito |
|-------|--------|
| `all` | Atualiza todas as parcelas da despesa de cartão |
| `current_and_future` | Atualiza parcela atual e futuras (installment_number >= atual) |
| `only_month` | Atualiza apenas a parcela alvo |

Response `200`: `CardInstallment`

---

#### `DELETE /api/card-expenses/{card_expense}/installments/{installment}`
**Request body:**
```json
{ "delete_type": "only_month" }
```
`delete_type`: required, enum: `only_month | current_and_future | all`

**Comportamento por `delete_type`:**
| Valor | Efeito | Response body |
|-------|--------|---------------|
| `all` | Remove todo o `card_expense` (cascade) | `"Expense successfully removed"` |
| `only_month` | Remove apenas a parcela alvo | `"Expense installment successfully removed"` |
| `current_and_future` | Remove parcela atual e futuras | `"Current and upcoming installments successfully deleted"` |

Response `200`:
```json
{ "message": "..." }
```

---

## Resumo de Status HTTP

| Operação | Sucesso | Erros possíveis |
|----------|---------|-----------------|
| GET list | 200 | 401 |
| GET item | 200 | 401, 404 |
| POST | 201 | 400, 401, 422 |
| PUT/PATCH | 200 | 400, 401, 403, 404, 422 |
| DELETE | 204 | 400, 401, 404 |
| DELETE card installments | 200 | 400, 401 |
| POST payment-status | 200 | 400, 401, 404 |

---

## Fluxo de Autenticação (resumo para implementação)

```
1. POST /api/auth/login  →  salvar token retornado
2. Todas as requisições: header "Authorization: Bearer {token}"
3. POST /api/auth/logout  →  remover token do storage local
4. Se receber 401: redirecionar para login
```

---

## Endpoints Não Implementados (retornam vazio)

| Endpoint | Método |
|----------|--------|
| `/api/card-expenses` | GET |
| `/api/card-expenses/{id}` | GET |
| `/api/card-expenses/{id}` | PUT/PATCH |
| `/api/card-expenses/{id}` | DELETE |
| `/api/card-expenses/{id}/installments` | GET |
| `/api/card-expenses/{id}/installments` | POST |
| `/api/card-expenses/{id}/installments/{id}` | GET |
