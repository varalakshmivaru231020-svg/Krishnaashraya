<?php
declare(strict_types=1);

// ---------------------------------------------------------------- database

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config('db');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'], $c['name'], $c['charset']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
    }
    return $pdo;
}

function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $v = $st->fetchColumn();
    return $v === false ? null : $v;
}

function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}

// ---------------------------------------------------------------- settings

function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        foreach (db_all('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return isset($all[$key]) && $all[$key] !== '' ? (string) $all[$key] : $default;
}

function setting_save(string $key, string $value): void
{
    db_exec(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

// ---------------------------------------------------------------- output

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escapes text and turns blank-line separated blocks into <p> paragraphs. */
function paragraphs(string $text, string $class = ''): string
{
    $out = '';
    $attr = $class !== '' ? ' class="' . e($class) . '"' : '';
    foreach (preg_split('/\R\s*\R/', trim($text)) as $p) {
        if (trim($p) !== '') {
            $out .= '<p' . $attr . '>' . nl2br(e(trim($p))) . "</p>\n";
        }
    }
    return $out;
}

/** Splits a textarea into non-empty trimmed lines. */
function lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), 'strlen'));
}

function url(string $path = ''): string
{
    return rtrim((string) config('app.base_url', ''), '/') . '/' . ltrim($path, '/');
}

/** URL for a stored image path (relative like "uploads/x.webp", or absolute http URL). */
function img(?string $path, string $fallback = 'assets/images/about-us-nurture-educate-empower.webp'): string
{
    $path = $path ?: $fallback;
    return preg_match('#^https?://#', $path) ? $path : url($path);
}

/** Formats an amount in rupees using Indian digit grouping: 1,00,000 */
function inr($rupees, bool $symbol = true): string
{
    $rupees = (float) $rupees;
    $whole = (string) (int) floor(abs($rupees));
    $paise = (int) round((abs($rupees) - floor(abs($rupees))) * 100);
    if (strlen($whole) > 3) {
        $last3 = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $whole = $rest . ',' . $last3;
    }
    $out = ($rupees < 0 ? '-' : '') . $whole . ($paise ? '.' . str_pad((string) $paise, 2, '0', STR_PAD_LEFT) : '');
    return $symbol ? '₹' . $out : $out;
}

function paise_to_inr(int $paise): string
{
    return inr($paise / 100);
}

function fmt_date(?string $datetime, string $format = 'd M Y, h:i A'): string
{
    return $datetime ? date($format, strtotime($datetime)) : '—';
}

function slugify(string $text): string
{
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $text !== '' ? substr($text, 0, 120) : 'item';
}

function unique_slug(string $table, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $candidate = $slug;
    $i = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE slug = ?" . ($ignoreId ? ' AND id <> ?' : '');
        $params = $ignoreId ? [$candidate, $ignoreId] : [$candidate];
        if ((int) db_value($sql, $params) === 0 && !in_array($candidate, reserved_slugs(), true)) {
            return $candidate;
        }
        $candidate = $slug . '-' . $i++;
    }
}

/** Top-level URLs taken by pages, so an activity category can never shadow them. */
function reserved_slugs(): array
{
    return ['about', 'projects', 'our-activities', 'contact', 'donate', 'admin', 'install', 'assets', 'uploads', 'app', 'templates', 'database'];
}

// ---------------------------------------------------------------- request / response

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function redirect(string $path): void
{
    // Paths starting with "/" (or full URLs) are used as-is; others are relative to the site root.
    header('Location: ' . (preg_match('#^(https?://|/)#', $path) ? $path : url($path)));
    exit;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * The visitor's IP address. Behind Cloudflare every request arrives from a Cloudflare server, so the real
 * address is taken from the CF-Connecting-IP header — but only when the request really comes from Cloudflare
 * (otherwise anyone could fake the header to dodge the login lock-out).
 */
function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $cf = (string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '');
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) && is_cloudflare_ip($remote)) {
        return substr($cf, 0, 45);
    }
    return substr($remote, 0, 45);
}

