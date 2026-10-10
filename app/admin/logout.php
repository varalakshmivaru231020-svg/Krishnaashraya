<?php
declare(strict_types=1);

if (is_post()) {
    csrf_check();
    $_SESSION = [];
    session_regenerate_id(true);
}
redirect('admin/login');
