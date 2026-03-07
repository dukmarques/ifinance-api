---
name: frontend-api-contract-implementation
description: Implement or update frontend forms and API integration from a domain-based backend contract, including field validation, request/response mapping, recurring rules, and HTTP error handling. Use this whenever building frontend screens that consume documented API endpoints.
---

# Frontend API Contract Implementation

## Purpose
Use this skill to build frontend features from an API contract document in a consistent, testable, and framework-agnostic way.

Default contract file for this project:
- `docs/FRONTEND_API_CONTRACT.md`

## When To Use
Use this skill when the request involves at least one of:
- Creating forms from backend validation rules
- Implementing API requests for a documented domain (`Auth`, `Expenses`, `Revenues`, etc.)
- Applying domain business rules (recurrence, update/delete modes, date semantics)
- Standardizing error handling (`401`, `404`, `422`, `400`)
- Refactoring integration code to match backend response contracts

## Inputs Required
Before implementation, confirm these inputs from context:
- Contract path (default: `docs/FRONTEND_API_CONTRACT.md`)
- Target frontend location (repository/folder)
- Domain scope (single domain or multiple)
- Existing frontend stack (if present)

If stack is not explicit, preserve existing project patterns. Do not introduce a new framework unless requested.

## Non-Negotiable Contract Rules
Always enforce these rules from the API contract:
- Monetary input in integer cents; display decimal string from API response
- Use domain enums exactly as documented (`update_type`, `delete_type`, `exclusion_type`)
- Send `date` for month-scoped listing and recurrence operations
- Respect special payload constraints (example: endpoints that require `id` in request body)
- Handle and surface `401`, `404`, `422`, and `400` distinctly

## Implementation Workflow
1. Read the contract and isolate only the requested domain(s).
2. Build a domain table with endpoint, method, request fields, validations, response shape, and business rules.
3. Implement or update API client methods for each endpoint in scope.
4. Implement form schema validation matching backend rules (required, format, enum, ranges, conditional fields).
5. Implement UI form state and request mapping (input -> payload).
6. Implement response mapping (API payload -> UI model), including money/date normalization.
7. Implement error mapping:
   - `401`: authentication/session flow
   - `404`: resource not found flow
   - `422`: field-level validation messages
   - `400`: business/domain error message
8. Add safeguards for recurrence/edit/delete modes where applicable.
9. Add integration tests or component tests for happy path and main failure paths.
10. Verify against contract before finishing.

## Output Contract
For each implemented domain, deliver:
- Form fields list and validation mapping
- API methods created/updated
- Error handling behavior summary
- Known backend quirks handled explicitly
- Tests added (or gaps declared when tests cannot be added)

## Done Criteria
A domain is complete only if:
- All required fields and conditions from contract are represented in UI validation
- Payloads match documented schema and enums
- Error status behavior is implemented for `401/404/422/400`
- Money and date transformations are correct
- At least one positive and one negative flow is tested

## Guardrails
- Do not invent endpoint fields not present in the contract.
- Do not silently ignore contract inconsistencies; document them in code comments or PR notes.
- Keep domain boundaries explicit; avoid mixing unrelated rules in one form/service.
- Prefer small, composable API and validation modules over large monolithic files.

---
name: new-domain
description: Scaffold all files for a new API domain following project conventions — Model, migration, Service, Controller, Resource, FormRequests, and Pest test file. Use whenever adding a brand-new resource to the API.
---

# New Domain Scaffolding

## Purpose
Create all files for a new domain with zero deviation from existing project patterns.

## Before Writing Any File
Read the following reference files to understand the exact patterns in use:
- `app/Models/Category.php` — simple model reference
- `app/Services/CategoryService.php` — simple service reference
- `app/Http/Controllers/CategoriesController.php` — controller reference
- `app/Http/Resources/CategoryResource.php` — resource reference
- `app/Http/Requests/Categories/CreateCategoryRequest.php` — FormRequest reference
- `tests/Feature/Api/Categories/CategoriesTest.php` — test reference

## Files to Create
For a domain named `{Domain}` (e.g. `Budget`):

| File | Path |
|------|------|
| Model | `app/Models/{Domain}.php` |
| Migration | `database/migrations/{timestamp}_create_{domains}_table.php` |
| Service | `app/Services/{Domain}Service.php` |
| Controller | `app/Http/Controllers/{Domain}Controller.php` |
| Resource | `app/Http/Resources/{Domain}Resource.php` |
| CreateRequest | `app/Http/Requests/{Domain}/Create{Domain}Request.php` |
| UpdateRequest | `app/Http/Requests/{Domain}/Update{Domain}Request.php` |
| Test | `tests/Feature/Api/{Domain}/{Domain}Test.php` |

## Non-Negotiable Rules
- Model must use `HasUuids`, `HasFactory`, and `#[ScopedBy(AuthScope::class)]`
- Service must extend `BaseService`, set `$this->model` and `$this->resourceClass` in constructor
- Controller must extend `BaseController`, inject the service, set `$this->storeFormRequest` and `$this->updateFormRequest`
- Resource must use `currency_format()` for any monetary field
- Test must use `RefreshDatabase`, `actingAs()`, and `describe()` + `beforeEach()` with a factory user
- Register the route as `Route::apiResource('{domains}', {Domain}Controller::class)` inside the `auth:sanctum` middleware group in `routes/api.php`

