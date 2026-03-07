# Revenues (Receitas)

> Requires: [`_globals.md`](_globals.md)
> All endpoints require auth.

---

## TypeScript Types

```typescript
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
  override?: RevenueOverride | null;  // present in monthly listing
}
```

---

## Recurrent Logic

| `update_type` / `exclusion_type` | Scope |
|----------------------------------|-------|
| `only_month` | Current month only — creates/marks a `RevenueOverride` |
| `current_and_future` | Current month and forward — creates new record, deprecates old |
| `all` | All occurrences — modifies/removes the base record directly |

`deprecated_date` on a recurrent revenue marks the series end date. The revenue stops appearing after the month following `deprecated_date`.

---

## Endpoints

### `GET /api/revenues?date=YYYY-MM-DD`
Monthly filter. If `date` is absent, uses the current date.
Response `200`: `{ "data": Revenue[] }`

---

### `POST /api/revenues`
**Request body:**
```json
{
  "title": "Salary",
  "amount": 500000,
  "receiving_date": "2026-02-05",
  "recurrent": true,
  "description": "Monthly salary",
  "category_id": "category-uuid",
  "deprecated_date": null
}
```
| Field | Rules |
|-------|-------|
| `title` | required, min:2, max:100 |
| `amount` | required, numeric |
| `receiving_date` | required, date |
| `recurrent` | required, boolean |
| `description` | optional (sometimes\|nullable), string, max:300 |
| `category_id` | optional (sometimes\|nullable), uuid |
| `deprecated_date` | optional (sometimes\|nullable), date — series end date |

> **UX — End date:** `deprecated_date` can be filled at creation for recurrent revenues with a known end (e.g. contract valid until December). The revenue will stop appearing after the month following `deprecated_date`.

Response `201`: `Revenue`

---

### `GET /api/revenues/{id}?date=YYYY-MM-DD`
Loads the revenue with category + override for the given month.
Response `200`: `Revenue`

---

### `PUT /api/revenues/{id}`
> **Quirk:** the body must include `id` (UUID) in addition to the fields to update, as validation requires it.

**Request body:**
```json
{
  "id": "revenue-uuid",
  "title": "New Title",
  "amount": 550000,
  "receiving_date": "2026-02-05",
  "description": "Updated",
  "category_id": "uuid",
  "date": "2026-02-01",
  "deprecated_date": null,
  "update_type": "only_month"
}
```
| Field | Rules |
|-------|-------|
| `id` | **required**, uuid |
| `title` | optional (filled), min:2, max:100 |
| `amount` | optional (filled), numeric |
| `receiving_date` | optional (filled), date |
| `description` | optional (filled), string, max:300 |
| `category_id` | optional (filled), uuid, nullable |
| `date` | optional (filled), date (month reference for recurrents) |
| `deprecated_date` | optional (filled), date — series end date |
| `update_type` | optional (filled), enum: `only_month \| current_and_future \| all` |

**Behavior by `update_type`:**
| Value | Effect |
|-------|--------|
| `all` | Updates the base recurrent revenue |
| `current_and_future` | Creates a new revenue from `date`; deprecates the old one |
| `only_month` | Creates or updates override for the month of `date` |

Response `200`: `Revenue`

---

### `DELETE /api/revenues/{id}`
No FormRequest. Optional body:
```json
{
  "exclusion_type": "only_month",
  "date": "2026-02-01"
}
```
| Field | Values |
|-------|--------|
| `exclusion_type` | `only_month \| current_and_future \| all` |
| `date` | reference date for recurrents |

**Behavior:**
| Value | Effect |
|-------|--------|
| `only_month` | Creates override with `is_deleted=true` |
| `current_and_future` | Sets `deprecated_date` to the previous month |
| other / omitted | Removes the base revenue (forceDelete) and overrides |

Response `204`
