<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);

$configFile = APP . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Missing app/config.php - copy app/config.sample.php to app/config.php and fill it in.');
}
$GLOBALS['__config'] = require $configFile;

date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));

if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('ksid');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => (config('app.base_url') ?: '') . '/',
    'secure'   => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require APP . '/helpers.php';
require APP . '/razorpay.php';
require APP . '/cashfree.php';
require APP . '/payments.php';
require APP . '/seo.php';

function config(string $key, $default = null)
{
    $value = $GLOBALS['__config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
