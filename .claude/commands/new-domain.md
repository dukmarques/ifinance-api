Create all files for a new API domain following project conventions. The domain name should be provided by the user (e.g. "Budget").

## Before Writing Any File
Read the following reference files to understand the exact patterns in use:
- `app/Models/Category.php`
- `app/Services/CategoryService.php`
- `app/Http/Controllers/CategoriesController.php`
- `app/Http/Resources/CategoryResource.php`
- `app/Http/Requests/Categories/CreateCategoryRequest.php`
- `tests/Feature/Api/Categories/CategoriesTest.php`

## Files to Create
For the given domain `{Domain}`:

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
- Test must use `actingAs()`, `describe()` + `beforeEach()` with a factory user
- Register the route as `Route::apiResource('{domains}', {Domain}Controller::class)` inside the `auth:sanctum` middleware group in `routes/api.php`

## After Scaffolding
Update `docs/FRONTEND_API_CONTRACT.md` with the new domain section.