/** Cloudflare's published server ranges (https://www.cloudflare.com/ips/). */
function is_cloudflare_ip(string $ip): bool
{
    static $ranges = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
        '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];
    $bin = @inet_pton($ip);
    if ($bin === false) {
        return false;
    }
    foreach ($ranges as $range) {
        [$net, $bits] = explode('/', $range);
        $netBin = inet_pton($net);
        if (strlen($netBin) !== strlen($bin)) {
            continue; // IPv4 vs IPv6
        }
        $bytes = intdiv((int) $bits, 8);
        $rest = (int) $bits % 8;
        if (substr($bin, 0, $bytes) !== substr($netBin, 0, $bytes)) {
            continue;
        }
        if ($rest === 0) {
            return true;
        }
        $mask = chr((0xFF << (8 - $rest)) & 0xFF);
        if ((substr($bin, $bytes, 1) & $mask) === (substr($netBin, $bytes, 1) & $mask)) {
            return true;
        }
    }
    return false;
}

/** Simple per-session limiter: allows $max actions per $seconds for a given bucket. */
function rate_limited(string $bucket, int $max, int $seconds): bool
{
    $now = time();
    $hits = array_filter($_SESSION['rl'][$bucket] ?? [], fn($t) => $t > $now - $seconds);
    if (count($hits) >= $max) {
        $_SESSION['rl'][$bucket] = $hits;
        return true;
    }
    $hits[] = $now;
    $_SESSION['rl'][$bucket] = $hits;
    return false;
}

function not_found(): void
{
    http_response_code(404);
    render('404', ['title' => 'Page not found']);
    exit;
}

// ---------------------------------------------------------------- views

function render(string $view, array $data = [], string $layout = 'layout'): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require ROOT . '/templates/' . $view . '.php';
    $content = ob_get_clean();
    require ROOT . '/templates/' . $layout . '.php';
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require ROOT . '/templates/partials/' . $name . '.php';
}

// ---------------------------------------------------------------- csrf + flash

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

function csrf_check(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        exit('Your session expired. Please go back, refresh the page and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------------------------------------------------------------- validation

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/** Normalises an Indian mobile number to 10 digits, or returns null. */
function normalize_phone(string $phone): ?string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
        $digits = substr($digits, 2);
    } elseif (strlen($digits) === 11 && $digits[0] === '0') {
        $digits = substr($digits, 1);
    }
    return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
}

/** reCAPTCHA v2 ("I'm not a robot") keys: Admin -> Site Settings, falling back to app/config.php. */
function recaptcha_site_key(): string
{
    return setting('recaptcha_site_key', (string) config('recaptcha.site_key', ''));
}

function recaptcha_secret_key(): string
{
    return setting('recaptcha_secret_key', (string) config('recaptcha.secret_key', ''));
}

/** The checkbox is shown whenever a site key is set. */
function recaptcha_enabled(): bool
{
    return recaptcha_site_key() !== '';
}

/** Server-side check. Only possible with the secret key; without it the checkbox works in the browser only. */
function recaptcha_passes(): bool
{
    if (!recaptcha_enabled() || recaptcha_secret_key() === '') {
        return true;
    }
    $token = $_POST['g-recaptcha-response'] ?? '';
    if (!is_string($token) || $token === '') {
        return false;
    }
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => recaptcha_secret_key(), 'response' => $token, 'remoteip' => client_ip()]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res = json_decode((string) curl_exec($ch), true);
    curl_close($ch);
    return !empty($res['success']);
}

