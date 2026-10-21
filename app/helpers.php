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

/**
 * URL for a CSS / JS file with its last-changed time added (?v=...). The server tells browsers to keep
 * these files for years, so a new version needs a new address to reach visitors straight away.
 */
function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    return url($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
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

/** reCAPTCHA v2 ("I'm not a robot") keys: Admin -> Spam Protection, falling back to app/config.php. */
function recaptcha_site_key(): string
{
    return setting('recaptcha_site_key', (string) config('recaptcha.site_key', ''));
}

function recaptcha_secret_key(): string
{
    return setting('recaptcha_secret_key', (string) config('recaptcha.secret_key', ''));
}

/**
 * 'v2' = the "I'm not a robot" checkbox; 'v3' = invisible, Google scores each visitor in the background.
 * Must match the type chosen when the keys were created at google.com/recaptcha/admin.
 */
function recaptcha_type(): string
{
    return setting('recaptcha_type', 'v2') === 'v3' ? 'v3' : 'v2';
}

/** Lowest v3 score (0.0 = bot, 1.0 = human) that is accepted. */
const RECAPTCHA_V3_MIN_SCORE = 0.5;

/**
 * Spam protection is active on the contact form when switched on and a site key is set.
 * v3 has no checkbox and only works with the server-side check, so it also needs the secret key.
 */
function recaptcha_enabled(): bool
{
    if (setting('recaptcha_on', '1') !== '1' || recaptcha_site_key() === '') {
        return false;
    }
    return recaptcha_type() === 'v2' || recaptcha_secret_key() !== '';
}

/** Asks Google about a token. Returns Google's answer (success, score, action, error-codes…) or ['error' => …]. */
function recaptcha_verify(string $secret, string $token): array
{
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => client_ip()]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $body = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($body === false) {
        return ['success' => false, 'error' => 'Could not reach Google: ' . $error];
    }
    return json_decode((string) $body, true) ?: ['success' => false];
}

/**
 * Admin key test: sends a real token (a v2 tick, or a v3 background check) to Google with the secret key.
 * Returns '' when both keys work together, otherwise a plain explanation.
 * (Google cannot check a secret key on its own, so the admin page creates a real token first.)
 */
function recaptcha_test_keys(string $secret, string $token): string
{
    $res = recaptcha_verify($secret, $token);
    if (!empty($res['success'])) {
        return '';
    }
    if (!empty($res['error'])) {
        return $res['error'];
    }
    $codes = (array) ($res['error-codes'] ?? []);
    if (array_intersect($codes, ['invalid-input-secret', 'missing-input-secret'])) {
        return 'Google says the secret key is not valid. Copy it again from google.com/recaptcha/admin.';
    }
    if (in_array('timeout-or-duplicate', $codes, true)) {
        return 'The check expired (they last 2 minutes) or was already used. Try the test again straight away.';
    }
    return 'Google did not accept the check with this secret key. Make sure the secret key belongs to the same reCAPTCHA as the site key, and that the key type is right.';
}

/** Server-side check of a contact form message. Without the secret key a v2 checkbox works in the browser only. */
function recaptcha_passes(): bool
{
    if (!recaptcha_enabled() || recaptcha_secret_key() === '') {
        return true;
    }
    $token = $_POST['g-recaptcha-response'] ?? '';
    if (!is_string($token) || $token === '') {
        return false;
    }
    $res = recaptcha_verify(recaptcha_secret_key(), $token);
    if (empty($res['success'])) {
        return false;
    }
    if (recaptcha_type() === 'v3') {
        // v3: also check the score and that the token was made for the contact form.
        return (float) ($res['score'] ?? 0) >= RECAPTCHA_V3_MIN_SCORE && ($res['action'] ?? 'contact') === 'contact';
    }
    return true;
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

/** Largest upload the server accepts, in bytes (the smaller of PHP's two limits). */
function server_upload_limit(): int
{
    $toBytes = function (string $v): int {
        $v = trim($v);
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) { 'g' => $n * 1024 ** 3, 'm' => $n * 1024 ** 2, 'k' => $n * 1024, default => $n };
    };
    $limits = array_filter([$toBytes((string) ini_get('upload_max_filesize')), $toBytes((string) ini_get('post_max_size'))]);
    return $limits ? min($limits) : 8 * 1024 ** 2;
}

/** Without ffmpeg a video is shown exactly as uploaded, so it has to be small already. */
const VIDEO_DIRECT_MAX_MB = 40;
/** With ffmpeg, bigger videos are accepted and shrunk on the server. */
const VIDEO_OPTIMIZE_MAX_MB = 500;

