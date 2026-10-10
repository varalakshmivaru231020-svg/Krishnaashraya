<?php
declare(strict_types=1);

// Admin -> SEO: Google title + description for every page, Google connections, and the SEO audit.

$pages = seo_pages();
$keys = array_column($pages, 'key');

if (is_post()) {
    csrf_check();
    $action = input('action');

    if ($action === 'page' && in_array(input('key'), $keys, true)) {
        $key = input('key');
        setting_save("seo_{$key}_title", mb_substr(preg_replace('/\s+/', ' ', input('title')), 0, 120));
        setting_save("seo_{$key}_description", mb_substr(preg_replace('/\s+/', ' ', input('description')), 0, 320));
        flash('success', 'Search settings saved for "' . ($pages[array_search($key, $keys, true)]['label']) . '".');
        redirect('admin/seo');
    }

    if ($action === 'reset' && in_array(input('key'), $keys, true)) {
        setting_save('seo_' . input('key') . '_title', '');
        setting_save('seo_' . input('key') . '_description', '');
        flash('success', 'Back to the automatic title and description.');
        redirect('admin/seo');
    }

    if ($action === 'google') {
        $errors = [];
        $ga = trim((string) ($_POST['ga_measurement_id'] ?? ''));
        $gsc = trim((string) ($_POST['gsc_verification'] ?? ''));
        if ($ga !== '') {
            // The whole Google tag / Tag Manager code, or just the ID — only the ID is kept.
            if (preg_match('/\b(G-[A-Z0-9]{4,15}|GTM-[A-Z0-9]{4,12})\b/i', $ga, $m)) {
                $ga = strtoupper($m[1]);
            } else {
                $errors[] = 'No Google Analytics ID found. Paste the Google tag code, or an ID that starts with G- (or GTM- for Tag Manager).';
            }
        }
        if ($gsc !== '') {
            if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $gsc, $m)) {
                $gsc = $m[1];
            }
            if (!preg_match('/^[A-Za-z0-9_\-]{10,100}$/', $gsc)) {
                $errors[] = 'The Search Console code does not look right. Paste the whole <meta …> tag from Search Console.';
            }
        }
        if ($errors) {
            foreach ($errors as $err) {
                flash('error', $err);
            }
        } else {
            setting_save('ga_measurement_id', $ga);
            setting_save('gsc_verification', $gsc);
            flash('success', 'Google settings saved.');
        }
        redirect('admin/seo#google');
    }
    redirect('admin/seo');
}

$rows = [];
foreach ($pages as $p) {
    $rows[] = seo_for_path($p['path']);
}

admin_render('seo', [
    'title' => 'SEO',
    'rows'  => $rows,
    'ga'    => setting('ga_measurement_id'),
    'gsc'   => setting('gsc_verification'),
]);
