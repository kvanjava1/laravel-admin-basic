# API Reference

All API routes are defined in `routes/api.php`. Base URL: `/api`.

All authenticated routes require both `auth:sanctum` and a specific `permission:*` middleware. See each module for required permission slugs.

## Authentication

| Method | URI | Route Name | Controller | Auth |
|--------|-----|------------|------------|------|
| POST | `/login` | `login` | `AuthController@login` | No |
| POST | `/logout` | `logout` | `AuthController@logout` | Sanctum |
| GET | `/me` | `me` | `AuthController@me` | Sanctum |

### Auth Details

- **Login**: Accepts `email` + `password` via `LoginRequest`. Returns `{ user, token }` where `user` includes `status`, `roles`, and `permissions` (from `getAllPermissions()`). Rejects banned/inactive users with contextual messages.
- **Logout**: Deletes the current Sanctum personal access token.
- **Me**: Returns authenticated user with `status`, `roles`, and `permissions`. Login/me responses now include permissions so the frontend can conditionally show/hide UI elements.

## Profile

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/profile` | `profile.show` | `ProfileController@show` | - | Sanctum |
| PUT | `/profile` | `profile.update` | `ProfileController@update` | - | Sanctum |

### Profile Details

- **Show**: Returns current user with `status` and `roles`.
- **Update**: Accepts `name`, `email`, optional `password`, optional `avatar` (file + crop coordinates). Uses `UpdateProfileRequest`.

## User Management

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/users` | `users.index` | `UserManagementController@index` | `view-users` | Sanctum |
| POST | `/users` | `users.store` | `UserManagementController@store` | `create-users` | Sanctum |
| GET | `/users/statuses` | `users.statuses.index` | `UserManagementController@getStatuses` | `view-users` | Sanctum |
| GET | `/users/{user}` | `users.show` | `UserManagementController@show` | `view-users` | Sanctum |
| PUT | `/users/{user}` | `users.update` | `UserManagementController@update` | `edit-users` | Sanctum |
| DELETE | `/users/{user}` | `users.destroy` | `UserManagementController@destroy` | `delete-users` | Sanctum |

### User Filters (Index)

| Param | Type | Description |
|-------|------|-------------|
| `search` | string | Searches user name/email |
| `status` | string | Filter by status name |
| `created_from` / `created_to` | date | Date range for creation |
| `updated_from` / `updated_to` | date | Date range for last update |
| `per_page` | int | Pagination size (default: 10) |

### User Behavior Notes

- **Store**: Validates via `StoreUserRequest`. Default status is `Active`. Banned status is blocked. Protected roles cannot be assigned.
- **Show**: Appends `is_protected` boolean for frontend logic.
- **Update**: `status_id` is **stripped** from the payload — status changes must go through governance endpoints.
- **Destroy**: Soft delete. Protected accounts and protected-role holders cannot be deleted.

## User Governance (Ban)

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/users/{user}/ban-history` | `users.ban-history` | `UserBanController@index` | `govern-users` | Sanctum |
| POST | `/users/{user}/ban` | `users.ban` | `UserBanController@ban` | `govern-users` | Sanctum |
| POST | `/users/{user}/unban` | `users.unban` | `UserBanController@unban` | `govern-users` | Sanctum |
| POST | `/users/{user}/activate` | `users.activate` | `UserBanController@activate` | `govern-users` | Sanctum |
| POST | `/users/{user}/deactivate` | `users.deactivate` | `UserBanController@deactivate` | `govern-users` | Sanctum |

### Governance Behavior

- All actions require a `reason` (string, min 3-5 chars depending on endpoint).
- **Ban**: Requires `type` (`permanent`/`temporary`), optional `expired_at`. Uses `BanUserRequest`.
- **Unban/Activate/Deactivate**: Use inline validation (not FormRequest).
- All actions create a `BanHistory` audit record within a `DB::transaction()` (via `UserBanService`).
- Protected accounts/roles are validated before action.

## Roles & Permissions

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/roles/options` | `roles.options` | `RoleController@options` | `view-roles` | Sanctum |
| GET | `/roles` | `roles.index` | `RoleController@index` | `view-roles` | Sanctum |
| POST | `/roles` | `roles.store` | `RoleController@store` | `create-roles` | Sanctum |
| GET | `/roles/{id}` | `roles.show` | `RoleController@show` | `view-roles` | Sanctum |
| PUT | `/roles/{role}` | `roles.update` | `RoleController@update` | `edit-roles` | Sanctum |
| DELETE | `/roles/{role}` | `roles.destroy` | `RoleController@destroy` | `delete-roles` | Sanctum |
| GET | `/permissions` | `permissions.index` | `PermissionController@index` | `view-roles` | Sanctum |

### Role Filters (Index)

| Param | Type |
|-------|------|
| `search` | string |
| `permissions` | string |
| `created_at_from` / `created_at_to` | date |
| `updated_at_from` / `updated_at_to` | date |
| `per_page` | int |

