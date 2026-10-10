<?php
declare(strict_types=1);

/** Fills an empty database with the content of the original static website. */
function seed_database(): void
{
    $pdo = db();

    // ---------------------------------------------------------- settings
    $settings = [
        'site_name'          => 'Sri Krishnashraya Educational Trust',
        'tagline'            => 'Empowering Lives Through Education and Care',
        'hero_title'         => "There Is No Joy\nGreater than the Joy\nof Giving",
        'phone_1'            => '+91 96207 94141',
        'phone_2'            => '+91 99860 64411',
        'email_1'            => 'aakrishnashraya@gmail.com',
        'email_2'            => 'krishnashrayahome@gmail.com',
        'address_registered' => "#50, Sri Krishnashraya Educational Trust, 2nd Cross\nRifco Shantiniketan Layout,\nNear SJES College, Medahalli, Bangalore 560049",
        'address_center'     => "Sri Krishnashraya Educational Trust, #88/1,\nSant Baba Hardas Ram Dham, Thammarasanahalli, Chikka Haralagere,\nSulibele, Bangalore - 562 129",
        'maps_url'           => 'https://maps.app.goo.gl/LEXxH6quqUyy7U1aA',
        'maps_embed'         => '',
        'visiting_hours'     => "Monday - Saturday\n8:00 AM - 7:00 PM IST\nSunday: Holiday",
        'whatsapp_number'    => '919364077341',
        'bank_name'          => 'ICICI BANK',
        'bank_account_name'  => 'Sri Krishnashraya Educational Trust',
        'bank_account_no'    => '390405000044',
        'bank_ifsc'          => 'ICIC0003904',
        'bank_branch'        => 'Ulsoor Road',
        'upi'                => '+91 70199 77341',
        'tax_note'           => 'You can avail tax exemption up to 50% on your financial support. We hold 80G and 12A certificates.',
        'social_facebook'    => '',
        'social_twitter'     => '',
        'social_instagram'   => '',
        'social_linkedin'    => '',
        'social_youtube'     => '',
        'stat_years'         => '12',
        'stat_children'      => '1000',
        'stat_cost_per_child'=> '60000',
        'donate_presets'     => '1000,2000,5000,10000',
        'donate_min'         => '1',
        'about_heading'      => 'Sri Krishnashraya Educational Trust!',
        'about_text'         => "Sri Krishnashraya Educational Trust has been working in Bangalore for more than 12 years. Focusing on providing food, shelter and education to deprived children. These children come with many difficulties, including lack of proper nutrition, love and care.\n\nWe strive to provide these children with proper nutrition, dress, shelter and education. This helps them improve their lives and livelihood. We make them understand their rights and responsibilities and give them an equal opportunity in learning.",
        'about_quote'        => 'We believe that in future these children could be an asset to our great nation.',
        'about_image'        => 'assets/images/about-us-nurture-educate-empower.webp',
        'about_badge'        => 'About Us',
        'about_title'        => 'Nurture - Educate - Empower',
        'about_subtitle'     => "Come Together; we can change our children's lives for better",
        'about_stat1_value'  => '12+',
        'about_stat1_label'  => 'Years of Service',
        'about_stat2_value'  => '1,000+',
        'about_stat2_label'  => 'Children Helped',
        'about_stat3_value'  => '24/7',
        'about_stat3_label'  => 'Care & Support',
        'about_mission_label' => 'Our Mission',
        'mission_short'      => 'Providing a loving home and peaceful family for every child',
        'vision'             => 'Our vision is to reach, support and educate orphaned, semi-orphaned, underprivileged, destitute and the needy.',
        'mission'            => 'The mission works hard to provide a loving home and peaceful family for every child. We also strive to promote family relationships and impart a spirit of responsibility in every member of our organisation.',
        'our_work'           => "Sri Krishnashraya Educational Trust believes in providing equal opportunity to poor children. We help the children break the shackles of poverty and make them independent and productive for the nation. Since no one chooses to be poor, Sri Krishnashraya is designed to transform the lives of these impoverished children.\n\nAt our house facility, children are not only fed, educated and given health care, but values like respect, responsibility, integrity and a love for learning are inculcated to maximize their human potential. We focus on the holistic development of the child so that they can live with dignity and pride.",
        'recaptcha_site_key' => '6LddkRUtAAAAAIoWJ84tzzQKEydLhNxelyRE1vaF',
        'footer_about'       => 'Empowering lives through education and care. Working tirelessly to provide food, shelter, and quality education to underprivileged children in Bangalore.',
    ];
    $st = $pdo->prepare('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($settings as $k => $v) {
        $st->execute([$k, $v]);
    }

    // ---------------------------------------------------------- activity categories + photos
    if ((int) db_value('SELECT COUNT(*) FROM activity_categories') === 0) {
        $base = 'assets/images/our-activities/';
        $categories = [
            ['education-programs', 'Education Programs', 'Quality schooling, tuition, bridge courses and skill training that help every child excel in studies and build a brighter future.', 'education-programs', 'education-programs-img-', 12, 7],
            ['food-and-nutrition', 'Food & Nutrition', 'Nutritious, balanced meals every day so that children grow up healthy, strong and ready to learn.', 'food-and-nutrition', 'food-and-nutrition-img-', 12, 12],
            ['community-support', 'Community Support', 'Reaching out to families and neighbourhoods with essentials, care and support when they need it most.', 'community-support', 'community-support-img-', 10, 3],
            ['extracurricular-activities', 'Extracurricular Activities', 'Sports, music, art, yoga and cultural activities that build confidence, teamwork and joy.', 'extracurricular-activities', 'extracurricular-activities-img-', 12, 7],
            ['events-and-initiatives', 'Events & Initiatives', 'Festivals, celebrations, awareness drives and special initiatives with our children and supporters.', 'events-and-initiatives', 'events-initiatives-img-', 6, 4],
            ['donor-testimonials', 'Donor Testimonials', 'Moments shared with the generous donors and well-wishers who make our work possible.', 'donor-testimonials', 'donor-testimonials-img-', 12, 9],
            ['media-gallery', 'Media Gallery', 'News coverage, photos and stories from life at Krishnashraya.', 'media-gallery', 'media-gallery-img-', 6, 1],
        ];
        $insCat = $pdo->prepare('INSERT INTO activity_categories (slug, title, description, cover_image, sort_order) VALUES (?, ?, ?, ?, ?)');
        $insPhoto = $pdo->prepare('INSERT INTO activity_photos (category_id, image, caption, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($categories as $i => [$slug, $title, $desc, $folder, $prefix, $count, $cover]) {
            $insCat->execute([$slug, $title, $desc, "$base$folder/$prefix$cover.webp", ($i + 1) * 10]);
            $catId = (int) $pdo->lastInsertId();
            for ($n = 1; $n <= $count; $n++) {
                $insPhoto->execute([$catId, "$base$folder/$prefix$n.webp", null, $n * 10]);
            }
        }
    }

    // ---------------------------------------------------------- projects
    if ((int) db_value('SELECT COUNT(*) FROM projects') === 0) {
        db_exec(
            'INSERT INTO projects (slug, title, summary, description, features, image, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                'support-and-education-of-underprivileged-children',
                'Support & Education of Underprivileged Children Who Have Lost One or Both Parents',
                'Sri Krishnashraya Educational Trust supports children in their holistic development, where education is one of the most important needs. We provide quality education for children along with training programs and bridge courses to help them excel in their studies, improve their skills, and build a brighter future.',
                'With proper education, guidance, and support, children can achieve their goals and build a successful future with confidence and dignity.',
                "Quality education for every child\nAccess to schools, libraries, and learning resources\nSupport from qualified and caring faculty\nMotivational exposure programs and skill development activities",
                'assets/images/our-activities/education-programs/education-programs-img-9.webp',
                'ongoing',
                10,
            ]
        );
    }

    // ---------------------------------------------------------- home slider
    seed_home_slides();

    // ---------------------------------------------------------- donation purposes
    seed_donation_purposes();

    // ---------------------------------------------------------- home page impact figures
    seed_impact_stats();

    // ---------------------------------------------------------- testimonials
    if ((int) db_value('SELECT COUNT(*) FROM testimonials') === 0) {
        $t = $pdo->prepare('INSERT INTO testimonials (name, role, message, sort_order) VALUES (?, ?, ?, ?)');
        $t->execute(['Radhika M.', 'Donor', 'Krishnashraya has given my child hope and a future. The care and education they provide is transforming lives. I am forever grateful for their dedication and love.', 10]);
        $t->execute(['Arun Kumar', 'Volunteer', "Volunteering at Krishnashraya has been the most rewarding experience of my life. Seeing the children's smiles and their eagerness to learn fills my heart with joy.", 20]);
        $t->execute(['Priya Sharma', 'Donor', "The work Krishnashraya does is truly inspiring. As a donor, I'm proud to support an organization that treats every child with dignity and provides them opportunities to thrive.", 30]);
    }
}