/** Path to the ffmpeg program that shrinks slider videos, or '' when the server has none. */
function ffmpeg_path(): string
{
    static $path = null;
    if ($path !== null) {
        return $path;
    }
    // ~/bin/ffmpeg next to the site's htdocs folder (CloudPanel), then the usual system places.
    foreach (array_filter([(string) config('video.ffmpeg', ''), dirname(ROOT, 2) . '/bin/ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg']) as $candidate) {
        if (is_file($candidate) && is_executable($candidate)) {
            return $path = $candidate;
        }
    }
    if (function_exists('exec')) {
        $out = [];
        @exec(PHP_OS_FAMILY === 'Windows' ? 'where ffmpeg 2>NUL' : 'command -v ffmpeg 2>/dev/null', $out);
        $found = trim((string) ($out[0] ?? ''));
        if ($found !== '' && is_file($found)) {
            return $path = $found;
        }
    }
    return $path = '';
}

function video_can_optimize(): bool
{
    return function_exists('exec') && ffmpeg_path() !== '';
}

/** Biggest slider video allowed: 500 MB when the server can shrink it, otherwise 40 MB (or less if the server is set lower). */
function video_upload_limit(): int
{
    return min((video_can_optimize() ? VIDEO_OPTIMIZE_MAX_MB : VIDEO_DIRECT_MAX_MB) * 1024 ** 2, server_upload_limit());
}

/** The PHP command-line program (PHP_BINARY is php-fpm on hosting). */
function php_cli_binary(): string
{
    if (PHP_BINARY !== '' && !preg_match('/fpm|cgi/i', basename(PHP_BINARY)) && is_file(PHP_BINARY)) {
        return PHP_BINARY;
    }
    foreach (['/usr/bin/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, '/usr/bin/php', '/usr/local/bin/php'] as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }
    return 'php';
}

/** Starts app/tools/video-optimize.php for a slide without making the admin wait. */
function video_optimize_in_background(int $slideId): void
{
    $cmd = escapeshellarg(php_cli_binary()) . ' ' . escapeshellarg(ROOT . '/app/tools/video-optimize.php') . ' ' . $slideId;
    if (PHP_OS_FAMILY === 'Windows') {
        pclose(popen('start "" /B ' . $cmd . ' >NUL 2>&1', 'r'));
    } else {
        exec('nohup ' . $cmd . ' > /dev/null 2>&1 &');
    }
}

/**
 * Makes a web version of an uploaded video: H.264 MP4, at most 1920x1080 and 30 fps, no sound, first 60 seconds,
 * "fast start" so it begins playing while it downloads. Also takes a still for the cover photo.
 * Returns [web video path, cover photo path or null]. Throws if ffmpeg fails.
 */
function video_optimize(string $source): array
{
    $in = ROOT . '/' . $source;
    $base = preg_replace('/\.[^.\/]+$/', '', $source);
    $video = $base . '-web.mp4';
    $poster = $base . '-cover.jpg';
    $ff = escapeshellarg(ffmpeg_path());
    $nice = PHP_OS_FAMILY === 'Windows' ? '' : 'nice -n 10 ';
    $out = [];
    exec($nice . $ff . ' -hide_banner -loglevel error -y -i ' . escapeshellarg($in)
        . ' -map 0:v:0 -t 300 -an -sn -dn -fpsmax 30'
        . ' -vf ' . escapeshellarg("scale='min(1920,iw)':'min(1080,ih)':force_original_aspect_ratio=decrease:force_divisible_by=2,format=yuv420p")
        . ' -c:v libx264 -preset veryfast -crf 28 -maxrate 2500k -bufsize 5M -threads 2 -movflags +faststart '
        . escapeshellarg(ROOT . '/' . $video) . ' 2>&1', $out, $code);
    clearstatcache();
    if ($code !== 0 || !is_file(ROOT . '/' . $video) || filesize(ROOT . '/' . $video) < 1024) {
        delete_upload($video);
        throw new RuntimeException('ffmpeg: ' . trim(implode(' ', array_slice($out, -3))));
    }
    $still = [];
    exec($ff . ' -hide_banner -loglevel error -y -ss 1 -i ' . escapeshellarg(ROOT . '/' . $video)
        . ' -frames:v 1 -q:v 3 ' . escapeshellarg(ROOT . '/' . $poster) . ' 2>&1', $still, $stillCode);
    return [$video, $stillCode === 0 && is_file(ROOT . '/' . $poster) ? $poster : null];
}

/**
 * Validates and stores an uploaded MP4 / WebM video under uploads/$folder. Returns the relative path.
 * The real file contents are checked (not just the name), so other files can't be passed off as videos.
 */
function store_video_upload(array $file, string $folder): string
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('The video is too large. The limit is ' . round(video_upload_limit() / 1024 ** 2) . ' MB — please shorten or compress it.');
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException($error === UPLOAD_ERR_NO_FILE ? 'No video was chosen.' : 'The video could not be uploaded. Please try again.');
    }
    if ($file['size'] > video_upload_limit()) {
        throw new RuntimeException('The video is too large. The limit is ' . round(video_upload_limit() / 1024 ** 2) . ' MB — please shorten or compress it.');
    }
    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/x-m4v' => 'mp4'];
    if (video_can_optimize()) {
        // The server turns these into MP4, so phone and camera formats are fine too.
        $types += ['video/quicktime' => 'mov', 'video/3gpp' => '3gp', 'video/x-matroska' => 'mkv', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpg'];
    }
    if (!isset($types[$mime])) {
        throw new RuntimeException(video_can_optimize()
            ? 'This file is not a video the website can use. Please choose an MP4, MOV or WebM video.'
            : 'Only MP4 or WebM videos are allowed' . ($mime === 'video/quicktime' ? ' (iPhone .MOV files must be saved as MP4 first).' : '.'));
    }
    $folder = trim(preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)), '/');
    $dir = ROOT . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create the upload folder.');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) {
        throw new RuntimeException('Could not save the video.');
    }
    return "uploads/$folder/$name";
}

