-- ============================================
-- Somali Cardiac Society (SCS) Database Schema
-- Created: 2026
-- ============================================

CREATE DATABASE IF NOT EXISTS `scs_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `scs_db`;

-- ============================================
-- 1. Admins Table (RBAC: super_admin, admin)
-- ============================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_role` (`role`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Members Table (Doctors / Specialists)
-- ============================================
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `photo` VARCHAR(255) DEFAULT NULL,
    `experience_years` INT DEFAULT 0,
    `hospital` VARCHAR(150) DEFAULT NULL,
    `specialization` VARCHAR(200) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active_order` (`is_active`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Content Table (Research, Education, News, Events)
-- ============================================
CREATE TABLE IF NOT EXISTS `content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `category` ENUM('research', 'education', 'news', 'events') NOT NULL,
    `summary` TEXT DEFAULT NULL,
    `body` TEXT DEFAULT NULL,
    `feature_image` VARCHAR(255) DEFAULT NULL,
    `event_date` DATE DEFAULT NULL,
    `author` VARCHAR(100) DEFAULT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_published` (`is_published`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- Seed Data: Default Super Admin
-- Password: SCS@2024!
-- ============================================
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('superadmin', 'admin@somalicardiac.org', '$2y$10$cQN9J1Au7NNbhYLUj/0O6eSBc75R8wLofoEDykqEmEGhKmcJkgaQO', 'Super Administrator', 'super_admin');

-- ============================================
-- Sample Content Data
-- ============================================
INSERT INTO `content` (`title`, `slug`, `category`, `summary`, `body`, `author`, `is_published`, `event_date`) VALUES
('Cardiovascular Disease Prevention in Somalia', 'cardiovascular-disease-prevention-somalia', 'research', 'A comprehensive study on the prevalence and prevention strategies for cardiovascular diseases across urban and rural Somalia.', 'This research paper examines the current state of cardiovascular disease (CVD) in Somalia, exploring key risk factors including hypertension, diabetes, and lifestyle-related causes. The study highlights the need for community-based prevention programs and outlines actionable strategies for healthcare providers to reduce CVD incidence across the country.', 'Dr. Ahmed Hassan', 1, NULL),

('Clinical Guidelines for Hypertension Management', 'clinical-guidelines-hypertension-management', 'education', 'Updated clinical guidelines for the diagnosis and management of hypertension in the Somali healthcare context.', 'These guidelines provide evidence-based recommendations for healthcare professionals in Somalia for the effective management of hypertension. Topics covered include diagnostic criteria, lifestyle modification counseling, pharmacological treatment algorithms, and patient follow-up protocols tailored to the local healthcare infrastructure.', 'SSC Medical Board', 1, NULL),

('SSC Annual Cardiology Conference 2026', 'ssc-annual-cardiology-conference-2026', 'events', 'Join us for the annual gathering of cardiac professionals in Mogadishu featuring international speakers and workshops.', 'The Somali Society of Cardiology is proud to announce its Annual Cardiology Conference, bringing together leading cardiac specialists, researchers, and healthcare professionals from across East Africa and beyond. The event will feature keynote presentations, panel discussions on emerging cardiovascular treatments, hands-on workshops, and networking opportunities.', 'SSC Events Committee', 1, '2026-11-15'),

('New Cardiac Catheterization Lab Opens in Mogadishu', 'new-cardiac-catheterization-lab-mogadishu', 'news', 'A state-of-the-art cardiac catheterization laboratory has been inaugurated at Mogadishu General Hospital.', 'In a landmark development for cardiovascular care in Somalia, a new cardiac catheterization laboratory has been officially opened at Mogadishu General Hospital. The facility, equipped with the latest imaging technology and interventional cardiology tools, will enable complex cardiac procedures including angiography, angioplasty, and stent placement to be performed locally, reducing the need for patients to travel abroad for treatment.', 'SSC Press Office', 1, NULL),

('Workshop: ECG Interpretation for Primary Care', 'workshop-ecg-interpretation-primary-care', 'education', 'A practical workshop designed to enhance ECG reading skills for primary care physicians and medical officers.', 'This hands-on workshop is designed to build confidence in ECG interpretation among primary care providers in Somalia. Participants will learn systematic approaches to reading 12-lead ECGs, identify common cardiac arrhythmias, recognize signs of acute myocardial infarction, and understand when to refer patients for specialist cardiac evaluation.', 'Dr. Fatima Ali', 1, '2026-09-20'),

('Community Heart Health Screening Campaign', 'community-heart-health-screening-campaign', 'events', 'Free cardiovascular health screening program launching across multiple locations in Mogadishu.', 'The Somali Society of Cardiology, in partnership with local healthcare facilities, is launching a community-wide heart health screening campaign. The initiative will offer free blood pressure checks, cholesterol screenings, blood glucose testing, and basic cardiac risk assessments at multiple locations throughout Mogadishu. The campaign aims to identify individuals at risk for cardiovascular disease and connect them with appropriate care.', 'SSC Outreach Team', 1, '2026-10-05');

-- ============================================
-- Sample Members Data
-- ============================================
INSERT INTO `members` (`full_name`, `photo`, `experience_years`, `hospital`, `specialization`, `bio`, `display_order`, `is_active`) VALUES
('Dr. Ahmed Mohamed Hassan', NULL, 15, 'Mogadishu General Hospital', 'Interventional Cardiology', 'Dr. Ahmed Hassan is a leading interventional cardiologist with over 15 years of experience in diagnosing and treating complex cardiovascular conditions. He specializes in coronary angioplasty, stent placement, and cardiac catheterization procedures.', 1, 1),

('Dr. Fatima Ali Omar', NULL, 12, 'Banadir Hospital', 'Pediatric Cardiology', 'Dr. Fatima Ali is a dedicated pediatric cardiologist specializing in congenital heart defects and childhood cardiovascular conditions. She has been instrumental in establishing pediatric cardiac care programs in Somalia.', 2, 1),

('Dr. Mohamed Abdi Nur', NULL, 18, 'SOS Hospital Mogadishu', 'Cardiac Surgery', 'Dr. Mohamed Abdi is a seasoned cardiac surgeon with extensive experience in coronary artery bypass grafting and valve repair surgeries. He has performed over 500 successful cardiac procedures throughout his career.', 3, 1),

('Dr. Amina Yusuf Ibrahim', NULL, 10, 'Erdogan Hospital', 'Clinical Cardiology', 'Dr. Amina Yusuf specializes in clinical cardiology with expertise in echocardiography, heart failure management, and preventive cardiovascular medicine. She is passionate about advancing women\'s heart health awareness in Somalia.', 4, 1),

('Dr. Abdirahman Osman Ali', NULL, 8, 'Hagarla Hospital', 'Electrophysiology', 'Dr. Abdirahman Osman is an electrophysiologist focused on cardiac arrhythmia management, pacemaker implantation, and cardiac rhythm disorders. He brings international training and expertise to the Somali healthcare landscape.', 5, 1),

('Dr. Halima Mohamud Aden', NULL, 14, 'Keysaney Hospital', 'Preventive Cardiology', 'Dr. Halima Mohamud is a preventive cardiologist dedicated to community health education and cardiovascular disease prevention programs. She leads multiple outreach initiatives across urban and rural Somalia.', 6, 1);
