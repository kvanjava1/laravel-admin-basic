# Project Context

This folder is a compact AI-facing context bundle for the `laravel-admin-basic` project.

Start here:

- `project_summary.md`
  What this project actually is, based on source code.
- `architecture_and_domain.md`
  Main modules, entities, workflows, and important boundaries.
- `runtime_and_docker.md`
  How this project is run in development, including the Docker-based workflow.
- `coding_conventions.md`
  Practical coding conventions and architectural patterns that are useful when modifying this codebase.

Important notes:

- Root `README.md` is still the default Laravel README and does not describe this application.
- Dashboard content is still largely placeholder/frontend-presentational.
- Media management is now backend-backed and should no longer be treated as a mock-only area.
- This folder intentionally separates:
  - current system facts, derived from code
  - implementation conventions and coding patterns, derived from how the project is structured
- If code and convention ever conflict, treat the codebase as the source of truth.
