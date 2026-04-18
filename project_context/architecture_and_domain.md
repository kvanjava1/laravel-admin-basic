# Architecture And Domain

## High-Level Architecture

This project uses:

- Laravel controllers for HTTP entrypoints
- form requests for input validation and some authorization checks
- services for business logic
- repositories for database access/query composition
- Eloquent models for relationships and persistence
- a Vue SPA frontend that consumes the Laravel API

In practice, the backend is organized like this:

- `app/Http/Controllers`
  API entrypoints
- `app/Http/Requests`
  validation and request authorization
- `app/Services`
  business rules
- `app/Repositories`
  query and persistence helpers
- `app/Models`
  entities and relations
- `routes/api.php`
  API surface
- `resources/js/admin-dashboard`
  Vue SPA

Preferred architectural pattern in the codebase:

- backend: Controller -> Service -> Repository
- frontend: View -> Composable -> Service -> API

These patterns are strongly present, but not absolute. Some modules contain pragmatic exceptions.

## Main Modules

### Auth

- login
- logout
- current authenticated user (`/api/me`)

Uses Sanctum personal access tokens. The frontend stores the token in `localStorage`.

### User Management

- list users
- create user
- edit user
- delete user
- read available user statuses

Important behavior:

- creation defaults to `Active` status if no status is supplied
- creation with `Banned` status is blocked
- protected roles cannot be assigned through the dashboard

### User Status Governance

- ban user
- restore banned user
- activate user
- deactivate user
- view ban history

Important behavior:

- every governance action writes an audit record into `ban_histories`
- temporary bans store `expired_at`
- protected accounts / protected roles cannot be banned
- system-protected accounts have stronger modification restrictions

### Role Management

- list roles
- create role
- show role details
- update role
- delete role
- list permissions

Important behavior:

- `Super Administrator` is protected by config
- protected roles cannot be modified or deleted through the dashboard

### Category Management

- list category groups
- list category tree by group
- create category
- update category
- delete category

Important behavior:

- categories belong to `category_groups`
- categories support parent/child hierarchy
- category slug uniqueness is scoped by `category_group_id`
- self-parenting is blocked
- deeper descendant-cycle protection is `UNKNOWN / NOT FOUND IN CODE`

### Media Management

- list media
- upload media
- show media detail
- update media metadata and crops
- soft delete media
- list tag suggestions

Important behavior:

- media belongs to an optional category
- media supports many-to-many tags
- upload requires crop data for both `16:9` and `4:3`
- upload stores a WEBP original output plus `16:9 medium`, `16:9 big`, `4:3 medium`, and `4:3 big`
- media edit currently supports metadata + recrop, not source-image replacement
- delete is soft delete only; files are not deleted immediately
- deleted media is marked with `file_cleanup_marked_at` for later manual cleanup

## Key Entities

### User

Fields explicitly present in schema:

- `id`
- `name`
- `email`
- `password`
- `status_id`
- `ban_expires_at`
- `avatar`
- timestamps
- soft delete timestamp

Relations:

- belongs to `UserStatus`
- has many `BanHistory`
- has roles via Spatie Permission

### UserStatus

Seeded statuses:

- `Active`
- `Inactive`
- `Banned`

### BanHistory

Tracks:

- target user
- acting admin
- action type
- reason
- optional expiration time

Actions explicitly allowed by migrations/controller usage:

- `banned`
- `restored`
- `activated`
- `deactivated`

### Role / Permission

Backed by Spatie Permission tables, with custom role model adding soft deletes.

### CategoryGroup

Top-level grouping for category trees.

Seeded examples:

- `Artikel`
- `Gallery`
- `Image`

### Category

Hierarchical category entity with:

- `category_group_id`
- `parent_id`
- `name`
- `slug`
- `is_active`

### Media

Key persisted fields:

- `uuid`
- `category_id`
- `created_by`
- `title`
- `slug`
- `alt_text`
- `caption`
- `description`
- `original_path`
- `ratio_16_9_medium_path`
- `ratio_16_9_big_path`
- `ratio_4_3_medium_path`
- `ratio_4_3_big_path`
- `original_mime_type`
- `output_mime_type`
- `original_size`
- `original_output_size`
- per-variant size columns
- crop coordinate fields for both ratios
- `file_cleanup_marked_at`
- soft delete timestamp

Relations:

- belongs to `Category`
- belongs to creator `User`
- belongs to many `Tag`

### Tag

Fields:

- `id`
- `name`
- `slug`

Relations:

- belongs to many `Media`

## Frontend Reality Check

### Live API-Backed Areas

- auth
- users
- roles
- profile
- categories
- media management

### Placeholder / Mock-Backed Areas

- dashboard metrics/content

The dashboard shell exists in the Vue app, but it is still mostly presentation, not real analytics.

## Useful Implementation Patterns

These are not guarantees, but they are recurring project conventions:

- controllers usually stay thin and return JSON through `ApiResponse`
- services usually own business rules and state transitions
- repositories usually own reusable filtering/pagination queries
- form requests are used for validation and some authorization decisions
- frontend views usually compose UI and call composables
- frontend services usually wrap HTTP calls through `useApi`
