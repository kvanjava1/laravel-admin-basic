# Project Summary

## What This Project Is

This is a Laravel 12 + Vue 3 admin dashboard SPA for:

- authentication via Laravel Sanctum token API
- user management
- role and permission management
- protected account / protected role governance
- user ban, restore, activate, and deactivate workflows
- hierarchical category management
- media management with upload, crop, metadata, category, tag, detail, edit, and soft-delete flows
- authenticated profile editing with avatar upload and image cropping

The SPA is mounted under `/admin`, and the backend API is exposed under `/api`.

## What This Project Is Not

- The root `README.md` is the default Laravel README, not project-specific documentation.
- The dashboard page is mostly placeholder UI, not a real analytics dashboard.

## Technology Stack

### Backend

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- Spatie `laravel-permission`
- Intervention Image

### Frontend

- Vue 3
- Vue Router
- TypeScript
- Vite
- Tailwind CSS
- Axios
- SweetAlert2

### Database / Storage

- SQLite is the default configured database
- Laravel filesystem disks: `local`, `public`, optional `s3`
- avatar images are stored on the `public` disk
- media images are stored on the `public` disk under date + UUID paths

## Primary Business Surface

### User Governance

The strongest domain logic in this project is around account governance:

- users have statuses: `Active`, `Inactive`, `Banned`
- bans may be `permanent` or `temporary`
- ban actions are audited in `ban_histories`
- some accounts and roles are protected by config and cannot be freely modified

### Roles And Permissions

Roles and permissions are managed using Spatie Permission. A special protected role exists:

- `Super Administrator`

The seeded protected account is:

- `admin@admin.com`

### Media Management

Media management is now a real backend-backed module, not a placeholder.

Current capabilities:

- upload image with required 16:9 and 4:3 crops
- generate WEBP original output plus four variants
- assign media category from the `Image` category group
- assign reusable tags
- browse media library with advanced filters
- view detail modal with metadata and variant inventory
- edit media metadata, tags, category, and crop coordinates
- soft delete media while marking files for later cleanup

Important media behavior:

- generated output format is WEBP
- variant sizes are persisted in the database
- tags are stored relationally via `tags` and `media_tag`
- delete does not remove files immediately; it sets `file_cleanup_marked_at`

## Important Caveats

- Permission middleware / policy enforcement is `UNKNOWN / NOT FOUND IN CODE` for route-level access control beyond authentication.
- Automatic scheduling of expired ban release is `UNKNOWN / NOT FOUND IN CODE`, even though a console command exists.
- Several older tests still target old Breeze-style web auth routes and do not match the current SPA/API architecture.
- Media feature tests now exist and use explicit cleanup because global `RefreshDatabase` was removed after it touched the real SQLite file when config cache was stale.
- The project has recognizable coding conventions, but some files diverge from the ideal layer boundaries. See `coding_conventions.md`.
