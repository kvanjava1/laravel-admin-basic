# Project Overview

## Identity

**Laravel Admin Basic** is a Laravel 12 + Vue 3 single-page application (SPA) that provides a full-featured admin dashboard for content and user management.

The SPA is served at `/admin` via a Blade catch-all route. The backend exposes a REST API at `/api` consumed by the Vue frontend.

## Technology Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.2 | Runtime |
| Laravel | 12.x | Framework |
| Laravel Sanctum | 4.x | Token-based API authentication |
| Spatie `laravel-permission` | 6.x | Role and permission management |
| Intervention Image | 3.x | Server-side image processing (crop, resize, WEBP conversion) |
| Ziggy | 2.x | Named Laravel routes accessible in JavaScript |
| Pest | 3.x | Testing framework (dev) |
| Laravel Breeze | 2.x | Scaffolding remnant (dev, not actively used) |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Vue | 3.4+ | UI framework (`<script setup lang="ts">`) |
| Vue Router | 4.6+ | Client-side routing |
| TypeScript | 5.6+ | Type safety |
| Vite | 7.x | Build tool (with `laravel-vite-plugin`) |
| Tailwind CSS | 3.x | Utility-first styling |
| Axios | 1.x | HTTP client |
| SweetAlert2 | 11.x | Dialogs, toasts, confirmations |
| Cropper.js | 1.x | Client-side image cropping |

### Database & Storage

| Component | Configuration |
|-----------|--------------|
| Database | SQLite (default, file at `database/database.sqlite`) |
| Session | `database` driver |
| Cache | `database` store |
| Queue | `database` connection |
| Filesystem | `local` default, `public` disk for uploads |
| Media storage | `public` disk under `media/YYYY/MM/DD/{uuid}/` |
| Avatar storage | `public` disk under `profile_pictures/YYYY/MM/DD/` |

## What This Project Is

- A production-quality admin dashboard with real backend-backed modules
- Modules: Auth, Users (with ban governance), Roles & Permissions, Categories, Media Library, Articles
- Professional image pipeline: upload → crop (16:9, 4:3) → resize → WEBP conversion → variant storage
- Article CMS with Tiptap rich-text editor, media bridge, SEO fields, and tag management
- Protected account/role governance with audit trail

## What This Project Is Not

- The root `README.md` is the default Laravel README (not project documentation)
- The dashboard page is mostly placeholder UI (no real analytics)
- There is no public-facing frontend — only the admin panel
- Email/notification features are not active (mail is set to `log` driver)
- No permission-based middleware beyond `auth:sanctum` on routes (the seeded permissions exist but are not enforced at route level)
