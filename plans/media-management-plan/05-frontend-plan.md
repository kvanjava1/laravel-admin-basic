# Frontend Plan

## Target Architecture

Follow the project pattern:

`View -> Composable -> Service -> useApi`

## Implemented / Planned Components

### Service

Implemented:

- `resources/js/admin-dashboard/services/mediaService.ts`

Current support:

- get paginated media
- show media
- store media
- update media
- delete media

### Composables

Implemented:

- `useMediaList.ts`
- `useMediaForm.ts`
- shared metadata options composable

Current support:

- create form state
- edit form state
- crop capture flow
- list pagination state
- create submit
- update submit
- delete flow support
- live index fetch
- advanced filtering by category and tags

### Views

Current state:

- `MediaCreate.vue`
  - live API-backed
- `MediaIndex.vue`
  - live API-backed
- `MediaEdit.vue`
  - live API-backed

### Cropper

Implemented:

- cropper uses a fixed viewport with movable image
- cropper emits clamped coordinates
- crop restore on reopen works
- mobile pan/pinch support exists

Still needs:

- real-world validation across multiple uploads

## Frontend Gaps

- source-image replacement in edit is not implemented
- crop still needs broader real-world validation
- dashboard remains unrelated placeholder UI
