Write Pest feature tests for an existing API endpoint or domain. The target domain/endpoint should be provided by the user.

## Before Writing Tests
Read:
- `tests/Feature/Api/Categories/CategoriesTest.php` — simple CRUD reference
- `tests/Feature/Api/Expenses/ExpensesTest.php` — recurrent + complex rules reference
- `tests/Feature/Api/ExpensesAssignees/ExpenseAssigneesSecurityTest.php` — AuthScope isolation reference

## Test Structure Rules
- Wrap everything in `describe('{Domain}', function () { ... })`
- `beforeEach()` creates `$this->user` via factory
- Every request uses `$this->actingAs($this->user)`
- `RefreshDatabase` is already declared globally in `tests/Pest.php` — do not repeat it per file
- Use `->assertStatus()` over `->assertOk()` for non-200 codes

## Required Coverage Per Endpoint
| Scenario | Assertion |
|----------|-----------|
| Auth required | Request without token returns `401` |
| Happy path | Returns correct data and status code |
| AuthScope isolation | Another user's record returns `404` |
| Not found | Non-existent ID returns `404` |
| Validation | Missing required field returns `422` |
| Domain rules | Each business rule (recurrence types, enum values, conditional fields) |

## Extra Coverage for Recurrent Domains
- `only_month`: assert Override record created/updated, base record unchanged
- `current_and_future`: assert new record created with correct `payment_month`, old record has `deprecated_date` set
- `all`: assert base record updated/deleted directly
