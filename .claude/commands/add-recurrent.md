Add full recurrent support to an existing simple domain. The domain name should be provided by the user.

## Before Writing Any File
Read these files to understand the full recurrent pattern:
- `app/Models/Expenses.php`
- `app/Models/ExpensesOverride.php`
- `app/Services/ExpensesService.php`
- `app/Traits/HasEditTypes.php`
- `app/Traits/HasDeleteTypes.php`
- `app/Http/Resources/ExpenseResource.php`
- `app/Http/Resources/ExpenseOverrideResource.php`
- `tests/Feature/Api/Expenses/ExpensesTest.php`

## Changes Required

### Model (`app/Models/{Domain}.php`)
- Add `use HasEditTypes, HasDeleteTypes`
- Add fields: `recurrent` (boolean), `deprecated_date` (date, nullable)
- Add relation: `public function overrides(): HasMany`
- Add method: `public function isRecurrent(): bool`

### New Override Model (`app/Models/{Domain}Override.php`)
- Fields: `{domain}_id`, `payment_month`, `is_deleted`, plus overrideable fields
- Add `#[ScopedBy(AuthScope::class)]` if queried standalone

### Migration
- Add `recurrent`, `deprecated_date` to the domain table
- Create `{domain}_overrides` table

### Service (`app/Services/{Domain}Service.php`)
- Override `index()`: filter by `date` query param — include active recurrent records and non-recurrent records for that month
- Override `update()`: branch on `isRecurrent()` then on `update_type` (`all`, `current_and_future`, `only_month`)
- Override `destroy()`: branch on `delete_type` (`all`, `current_and_future`, `only_month`)

### Resource
- Add `override` key using `$this->whenLoaded('overrides', ...)` returning the first override for the queried month

### FormRequests
- Add `update_type` to UpdateRequest with `in:` rule using trait constants
- Add `delete_type` to destroy validation

## After Changes
Update `docs/FRONTEND_API_CONTRACT.md` with the new recurrent behavior, enum values, and any new fields.
