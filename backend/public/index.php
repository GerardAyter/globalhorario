<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// En producció (Plesk), el projecte Laravel viu fora del web root.
// En local, viu al directori pare de public/ com de costum.
$laravelBase = file_exists(__DIR__.'/../bootstrap/app.php')
    ? __DIR__.'/..'                    // local: estructura normal
    : __DIR__.'/../../laravel';        // producció: fora del web root

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelBase.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
