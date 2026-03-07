# Prosperify API — Frontend Contract

> Source: project source code (routes, controllers, form requests, resources, services).
> Goal: direct input for frontend generation tools (Replit, Lovable, v0, etc.).

---

## How to use this contract

Each domain has its own file in [`docs/api/`](api/). When prompting an AI to implement a feature, always include:

1. **[`docs/api/_globals.md`](api/_globals.md)** — base URL, auth, money format, dates, standard errors, HTTP status codes, shared enums, TypeScript primitives
2. **The domain file** for what you want to build

### Example prompt structure
```
Context files:
- docs/api/_globals.md
- docs/api/expenses.md

Task: implement the monthly expenses listing screen
```

---

## Domain Files

| Domain | File | Endpoints |
|--------|------|-----------|
| Global conventions & types | [api/_globals.md](api/_globals.md) | — |
| Health & Auth | [api/auth.md](api/auth.md) | `POST /auth/login`, `POST /auth/logout`, `GET /ping` |
| Users | [api/users.md](api/users.md) | `POST /users`, `GET /users/profile`, `PUT /users/profile` |
| Categories | [api/categories.md](api/categories.md) | CRUD `/categories` |
| Cards | [api/cards.md](api/cards.md) | CRUD `/cards` |
| Expense Assignees | [api/expense-assignees.md](api/expense-assignees.md) | CRUD `/expense-assignees` |
| Expenses | [api/expenses.md](api/expenses.md) | CRUD `/expenses` + payment status |
| Revenues | [api/revenues.md](api/revenues.md) | CRUD `/revenues` |
| Card Expenses & Installments | [api/card-expenses.md](api/card-expenses.md) | `POST /card-expenses` + installment PUT/DELETE |

---

## Quick Reference

### Authentication
```
POST /api/auth/login   →  { token }
Authorization: Bearer <token>   (all protected endpoints)
```

### Money
- **Send:** integer cents — `150000` = R$1,500.00
- **Receive:** decimal string — `"1500.00"`

### Recurrent records
All domains (expenses, revenues, card installments) share the same enum values:
- `update_type` / `delete_type` / `exclusion_type`: `only_month | current_and_future | all`

### Not yet implemented endpoints
| Endpoint | Method |
|----------|--------|
| `/api/card-expenses` | GET |
| `/api/card-expenses/{id}` | GET, PUT/PATCH, DELETE |
| `/api/card-expenses/{id}/installments` | GET, POST |
| `/api/card-expenses/{id}/installments/{id}` | GET |
