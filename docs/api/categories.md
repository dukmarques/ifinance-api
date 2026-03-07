# Categories

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth. List supports `?paginate=true`.

---

## TypeScript Types

```typescript
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
```

---

## Endpoints

### `GET /api/categories`
Response `200`: `{ "data": Category[] }`

---

### `POST /api/categories`
**Request body:**
```json
{ "name": "Food" }
```
| Field | Rules |
|-------|-------|
| `name` | required, min:2 |

Response `201`: `Category`

---

### `GET /api/categories/{id}`
Response `200`: `Category`

---

### `PUT /api/categories/{id}`
**Request body:**
```json
{ "name": "New Name" }
```
| Field | Rules |
|-------|-------|
| `name` | optional (filled), min:2 |

Response `200`: `Category`

---

### `DELETE /api/categories/{id}`
Response `204` (no body)

---

**Common errors:** `401`, `404`, `422`, `400`
