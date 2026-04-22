# Coding Conventions

Patterns and rules derived from existing code. When convention and code disagree, follow the actual code in the target module.

## Backend Conventions

### Layering Rules

| Layer | Does | Does Not |
|-------|------|----------|
| Controller | Accept input, delegate, return JSON | Hold business logic, query DB directly |
| FormRequest | Validate input, some authorization | Orchestrate business operations |
| Service | Business rules, state transitions, cross-model work | Format HTTP responses |
| Repository | Reusable queries, filtering, pagination, CRUD | Hold business rules |
| Model | Relationships, persistence, attribute accessors | Hold service-level logic |

**Reality check**: Some controllers still do simple model reads directly. Some services still query Eloquent for simple lookups. Follow the pattern in the specific module you're modifying.

### Controller Conventions

- Constructor injection for primary service dependency
- `ApiResponse` trait for standardized JSON responses
- Route model binding where convenient (`User $user`, `Media $media`, `Article $article`)
- Docblocks on public methods
- Return types: `JsonResponse` or inferred

```php
class ExampleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ExampleService $exampleService
    ) {}

    public function index(Request $request)
    {
        $data = $this->exampleService->list($request->only([...]));
        return $this->successResponse($data, 'Retrieved successfully');
    }
}
```

### Service Conventions

- Constructor injection for repository and other service dependencies
- Numbered inline comments for multi-step flows (`// 1. Validate`, `// 2. Process`)
- Throw `ApiException` for business-rule violations
- Use `DB::transaction()` when multiple writes are involved
- Shared image upload logic via `HandlesImageUploads` trait

### Repository Conventions

- Methods: `paginate()`, `create()`, `update()`, `delete()`, `findById()` / `find()`
- Filter-based pagination with `when()` chains
- Eager loading declared close to the query
- No business logic

### Model Conventions

- Explicit `$fillable` arrays (no `$guarded`)
- `$hidden` for sensitive fields (`password`, `remember_token`)
- `casts()` method or `$casts` property for booleans and datetimes
- Explicit relationship methods with docblocks
- `SoftDeletes` on major entities: `User`, `Role`, `Category`, `CategoryGroup`, `Media`, `Article`
- `$appends` for computed URL attributes (Media model)

### Form Request Conventions

- Grouped under `app/Http/Requests/AdminDashboard/{Module}/`
- Separate `StoreRequest` and `UpdateRequest` when rules differ
- Shared `CategoryRequest` when create/update rules are identical
- Use `$request->validated()` payload when passing to services
- Authorization checks in `authorize()` method for protected operations

### Error Handling Pattern

```
Service throws ApiException(message, statusCode)
  → ApiExceptionHandler catches in bootstrap/app.php
    → Returns { success: false, message, errors }
```

- `ApiException` — custom exception with configurable HTTP status (default 400)
- `ApiExceptionHandler` — registered globally, only processes `/api/*` requests
- Normalized handling for: `ValidationException` (422), `AuthenticationException` (401), `AuthorizationException` (403), `ModelNotFoundException` (404)
- Debug mode includes stack trace in `errors` field

### Routing Conventions

- Route names use resource-style naming: `users.index`, `roles.update`, `categories.groups`
- Authenticated API routes grouped under `auth:sanctum` middleware
- SPA catch-all in `routes/web.php`: `/admin/{any?}`
- Custom endpoints break out of resource pattern when needed (e.g., `users/{user}/ban`)

### Naming Conventions (Backend)

| Item | Convention | Example |
|------|-----------|---------|
| Controller | PascalCase, suffixed | `UserManagementController` |
| Service | PascalCase, suffixed | `ArticleService` |
| Repository | PascalCase, suffixed | `MediaRepository` |
| FormRequest | PascalCase, action prefix | `StoreUserRequest`, `UpdateRequest` |
| Model | PascalCase, singular | `Article`, `BanHistory` |
| Migration | snake_case, timestamped | `2026_04_19_140351_create_articles_table` |
| Config file | snake_case | `protection.php`, `media.php` |
| Route name | dot-separated resource | `users.ban-history` |

