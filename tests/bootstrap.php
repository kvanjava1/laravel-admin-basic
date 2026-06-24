<?php

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Test Bootstrap
|--------------------------------------------------------------------------
|
| Clear any cached configuration so phpunit.xml env vars are respected.
| Without this, `php artisan config:cache` can cause tests to hit
| the real database instead of the :memory: SQLite defined in phpunit.xml.
|
*/

$cachedConfig = __DIR__.'/../bootstrap/cache/config.php';

if (file_exists($cachedConfig)) {
    @unlink($cachedConfig);
}
