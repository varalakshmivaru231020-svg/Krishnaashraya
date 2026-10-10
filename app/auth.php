<?php
declare(strict_types=1);

function current_admin(): ?array
{
    static $admin = false;
    if ($admin === false) {
        $id = $_SESSION['admin_id'] ?? null;
        $admin = $id ? db_one('SELECT id, name, email FROM admins WHERE id = ?', [$id]) : null;
    }
    return $admin;
}

function require_admin(): void
{
    if (!current_admin()) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('admin/login');
    }
    // Admin pages must never be cached or framed.
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
}

function admin_login(string $email, string $password): bool
{
    $row = db_one('SELECT * FROM admins WHERE email = ?', [strtolower(trim($email))]);
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $row['id'];
    db_exec('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$row['id']]);
    return true;
}

function admin_render(string $view, array $data = []): void
{
    render('admin/' . $view, $data, 'admin/layout');
}

/** Builds a pagination descriptor: ['page','pages','offset','limit','total']. */
function paginate(int $total, int $perPage = 25): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage, 'limit' => $perPage, 'total' => $total];
}

/** Current query string with some keys replaced, for filter / pagination links. */
function qs(array $replace): string
{
    $q = array_merge($_GET, $replace);
    unset($q['slug']);
    $q = array_filter($q, fn($v) => $v !== null && $v !== '');
    return $q ? '?' . http_build_query($q) : '';
}

function csv_download(string $filename, array $header, iterable $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows ₹ and Indian names correctly
    fputcsv($out, $header);
    foreach ($rows as $row) {
        // Prevent spreadsheet formula injection from user-entered values.
        $row = array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v, $row);
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

/** Moves a row one place up or down in a sort_order list, renumbering the list 10, 20, 30… */
function move_row(string $table, int $id, string $direction): void
{
    $ids = array_map('intval', array_column(db_all("SELECT id FROM `$table` ORDER BY sort_order, id"), 'id'));
    $pos = array_search($id, $ids, true);
    $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
    if ($pos !== false && isset($ids[$swap])) {
        [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
    }
    foreach ($ids as $i => $rowId) {
        db_exec("UPDATE `$table` SET sort_order = ? WHERE id = ?", [($i + 1) * 10, $rowId]);
    }
}