## Frontend Conventions

### Component Conventions

- `<script setup lang="ts">` on all components
- Page-level components in `views/{module}/`
- Reusable atomic UI in `components/ui/Base*.vue`
- Domain-specific in `components/{module}/`
- Heavy reuse of Base* components — prefer extending them over creating one-off elements

### Composable Conventions

- Filename starts with `use` (e.g., `useArticleForm.ts`)
- Exported as named functions
- Manage `ref`, `computed`, `watch` for page/module state
- Use `alertService` (from `utils/sweetalert.ts`) for user feedback
- Expose only the state and methods the view needs

### Service Conventions

- Exported as object literals (e.g., `export const articleService = { ... }`)
- Use `useApi()` for all HTTP calls
- Use Ziggy `route()` for URL generation
- Types and interfaces co-located in the same file (not in `types/`)
- Keep HTTP details in services, workflow logic in composables

### Styling Conventions

- **Tailwind CSS** is the primary styling system
- **Google Material Symbols Outlined** is the icon system
- **Inter** font family (configured in `tailwind.config.js`)
- Custom theme colors defined in `tailwind.config.js`:

| Token | Value | Usage |
|-------|-------|-------|
| `primary` | `#526D82` | Primary actions, buttons |
| `background-light` | `#DDE6ED` | Page background |
| `sidebar-bg` | `#27374D` | Sidebar |
| `surface-card` | `#ffffff` | Card backgrounds |
| `text-primary` | `#27374D` | Primary text |
| `text-secondary` | `#526D82` | Secondary text |

### Rounded Container Standard

| Element | Border Radius | Usage |
|---------|--------------|-------|
| `rounded-3xl` | Large | Primary parent containers: Modals, Panels, Main Cards |
| `rounded-2xl` | Medium | Interactive elements: Inputs, Buttons, Selects, Inner Cards |
| `rounded-xl` | Small | Compact buttons (sm size), badges |

### SEO Hint Pattern

Critical SEO fields include a `hint` prop with actionable guidance:

```vue
<BaseInput
  label="SEO Title"
  :hint="'Recommended: 50-60 characters. Include your primary keyword.'"
/>
```

Hint styling: `text-[11px] italic text-slate-400`

### SweetAlert Conventions

Use `alertService` from `utils/sweetalert.ts` for all user feedback:

```typescript
import { alertService } from '../utils/sweetalert';

// Success notification
alertService.successToast('Item created successfully');

// Error notification
alertService.errorToast('Failed to save', error.message);

// Confirmation dialog
const result = await alertService.confirm({
    title: 'Delete Item?',
    text: 'This action cannot be undone.',
    danger: true
});
if (result.isConfirmed) { ... }
```

### Naming Conventions (Frontend)

| Item | Convention | Example |
|------|-----------|---------|
| View file | PascalCase | `ArticleCreate.vue` |
| Component file | PascalCase, Base* for atomic | `BaseInput.vue`, `ArticleStatusBadge.vue` |
| Composable file | camelCase, use* prefix | `useArticleForm.ts` |
| Service file | camelCase, *Service suffix | `articleService.ts` |
| Route name | dot-separated resource | `articles.index`, `users.edit` |

## Practical Rules For Changes

1. **Follow the target module's patterns** — if the module uses `ApiResponse`, your changes should too.
2. **Check for existing utilities** before creating new ones — `alertService`, `useApi`, `HandlesImageUploads` already exist.
3. **Do not mix response formats** — use `ApiResponse` trait unless you are in a module that already uses raw `response()->json()`.
4. **New modules should follow the dominant pattern**: Controller (thin) → Service (business logic) → Repository (queries).
5. **Preserve all existing comments and docblocks** unless they are directly contradicted by your changes.
