-- Migration: Create student-related tables for School Admin
-- File: SQL/003-create-school-students-tables.sql
-- Creates: school_students, school_student_guardians, school_student_academics, school_student_documents

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `school_students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `admission_no` VARCHAR(64) DEFAULT NULL,
  `first_name` VARCHAR(150) NOT NULL,
  `last_name` VARCHAR(150) DEFAULT NULL,
  `other_names` VARCHAR(255) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `gender` VARCHAR(16) DEFAULT NULL,
  `admission_date` DATE DEFAULT NULL,
  `religion` VARCHAR(64) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_school_students_school` (`school_id`),
  INDEX `idx_school_students_adm` (`admission_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_student_guardians` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `relation` VARCHAR(64) DEFAULT NULL,
  `cnic_passport` VARCHAR(64) DEFAULT NULL,
  `occupation` VARCHAR(128) DEFAULT NULL,
  `mobile` VARCHAR(32) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_guardians_student` (`student_id`),
  INDEX `idx_guardians_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_student_academics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `session_id` INT UNSIGNED DEFAULT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `is_transferred` TINYINT(1) NOT NULL DEFAULT 0,
  `previous_school` VARCHAR(255) DEFAULT NULL,
  `previous_class` VARCHAR(128) DEFAULT NULL,
  `previous_admission_no` VARCHAR(64) DEFAULT NULL,
  `previous_result` VARCHAR(255) DEFAULT NULL,
  `enrolled_at` DATETIME DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_acad_student` (`student_id`),
  INDEX `idx_acad_school` (`school_id`),
  INDEX `idx_acad_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_student_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `doc_type` VARCHAR(64) NOT NULL,
  `file_path` VARCHAR(1024) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_docs_student` (`student_id`),
  INDEX `idx_docs_school` (`school_id`),
  INDEX `idx_docs_type` (`doc_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

-- Notes:
-- * `doc_type` can store values such as 'photo', 'guardian_cnic', 'birth_certificate', 'form_b', 'other'
-- * Use application logic to ensure exactly one guardian has `is_primary=1` per student.
-- * `school_id` is included on every table for multi-school isolation and indexing.

COMMIT;