### Role Behavior Notes

- **Options**: Returns simplified role list for dropdowns.
- **Store/Update**: Accepts `permissions` array for syncing. Uses `StoreRoleRequest`/`UpdateRoleRequest`.
- **Destroy**: Protected roles (`Super Administrator`) cannot be deleted. Protection validated in controller via `RoleAndAccountProtectionService`.

## Categories

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/categories/groups` | `categories.groups` | `CategoryController@getGroups` | `view-categories` | Sanctum |
| GET | `/categories` | `categories.index` | `CategoryController@index` | `view-categories` | Sanctum |
| POST | `/categories` | `categories.store` | `CategoryController@store` | `create-categories` | Sanctum |
| GET | `/categories/{category}` | `categories.show` | `CategoryController@show` | `view-categories` | Sanctum |
| PUT | `/categories/{category}` | `categories.update` | `CategoryController@update` | `edit-categories` | Sanctum |
| DELETE | `/categories/{category}` | `categories.destroy` | `CategoryController@destroy` | `delete-categories` | Sanctum |

### Category Details

- **Index**: Accepts `group_id` query param. Returns category tree with recursive children.
- **Store/Update**: Uses shared `CategoryRequest`. Self-parenting is blocked.
- **Response format**: Uses raw `response()->json()` with `{ status, data }` envelope (does NOT use `ApiResponse` trait).

## Tags

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/tags/options` | `tags.options` | `TagController@options` | - | Sanctum |

### Tag Details

- Returns tag suggestions for autocomplete. Accepts `search` and `limit` query params.

## Media

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/media` | `media.index` | `MediaController@index` | `view-media` | Sanctum |
| POST | `/media` | `media.store` | `MediaController@store` | `create-media` | Sanctum |
| GET | `/media/{media}` | `media.show` | `MediaController@show` | `view-media` | Sanctum |
| PUT | `/media/{media}` | `media.update` | `MediaController@update` | `edit-media` | Sanctum |
| DELETE | `/media/{media}` | `media.destroy` | `MediaController@destroy` | `delete-media` | Sanctum |

### Media Filters (Index)

| Param | Type |
|-------|------|
| `search` | string |
| `title` | string |
| `alt_text` | string |
| `caption` | string |
| `description` | string |
| `category_id` | int |
| `tags` | string |
| `per_page` | int (default: 12) |

### Media Behavior Notes

- **Index**: Returns `MediaDetailResource` collection with pre-fetched detail data. Wrapped in `ApiResponse` envelope.
- **Store**: Accepts multipart form with `image` file + crop coordinates. Uses `StoreMediaRequest`.
- **Show**: Returns `MediaDetailResource` with `category`, `creator`, `tags` eager loaded.
- **Update**: Metadata + recrop only (no source image replacement). Uses `UpdateMediaRequest`.
- **Destroy**: Soft delete + marks `file_cleanup_marked_at`. Files remain on disk.

## Articles

| Method | URI | Route Name | Controller | Permission | Auth |
|--------|-----|------------|------------|------------|------|
| GET | `/articles` | `articles.index` | `ArticleController@index` | `view-articles` | Sanctum |
| POST | `/articles` | `articles.store` | `ArticleController@store` | `create-articles` | Sanctum |
| GET | `/articles/{article}` | `articles.show` | `ArticleController@show` | `view-articles` | Sanctum |
| PUT | `/articles/{article}` | `articles.update` | `ArticleController@update` | `edit-articles` | Sanctum |
| DELETE | `/articles/{article}` | `articles.destroy` | `ArticleController@destroy` | `delete-articles` | Sanctum |

### Article Filters (Index)

| Param | Type |
|-------|------|
| `search` | string (title search) |
| `category_id` | int |
| `status` | string (`draft`, `published`, `scheduled`) |
| `per_page` | int (default: 10) |

### Article Behavior Notes

- **Store**: Uses `StoreRequest`. Auto-sets `author_id` from authenticated user. Auto-sets `published_at` when status is `published`.
- **Update**: Uses `UpdateRequest`. Auto-sets `published_at` on first publish.
- **Response format**: Returns raw `response()->json()` (does NOT use `ApiResponse` trait).
- Tags are synced via `TagRepository::findOrCreateByNames()`.

## Response Format Inconsistencies

| Controller | Uses ApiResponse Trait | Envelope Format |
|------------|----------------------|-----------------|
| AuthController | ✅ | `{ success, message, data }` |
| UserManagementController | ✅ | `{ success, message, data }` |
| UserBanController | ✅ | `{ success, message, data }` |
| RoleController | ✅ | `{ success, message, data }` |
| ProfileController | ✅ | `{ success, message, data }` |
| MediaController | ✅ | `{ success, message, data }` |
| TagController | ✅ | `{ success, message, data }` |
| PermissionController | ✅ | `{ success, message, data }` |
| CategoryController | ❌ | `{ status, data }` or `{ status, message, data }` |
| ArticleController | ❌ | `{ message, article }` or raw model |
