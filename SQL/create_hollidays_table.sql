-- Create school_holliday_calendar table
CREATE TABLE IF NOT EXISTS `school_holliday_calendar` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `school_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `event_type` ENUM('WEEKLY_OFF', 'HOLIDAY', 'VACATION', 'EVENT') NOT NULL DEFAULT 'HOLIDAY',
  `day_of_week` TINYINT NULL COMMENT '1=Monday, 2=Tuesday, ..., 7=Sunday',
  `start_date` DATE NULL COMMENT 'For non-WEEKLY_OFF events',
  `end_date` DATE NULL COMMENT 'For multi-day events',
  `applies_to` ENUM('ALL', 'STUDENTS', 'STAFF') NOT NULL DEFAULT 'ALL',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_school_id` (`school_id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