// ---------------------------------------------------------------- website popup (Admin -> Website Popup)

/** Starting values for the popup. Stored in settings as popup_<key>. */
function popup_defaults(): array
{
    return [
        'on'        => '0',
        'layout'    => 'card',      // card: photo + heading + message + button · poster: the photo alone (a flyer), clickable
        'image'     => '',
        'title'     => 'Help a Child Go to School',
        'text'      => 'Your gift pays for books, a uniform and a warm meal for a child at Krishnashraya.',
        'button'    => 'Donate Now',
        'link'      => 'donate',
        'pages'     => 'home',      // home | all
        'frequency' => 'session',   // session: once per visit · day: once a day · always: every time a page opens
        'delay'     => '3',         // seconds after the page opens
        'start'     => '',          // optional Y-m-d dates
        'end'       => '',
    ];
}

/** The popup as saved in the admin (an empty heading stays empty, unlike setting()). */
function popup(): array
{
    $all = settings_all();
    $out = [];
    foreach (popup_defaults() as $k => $d) {
        $out[$k] = array_key_exists("popup_$k", $all) && $all["popup_$k"] !== null ? (string) $all["popup_$k"] : $d;
    }
    return $out;
}

/** Turns a button link typed in the admin into a URL: a page of this site ("donate", "contact") or a full http(s) address. '' if unusable. */
function popup_link(string $link): string
{
    $link = trim($link);
    if ($link === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $link)) {
        return filter_var($link, FILTER_VALIDATE_URL) ? $link : '';
    }
    $link = ltrim($link, '/');
    return preg_match('/^[a-z0-9\/_.?=&%#-]*$/i', $link) ? url($link) : '';
}

/** Where the popup stands today: [state, headline, detail]; state is full (showing), half (scheduled/ended) or off. */
function popup_status(array $p): array
{
    $where = $p['pages'] === 'all' ? 'on every page' : 'on the home page';
    $often = ['session' => 'once per visit', 'day' => 'once a day', 'always' => 'every time a page opens'][$p['frequency']] ?? 'once per visit';
    $today = date('Y-m-d');
    if ($p['on'] !== '1') {
        return ['off', 'The popup is turned off', 'Visitors don\'t see it. Switch it on below when you have something to announce.'];
    }
    if ($p['start'] !== '' && $today < $p['start']) {
        return ['half', 'Scheduled — starts on ' . date('j M Y', strtotime($p['start'])), "It will then show $where, $often."];
    }
    if ($p['end'] !== '' && $today > $p['end']) {
        return ['half', 'Ended on ' . date('j M Y', strtotime($p['end'])), 'Change or clear the "Show until" date to show it again.'];
    }
    return ['full', 'Showing ' . $where . ' now', ucfirst($often) . ', ' . ((int) $p['delay'] ? $p['delay'] . ' seconds after the page opens' : 'as soon as the page opens') . ($p['end'] !== '' ? ', until ' . date('j M Y', strtotime($p['end'])) : '') . '.'];
}

