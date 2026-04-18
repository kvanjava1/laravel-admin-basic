# Phases

## Phase 1. Domain And Contract Finalization

- status: completed
- purpose:
  - finalize media fields, storage, dimensions, delete behavior, and SEO scope

## Phase 2. Backend Foundation

- status: completed for current scope
- purpose:
  - establish migration, model, requests, repository, controller, and routes

## Phase 3. Image Processing And Storage

- status: completed for current flow
- purpose:
  - implement original retention, derivative generation, and storage pathing

## Phase 4. Backend Verification

- status: mostly completed for MVP
- purpose:
  - verify backend behavior, debug runtime issues, and add tests

## Phase 5. Frontend Service And State Layer

- status: completed
- purpose:
  - replace mock behavior with `service + composable` structure

## Phase 6. Frontend View Refactor

- status: completed
- purpose:
  - connect create/index UI to the real API

## Phase 7. Crop Accuracy Validation

- status: partially completed
- purpose:
  - validate crop correctness across real-world uploads and interaction patterns

## Phase 8. SEO And Publishing Readiness

- status: partially completed
- purpose:
  - make media reusable by other modules with stable metadata and rendering behavior

## Phase 9. Hardening And Cleanup

- status: partially completed
- purpose:
  - remove prototype leftovers, close lifecycle gaps, and document the final behavior

## MVP Delivery Slice

The current implementation has already moved beyond the original MVP slice.

The remaining practical path is:

1. Finish Phase 4 hardening gaps
2. Close Phase 7 crop-validation gaps
3. Decide how to operationalize Phase 9 file cleanup
