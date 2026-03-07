# Cards (Credit Cards)

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth. List supports `?paginate=true`.

---

## TypeScript Types

```typescript
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
```

---

## Endpoints

### `GET /api/cards`
Response `200`: `{ "data": Card[] }`

---

### `POST /api/cards`
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
| Field | Rules |
|-------|-------|
| `name` | required, min:2 |
| `closing_day` | required, integer, 1..31 |
| `due_day` | required, integer, 1..31 |
| `limit` | required, integer, min:0, max:1000000000 |
| `background_color` | optional (filled), min:2 |
| `card_flag` | optional (filled), min:2 |

Response `201`: `Card`

---

### `GET /api/cards/{id}`
Response `200`: `Card`

---

### `PUT /api/cards/{id}`
Same fields as create, all optional (filled).
Response `200`: `Card`

---

### `DELETE /api/cards/{id}`
Response `204`