/** Adds columns introduced after the first release to an existing database. Safe to run repeatedly. */
function upgrade_database(): void
{
    $has = fn(string $table, string $col) => (bool) db_value(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
        [$table, $col]
    );
    db()->exec("CREATE TABLE IF NOT EXISTS home_slides (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, image VARCHAR(255) NOT NULL, title VARCHAR(255) NULL, subtitle VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    seed_home_slides();
    if (!$has('donations', 'gender')) {
        db()->exec('ALTER TABLE donations ADD COLUMN gender VARCHAR(20) NULL AFTER phone');
    }
    // Payment columns became gateway-neutral when Cashfree was added next to Razorpay.
    if ($has('donations', 'razorpay_order_id')) {
        db()->exec(
            'ALTER TABLE donations
                CHANGE razorpay_order_id   gateway_order_id   VARCHAR(64) NULL,
                CHANGE razorpay_payment_id gateway_payment_id VARCHAR(64) NULL,
                CHANGE razorpay_signature  gateway_signature  VARCHAR(128) NULL'
        );
    }
    if (!$has('donations', 'gateway')) {
        db()->exec('ALTER TABLE donations ADD COLUMN gateway VARCHAR(20) NULL AFTER status');
        db()->exec("UPDATE donations SET gateway = 'razorpay' WHERE gateway_order_id IS NOT NULL");
    }

    // Donation form v2: only the first name is required, donors pick a purpose, and "cancelled" is its own status.
    db()->exec("CREATE TABLE IF NOT EXISTS donation_purposes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(120) NOT NULL, description VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    seed_donation_purposes();
    db()->exec("ALTER TABLE donations
        MODIFY email VARCHAR(190) NULL,
        MODIFY phone VARCHAR(20) NULL,
        MODIFY status ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending'");
    if (!$has('donations', 'purpose_id')) {
        db()->exec('ALTER TABLE donations ADD COLUMN purpose_id INT UNSIGNED NULL AFTER project_id,
            ADD CONSTRAINT fk_donations_purpose FOREIGN KEY (purpose_id) REFERENCES donation_purposes(id) ON DELETE SET NULL');
    }
    // New suggested amounts — only replace the old default, never amounts the trust chose itself.
    db_exec("UPDATE settings SET setting_value = '1000,2000,5000,10000' WHERE setting_key = 'donate_presets' AND setting_value = '500,1000,2500,5000,10000'");
    db_exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('donate_min', '1')");

    // Home page impact figures became editable.
    db()->exec("CREATE TABLE IF NOT EXISTS impact_stats (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, icon VARCHAR(60) NOT NULL DEFAULT 'star-fill', prefix VARCHAR(10) NULL,
        value INT UNSIGNED NOT NULL DEFAULT 0, suffix VARCHAR(10) NULL, label VARCHAR(120) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    seed_impact_stats();

    // About section became fully editable: carry the old "years" and "children" numbers over.
    $years = (int) setting('stat_years', '12') ?: 12;
    $children = (int) setting('stat_children', '1000') ?: 1000;
    foreach ([
        'about_badge' => 'About Us', 'about_title' => 'Nurture - Educate - Empower',
        'about_subtitle' => "Come Together; we can change our children's lives for better",
        'about_stat1_value' => $years . '+', 'about_stat1_label' => 'Years of Service',
        'about_stat2_value' => number_format($children) . '+', 'about_stat2_label' => 'Children Helped',
        'about_stat3_value' => '24/7', 'about_stat3_label' => 'Care & Support', 'about_mission_label' => 'Our Mission',
    ] as $k => $v) {
        db_exec('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)', [$k, $v]);
    }
}

/** The four figures of the original home page. Only runs when the list is empty. */
function seed_impact_stats(): void
{
    if ((int) db_value('SELECT COUNT(*) FROM impact_stats') > 0) {
        return;
    }
    $st = db()->prepare('INSERT INTO impact_stats (icon, prefix, value, suffix, label, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
    $st->execute(['currency-rupee', null, (int) setting('stat_cost_per_child', '60000'), null, 'Cost per child/year', 10]);
    $st->execute(['calendar-check', null, 3, '+', 'Years of support needed', 20]);
    $st->execute(['bullseye', null, 100, '%', 'Holistic development', 30]);
    $st->execute(['cash-stack', null, 50, '%', 'Tax exemption (80G)', 40]);
}

/** The trust's standard donation purposes. Only runs when the list is empty. */
function seed_donation_purposes(): void
{
    if ((int) db_value('SELECT COUNT(*) FROM donation_purposes') > 0) {
        return;
    }
    $st = db()->prepare('INSERT INTO donation_purposes (title, description, sort_order) VALUES (?, ?, ?)');
    $st->execute(['General Donation', 'Used wherever it is needed most', 10]);
    $st->execute(['Education', 'School fees, books, tuition and learning materials', 20]);
    $st->execute(['Food & Nutrition', 'Healthy daily meals for the children', 30]);
    $st->execute(['Shelter & Care', 'A safe home, clothing and health care', 40]);
}

/** The five slides of the original site. Only runs when the slider is empty. */
function seed_home_slides(): void
{
    if ((int) db_value('SELECT COUNT(*) FROM home_slides') > 0) {
        return;
    }
    $st = db()->prepare('INSERT INTO home_slides (image, title, subtitle, sort_order) VALUES (?, ?, ?, ?)');
    for ($i = 1; $i <= 5; $i++) {
        $st->execute(["assets/images/slider/slider-$i.webp", "There Is No Joy\nGreater than the Joy\nof Giving", 'Empowering Lives Through Education and Care', $i * 10]);
    }
}
