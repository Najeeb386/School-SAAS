-- Create School Exams Table
CREATE TABLE IF NOT EXISTS `school_exams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NOT NULL COMMENT 'Reference to school',
    `session_id` INT NOT NULL COMMENT 'Reference to academic session',
    `exam_name` VARCHAR(50) NOT NULL COMMENT 'Name/title of the exam',
    `exam_type` ENUM('midterm', 'final', 'annual', 'board_prep', 'monthly') 
        NOT NULL COMMENT 'Type of examination',
    `start_date` DATE NOT NULL COMMENT 'Start date of exam period',
    `end_date` DATE NOT NULL COMMENT 'End date of exam period',
    `description` VARCHAR(255) NULL COMMENT 'Additional details about the exam',
    `status` ENUM('draft', 'published', 'completed') DEFAULT 'draft' COMMENT 'Status of the exam',
    `created_by` INT NULL COMMENT 'User who created this exam',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uniq_school_exam_session_name` (`school_id`, `session_id`, `exam_name`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_exam_type` (`exam_type`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_end_date` (`end_date`),
    KEY `idx_status` (`status`),
    CONSTRAINT `chk_exam_dates` CHECK (`start_date` <= `end_date`),
    
    FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`session_id`) REFERENCES `school_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='School Exams - Exam Schedule and Details';
