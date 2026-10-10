<?php
declare(strict_types=1);

$stats = db_one(
    "SELECT
        COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_paise END), 0) AS total_paid,
        COUNT(CASE WHEN status = 'paid' THEN 1 END) AS count_paid,
        COALESCE(SUM(CASE WHEN status = 'paid' AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN amount_paise END), 0) AS month_paid,
        COUNT(CASE WHEN status = 'paid' AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) AS month_count,
        COUNT(CASE WHEN status <> 'paid' AND followed_up = 0 THEN 1 END) AS to_follow_up,
        COUNT(DISTINCT CASE WHEN status = 'paid' THEN COALESCE(NULLIF(email, ''), phone, CONCAT('#', id)) END) AS donors
     FROM donations"
);

admin_render('dashboard', [
    'title'           => 'Dashboard',
    'stats'           => $stats,
    'newEnquiries'    => (int) db_value("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
    'recentDonations' => db_all('SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id ORDER BY d.created_at DESC LIMIT 8'),
    'recentEnquiries' => db_all('SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 6'),
    'monthly'         => db_all(
        "SELECT DATE_FORMAT(paid_at, '%Y-%m') AS ym, SUM(amount_paise) AS total, COUNT(*) AS cnt
           FROM donations WHERE status = 'paid' AND paid_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
          GROUP BY ym ORDER BY ym"
    ),
    'counts'          => [
        'projects'     => (int) db_value('SELECT COUNT(*) FROM projects WHERE is_active = 1'),
        'categories'   => (int) db_value('SELECT COUNT(*) FROM activity_categories WHERE is_active = 1'),
        'photos'       => (int) db_value('SELECT COUNT(*) FROM activity_photos'),
        'testimonials' => (int) db_value('SELECT COUNT(*) FROM testimonials WHERE is_active = 1'),
    ],
]);
