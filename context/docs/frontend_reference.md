# Frontend Reference

## SPA Architecture

The Vue SPA lives at `resources/js/admin-dashboard/`. It is initialized from `resources/js/app.ts` → `admin-dashboard/index.ts` and mounted into a Blade shell served at `/admin/{any?}`.

### Entry Point Chain

```
resources/js/app.ts
  → admin-dashboard/index.ts       # createApp(App), use(router), mount('#app')
    → admin-dashboard/App.vue      # Minimal root: <router-view />
      → router.ts                  # Route definitions + navigation guards
```

### State Management

No Vuex or Pinia. State is managed entirely through:
- **Composables** with module-scoped `ref()` / `computed()` for page-level state
- **`useAuth()`** with global singleton `ref()` backed by `localStorage` for auth state
- **`useApi()`** with a global singleton Axios instance + interceptors

## Routing

All routes use **lazy-loaded** components. Routes are grouped by module arrays then spread into the main route config.

### Route Structure

```
/admin/login                    → LoginIndex.vue           (meta: guest)
/admin/                         → AdminLayout wrapper
  /admin/dashboard              → Dashboard.vue            (meta: auth)
  /admin/users                  → UserIndex.vue
  /admin/users/create           → UserCreate.vue
  /admin/users/:id/edit         → UserEdit.vue
  /admin/users/:id/status       → UserStatusManagement.vue
  /admin/roles                  → RoleIndex.vue
  /admin/roles/create           → RoleCreate.vue
  /admin/roles/:id/edit         → RoleEdit.vue
  /admin/categories             → CategoryManagement.vue
  /admin/categories/create      → CategoryCreate.vue
  /admin/categories/:id/edit    → CategoryEdit.vue
  /admin/media                  → MediaIndex.vue
  /admin/media/create           → MediaCreate.vue
  /admin/media/:id/edit         → MediaEdit.vue
  /admin/articles               → ArticleIndex.vue
  /admin/articles/create        → ArticleCreate.vue
  /admin/articles/:id/edit      → ArticleEdit.vue
  /admin/profile                → ProfileEdit.vue
  /admin                        → redirects to dashboard
```

### Navigation Guards

- Routes with `meta.auth`: redirect to `/admin/login` if no token in localStorage
- Routes with `meta.guest`: redirect to `/admin/dashboard` if token exists

## Components

### Layout (`components/layout/`)

| Component | Purpose |
|-----------|---------|
| `AdminLayout.vue` | Main shell: sidebar + header + `<router-view>`. Responsive with mobile overlay. |
| `Sidebar.vue` | Navigation sidebar with module links filtered by `usePermission` |
| `Header.vue` | Top bar with page title and sidebar toggle |

### Base UI (`components/ui/`)

Atomic, reusable components used across all modules:

| Component | Purpose |
|-----------|---------|
| `BaseInput.vue` | Text/email/password input with label, hint, error display |
| `BaseSelect.vue` | Dropdown select with label and error |
| `BaseButton.vue` | Button with variants and loading state |
| `BaseModal.vue` | Modal dialog wrapper |
| `BasePanel.vue` | Card/panel container |
| `BaseLabel.vue` | Form label |
| `BaseEditor.vue` | Tiptap rich text editor integration |
| `BaseImageCropper.vue` | Cropper.js integration for profile images |
| `MediaImageCropper.vue` | Extended cropper for media (16:9 + 4:3 dual crop) |
| `BaseAvatarUpload.vue` | Avatar upload with preview |
| `BaseTagsInput.vue` | Tag input with autocomplete |
| `BasePageContainer.vue` | Standard page wrapper |
| `BasePageHeader.vue` | Page title + action buttons header |
| `ActionMenu.vue` | Dropdown action menu for table rows |
| `TablePagination.vue` | Pagination controls |
| `ActivityFeed.vue` | Activity timeline display |
| `Figure.ts` | Tiptap figure node extension |
| `table-atomic/` | Table sub-components |

### Domain Components

| Directory | Components |
|-----------|-----------|
| `components/article/` | `ArticleDetailsModal`, `ArticleFilterModal`, `ArticleStatusBadge`, `MediaLibraryModal` |
| `components/media/` | `MediaDetailsModal`, `MediaFilterModal`, `MediaUploadWorkflow` |
| `components/role/` | Role-specific components |
| `components/user/` | User-specific components |
| `components/dashboard/` | Dashboard widgets |

## Composables (`composables/`)

