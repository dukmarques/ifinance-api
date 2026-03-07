# Expense Assignees (Responsible Parties)

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth. List supports `?paginate=true`.

---

## TypeScript Types

```typescript
interface ExpenseAssignee {
  id: number;              // integer, not UUID
  name: string;
  description: string | null;
  user_id: UUID;
  expense_count: number;
  card_expense_count: number;
}
```

---

## Endpoints

### `GET /api/expense-assignees`
Response `200`: `{ "data": ExpenseAssignee[] }`

---

### `POST /api/expense-assignees`
**Request body:**
```json
{
  "name": "João",
  "description": "Brother"
}
```
| Field | Rules |
|-------|-------|
| `name` | required, string, min:2, max:50 |
| `description` | nullable, string, max:300 |

Response `201`: `ExpenseAssignee`

---

### `GET /api/expense-assignees/{id}`
Response `200`: `ExpenseAssignee`
> User scope: records from another user return `404`.

---

### `PUT /api/expense-assignees/{id}`
```json
{
  "name": "João Silva",
  "description": null
}
```
| Field | Rules |
|-------|-------|
| `name` | optional (filled), max:50 |
| `description` | nullable, max:300 |

Response `200`: `ExpenseAssignee`
> User scope: records from another user return `404`.

---

### `DELETE /api/expense-assignees/{id}`
Response `204`

If there are links to `expenses` or `card_expenses`, returns `422`:
```json
{ "message": "Responsible cannot be removed because it is linked to expenses." }
```
