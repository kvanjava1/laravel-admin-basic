# Domain And Entities

## Entity Relationship Diagram

```
User ──belongsTo──→ UserStatus
 │
 ├── hasMany ──→ BanHistory (as user)
 ├── hasMany ──→ BanHistory (as admin, auditor)
 ├── hasMany ──→ Article (as author)
 ├── hasMany ──→ Media (as creator)
 └── hasManyThrough ──→ Role (via Spatie Permission)

Article ──belongsTo──→ User (author)
 ├── belongsTo ──→ Category
 ├── belongsTo ──→ Media (featured image)
 └── belongsToMany ──→ Tag (via article_tag pivot)

Media ──belongsTo──→ Category
 ├── belongsTo ──→ User (creator)
 └── belongsToMany ──→ Tag (via media_tag pivot)

Category ──belongsTo──→ CategoryGroup
 ├── belongsTo ──→ Category (parent, self-referential)
 └── hasMany ──→ Category (children, recursive)

Tag ──belongsToMany──→ Media (via media_tag)
 └── belongsToMany ──→ Article (via article_tag)
```

## Entities

### User

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `name` | string | |
| `email` | string | unique |
| `password` | string | hashed, hidden |
| `status_id` | FK → user_statuses | |
| `avatar` | string, nullable | Path on public disk |
| `ban_expires_at` | datetime, nullable | For temporary bans |
| `email_verified_at` | datetime, nullable | |
| `remember_token` | string | hidden |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | timestamp, nullable | soft delete |

Traits: `HasApiTokens`, `HasFactory`, `Notifiable`, `HasRoles`, `SoftDeletes`

### UserStatus

Seeded lookup table. No soft deletes.

| Value |
|-------|
| `Active` |
| `Inactive` |
| `Banned` |

### BanHistory

Audit trail for all user governance actions.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `user_id` | FK → users | Target user |
| `admin_id` | FK → users, nullable | Acting admin (`null` = system auto-action) |
| `type` | string | `permanent` or `temporary` |
| `action` | string | `banned`, `restored`, `activated`, `deactivated` |
| `reason` | string | Required on all governance actions |
| `expired_at` | datetime, nullable | For temporary bans |
| `created_at` / `updated_at` | timestamps | |

### Role

Extended from Spatie `Role` model with `SoftDeletes`.

### Permission

Uses Spatie model with custom columns: `group`, `display_name`.

Seeded permissions:

| Permission Slug | Group |
|----------------|-------|
| `view-users` | User Management |
| `create-users` | User Management |
| `edit-users` | User Management |
| `delete-users` | User Management |
| `govern-users` | User Management |
| `view-roles` | Role Management |
| `create-roles` | Role Management |
| `edit-roles` | Role Management |
| `delete-roles` | Role Management |
| `view-categories` | Category Management |
| `create-categories` | Category Management |
| `edit-categories` | Category Management |
| `delete-categories` | Category Management |
| `view-media` | Media Management |
| `create-media` | Media Management |
| `edit-media` | Media Management |
| `delete-media` | Media Management |
| `view-articles` | Article Management |
| `create-articles` | Article Management |
| `edit-articles` | Article Management |
| `delete-articles` | Article Management |

Seeded roles: `Super Administrator` (has all permissions via `syncPermissions()`). Permissions are enforced at route level via `permission:*` middleware (Spatie).

### CategoryGroup

| Column | Type |
|--------|------|
| `id` | bigint |
| `name` | string |
| `slug` | string |
| `is_active` | boolean |
| `deleted_at` | soft delete |

Seeded groups: `Artikel`, `Image`

### Category

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `category_group_id` | FK → category_groups | |
| `parent_id` | FK → categories, nullable | Self-referential for hierarchy |
| `name` | string | |
| `slug` | string | Unique scoped by group |
| `is_active` | boolean | |
| `deleted_at` | soft delete | |

Supports recursive children loading via `childrenRecursive()`.

