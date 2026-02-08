-- Create School Exam Classes Assignment Table
CREATE TABLE IF NOT EXISTS `school_exam_classes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NOT NULL COMMENT 'Reference to school',
    `exam_id` INT NOT NULL COMMENT 'Reference to exam',
    `class_id` INT NOT NULL COMMENT 'Reference to class',
    `section_id` INT NOT NULL COMMENT 'Reference to section',
    `status` VARCHAR(50) DEFAULT 'active' COMMENT 'Status: active, inactive, completed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uniq_school_exam_class_section` (`school_id`, `exam_id`, `class_id`, `section_id`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_exam_id` (`exam_id`),
    KEY `idx_class_id` (`class_id`),
    KEY `idx_section_id` (`section_id`),
    KEY `idx_status` (`status`),
    
    FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_id`) REFERENCES `school_exams` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`) REFERENCES `school_class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Exam Assignments to Classes and Sections';

-- Create School Exam Subjects Table
CREATE TABLE IF NOT EXISTS `school_exam_subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NOT NULL COMMENT 'Reference to school',
    `exam_class_id` INT NOT NULL COMMENT 'Reference to exam class assignment',
    `subject_id` INT NOT NULL COMMENT 'Reference to subject',
    `exam_date` DATE NOT NULL COMMENT 'Exam date for this subject',
    `exam_time` TIME NOT NULL COMMENT 'Exam time for this subject',
    `total_marks` INT NOT NULL DEFAULT 100 COMMENT 'Total marks for this subject',
    `passing_marks` INT NOT NULL DEFAULT 40 COMMENT 'Passing marks required',
    `status` VARCHAR(50) DEFAULT 'active' COMMENT 'Status: active, inactive',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uniq_exam_class_subject` (`exam_class_id`, `subject_id`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_exam_class_id` (`exam_class_id`),
    KEY `idx_subject_id` (`subject_id`),
    KEY `idx_exam_date` (`exam_date`),
    KEY `idx_status` (`status`),
    
    FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_class_id`) REFERENCES `school_exam_classes` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `school_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Exam Subjects with Dates and Marks';
