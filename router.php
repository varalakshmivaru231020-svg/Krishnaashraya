<?php
// Router for PHP's built-in server (local development only):
//   php -S localhost:8000 router.php
// On Apache hosting, .htaccess does this job instead.

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/((assets|uploads)/|favicon\.ico$|site\.webmanifest$)#', $path) && is_file(__DIR__ . $path) && !preg_match('/\.php\d?$/i', $path)) {
    return false;
}
if (preg_match('#^/(app|templates|database)(/|$)#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
require __DIR__ . '/index.php';
