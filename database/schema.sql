-- Social Share Tracker
-- Import with: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS social_share_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE social_share_tracker;

CREATE TABLE IF NOT EXISTS platforms (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(32) NOT NULL,
    name VARCHAR(64) NOT NULL,
    color CHAR(7) NOT NULL DEFAULT '#64748b',
    share_url_template VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_platforms_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS share_clicks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    platform_id SMALLINT UNSIGNED NOT NULL,
    page_url VARCHAR(1000) NOT NULL,
    page_title VARCHAR(255) NOT NULL,
    page_path VARCHAR(255) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    clicked_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_clicks_clicked_at (clicked_at),
    KEY idx_clicks_platform_time (platform_id, clicked_at),
    KEY idx_clicks_path_time (page_path, clicked_at),
    KEY idx_clicks_ip_platform_time (ip_hash, platform_id, clicked_at),
    CONSTRAINT fk_clicks_platform
        FOREIGN KEY (platform_id) REFERENCES platforms (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO platforms (slug, name, color, share_url_template, is_active, sort_order) VALUES
    ('facebook', 'Facebook', '#1877F2', 'https://www.facebook.com/sharer/sharer.php?u={url}', 1, 1),
    ('x', 'X', '#0F172A', 'https://twitter.com/intent/tweet?url={url}&text={title}', 1, 2),
    ('whatsapp', 'WhatsApp', '#25D366', 'https://wa.me/?text={title}%20{url}', 1, 3),
    ('telegram', 'Telegram', '#229ED9', 'https://t.me/share/url?url={url}&text={title}', 1, 4),
    ('email', 'Email', '#EA4335', 'mailto:?subject={title}&body={description}%0A%0A{url}', 1, 5)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    color = VALUES(color),
    share_url_template = VALUES(share_url_template),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order);

-- Default login: admin / admin123
-- Change it after setup with: php scripts/set_admin_password.php <new-password>
INSERT IGNORE INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$3Ws/KBYCQTTOXQFD1xpa8Ot5gu/W3BX/JeYz8583JLzohs4S8B/AC');
