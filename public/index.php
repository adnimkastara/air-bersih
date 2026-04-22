<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$wantsJson = static function (): bool {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

    return str_starts_with($uri, '/api/')
        || str_contains($accept, 'application/json')
        || str_contains($contentType, 'application/json');
};

$renderUnavailable = static function (string $message = 'Service Unavailable') use ($wantsJson): never {
    http_response_code(503);

    if ($wantsJson()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Service Unavailable</h1>';

    exit;
};

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

$autoloadPath = __DIR__.'/../vendor/autoload.php';
if (! file_exists($autoloadPath)) {
    error_log('Laravel bootstrap failed: vendor/autoload.php is missing. Run "composer install --no-dev --optimize-autoloader".');
    $renderUnavailable('Service temporarily unavailable. Dependency autoloader missing.');
}

require $autoloadPath;

try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
} catch (Throwable $e) {
    error_log(sprintf('Laravel bootstrap failed: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    $renderUnavailable('Service temporarily unavailable. Application bootstrap failed.');
}

$app->handleRequest(Request::capture());
