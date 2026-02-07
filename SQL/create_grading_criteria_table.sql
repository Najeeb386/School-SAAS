-- Create Grading Criteria Table
CREATE TABLE IF NOT EXISTS `school_grading_criteria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NOT NULL,
    `grade_name` VARCHAR(20) NOT NULL COMMENT 'A+, A, B, C, D, F, etc.',
    `min_percentage` DECIMAL(5,2) NOT NULL COMMENT 'Minimum percentage for this grade',
    `max_percentage` DECIMAL(5,2) NOT NULL COMMENT 'Maximum percentage for this grade',
    `gpa` DECIMAL(3,2) NULL COMMENT 'GPA value (optional)',
    `remarks` VARCHAR(100) NULL COMMENT 'Excellent, Good, Pass, Fail, etc.',
    `is_pass` TINYINT(1) DEFAULT 1 COMMENT '1 = Passing grade, 0 = Failing grade',
    `grading_system` ENUM('percentage', 'gpa', 'both') DEFAULT 'percentage' COMMENT 'Grading system type',
    `status` TINYINT(1) DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_school_grade` (`school_id`, `grade_name`),
    UNIQUE KEY `uniq_school_range` (`school_id`, `min_percentage`, `max_percentage`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_grading_system` (`grading_system`),
    KEY `idx_is_pass` (`is_pass`),
    FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='School Grading Criteria - Grade ranges and criteria';
