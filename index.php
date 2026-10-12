<?php
declare(strict_types=1);

// Every request (except real files in assets/ and uploads/) is routed through here.

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// The installer must work before app/config.php has database tables.
require __DIR__ . '/app/bootstrap.php';

$base = rtrim((string) config('app.base_url', ''), '/');
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$path = trim(rawurldecode($path), '/');
// Old static site used folder/index.html URLs.
$path = preg_replace('#(^|/)index\.(html|php)$#', '', $path);
$GLOBALS['__path'] = $path;

$routes = [
    ''                 => 'pages/home.php',
    'about'            => 'pages/about.php',
    'projects'         => 'pages/projects.php',
    'our-activities'   => 'pages/activities.php',
    'contact'          => 'pages/contact.php',
    'donate'           => 'pages/donate.php',
    'donate/order'     => 'pages/donate-order.php',
    'donate/verify'    => 'pages/donate-verify.php',
    'donate/failed'    => 'pages/donate-failed.php',
    'donate/webhook'   => 'pages/donate-webhook.php',
    'donate/webhook/cashfree' => 'pages/donate-webhook-cashfree.php',
    'donate/thank-you' => 'pages/donate-thanks.php',
    'install'          => 'pages/install.php',
    'sitemap.xml'      => 'pages/sitemap.php',
    'robots.txt'       => 'pages/robots.php',

    'admin'                    => 'admin/dashboard.php',
    'admin/login'              => 'admin/login.php',
    'admin/logout'             => 'admin/logout.php',
    'admin/donations'          => 'admin/donations.php',
    'admin/donations/view'     => 'admin/donation-view.php',
    'admin/donations/export'   => 'admin/donations-export.php',
    'admin/enquiries'          => 'admin/enquiries.php',
    'admin/enquiries/view'     => 'admin/enquiry-view.php',
    'admin/enquiries/export'   => 'admin/enquiries-export.php',
    'admin/projects'           => 'admin/projects.php',
    'admin/projects/edit'      => 'admin/project-edit.php',
    'admin/activities'         => 'admin/activities.php',
    'admin/activities/edit'    => 'admin/activity-edit.php',
    'admin/testimonials'       => 'admin/testimonials.php',
    'admin/testimonials/edit'  => 'admin/testimonial-edit.php',
    'admin/settings'           => 'admin/settings.php',
    'admin/payments'           => 'admin/payments.php',
    'admin/purposes'           => 'admin/purposes.php',
    'admin/banners'            => 'admin/banners.php',
    'admin/banners/slide'      => 'admin/banner-slide.php',
    'admin/banners/page'       => 'admin/banner-page.php',
    'admin/banners/cta'        => 'admin/banner-cta.php',
    'admin/impact'             => 'admin/impact.php',
    'admin/about'              => 'admin/about.php',
    'admin/seo'                => 'admin/seo.php',
    'admin/recaptcha'          => 'admin/recaptcha.php',
    'admin/popup'              => 'admin/popup.php',
    'admin/account'            => 'admin/account.php',
];

try {
    if (isset($routes[$path])) {
        if (str_starts_with($path, 'admin')) {
            require APP . '/auth.php';
            if ($path !== 'admin/login') {
                require_admin();
            }
        }
        require APP . '/' . $routes[$path];
        exit;
    }

    // /projects/{slug}
    if (preg_match('#^projects/([a-z0-9-]+)$#', $path, $m)) {
        $_GET['slug'] = $m[1];
        require APP . '/pages/project.php';
        exit;
    }

    // /{activity-category-slug}, e.g. /education-programs (keeps the old URLs working)
    if (preg_match('#^([a-z0-9-]+)$#', $path, $m)) {
        $category = db_one('SELECT * FROM activity_categories WHERE slug = ? AND is_active = 1', [$m[1]]);
        if ($category) {
            require APP . '/pages/activity.php';
            exit;
        }
    }

    not_found();
} catch (PDOException $ex) {
    error_log((string) $ex);
    http_response_code(500);
    if (config('app.debug')) {
        echo '<pre>' . e((string) $ex) . '</pre>';
    }
    echo '<p style="font-family:sans-serif;padding:2rem">The website database is not reachable right now. '
        . 'If you are the site owner, check the database settings in app/config.php, or run <a href="' . e(url('install')) . '">/install</a>.</p>';
}
