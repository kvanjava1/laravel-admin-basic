# Coding Conventions

This file collects the useful parts of the older project rules and rewrites them in a safer form.

Important:

- these are conventions and patterns, not guaranteed truths in every file
- the current codebase mostly follows them, but there are exceptions
- when code and convention differ, follow the actual code and nearby module patterns

## Backend Conventions

### Preferred Layering

The intended backend flow is:

`Route -> FormRequest -> Controller -> Service -> Repository -> Model/DB`

In practice:

- controllers usually orchestrate requests and responses
- services usually hold business logic
- repositories usually hold reusable query logic
- models define relationships and persistence

This convention is visible across users, roles, and categories, even though some modules do not follow it perfectly.

### Controllers

Preferred controller responsibilities:

- accept HTTP request input
- use `FormRequest` validation where available
- delegate business operations to services
- return JSON responses

Observed local conventions:

- constructor injection for primary services
- `ApiResponse` trait for standardized success/error JSON
- route model binding where convenient
- docblocks on public methods

Reality check:

- some controllers still perform direct model reads or inline validation for simple cases
- do not assume every controller is perfectly thin

### Services

Preferred service responsibilities:

- business rules
- cross-model orchestration
- protection checks
- state transitions
- coordination with repositories and traits

Observed local conventions:

- constructor injection for repository/service dependencies
- numbered inline comments for complex multi-step flows
- `ApiException` for business-rule failures
- shared upload logic moved into `HandlesImageUploads`

Reality check:

- some services still query Eloquent models directly for simple lookups and counts
- this project uses repositories heavily, but not as an absolute rule

### Repositories

Preferred repository responsibilities:

- reusable queries
- filtering
- pagination
- eager loading
- basic CRUD wrappers

Observed local conventions:

- `create`, `update`, `delete`
- paginated list methods with structured filters
- relationship eager loading declared close to the query

Reality check:

- repositories are important in this project, but not the only place where Eloquent appears

### Models

Observed model patterns:

- explicit `$fillable`
- hidden password fields where needed
- `casts()` or `$casts` for booleans and datetimes
- explicit relationship methods
- soft deletes on major entities such as `User`, `Role`, `Category`, `CategoryGroup`

Useful conventions:

- prefer descriptive relationship docblocks
- keep model methods focused on persistence and relationships

### Request Validation

Observed request conventions:

- request classes are grouped under `app/Http/Requests/AdminDashboard`
- create and update requests are split when rules differ
- request authorization is used in some modules, especially protected user/role updates

Useful guidance:

- prefer `validated()` payloads entering services
- keep business-rule validation in services, not only request rules

### Error Handling

Observed project pattern:

- API controllers generally rely on `ApiResponse`
- business-rule failures use `ApiException`
- `/api/*` exceptions are normalized through `ApiExceptionHandler`

Useful guidance:

- prefer throwing domain-specific failures upward instead of formatting ad hoc controller errors

### Routing

Observed backend route conventions:

- route names use resource-style names such as `users.index`, `roles.update`, `categories.groups`
- authenticated API routes are grouped under `auth:sanctum`
- SPA catch-all is handled in `routes/web.php` under `/admin/{any?}`

## Frontend Conventions

### Preferred Layering

The intended frontend flow is:

`View -> Composable -> Service -> useApi -> Backend API`

This is the dominant pattern in the codebase, especially for categories, users, profile, and parts of roles.

Reality check:

- some views still contain substantial page logic directly
- media pages now follow the real `service + composable` flow, but they remain one of the denser frontend modules because crop/edit/detail interactions are stateful

### Views

Observed view conventions:

- Vue 3 with `<script setup lang="ts">`
- page-level components live in `views/`
- routes grouped by module folders such as `user`, `role`, `category`, `profile`
- heavy reuse of `Base*` UI components

Useful guidance:

- keep views focused on page composition and user interactions
- move reusable stateful logic into composables when it becomes non-trivial

### Composables

Observed composable conventions:

- filenames start with `use`
- exported as named functions
- manage `ref`, `computed`, `watch`, and page/module state
- commonly handle user feedback with `alertService`

Useful guidance:

- expose only the state and methods the view needs
- prefer module-specific composables when a page has meaningful state transitions

### Services

Observed frontend service conventions:

- exported as object literals such as `userService`, `roleService`
- use `useApi()` for requests
- often define related TypeScript interfaces in the same file

Useful guidance:

- keep HTTP details in services
- keep composables focused on state and workflow, not URL construction

### Components

Observed component grouping:

- `components/layout`
- `components/ui`
- domain components like `components/user` and `components/role`

Useful guidance:

- use `Base*` atomic UI components where possible
- use domain components for module-specific badges, filters, and modals
- for media specifically, category/tag option loading is shared through a dedicated composable instead of being hardcoded per page

### Routing

Observed frontend route conventions:

- lazy-loaded route components
- route names use resource-style naming
- route meta distinguishes authenticated vs guest pages
- `AdminLayout` wraps most admin pages under `/admin`

### Styling

Observed styling conventions:

- Tailwind CSS is primary
- Material Symbols Outlined is the icon system
- custom theme colors are defined in `tailwind.config.js`
- SweetAlert styling is centralized in `utils/sweetalert.ts`

Useful guidance:

- prefer existing utility classes and shared components over one-off styling
- preserve the current visual language unless deliberately redesigning a feature

## Practical Rule For Future Changes

When changing this project:

1. follow actual patterns already used in the target module
2. use the conventions in this file to stay consistent
3. if the convention and code disagree, keep behavior compatible with existing code

## Media-Specific Reality Notes

- media now has real create, index, detail, update, and delete flows
- list and detail use intentionally different API contracts:
  - list is lightweight and returns only what the index needs
  - detail returns richer metadata, variants, and tags
- media delete currently means:
  - soft delete the record
  - set `file_cleanup_marked_at`
  - keep files on disk for later manual cleanup
- tags use a dedicated `tags` table plus `media_tag` pivot, not a JSON column on `media`
- media tests avoid global database refresh and instead clean up the specific records they create
