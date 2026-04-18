# Runtime And Docker

## Development Runtime

This project is intended to run through Docker containers, based on the existing notes in `project_context_old/docker_containers/`.

Do not assume local host commands like plain `php artisan ...` or `npm ...` are the intended workflow.

## Containers

### Nginx

- container name: `nginx_php_dev`
- app domain: `http://laravel-admin-basic.test`
- image/media domain note: `media.laravel-admin-basic.test.conf`
- project path inside container: `/var/www/php/php8.2/laravel-admin-basic`

Important note from existing context:

- do not use `php artisan serve` to access the application in browser

### PHP

- container name: `php8.2`
- project path inside container: `/var/www/php/php8.2/laravel-admin-basic`
- recorded PHP version note: `PHP 8.2.30`
- recorded Composer note: `Composer version 2.9.3`

Intended usage pattern:

- run PHP / Composer / Artisan commands through `docker exec php8.2 ...`

### Node

- container name: `node20.19.1`
- project path inside container: `/var/www/node20.19.1/php8.2/laravel-admin-basic`
- recorded npm version note: `10.8.2`
- recorded node version note: `v18.17.1`

Important inconsistency:

- the container is named `node20.19.1`, but the recorded `node -v` output says `v18.17.1`
- treat this as an environment note inconsistency until verified

## App Entry Points

### Browser Access

- open `http://laravel-admin-basic.test`

### Web App

- Laravel serves a Blade shell from `/admin/{any?}`
- Vue mounts into `#app`

### API

- main API routes live in `routes/api.php`
- authenticated routes use `auth:sanctum`

## Build / Dev Commands In Repo

The repo still contains standard Laravel/Vite script definitions:

- Composer `dev`
- Composer `test`
- npm `dev`
- npm `build`

But project-specific context says command execution should happen through Docker containers, not directly on the host shell.

## Operational Notes

### Queue

- queue tables exist
- default queue connection is `database`
- Composer `dev` includes `php artisan queue:listen`

### Scheduled Tasks

- there is a command `app:release-expired-bans`
- automatic scheduler registration is `UNKNOWN / NOT FOUND IN CODE`

### Storage

- avatar uploads are written to Laravel `public` disk
- public storage symlink is configured through standard Laravel `storage:link`