/** Whether the popup belongs on the page being shown. Never on the donate pages, so a payment is not interrupted. */
function popup_visible(): bool
{
    $p = popup();
    if (popup_status($p)[0] !== 'full') {
        return false;
    }
    if ($p['layout'] === 'poster' ? $p['image'] === '' : ($p['image'] === '' && $p['title'] === '' && $p['text'] === '')) {
        return false;
    }
    $path = current_path();
    if ($path === 'donate' || str_starts_with($path, 'donate/')) {
        return false;
    }
    return $p['pages'] === 'all' || $path === '';
}

// ---------------------------------------------------------------- donation receipt (Admin -> Site Settings -> Donation receipt)

/** What the receipt prints when nothing else has been set. */
function receipt_defaults(): array
{
    return [
        'receipt_org'            => 'Sri Krishnashraya Educational Trust',
        'receipt_tagline'        => '(Registered Charitable Trust)',
        'receipt_address'        => '# 88/1, 84/1, 84/5, Sant Baba Hardas Ram Dam, Thammarasanahalli, Chikka Haralagere, Sulibele, Bangalore - 562129.',
        'receipt_pan'            => 'AATTS6672D',
        'receipt_12aa'           => 'AATTS6672D25BL01',
        'receipt_80g'            => 'AATTS6672D25BL02',
        'receipt_email'          => 'krishnashrayahome@gmail.com',
        'receipt_closing'        => 'Thanks for your support!',
        'receipt_thanks'         => 'Thank you for your generous contribution to Sri Krishnashraya Educational Trust. Your support enables us to provide education, shelter, nutrition, healthcare, and holistic development for orphan, semi-orphan, and underprivileged children.',
        'receipt_prefix'         => 'SKET/WEB',
        'receipt_statement_prefix' => 'SKET/DCS',
        'receipt_donor_prefix'   => 'DIDW',
    ];
}

function receipt_text(string $key): string
{
    return setting($key, receipt_defaults()[$key] ?? '');
}

/**
 * The donation's receipt number, e.g. SKET/WEB/2026/00001 — a separate series per year for website donations,
 * given once when the donation is paid (or the first time an older paid donation's receipt is opened).
 */
function donation_receipt_no(array $d): string
{
    if (!empty($d['receipt_no'])) {
        return (string) $d['receipt_no'];
    }
    if (($d['status'] ?? '') !== 'paid') {
        return '';
    }
    $year = date('Y', strtotime($d['paid_at'] ?: $d['created_at']));
    $key = "receipt_seq_$year";
    db_exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, '0')", [$key]);
    db_exec('UPDATE settings SET setting_value = LAST_INSERT_ID(CAST(setting_value AS UNSIGNED) + 1) WHERE setting_key = ?', [$key]);
    $no = sprintf('%s/%s/%05d', rtrim(receipt_text('receipt_prefix'), '/'), $year, (int) db_value('SELECT LAST_INSERT_ID()'));
    db_exec('UPDATE donations SET receipt_no = ? WHERE id = ? AND receipt_no IS NULL', [$no, $d['id']]);
    return (string) db_value('SELECT receipt_no FROM donations WHERE id = ?', [$d['id']]);
}

/** Donor ID for the receipt: the same for every paid donation from one phone number or email address. */
function donation_donor_id(array $d): string
{
    $first = (int) $d['id'];
    if ($d['phone'] || $d['email']) {
        $first = (int) (db_value(
            "SELECT MIN(id) FROM donations WHERE status = 'paid' AND ((phone <> '' AND phone = ?) OR (email <> '' AND email = ?))",
            [(string) $d['phone'], (string) $d['email']]
        ) ?: $first);
    }
    return sprintf('%s%05d', receipt_text('receipt_donor_prefix'), $first);
}

/** 4000 -> "Rupees Four Thousand Only" (Indian system: thousand, lakh, crore). */
function amount_in_words(float $amount): string
{
    $paiseTotal = (int) round($amount * 100);
    $rupees = intdiv($paiseTotal, 100);
    $paise = $paiseTotal % 100;
    return 'Rupees ' . ($rupees ? number_in_words($rupees) : 'Zero') . ($paise ? ' and ' . number_in_words($paise) . ' Paise' : '') . ' Only';
}

function number_in_words(int $n): string
{
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
             'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    if ($n < 20) {
        return $ones[$n];
    }
    if ($n < 100) {
        return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
    }
    if ($n < 1000) {
        return trim($ones[intdiv($n, 100)] . ' Hundred ' . number_in_words($n % 100));
    }
    foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand']] as [$size, $name]) {
        if ($n >= $size) {
            return trim(number_in_words(intdiv($n, $size)) . " $name " . number_in_words($n % $size));
        }
    }
    return '';
}
