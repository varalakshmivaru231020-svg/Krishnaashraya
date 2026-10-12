-- Krishnashraya Educational Trust - database schema (MySQL 5.7+ / MariaDB 10.3+)
-- Run via the web installer (/install) or import in phpMyAdmin.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(64) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_categories (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(100) NOT NULL UNIQUE,
    title        VARCHAR(150) NOT NULL,
    description  TEXT NULL,
    cover_image  VARCHAR(255) NULL,
    banner_image VARCHAR(255) NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_photos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    image       VARCHAR(255) NOT NULL,
    caption     VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_photos_category (category_id, sort_order),
    CONSTRAINT fk_photos_category FOREIGN KEY (category_id) REFERENCES activity_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(150) NOT NULL UNIQUE,
    title       VARCHAR(255) NOT NULL,
    summary     TEXT NULL,
    description TEXT NULL,
    features    TEXT NULL,
    image       VARCHAR(255) NULL,
    status      ENUM('ongoing','completed') NOT NULL DEFAULT 'ongoing',
    goal_amount INT UNSIGNED NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    role       VARCHAR(120) NULL,
    message    TEXT NOT NULL,
    photo      VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- What a donor can give towards (Education, Food & Nutrition...). Managed in Admin -> Donation Purposes.
CREATE TABLE IF NOT EXISTS donation_purposes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- amount_paise: amounts are stored in paise (1 rupee = 100 paise).
-- Only the first name is required from donors; everything else is optional.
-- gateway: 'razorpay' or 'cashfree'; gateway_order_id / gateway_payment_id are that gateway's own IDs.
CREATE TABLE IF NOT EXISTS donations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ref                 CHAR(16) NOT NULL UNIQUE,
    name                VARCHAR(120) NOT NULL,
    email               VARCHAR(190) NULL,
    phone               VARCHAR(20) NULL,
    gender              VARCHAR(20) NULL,
    pan                 VARCHAR(10) NULL,
    amount_paise        INT UNSIGNED NOT NULL,
    currency            CHAR(3) NOT NULL DEFAULT 'INR',
    project_id          INT UNSIGNED NULL,
    purpose_id          INT UNSIGNED NULL,
    message             TEXT NULL,
    status              ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
    gateway             VARCHAR(20) NULL,
    gateway_order_id    VARCHAR(64) NULL,
    gateway_payment_id  VARCHAR(64) NULL,
    gateway_signature   VARCHAR(128) NULL,
    failure_reason      VARCHAR(255) NULL,
    followed_up         TINYINT(1) NOT NULL DEFAULT 0,
    admin_notes         TEXT NULL,
    ip                  VARCHAR(45) NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at             DATETIME NULL,
    INDEX idx_donations_status (status),
    INDEX idx_donations_created (created_at),
    INDEX idx_donations_order (gateway_order_id),
    CONSTRAINT fk_donations_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_donations_purpose FOREIGN KEY (purpose_id) REFERENCES donation_purposes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enquiries (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type        VARCHAR(30) NOT NULL DEFAULT 'general',
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    phone       VARCHAR(20) NULL,
    message     TEXT NOT NULL,
    status      ENUM('new','read','replied') NOT NULL DEFAULT 'new',
    admin_notes TEXT NULL,
    ip          VARCHAR(45) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enquiries_status (status),
    INDEX idx_enquiries_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed admin logins, used to slow down password guessing (per IP address).
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip           VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_ip (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Home page slider. Page banners for About / Projects / Activities / Contact / Donate live in settings.
-- media_type 'video': `video` is the clip and `image` its cover photo (shown while it loads and to visitors who prefer less motion).
CREATE TABLE IF NOT EXISTS home_slides (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
    image      VARCHAR(255) NULL,
    video      VARCHAR(255) NULL,
    title      VARCHAR(255) NULL,
    subtitle   VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Your Contribution Creates Measurable Change" figures on the home page. Admin -> Impact Numbers.
CREATE TABLE IF NOT EXISTS impact_stats (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    icon       VARCHAR(60) NOT NULL DEFAULT 'star-fill',
    prefix     VARCHAR(10) NULL,
    value      INT UNSIGNED NOT NULL DEFAULT 0,
    suffix     VARCHAR(10) NULL,
    label      VARCHAR(120) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