function notify_admin(string $subject, string $body): void
{
    $to = (string) config('mail.notify_to', '');
    if ($to === '') {
        return;
    }
    $headers = 'From: ' . config('mail.from', 'no-reply@localhost') . "\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

// ---------------------------------------------------------------- uploads

/**
 * Validates and stores an uploaded image under uploads/$folder.
 * Large photos are resized to $maxWidth and saved as WebP when GD supports it.
 * Returns the relative path (e.g. "uploads/activities/ab12cd.webp").
 */
function store_upload(array $file, string $folder, int $maxWidth = 1600): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $msg = [
            UPLOAD_ERR_INI_SIZE  => 'The image is larger than the server allows.',
            UPLOAD_ERR_FORM_SIZE => 'The image is too large.',
            UPLOAD_ERR_PARTIAL   => 'The image only partly uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE   => 'No image was chosen.',
        ][$file['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'The image could not be uploaded.';
        throw new RuntimeException($msg);
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Images must be 10 MB or smaller.');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Only JPG, PNG, WebP or GIF images are allowed.');
    }

    $folder = trim(preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)), '/');
    $dir = ROOT . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create the upload folder.');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6));

    $image = null;
    if (function_exists('imagewebp') && $info[2] !== IMAGETYPE_GIF) {
        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
            IMAGETYPE_PNG  => @imagecreatefrompng($file['tmp_name']),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file['tmp_name']),
            default        => null,
        };
    }

    if ($image) {
        if ($info[2] === IMAGETYPE_JPEG) {
            $image = exif_orient($image, $file['tmp_name']);
        }
        $w = imagesx($image);
        $h = imagesy($image);
        if ($w > $maxWidth) {
            $resized = imagescale($image, $maxWidth, (int) round($h * $maxWidth / $w), IMG_BICUBIC);
            imagedestroy($image);
            $image = $resized;
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        $target = "$dir/$name.webp";
        imagewebp($image, $target, 82);
        imagedestroy($image);
        return "uploads/$folder/$name.webp";
    }

    $target = "$dir/$name." . $types[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save the image.');
    }
    return "uploads/$folder/$name." . $types[$info[2]];
}

/** Phone photos store rotation in EXIF; apply it so images are not sideways. */
function exif_orient($image, string $path)
{
    if (!function_exists('exif_read_data')) {
        return $image;
    }
    $exif = @exif_read_data($path);
    $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
    if ($angle) {
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated) {
            imagedestroy($image);
            return $rotated;
        }
    }
    return $image;
}

