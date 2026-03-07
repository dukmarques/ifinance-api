# Card Expenses & Card Installments (Despesas de Cartão e Parcelas)

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth.

---

## TypeScript Types

```typescript
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
  assignee?: ExpenseAssignee;   // see expense-assignees.md
  card_id: UUID;
  category_id: UUID;
  installments?: CardInstallment[];
}
```

---

## Implementation Status

| Endpoint | Status |
|----------|--------|
| `POST /api/card-expenses` | Implemented |
| `PUT /api/card-expenses/{id}/installments/{id}` | Implemented |
| `DELETE /api/card-expenses/{id}/installments/{id}` | Implemented |
| `GET /api/card-expenses` | Not implemented (returns empty) |
| `GET /api/card-expenses/{id}` | Not implemented (returns empty) |
| `PUT /api/card-expenses/{id}` | Not implemented (returns empty) |
| `DELETE /api/card-expenses/{id}` | Not implemented (returns empty) |
| `GET /api/card-expenses/{id}/installments` | Not implemented (returns empty) |
| `POST /api/card-expenses/{id}/installments` | Not implemented (returns empty) |
| `GET /api/card-expenses/{id}/installments/{id}` | Not implemented (returns empty) |

---

## Endpoints

### `POST /api/card-expenses`
**Request body:**
```json
{
  "title": "Notebook",
  "total_amount": 450000,
  "is_owner": false,
  "assignee_id": 1,
  "card_id": "card-uuid",
  "category_id": "category-uuid",
  "date": "2026-02-01",
  "initial_installment": 1,
  "final_installment": 12
}
```
| Field | Rules |
|-------|-------|
| `title` | required, string, max:100 |
| `total_amount` | required, numeric, min:1 |
| `is_owner` | required, boolean |
| `assignee_id` | required_if:`is_owner=false`, exists:expense_assignees of the authenticated user |
| `card_id` | required, exists:cards |
| `category_id` | required, exists:categories |
| `date` | required, date (month of 1st installment) |
| `initial_installment` | required, integer, min:1 |
| `final_installment` | required, integer, >= initial_installment |

Automatic installment generation:
- Creates 1 `card_expense` and N installments (`initial_installment` to `final_installment`).
- `payment_month` of each installment = `date + (installment_number - 1) months`.
- `amount` of each installment = `total_amount / final_installment`.
- `paid` starts as `false`.

**Response `201`:** `CardExpense` (with `installments` loaded)
When `assignee_id` is sent on create, `assignee` also returns loaded.

---

### `PUT /api/card-expenses/{card_expense}/installments/{installment}`
Authorization: the installment must belong to the `card_expense` in the route, and `card_expense.user_id` must belong to the authenticated user.

**Request body:**
```json
{
  "title": "Notebook 2/12",
  "amount": 37500,
  "paid": true,
  "notes": "Paid via debit",
  "update_type": "only_month"
}
```
| Field | Rules |
|-------|-------|
| `update_type` | **required**, enum: `only_month \| current_and_future \| all` |
| `title` | optional (filled), string, max:100 |
| `amount` | optional (filled), numeric, min:0.01 |
| `paid` | optional (filled), boolean |
| `notes` | optional (filled), string, max:300 |

**Behavior by `update_type`:**
| Value | Effect |
|-------|--------|
| `all` | Updates all installments of the card expense |
| `current_and_future` | Updates current and future installments (installment_number >= current) |
| `only_month` | Updates only the target installment |

Response `200`: `CardInstallment`

---

### `DELETE /api/card-expenses/{card_expense}/installments/{installment}`
**Request body:**
```json
{ "delete_type": "only_month" }
```
`delete_type`: required, enum: `only_month | current_and_future | all`

**Behavior by `delete_type`:**
| Value | Effect | Response body |
|-------|--------|---------------|
| `all` | Removes the entire `card_expense` (cascade) | `"Expense successfully removed"` |
| `only_month` | Removes only the target installment | `"Expense installment successfully removed"` |
| `current_and_future` | Removes current and future installments | `"Current and upcoming installments successfully deleted"` |

Response `200`:
```json
{ "message": "..." }
```
