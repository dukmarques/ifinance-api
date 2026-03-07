# Users

> Requires: [`_globals.md`](_globals.md)

---

## TypeScript Types

```typescript
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
```

---

## Endpoints

### `POST /api/users` — Registration
Auth: no

**Request body:**
```json
{
  "name": "Full Name",
  "email": "user@email.com",
  "password": "Password@123",
  "image": "https://..."
}
```

| Field | Rules |
|-------|-------|
| `name` | required, string |
| `email` | required, email, unique |
| `password` | required, min:8, uppercase + lowercase + numbers + symbols |
| `image` | optional, string (URL) |

**Response `201`:** `User`
**Errors:** `400`

---

### `GET /api/users/profile`
Auth: **yes**
Response `200`: `User`
Error: `404`

---

### `PUT /api/users/profile`
Auth: **yes**

**Request body (all optional):**
```json
{
  "name": "New Name",
  "email": "new@email.com"
}
```

Response `200`: `User`
Error: `400`
