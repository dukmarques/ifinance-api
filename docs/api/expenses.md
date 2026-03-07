# Expenses (Despesas)

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth.

---

## TypeScript Types

```typescript
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
  assignee?: ExpenseAssignee;   // see expense-assignees.md
  payment_month: ISODate;
  deprecated_date: ISODate | null;
  description: string | null;
  category_id: UUID | null;
  category?: Category;          // see categories.md
  user_id: UUID;
  override?: ExpenseOverride | null;  // present in monthly listing
}
```

---

## Recurrent Logic

| `update_type` / `delete_type` | Scope |
|-------------------------------|-------|
| `only_month` | Current month only — creates/marks an `ExpenseOverride` |
| `current_and_future` | Current month and forward — creates new record, deprecates old |
| `all` | All occurrences — modifies/removes the base record directly |

`deprecated_date` on a recurrent expense marks the series end date. The expense stops appearing after the month following `deprecated_date`.

---

## Endpoints

### `GET /api/expenses?date=YYYY-MM-DD`
Monthly filter. If `date` is absent, uses the current date.
Returns expenses for the given month, including active recurrents.
`override` field is populated with the month's override (or `null`).

> **UX:** when `is_owner=false`, display a "Not mine" visual indicator.

Response `200`: `{ "data": Expense[] }`

---

### `POST /api/expenses`
**Request body:**
```json
{
  "title": "Rent",
  "recurrent": true,
  "amount": 150000,
  "is_owner": true,
  "assignee_id": null,
  "owner": null,
  "paid": false,
  "payment_month": "2026-02-01",
  "deprecated_date": null,
  "description": "Apartment",
  "category_id": "category-uuid"
}
```
| Field | Rules |
|-------|-------|
| `title` | required, string, max:100 |
| `recurrent` | required, boolean |
| `amount` | required, numeric, min:1 |
| `is_owner` | required, boolean |
| `assignee_id` | required_if:`is_owner=false`, exists:expense_assignees of the authenticated user |
| `owner` | required_if:`is_owner=false`, string, max:50 |
| `paid` | optional (filled), boolean |
| `payment_month` | required, date |
| `deprecated_date` | optional (filled), date — series end date |
| `description` | optional (filled), string, max:300 |
| `category_id` | optional (filled), nullable, exists:categories — send `null` to remove |

Cross-validations for `deprecated_date`:
- Cannot be the same month as `payment_month`.
- `payment_month` cannot be greater than `deprecated_date`.

> **UX — End date:** `deprecated_date` can be filled at creation time to indicate the recurrent series has a known end (e.g. 36-month financing). The expense will stop appearing after the month following `deprecated_date` without needing manual deletion later.

Response `201`: `Expense`

---

### `GET /api/expenses/{id}?date=YYYY-MM-DD`
Loads the expense with `category`, `assignee`, and `override` for the given month.
If `date` is absent, uses the current date.
Response `200`: `Expense`

---

### `PUT /api/expenses/{id}`
Same fields as create, all optional (filled), plus (`category_id` also accepts `null` to remove):

| Extra field | Rules |
|-------------|-------|
| `update_type` | required for recurrent expense, enum: `only_month \| current_and_future \| all` |

Authorization: `expense.user_id` must belong to the authenticated user.

**Behavior by `update_type`:**
| Value | Effect |
|-------|--------|
| `all` | Updates the base recurrent record directly |
| `current_and_future` | Creates a new record from `payment_month`; deprecates the old one |
| `only_month` | Creates or updates an `ExpenseOverride` for the month |

Response `200`: `Expense`

---

### `DELETE /api/expenses/{id}`
**Request body:**
```json
{
  "delete_type": "only_month",
  "date": "2026-02-01"
}
```
| Field | Rules |
|-------|-------|
| `delete_type` | required, enum: `only_month \| current_and_future \| all` |
| `date` | not required, but necessary for recurrents |

**Behavior by `delete_type`:**
| Value | Effect |
|-------|--------|
| `only_month` | Creates override with `is_deleted=true` for the month |
| `current_and_future` | Sets `deprecated_date` to the previous month (if not the initial month) |
| `all` | Removes the base record and all overrides |

Response `204`

---

### `POST /api/expenses/{id}/update-expense-payment-status`
**Request body:**
```json
{
  "paid": true,
  "date": "2026-02-01"
}
```
| Field | Rules |
|-------|-------|
| `paid` | required, boolean |
| `date` | optional (filled), format `YYYY-MM-DD` |

**Behavior:**
- Non-recurrent expense: updates `paid` directly.
- Recurrent expense: creates or updates the month's override with `paid`.

Response `200`: `Expense`
