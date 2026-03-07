# Auth

> Requires: [`_globals.md`](_globals.md)

---

## TypeScript Types

```typescript
interface AuthResponse {
  id: UUID;
  name: string;
  email: string;
  token: string;
  image: string | null;
}
```

---

## Endpoints

### `POST /api/auth/login`
Auth: no

**Request body:**
```json
{
  "email": "user@email.com",
  "password": "Password@123",
  "device_name": "web"
}
```

**Response `200`:** `AuthResponse`

**Errors:**
- `400`: `{ "message": "The provided credentials are incorrect" }`

Behavior: removes all previous user tokens and generates a new one.

---

### `POST /api/auth/logout`
Auth: **yes**
Body: empty
Response `200`: `{}` (empty json)
Behavior: revokes all tokens for the authenticated user.

---

### `GET /api/ping`
Auth: no
Response `200`: `{ "message": "pong" }`
