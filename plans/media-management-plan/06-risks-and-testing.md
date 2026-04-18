# Risks And Testing

## Main Risks

### 1. Crop Coordinate Correctness

- frontend crop math was already wrong once
- backend now also clamps crop geometry
- still needs real-world verification

### 2. Config Availability

- runtime showed `config('media.*')` could resolve to `null`
- service now contains fallbacks
- later cleanup may still be needed once config loading is stabilized

### 3. File Lifecycle

- soft delete currently keeps physical files by design
- deleted media is now marked with `file_cleanup_marked_at`
- manual cleanup tooling is still unresolved

### 4. Partial Module State

- the core media module is implemented
- the remaining gaps are hardening/lifecycle concerns, not missing CRUD basics
- a new chat should treat media as real, but not assume authorization and cleanup tooling are finished

## Testing Status

### Completed Checks

- PHP syntax checks in container
- route checks in container
- frontend production build in container
- live runtime debugging via Laravel logs
- Pest feature tests for:
  - create upload
  - validation failure
  - list search
  - category + tag filtering
  - detail tags
  - tag options
  - update metadata/crops/tags
  - soft delete cleanup marker

### Not Completed

- crop regression tests
- authorization tests
- manual cleanup flow tests

## Recommended Immediate Test Sequence

1. Add authorization tests for create/update/delete.
2. Add a few crop edge-case tests.
3. Decide whether to add a manual cleanup command for records marked with `file_cleanup_marked_at`.