| Composable | Module | Key Responsibilities |
|------------|--------|---------------------|
| `useApi.ts` | Core | Singleton Axios instance, Bearer token injection, 401 redirect, typed request methods |
| `useAuth.ts` | Core | Global auth state (`user`, `token`), `setAuth()`, `clearAuth()`, `isAuthenticated` |
| `useArticleForm.ts` | Article | Create/edit form state, validation, submission |
| `useArticleList.ts` | Article | Paginated list, filters, loading state |
| `useCategoryForm.ts` | Category | Form state for create/edit |
| `useCategoryList.ts` | Category | Category tree loading and management |
| `useMediaForm.ts` | Media | Upload/edit form with crop data |
| `useMediaList.ts` | Media | Paginated media grid |
| `useMediaMetadataOptions.ts` | Media | Shared category and tag option loading |
| `useProfileForm.ts` | Profile | Profile edit form with avatar upload |
| `useRoleForm.ts` | Role | Role create/edit with permission sync |
| `useUserForm.ts` | User | User create/edit form |
| `useUserList.ts` | User | Paginated user list with filters |
| `useUserStatus.ts` | User | Governance actions (ban, restore, activate, deactivate) |
| `usePermission.ts` | Core | Permission checker (`can(perm)`, `canAny(...perms)`) backed by `user.permissions` from auth response |

## Services (`services/`)

All services export an object literal and use `useApi()` for HTTP calls. Named route generation uses Ziggy (`route()` function).

| Service | Methods |
|---------|---------|
| `authService.ts` | `login()`, `logout()`, `getUser()` |
| `userService.ts` | `getPaginated()`, `store()`, `show()`, `update()`, `destroy()`, `getStatuses()`, `banUser()`, `unbanUser()`, `activateUser()`, `deactivateUser()`, `getBanHistory()` |
| `roleService.ts` | `getPaginated()`, `store()`, `show()`, `update()`, `destroy()`, `getOptions()`, `getPermissions()` |
| `categoryService.ts` | `getGroups()`, `getTree()`, `store()`, `show()`, `update()`, `destroy()` |
| `mediaService.ts` | `getPaginated()`, `store()`, `show()`, `update()`, `destroy()`, `getTagOptions()` |
| `articleService.ts` | `getPaginated()`, `store()`, `show()`, `update()`, `destroy()`, `getStatuses()` |
| `profileService.ts` | `getProfile()`, `updateProfile()` |
| `tagService.ts` | `getOptions()` |

### Article Status Note

`articleService.getStatuses()` returns **static data** (not from API):
- `draft` → `text-slate-500`
- `published` → `text-green-600`
- `scheduled` → `text-blue-600`

## Views (`views/`)

| Directory | Files | Notes |
|-----------|-------|-------|
| `views/auth/` | `LoginIndex.vue` | Login page |
| `views/Dashboard.vue` | Single file | Placeholder dashboard |
| `views/user/` | `UserIndex`, `UserCreate`, `UserEdit`, `UserStatusManagement` | Full CRUD + governance |
| `views/role/` | `RoleIndex`, `RoleCreate`, `RoleEdit` | Role management |
| `views/profile/` | `ProfileEdit` | Self-profile editing |
| `views/category/` | `CategoryManagement`, `CategoryCreate`, `CategoryEdit` | Category tree management |
| `views/media/` | `MediaIndex`, `MediaCreate`, `MediaEdit` | Media library |
| `views/post/article/` | `ArticleIndex`, `ArticleCreate`, `ArticleEdit` | Article CMS |

Note: Article views are nested under `views/post/article/` (not `views/article/`). This accommodates potential future post types under the `post/` namespace.

## Permissions

Permissions from the API login/me response (`user.permissions`) are checked via `usePermission()`:

```typescript
import { usePermission } from '../composables/usePermission';
const { can, canAny } = usePermission();

// Hide button if user lacks permission
<BaseButton v-if="can('create-users')">Add User</BaseButton>

// Hide section if user lacks any of these
<div v-if="canAny('view-media', 'view-articles')">...</div>
```

The `Sidebar.vue` uses `can()` to show/hide navigation menus based on `view-*` permissions.

## Utilities (`utils/`)

### `sweetalert.ts`

Centralized SweetAlert2 configuration. Exports:
- `colors` — Design system color constants
- `commonConfig` — Shared popup styling (rounded-2xl, button styles)
- `toast` — Pre-configured toast mixin (top-end, 3s timer)
- `alertService` — Ready-to-use methods:
  - `successToast(title)` — Green toast
  - `infoToast(title)` — Blue toast
  - `errorToast(title, text?)` — Red toast
  - `confirm({ title, text, icon, danger })` — Confirmation dialog
  - `info(title, html)` — Info modal with HTML
  - `alert(title, text, icon)` — Generic alert
