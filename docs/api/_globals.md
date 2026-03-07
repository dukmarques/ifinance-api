# Prosperify API — Global Conventions

> Include this file alongside any domain file when prompting an AI to implement frontend features.

---

## Base URL
```
/api
```

## Authentication
All protected endpoints require:
```
Authorization: Bearer <token>
Content-Type: application/json
```
Token obtained via `POST /api/auth/login`.

---

## IDs
- **UUID (string)**: `users`, `cards`, `categories`, `expenses`, `revenues`, `card_expenses`, `card_installments` and their overrides.
- **Integer**: `expense_assignees.id`.

---

## Monetary Values
| Side | Format | Example |
|------|--------|---------|
| Request (send) | integer in cents | `150000` = R$1,500.00 |
| Response (receive) | decimal string, 2 places | `"1500.00"` |

Affected fields: `amount`, `total_amount`, `limit`.

---

## Dates
- Format: `YYYY-MM-DD` (e.g. `2026-02-01`).
- Monthly fields (`payment_month`, `receiving_date`, `date`) accept any day of the month; backend treats them as a month reference.

---

## Response Shape
| Situation | Shape |
|-----------|-------|
| Collection | `{ "data": [...] }` |
| Paginated (`?paginate=true`) | `{ "data": [...], "links": {...}, "meta": {...} }` |
| Single item | direct object (no `data` wrapper) |
| No content | HTTP 204, no body |

---

## Standard Errors
```jsonc
// 401 - unauthenticated
{ "message": "Unauthenticated." }

// 404 - not found
{ "message": "Resource not found" }   // generic domains
{ "message": "Expense not found" }    // expenses (custom endpoints)
{ "message": "Revenue not found" }    // revenues

// 422 - validation (FormRequest)
{ "message": "...", "errors": { "field": ["message"] } }

// 400 - business error / handled exception
{ "message": "..." }
```

---

## HTTP Status Summary
| Operation | Success | Possible Errors |
|-----------|---------|-----------------|
| GET list | 200 | 401 |
| GET item | 200 | 401, 404 |
| POST | 201 | 400, 401, 422 |
| PUT/PATCH | 200 | 400, 401, 403, 404, 422 |
| DELETE | 204 | 400, 401, 404 |
| DELETE card installments | 200 | 400, 401 |
| POST payment-status | 200 | 400, 401, 404 |

---

## Shared Enums
```typescript
// Used by expenses, revenues, and card_installments — all domains share the same values
type EditType   = "only_month" | "current_and_future" | "all";
type DeleteType = "only_month" | "current_and_future" | "all";
```

---

## TypeScript Primitive Types
```typescript
type UUID = string;
type ISODate = string;       // "YYYY-MM-DD"
type MoneyString = string;   // "1500.00"
type MoneyCents = number;    // 150000
```

---

## Authentication Flow
```
1. POST /api/auth/login  →  save returned token
2. All requests: header "Authorization: Bearer {token}"
3. POST /api/auth/logout  →  remove token from local storage
4. On 401: redirect to login
```
