# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**iFinance API** is a RESTful financial management API built with Laravel 12 (PHP 8.4+), using PostgreSQL for production and SQLite in-memory for tests. Authentication is handled via Laravel Sanctum tokens.

## Commands

> **Always use Laravel Sail** (`./vendor/bin/sail`) for all commands. Never use `php`, `php artisan`, or `./vendor/bin/pest` directly.

### Docker (Laravel Sail)
```bash
./vendor/bin/sail up -d                    # Start containers in background
./vendor/bin/sail down                     # Stop containers
```

### Testing
```bash
./vendor/bin/sail pest                                              # Run all tests
./vendor/bin/sail pest --filter=ExpensesTest                        # Run a specific test class
./vendor/bin/sail pest tests/Feature/Api/Expenses/                  # Run tests in a directory
```

### Code Style
```bash
./vendor/bin/sail pint                     # Fix code style (Laravel Pint)
./vendor/bin/sail pint --test              # Check style without fixing
```

### Development
```bash
./vendor/bin/sail artisan migrate                  # Run migrations
./vendor/bin/sail artisan migrate:fresh --seed     # Reset and reseed database
```

## Architecture

### Request Lifecycle
```
Request → FormRequest (validation) → Controller → Service → Model → Resource (JSON) → Response
```

### Key Patterns

**BaseService** (`app/Services/BaseService.php`) — Provides standard CRUD (`index`, `show`, `store`, `update`, `destroy`). Domain services set `$this->model` and `$this->resourceClass` in their constructor and override methods for custom logic.

**BaseController** (`app/Http/Controllers/BaseController.php`) — Controllers delegate entirely to their injected service. Each controller sets `$this->service`, `$this->storeFormRequest`, and `$this->updateFormRequest` in its constructor. Almost no logic lives in controllers.

**Resources** — `app/Http/Resources/` transforms Eloquent models to JSON. Use `whenLoaded()` for relationships and `currency_format()` for monetary amounts (stored as integers in cents, divided by 100 for output).

**AuthScope** (`app/Models/Scopes/AuthScope.php`) — Applied via `#[ScopedBy(AuthScope::class)]` on models. Automatically filters all queries to the authenticated user's data.

**All models use UUIDs** via `HasUuids`.

### API Routes (all under `auth:sanctum` middleware except login/register)

| Resource | Controller |
|---|---|
| `cards` | `CardsController` |
| `categories` | `CategoriesController` |
| `revenues` | `RevenuesController` |
| `expenses` | `ExpensesController` |
| `card-expenses` | `CardExpensesController` |
| `card-expenses.installments` | `CardInstallmentsController` |
| `expense-assignees` | `ExpenseAssigneesController` |

### Recurrent Expenses

The most complex domain. `ExpensesOverride` stores month-specific overrides. When editing/deleting recurrent expenses, `update_type`/`delete_type` determines scope:

- `only_month` — Creates/marks an override for that specific month
- `current_and_future` — Replicates the expense with new rules, sets `deprecated_date` on old one
- `all` — Modifies/deletes all instances directly

The `index` query filters expenses by a `date` query param, matching both non-recurrent expenses by `payment_month` and recurrent ones active within that month (using `deprecated_date`).

Revenues follow the same recurrent override pattern as Expenses.

### Monetary Values
Stored as integers (cents). Use `currency_format($value)` in Resources to divide by 100. Accept raw integers when receiving amounts.

### Date Handling
Use helpers from `app/helpers.php`: `createCarbonDateFromString()`, `isSameMonthAndYear()`, `isDateGreaterThan()`. The `payment_month` field is a `DATE` column used for month/year filtering.

### Test Patterns
Tests use **Pest PHP** with `describe()` blocks and `beforeEach()` for setup. Always use factories and `RefreshDatabase`. Auth via `actingAs()`.

```php
describe('Expenses', function () {
    beforeEach(function () {
        $this->user = User::factory()->createOne();
    });

    it('returns expenses for authenticated user', function () {
        $response = $this->actingAs($this->user)->getJson('/api/expenses');
        $response->assertStatus(200);
    });
});
```

### API Contract

`docs/FRONTEND_API_CONTRACT.md` is the source of truth for frontend consumers. **Always update it after any change that affects the API surface**, including:
- New or removed endpoints
- Request field additions, removals, or rule changes
- Response shape changes (new fields, renamed fields, type changes)
- New enum values for `update_type`, `delete_type`, `exclusion_type`
- Changes to HTTP status codes or error messages
- New endpoints marked as not yet implemented

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/     # Thin controllers extending BaseController
│   ├── Requests/        # FormRequest validation (grouped by domain)
│   └── Resources/       # JSON response transformers
├── Models/
│   └── Scopes/          # AuthScope — global user filter
├── Services/            # Business logic extending BaseService
├── Traits/              # HasRecurrentTypes (unified edit/delete type constants for all recurrent domains)
└── helpers.php          # Global helpers for dates and currency formatting
routes/
└── api.php              # All API routes
tests/
├── Feature/Api/         # HTTP feature tests grouped by domain
└── Unit/                # Unit tests for services
```
