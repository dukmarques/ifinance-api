Update `docs/FRONTEND_API_CONTRACT.md` for one or more domains after backend changes. The domain(s) in scope should be provided by the user.

## Source Files to Read (per domain)
| Source | Purpose |
|--------|---------|
| `routes/api.php` | Endpoint list and HTTP methods |
| `app/Http/Requests/{Domain}/Create{Domain}Request.php` | Create validation rules |
| `app/Http/Requests/{Domain}/Update{Domain}Request.php` | Update validation rules |
| `app/Http/Resources/{Domain}Resource.php` | Response shape |
| `app/Services/{Domain}Service.php` | Business rules, enum branching |
| `app/Traits/HasEditTypes.php`, `HasDeleteTypes.php` | Enum constant values |

## What to Update in the Contract
- Endpoint table (method, path, auth requirement)
- Request body schema and validation rules table
- Response shape
- `update_type` / `delete_type` / `exclusion_type` enum values and behavior table
- HTTP status codes
- Quirks and special constraints (e.g. required `date` param, required `id` in body)
- "Endpoints Não Implementados" table if applicable

## Rules
- Do not invent behavior not present in source code.
- If a rule is ambiguous in source, document it as a note rather than asserting.
- Preserve sections of the contract for unmodified domains exactly as they are.
- Keep any TypeScript types in sync with response shape changes.
