-- =============================================================
--  Somali Cardiac Society — Conference Module
--  conference_tables.sql
--  Run this against your scs database to install all 9 tables.
--  Safe to run multiple times (IF NOT EXISTS on every table).
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- 1. conferences
--    The single "active" row represents the current/upcoming
--    annual conference. Multiple rows can exist; only the one
--    with status='active' is shown publicly.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conferences` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `title`            VARCHAR(255)  NOT NULL,
  `slug`             VARCHAR(255)  NOT NULL UNIQUE,
  `year`             YEAR          NOT NULL,
  `status`           ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  -- Hero / landing
  `hero_image`       VARCHAR(255)  DEFAULT NULL,
  `hero_text`        VARCHAR(500)  DEFAULT NULL,
  -- About page content
  `background_text`  LONGTEXT      DEFAULT NULL,
  `head_name`        VARCHAR(100)  DEFAULT NULL,
  `head_photo`       VARCHAR(255)  DEFAULT NULL,
  `head_message`     LONGTEXT      DEFAULT NULL,
  `objectives`       LONGTEXT      DEFAULT NULL,
  `who_should_attend` TEXT         DEFAULT NULL,
  -- Timestamps
  `created_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Annual SCS conferences — one active row = current conference';

-- -------------------------------------------------------------
-- 2. conference_speakers
--    Speakers appear in two sections: welcome ceremony & keynote.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_speakers` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id`  INT NOT NULL,
  `section`        ENUM('welcome_ceremony','keynote') NOT NULL DEFAULT 'keynote',
  `full_name`      VARCHAR(150) NOT NULL,
  `position_title` VARCHAR(255) DEFAULT NULL,
  `photo`          VARCHAR(255) DEFAULT NULL,
  `is_active`      TINYINT(1)  NOT NULL DEFAULT 1,
  `display_order`  INT         DEFAULT 0,
  `created_at`     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE,
  INDEX `idx_conf_section` (`conference_id`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Speakers per conference, split by section (welcome / keynote)';

-- -------------------------------------------------------------
-- 3. conference_abstract_settings
--    One row per conference; stores CFA page content.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_abstract_settings` (
  `id`                  INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id`       INT NOT NULL UNIQUE,
  `submission_deadline` DATETIME  DEFAULT NULL,
  `format_requirements` LONGTEXT  DEFAULT NULL,
  `structure`           LONGTEXT  DEFAULT NULL,
  `review_process`      LONGTEXT  DEFAULT NULL,
  `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Call-for-Abstracts settings per conference';

-- -------------------------------------------------------------
-- 3b. conference_abstracts
--    Submitted abstracts received from the public form.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_abstracts` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id`  INT NOT NULL,
  `subtheme_id`    INT NULL,
  `subtheme_title` VARCHAR(255) DEFAULT NULL,
  `full_name`      VARCHAR(150) NOT NULL,
  `email`          VARCHAR(150) NOT NULL,
  `organization`   VARCHAR(255) DEFAULT NULL,
  `title`          VARCHAR(255) NOT NULL,
  `summary`        TEXT DEFAULT NULL,
  `file_path`      VARCHAR(255) DEFAULT NULL,
  `status`         ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE,
  INDEX `idx_abstract_status` (`status`),
  INDEX `idx_abstract_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Abstract submissions received for the conference';

-- -------------------------------------------------------------
-- 4. conference_subthemes
--    Listed on the CFA page — scientific topics accepting abstracts.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_subthemes` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id` INT NOT NULL,
  `title`         VARCHAR(255) NOT NULL,
  `detail`        TEXT         DEFAULT NULL,
  `display_order` INT          DEFAULT 0,
  `is_active`     TINYINT(1)  NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sub-themes / scientific tracks per conference';

-- -------------------------------------------------------------
-- 5. conference_evaluation_criteria
--    Abstract judging criteria shown on the CFA page.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_evaluation_criteria` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id` INT NOT NULL,
  `title`         VARCHAR(255) NOT NULL,
  `detail`        TEXT         DEFAULT NULL,
  `display_order` INT          DEFAULT 0,
  `is_active`     TINYINT(1)  NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Abstract evaluation/scoring criteria per conference';

-- -------------------------------------------------------------
-- 6. conference_registration_settings
--    One row per conference; stores payment / process text.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_registration_settings` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id`  INT NOT NULL UNIQUE,
  `process_text`   LONGTEXT     DEFAULT NULL,
  `account_number` VARCHAR(100) DEFAULT NULL,
  `bank_name`      VARCHAR(150) DEFAULT NULL,
  `account_holder` VARCHAR(150) DEFAULT NULL,
  `created_at`     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registration page settings (payment info, process text) per conference';

-- -------------------------------------------------------------
-- 7. conference_registrations
--    Actual attendee registration submissions.
--    Three fee tiers: student | professional | ingo
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conference_registrations` (
  `id`                 INT AUTO_INCREMENT PRIMARY KEY,
  `conference_id`      INT NOT NULL,
  `category`           ENUM('student','professional','ingo') NOT NULL,
  -- Personal info
  `first_name`         VARCHAR(100) NOT NULL,
  `last_name`          VARCHAR(100) NOT NULL,
  `email`              VARCHAR(150) NOT NULL,
  `phone`              VARCHAR(50)  DEFAULT NULL,
  `organization`       VARCHAR(200) DEFAULT NULL,
  `country`            VARCHAR(100) DEFAULT NULL,
  -- Documents
  `student_id_path`    VARCHAR(255) DEFAULT NULL  COMMENT 'Required for student category',
  `payment_screenshot` VARCHAR(255) DEFAULT NULL  COMMENT 'Bank transfer screenshot',
  `payment_phone`      VARCHAR(50)  DEFAULT NULL  COMMENT 'EVC / Zaad / E-dahab phone',
  -- Admin
  `status`             ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `admin_notes`        TEXT         DEFAULT NULL,
  -- Timestamps
  `registered_at`      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON DELETE CASCADE,
  INDEX `idx_status`   (`status`),
  INDEX `idx_category` (`category`),
  INDEX `idx_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conference attendee registrations submitted via public form';

-- -------------------------------------------------------------
-- 8. past_conferences
--    Archive of previous years; each has a rich-text body and
--    an associated gallery (table #9).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `past_conferences` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `title`           VARCHAR(255) NOT NULL,
  `slug`            VARCHAR(255) NOT NULL UNIQUE,
  `body`            LONGTEXT     DEFAULT NULL,
  `conference_date` DATE         DEFAULT NULL,
  `is_active`       TINYINT(1)  NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`),
  INDEX `idx_date`   (`conference_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Archive entries for past SCS annual conferences';

-- -------------------------------------------------------------
-- 9. past_conference_gallery
--    Photo gallery rows linked to a past_conferences entry.
--    The archive manager enforces a maximum of 10 images per entry.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `past_conference_gallery` (
  `id`                  INT AUTO_INCREMENT PRIMARY KEY,
  `past_conference_id`  INT NOT NULL,
  `image_path`          VARCHAR(255) NOT NULL,
  `caption`             VARCHAR(255) DEFAULT NULL,
  `display_order`       INT          DEFAULT 0,
  FOREIGN KEY (`past_conference_id`) REFERENCES `past_conferences`(`id`) ON DELETE CASCADE,
  INDEX `idx_order` (`past_conference_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Up to 10 photo gallery images per past conference entry';

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
--  End of conference_tables.sql
-- =============================================================