## After Scaffolding
Update `docs/FRONTEND_API_CONTRACT.md` with the new domain section.

---
name: add-recurrent
description: Add full recurrent support to an existing simple domain — recurring fields, Override model, HasEditTypes/HasDeleteTypes traits, and override logic in the Service. Use when a domain needs month-based recurring behavior like Expenses or Revenues.
---

# Add Recurrent Support to a Domain

## Purpose
Extend an existing domain to support recurrent records with month-specific overrides, matching the Expenses/Revenues pattern exactly.

## Before Writing Any File
Read these files to understand the full recurrent pattern:
- `app/Models/Expenses.php` and `app/Models/ExpensesOverride.php`
- `app/Services/ExpensesService.php` — full override logic
- `app/Traits/HasEditTypes.php` and `app/Traits/HasDeleteTypes.php`
- `app/Http/Resources/ExpenseResource.php` and `app/Http/Resources/ExpenseOverrideResource.php`
- `tests/Feature/Api/Expenses/ExpensesTest.php`

## Changes Required

### Model (`app/Models/{Domain}.php`)
- Add `use HasEditTypes, HasDeleteTypes`
- Add fields: `recurrent` (boolean), `deprecated_date` (date, nullable)
- Add relation: `public function overrides(): HasMany`
- Add method: `public function isRecurrent(): bool`

### New Override Model (`app/Models/{Domain}Override.php`)
- Fields: `{domain}_id`, `payment_month`, `is_deleted`, plus overrideable fields (title, amount, etc.)
- Add `#[ScopedBy(AuthScope::class)]` if queried standalone

### Migration
- Add `recurrent`, `deprecated_date` to the domain table
- Create `{domain}_overrides` table

### Service (`app/Services/{Domain}Service.php`)
- Override `index()`: filter by `date` query param using `buildRecurringQuery()` pattern — include active recurrent records and non-recurrent records for that month
- Override `update()`: branch on `isRecurrent()` then `update_type` (`all`, `current_and_future`, `only_month`)
- Override `destroy()`: branch on `delete_type` (`all`, `current_and_future`, `only_month`)

### Resource
- Add `override` key using `$this->whenLoaded('overrides', ...)` returning the first override for the month

### FormRequests
- Add `update_type` to UpdateRequest with `in:` rule using trait constants
- Add `delete_type` to destroy validation (inline or dedicated request)

## After Changes
Update `docs/FRONTEND_API_CONTRACT.md` with the new recurrent behavior and enums.

---
name: write-tests
description: Write Pest feature tests for an existing API endpoint following project conventions — describe blocks, beforeEach, actingAs, RefreshDatabase, AuthScope isolation, and coverage of happy path, not found, and domain-specific rules.
---

# Write Pest Feature Tests

## Purpose
Write complete, idiomatic Pest tests for a given endpoint or domain, matching the project's test patterns exactly.

## Before Writing Tests
Read:
- `tests/Feature/Api/Categories/CategoriesTest.php` — simple CRUD reference
- `tests/Feature/Api/Expenses/ExpensesTest.php` — recurrent + complex rules reference
- `tests/Feature/Api/ExpensesAssignees/ExpenseAssigneesSecurityTest.php` — AuthScope isolation reference

## Test Structure Rules
- Wrap everything in `describe('{Domain}', function () { ... })`
- `beforeEach()` creates `$this->user` via factory
- Every request uses `$this->actingAs($this->user)`
- Every test class uses `uses(RefreshDatabase::class)` (declared in `tests/Pest.php`, no need to repeat per file)
- Use `->assertStatus()` over `->assertOk()` for explicitness on non-200 codes

## Required Coverage Per Endpoint
| Scenario | Test |
|----------|------|
| Auth required | Request without token returns `401` |
| Happy path | Returns correct data and status |
| AuthScope isolation | Another user's record returns `404` |
| Not found | Non-existent ID returns `404` |
| Validation | Missing required field returns `422` |
| Domain rules | Test each business rule (recurrence types, enum values, cross-field validation) |

## Recurrent Domain Extra Coverage
For domains with recurrent support, add tests for each `update_type` and `delete_type` value:
- `only_month`: verify Override record created/updated, base record unchanged
- `current_and_future`: verify new record created with correct `payment_month`, old record has `deprecated_date` set
- `all`: verify base record updated/deleted directly

---
name: doc-domain
description: Update docs/FRONTEND_API_CONTRACT.md for one or more domains after backend changes. Reads the source files (routes, FormRequests, Resources, Services) and rewrites the relevant contract sections. Use after any change to a domain's API surface.
---

# Update API Contract for Domain

## Purpose
Keep `docs/FRONTEND_API_CONTRACT.md` accurate after backend changes by re-deriving the contract directly from source code.

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
- Response shape (TypeScript interface if it changed)
- `update_type` / `delete_type` / `exclusion_type` enum values and behavior table
- HTTP status codes
- Quirks and special constraints (e.g. required `id` in body, required `date` param)
- "Endpoints Não Implementados" table if applicable

## Rules
- Do not invent behavior not present in source code.
- If a rule is ambiguous in source, document it as a note rather than asserting.
- Preserve sections of the contract for unmodified domains exactly as they are.
- Keep the TypeScript types block in sync with any response shape changes.