### Media

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `uuid` | string | Unique, used in storage paths |
| `category_id` | FK → categories, nullable | |
| `created_by` | FK → users | |
| `title` | string | |
| `slug` | string | Unique (including soft-deleted) |
| `alt_text` | string | Required |
| `caption` | text, nullable | |
| `description` | text, nullable | |
| `original_path` | string | |
| `ratio_16_9_medium_path` | string | |
| `ratio_16_9_big_path` | string | |
| `ratio_4_3_medium_path` | string | |
| `ratio_4_3_big_path` | string | |
| `original_mime_type` | string | Source file MIME |
| `output_mime_type` | string | Always `image/webp` |
| `original_size` | int | Source file bytes |
| `original_output_size` | int | WEBP original bytes |
| `ratio_16_9_medium_size` | int | Variant bytes |
| `ratio_16_9_big_size` | int | |
| `ratio_4_3_medium_size` | int | |
| `ratio_4_3_big_size` | int | |
| `crop_16_9_x/y/width/height` | int | 16:9 crop coordinates |
| `crop_4_3_x/y/width/height` | int | 4:3 crop coordinates |
| `file_cleanup_marked_at` | datetime, nullable | Set on soft delete |
| `deleted_at` | soft delete | |

Appended attributes: `original_url`, `ratio_16_9_medium_url`, `ratio_16_9_big_url`, `ratio_4_3_medium_url`, `ratio_4_3_big_url`, `thumbnail_url` (alias for 4:3 medium), `variants`, `total_variant_size`.

### Tag

| Column | Type |
|--------|------|
| `id` | bigint |
| `name` | string |
| `slug` | string |

Shared between Media and Article via separate pivot tables (`media_tag`, `article_tag`). No soft deletes.

### Article

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `author_id` | FK → users | CASCADE delete |
| `category_id` | FK → categories | CASCADE delete |
| `title` | string | |
| `slug` | string | Unique |
| `excerpt` | text, nullable | |
| `content` | longText | Rich HTML from Tiptap editor |
| `featured_image_id` | FK → media, nullable | SET NULL on delete |
| `featured_image_alt` | string, nullable | |
| `featured_image_caption` | string, nullable | |
| `seo_title` | string, nullable | |
| `seo_description` | text, nullable | |
| `seo_focus_keyword` | string, nullable | |
| `is_indexable` | boolean | Default `true` |
| `status` | enum | `draft`, `published`, `scheduled` |
| `published_at` | datetime, nullable | Auto-set when status → published |
| `deleted_at` | soft delete | |

## Business Workflows

### Authentication

1. `POST /api/login` validates credentials
2. If user status is `Banned`: rejects with ban reason and optional expiry
3. If user status is `Inactive`: rejects with inactive message
4. On success: creates Sanctum personal access token named `admin-dashboard`
5. Frontend stores token + user data in `localStorage`
6. Logout deletes the current access token

### User Governance (Ban/Restore/Activate/Deactivate)

Every governance action:
1. Validates protection rules (protected accounts and roles cannot be affected)
2. Creates a `BanHistory` audit record with reason, acting admin, and action type
3. Updates user `status_id` and `ban_expires_at`

Ban types:
- **Permanent**: no expiry, manual restore required
- **Temporary**: has `expired_at`, system can auto-restore via `app:release-expired-bans`

Protection rules (enforced by `RoleAndAccountProtectionService`):
- Protected accounts (config: `admin@admin.com`) can only be edited by themselves
- Protected role holders (config: `Super Administrator`) cannot be banned, deleted, or edited by others (only by themselves when logged in)
- Protected roles cannot be assigned through the dashboard (existing assignments are maintained)
- Users cannot be created with `Banned` status
- `status_id` is stripped from basic user updates — status changes go through governance endpoints

### Media Upload

1. Validate: image file + crop coordinates for 16:9 and 4:3
2. Generate UUID and date-based storage directory
3. Process original → WEBP at configured quality
4. For each ratio × size: crop → resize → WEBP → store
5. Create database record with all paths, sizes, and crop coordinates
6. Sync tags (find-or-create by name/slug)

### Media Delete

1. Set `file_cleanup_marked_at` to now
2. Soft delete the record
3. Files remain on disk for later manual cleanup

### Article Create/Update

1. If status is `published` and `published_at` is not set: auto-set to `now()`
2. Create/update within a DB transaction
3. Tags: find-or-create by names, then sync pivot

### Category Hierarchy

- Self-parenting is blocked in `CategoryService`
- Deeper descendant-cycle protection is **not implemented**
- Categories use recursive eager loading for tree display

### Expired Ban Release

Command: `php artisan app:release-expired-bans`
- Finds users with `Banned` status where `ban_expires_at <= now`
- Creates system `BanHistory` record (admin_id = null)
- Sets status to `Active`, clears `ban_expires_at`
- **Not registered in the scheduler** — must be run manually or via cron

## Database Seeding Order

```
UserStatusSeeder → RolesAndPermissionsSeeder → AdminSeeder → CategoryGroupSeeder → CategorySeeder → ArticleSeeder
```