/** Normalises the $_FILES structure of a multiple file input into a list. */
function uploaded_files(string $field): array
{
    if (empty($_FILES[$field]['name'])) {
        return [];
    }
    $f = $_FILES[$field];
    if (!is_array($f['name'])) {
        return $f['error'] === UPLOAD_ERR_NO_FILE ? [] : [$f];
    }
    $out = [];
    foreach ($f['name'] as $i => $n) {
        if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

/** Deletes a previously uploaded file, but never anything outside uploads/. */
function delete_upload(?string $path): void
{
    if ($path && str_starts_with($path, 'uploads/') && !str_contains($path, '..')) {
        $full = ROOT . '/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

// ---------------------------------------------------------------- site data used in the layout

function nav_categories(): array
{
    static $cats = null;
    return $cats ??= db_all('SELECT id, slug, title FROM activity_categories WHERE is_active = 1 ORDER BY sort_order, id');
}

function nav_projects(): array
{
    static $projects = null;
    return $projects ??= db_all("SELECT id, slug, title, status FROM projects WHERE is_active = 1 ORDER BY status = 'completed', sort_order, id");
}

function current_path(): string
{
    return $GLOBALS['__path'] ?? '';
}

function nav_active(string $prefix): string
{
    $p = current_path();
    if ($prefix === '') {
        return $p === '' ? ' active' : '';
    }
    return ($p === $prefix || str_starts_with($p, $prefix . '/')) ? ' active' : '';
}

function social_links(): array
{
    $out = [];
    foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'] as $network) {
        $link = setting('social_' . $network);
        if ($link !== '') {
            $out[$network] = $link;
        }
    }
    return $out;
}

function tel_href(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    return 'tel:+' . (strlen($digits) === 10 ? '91' . $digits : $digits);
}

function enquiry_types(): array
{
    return [
        'general'   => 'General enquiry',
        'volunteer' => 'I want to volunteer',
        'sponsor'   => 'Sponsor a child',
        'donation'  => 'Question about donating',
        'visit'     => 'Visit the home',
    ];
}

/** Active projects with the rupee amount raised through paid online donations. */
function projects_list(?string $status = null, ?int $limit = null): array
{
    $sql = "SELECT p.*, COALESCE((SELECT SUM(d.amount_paise) FROM donations d WHERE d.project_id = p.id AND d.status = 'paid'), 0) / 100 AS raised
              FROM projects p WHERE p.is_active = 1" . ($status ? ' AND p.status = ?' : '') . "
             ORDER BY p.status = 'completed', p.sort_order, p.id" . ($limit ? ' LIMIT ' . (int) $limit : '');
    return db_all($sql, $status ? [$status] : []);
}

/** Editable top banners of the inner pages: key => [admin label, default image, default title, default subtitle]. */
function page_banner_defaults(): array
{
    return [
        'about'      => ['About Us', 'assets/images/slider/slider-2.webp', 'About Us', "Nurture - Educate - Empower. Come together; we can change our children's lives for the better."],
        'projects'   => ['Our Projects', 'assets/images/slider/slider-3.webp', 'Our Projects', 'Making a real difference in the lives of underprivileged children'],
        'activities' => ['Our Activities', 'assets/images/slider/slider-4.webp', 'Our Activities', 'Capturing the joy, learning, and growth of our children'],
        'contact'    => ['Contact Us', 'assets/images/slider/slider-5.webp', 'Contact Us', "We'd love to hear from you. Reach out to learn more, visit us, volunteer or get involved."],
        'donate'     => ['Donate', 'assets/images/slider/slider-1.webp', 'Make a Donation', 'There is no joy greater than the joy of giving. Your gift gives a child food, a safe home and an education.'],
    ];
}

/** ['image' => ..., 'title' => ..., 'subtitle' => ...] for a page banner, falling back to the defaults. */
function page_banner(string $key): array
{
    [, $image, $title, $subtitle] = page_banner_defaults()[$key];
    return [
        'image'    => setting("banner_{$key}_image", $image),
        'title'    => setting("banner_{$key}_title", $title),
        'subtitle' => setting("banner_{$key}_subtitle", $subtitle),
    ];
}

/** Smallest donation accepted, in rupees (Admin -> Site Settings), never below ₹1. */
function donation_min_rupees(): int
{
    return max(1, (int) setting('donate_min', (string) config('donation.min_rupees', 1)));
}

/** Active donation purposes, in display order. */
function donation_purposes(): array
{
    static $rows = null;
    return $rows ??= db_all('SELECT id, title, description FROM donation_purposes WHERE is_active = 1 ORDER BY sort_order, id');
}

/** What a donation was given towards: the project, else the purpose, else "General Donation". */
function donation_towards(array $d): string
{
    return ($d['project_title'] ?? '') ?: (($d['purpose_title'] ?? '') ?: 'General Donation');
}

/** Every editable text of the About section (home page + About Us page), with its original wording. */
function about_defaults(): array
{
    return [
        'about_badge'         => 'About Us',
        'about_title'         => 'Nurture - Educate - Empower',
        'about_subtitle'      => "Come Together; we can change our children's lives for better",
        'about_heading'       => 'Sri Krishnashraya Educational Trust!',
        'about_text'          => '',
        'about_quote'         => '',
        'about_image'         => 'assets/images/about-us-nurture-educate-empower.webp',
        'about_stat1_value'   => '12+',
        'about_stat1_label'   => 'Years of Service',
        'about_stat2_value'   => '1,000+',
        'about_stat2_label'   => 'Children Helped',
        'about_stat3_value'   => '24/7',
        'about_stat3_label'   => 'Care & Support',
        'about_mission_label' => 'Our Mission',
        'mission_short'       => 'Providing a loving home and peaceful family for every child',
        'vm_title'            => "Guided by our core values and commitment to children's welfare",
        'goals'               => "Quality education for every child in our care\nNutritious meals and good health care every day\nA safe, loving home and a family for every child\nValues, life skills and confidence for an independent future",
        'vision'              => '',
        'mission'             => '',
        'our_work'            => '',
    ];
}

/** An About text. A box the admin cleared stays empty (so that line is hidden); unset keys use the original wording. */
function about(string $key): string
{
    $all = settings_all();
    return array_key_exists($key, $all) ? (string) $all[$key] : (about_defaults()[$key] ?? '');
}

/**
 * Splits a figure like "1,000+", "₹60,000" or "24/7" for the count-up animation.
 * Returns [prefix, number, suffix], or null when it is not a simple number (shown as plain text).
 */
function split_figure(string $value): ?array
{
    if (preg_match('/^([^\d]*)(\d[\d,]*)([^\d]*)$/u', trim($value), $m)) {
        return [$m[1], (int) str_replace(',', '', $m[2]), $m[3]];
    }
    return null;
}

/** Call-to-action panels (full-photo boxes near the bottom of pages). Edited in Admin -> Banners -> CTA panels. */
function cta_defaults(): array
{
    return [
        'projects' => ['label' => 'Projects page', 'page' => 'projects',
            'image' => 'assets/images/our-activities/education-programs/education-programs-img-10.webp', 'focus' => 'top',
            'eyebrow' => 'Every gift counts', 'heading' => 'Not sure which project to support?',
            'text' => "Give to the general fund and we'll put it where it is needed most — or talk to us about sponsoring a child."],
        'about' => ['label' => 'About Us page', 'page' => 'about',
            'image' => 'assets/images/our-activities/extracurricular-activities/extracurricular-activities-img-7.webp', 'focus' => 'top',
            'eyebrow' => 'Get involved', 'heading' => 'Visit us, volunteer, or support a child',
            'text' => 'You can avail tax exemption up to 50% on your financial support. We hold 80G and 12A certificates.'],
    ];
}

/** Which part of a photo stays visible when it is cropped: label => CSS object-position. */
function photo_focus_options(): array
{
    return ['top' => ['Top', 'center 12%'], 'upper' => ['Upper', 'center 30%'], 'center' => ['Centre', 'center 50%'], 'lower' => ['Lower', 'center 70%'], 'bottom' => ['Bottom', 'center 88%']];
}

function cta(string $key): array
{
    $d = cta_defaults()[$key];
    $out = $d;
    foreach (['image', 'focus', 'eyebrow', 'heading', 'text'] as $f) {
        $v = setting("cta_{$key}_{$f}");
        if ($v !== '') {
            $out[$f] = $v;
        }
    }
    $out['position'] = photo_focus_options()[$out['focus']][1] ?? 'center 12%';
    return $out;
}

/**
 * Curated font pairings: a display font for big headings + a text font for everything else.
 * Chosen in Admin -> Site Settings -> Website fonts.
 */
function font_pairs(): array
{
    return [
        'warm'    => ['label' => 'Warm & editorial', 'note' => 'Recommended — friendly, trustworthy, distinctive',
                      'head' => 'Fraunces', 'body' => 'Plus Jakarta Sans',
                      'query' => 'family=Fraunces:opsz,wght@9..144,400..800&family=Plus+Jakarta+Sans:wght@300..800'],
        'elegant' => ['label' => 'Elegant & classic', 'note' => 'Formal, traditional charity feel',
                      'head' => 'Playfair Display', 'body' => 'Lato',
                      'query' => 'family=Playfair+Display:wght@400..900&family=Lato:wght@300;400;700;900'],
        'gentle'  => ['label' => 'Gentle & readable', 'note' => 'Soft and very easy to read',
                      'head' => 'Lora', 'body' => 'Nunito Sans',
                      'query' => 'family=Lora:wght@400..700&family=Nunito+Sans:wght@300..800'],
        'modern'  => ['label' => 'Clean & modern', 'note' => 'One font everywhere, bold headings',
                      'head' => 'Poppins', 'body' => 'Poppins',
                      'query' => 'family=Poppins:wght@300;400;500;600;700;800'],
        'classic' => ['label' => 'Original', 'note' => 'The fonts the site used before',
                      'head' => 'Merriweather', 'body' => 'Montserrat',
                      'query' => 'family=Merriweather:opsz,wght@18..144,300..900&family=Montserrat:wght@300..800'],
    ];
}

function font_pair(): array
{
    $pairs = font_pairs();
    return $pairs[setting('font_pair', 'warm')] ?? $pairs['warm'];
}

/** <link> + CSS variables for the chosen fonts. */
function font_tags(): string
{
    $f = font_pair();
    $serif = in_array($f['head'], ['Fraunces', 'Playfair Display', 'Lora', 'Merriweather'], true) ? 'Georgia, serif' : 'system-ui, sans-serif';
    return '<link href="https://fonts.googleapis.com/css2?' . e($f['query']) . '&amp;display=swap" rel="stylesheet">' . "\n"
        . '    <style>:root{--font-head:"' . e($f['head']) . '",' . $serif . ';--font-body:"' . e($f['body']) . '",system-ui,-apple-system,"Segoe UI",sans-serif;}</style>';
}
