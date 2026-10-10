<?php
declare(strict_types=1);

// /robots.txt — lets search engines index the public site, keeps them out of the admin and
// donation receipts, and points them to the sitemap.

header('Content-Type: text/plain; charset=utf-8');

$base = rtrim((string) config('app.base_url', ''), '/');
$lines = [
    'User-agent: *',
    'Allow: /',
    "Disallow: $base/admin",
    "Disallow: $base/install",
    "Disallow: $base/donate/",   // order, payment-check and thank-you (receipt) pages; /donate itself stays indexed
    '',
    'Sitemap: ' . site_origin() . url('sitemap.xml'),
];

// A copy of the site running on a test address should not show up in Google at all.
if (is_local_site()) {
    $lines = ['# Local copy of the site: search engines are asked not to index it.', 'User-agent: *', 'Disallow: /'];
}

echo implode("\n", $lines), "\n";
