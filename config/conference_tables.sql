-- ============================================================
-- Conference Module – Somali Cardiac Society
-- Run this migration once to create all required tables.
-- ============================================================

-- -----------------------------------------------------------
-- 1. conferences  (the single "ongoing" + past records)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conferences` (
    `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`                  VARCHAR(255)  NOT NULL DEFAULT '',
    `slug`                   VARCHAR(255)  NOT NULL UNIQUE,
    `year`                   YEAR          NULL,
    `conference_year`        YEAR          NULL,
    `status`                 ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `hero_image`             VARCHAR(512)  NULL,
    `hero_text`              TEXT          NULL,
    `background_text`        LONGTEXT      NULL,
    `head_name`              VARCHAR(255)  NULL,
    `head_photo`             VARCHAR(512)  NULL,
    `head_message`           LONGTEXT      NULL,
    `objectives`             LONGTEXT      NULL,
    `who_should_attend`      JSON          NULL,
    `abstract_deadline`      DATETIME      NULL,
    `abstract_format`        LONGTEXT      NULL,
    `abstract_structure`     LONGTEXT      NULL,
    `abstract_review`        LONGTEXT      NULL,
    `reg_account_number`     VARCHAR(100)  NULL,
    `reg_bank_name`          VARCHAR(100)  NULL,
    `reg_account_holder`     VARCHAR(100)  NULL,
    `reg_process_text`       LONGTEXT      NULL,
    `is_past`                TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_is_past` (`is_past`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 2. conference_speakers
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_speakers` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`  INT UNSIGNED NOT NULL,
    `section`        ENUM('welcome','welcome_ceremony','keynote') NOT NULL DEFAULT 'keynote',
    `full_name`      VARCHAR(255) NOT NULL,
    `position`       VARCHAR(255) NULL,
    `position_title` VARCHAR(255) NULL,
    `photo`          VARCHAR(512) NULL,
    `is_active`      TINYINT(1)  NOT NULL DEFAULT 1,
    `display_order`  INT         NOT NULL DEFAULT 0,
    `created_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conf_section` (`conference_id`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 2b. conference_abstract_settings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_abstract_settings` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`       INT UNSIGNED NOT NULL,
    `submission_deadline` DATETIME NULL,
    `format_requirements` LONGTEXT NULL,
    `structure`           LONGTEXT NULL,
    `review_process`      LONGTEXT NULL,
    `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_abstract_settings_conference` (`conference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 3. conference_abstracts
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_abstracts` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`  INT UNSIGNED NOT NULL,
    `subtheme_id`    INT UNSIGNED NULL,
    `subtheme_title` VARCHAR(255) NULL,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL,
    `organization`   VARCHAR(255) NULL,
    `title`          VARCHAR(255) NOT NULL,
    `summary`        TEXT NULL,
    `file_path`      VARCHAR(512) NULL,
    `status`         ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conference_status` (`conference_id`, `status`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. conference_subthemes
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_subthemes` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`  INT UNSIGNED NOT NULL,
    `title`          VARCHAR(255) NOT NULL,
    `detail`         TEXT         NULL,
    `is_active`      TINYINT(1)  NOT NULL DEFAULT 1,
    `display_order`  INT         NOT NULL DEFAULT 0,
    `created_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conference_id` (`conference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. conference_evaluation_criteria
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_evaluation_criteria` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`  INT UNSIGNED NOT NULL,
    `title`          VARCHAR(255) NOT NULL,
    `detail`         TEXT         NULL,
    `is_active`      TINYINT(1)  NOT NULL DEFAULT 1,
    `display_order`  INT         NOT NULL DEFAULT 0,
    `created_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conference_id` (`conference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. conference_registrations
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_registrations` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`     INT UNSIGNED NOT NULL,
    `full_name`         VARCHAR(255) NULL,
    `email`             VARCHAR(255) NOT NULL,
    `phone`             VARCHAR(50)  NULL,
    `organization`      VARCHAR(255) NULL,
    `country`           VARCHAR(100) NULL,
    `category`          ENUM('student','professional','ngo','ingo') NOT NULL DEFAULT 'professional',
    `first_name`        VARCHAR(100) NULL,
    `last_name`         VARCHAR(100) NULL,
    `dietary`           VARCHAR(100) NULL,
    `special_needs`     TEXT         NULL,
    `payment_screenshot` VARCHAR(512) NULL,
    `student_id_path`   VARCHAR(512) NULL,
    `payment_phone`     VARCHAR(50) NULL,
    `status`            ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    `admin_notes`       TEXT         NULL,
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `registered_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conference_status` (`conference_id`, `status`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5b. conference_registration_settings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_registration_settings` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conference_id`  INT UNSIGNED NOT NULL,
    `process_text`   LONGTEXT NULL,
    `account_number` VARCHAR(100) NULL,
    `bank_name`      VARCHAR(150) NULL,
    `account_holder` VARCHAR(150) NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_registration_settings_conference` (`conference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5c. past_conferences
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `past_conferences` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) NOT NULL UNIQUE,
    `body`            LONGTEXT NULL,
    `conference_date` DATE NULL,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_past_active` (`is_active`),
    KEY `idx_past_date` (`conference_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. past_conference_gallery
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `past_conference_gallery` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `past_conference_id` INT UNSIGNED NOT NULL,
    `image_path`     VARCHAR(512) NOT NULL,
    `caption`        VARCHAR(255) NULL,
    `display_order`  INT         NOT NULL DEFAULT 0,
    `created_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
        KEY `idx_past_conference_order` (`past_conference_id`, `display_order`),
        CONSTRAINT `fk_past_gallery_conference` FOREIGN KEY (`past_conference_id`) REFERENCES `past_conferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Archive gallery; the admin application limits each conference to 10 images';
