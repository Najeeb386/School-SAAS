-- Create School Exam Results Table
-- This table stores student marks for exams

CREATE TABLE IF NOT EXISTS `school_exam_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NOT NULL COMMENT 'Reference to school',
    `exam_class_id` INT NOT NULL COMMENT 'Reference to exam class assignment',
    `student_id` INT NOT NULL COMMENT 'Reference to student',
    `subject_id` INT NOT NULL COMMENT 'Reference to subject',
    `marks` DECIMAL(5, 2) NULL COMMENT 'Marks obtained by student',
    `grade` VARCHAR(5) NULL COMMENT 'Grade assigned',
    `remarks` VARCHAR(255) NULL COMMENT 'Teacher remarks',
    `uploaded_by` INT NULL COMMENT 'User who uploaded the marks',
    `uploaded_at` TIMESTAMP NULL COMMENT 'When marks were uploaded',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uniq_exam_student_subject` (`exam_class_id`, `student_id`, `subject_id`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_exam_class_id` (`exam_class_id`),
    KEY `idx_student_id` (`student_id`),
    KEY `idx_subject_id` (`subject_id`),
    KEY `idx_uploaded_at` (`uploaded_at`),
    
    FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_class_id`) REFERENCES `school_exam_classes` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `school_students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `school_subjects` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `school_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Student Exam Results and Marks';
