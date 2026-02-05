-- =====================================================
-- STAFF ATTENDANCE SYSTEM - DATABASE TABLES
-- =====================================================
-- Created: February 2026
-- Purpose: Track staff attendance records for schools
-- =====================================================

-- =====================================================
-- 1. SCHOOL STAFF TABLE (Master data for staff members)
-- =====================================================
CREATE TABLE IF NOT EXISTS `school_staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique staff ID',
  `school_id` INT NOT NULL COMMENT 'Reference to school',
  `employee_id` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique employee identifier',
  `name` VARCHAR(100) NOT NULL COMMENT 'Full name of staff member',
  `designation` VARCHAR(100) COMMENT 'Job title/designation',
  `department` VARCHAR(100) COMMENT 'Department name',
  `email` VARCHAR(100) COMMENT 'Email address',
  `phone` VARCHAR(20) COMMENT 'Contact phone number',
  `date_of_joining` DATE COMMENT 'Date when staff joined',
  `status` ENUM('active', 'inactive', 'on_leave', 'terminated') DEFAULT 'active' COMMENT 'Employment status',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  INDEX `idx_school_id` (`school_id`),
  INDEX `idx_employee_id` (`employee_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Master table for school staff members';

-- =====================================================
-- 2. STAFF ATTENDANCE TABLE (Daily attendance records)
-- =====================================================
CREATE TABLE IF NOT EXISTS `staff_attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique attendance record ID',
  `staff_id` INT NOT NULL COMMENT 'Reference to school_staff',
  `school_id` INT NOT NULL COMMENT 'Reference to school',
  `attendance_date` DATE NOT NULL COMMENT 'Date of attendance',
  `status` ENUM('present', 'absent', 'leave', 'halfday', 'not_marked') DEFAULT 'not_marked' COMMENT 'Attendance status',
  `remarks` TEXT COMMENT 'Additional notes/remarks',
  `marked_by` INT COMMENT 'User ID who marked the attendance',
  `marked_at` TIMESTAMP COMMENT 'When the attendance was marked',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  
  FOREIGN KEY (`staff_id`) REFERENCES `school_staff` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  
  -- Unique constraint: One attendance record per staff per date
  UNIQUE KEY `unique_attendance` (`staff_id`, `attendance_date`),
  
  -- Indexes for efficient querying
  INDEX `idx_school_id` (`school_id`),
  INDEX `idx_staff_id` (`staff_id`),
  INDEX `idx_attendance_date` (`attendance_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_staff_date` (`staff_id`, `attendance_date`),
  INDEX `idx_school_date` (`school_id`, `attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Staff attendance records';

-- =====================================================
-- 3. ATTENDANCE SUMMARY TABLE (Monthly summary for performance)
-- =====================================================
CREATE TABLE IF NOT EXISTS `staff_attendance_summary` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique summary record ID',
  `staff_id` INT NOT NULL COMMENT 'Reference to school_staff',
  `school_id` INT NOT NULL COMMENT 'Reference to school',
  `year` INT NOT NULL COMMENT 'Year',
  `month` INT NOT NULL COMMENT 'Month (1-12)',
  `total_days` INT DEFAULT 0 COMMENT 'Total working days in month',
  `present_days` INT DEFAULT 0 COMMENT 'Days marked as present',
  `absent_days` INT DEFAULT 0 COMMENT 'Days marked as absent',
  `leave_days` INT DEFAULT 0 COMMENT 'Days marked as leave',
  `halfday_days` INT DEFAULT 0 COMMENT 'Half days marked',
  `not_marked_days` INT DEFAULT 0 COMMENT 'Days not marked',
  `attendance_percentage` DECIMAL(5, 2) COMMENT 'Attendance percentage',
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last calculation time',
  
  FOREIGN KEY (`staff_id`) REFERENCES `school_staff` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  
  UNIQUE KEY `unique_summary` (`staff_id`, `year`, `month`),
  INDEX `idx_school_id` (`school_id`),
  INDEX `idx_staff_id` (`staff_id`),
  INDEX `idx_year_month` (`year`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Monthly attendance summary for reporting';

-- =====================================================
-- 4. LEAVE TYPES TABLE (Define different leave types)
-- =====================================================
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique leave type ID',
  `school_id` INT NOT NULL COMMENT 'Reference to school',
  `name` VARCHAR(100) NOT NULL COMMENT 'Name of leave type',
  `code` VARCHAR(10) NOT NULL COMMENT 'Short code for leave type',
  `max_days` INT COMMENT 'Maximum days allowed per year',
  `description` TEXT COMMENT 'Description of leave type',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_code` (`school_id`, `code`),
  INDEX `idx_school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Types of leaves available in the school';

-- =====================================================
-- 5. ATTENDANCE SETTINGS TABLE (School-specific settings)
-- =====================================================
CREATE TABLE IF NOT EXISTS `attendance_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique setting ID',
  `school_id` INT NOT NULL UNIQUE COMMENT 'Reference to school',
  `working_days_per_week` INT DEFAULT 5 COMMENT 'Working days per week',
  `working_hours_per_day` DECIMAL(4, 2) DEFAULT 8 COMMENT 'Working hours per day',
  `min_working_hours_halfday` DECIMAL(4, 2) DEFAULT 4 COMMENT 'Minimum hours for half day',
  `weekend_days` VARCHAR(50) COMMENT 'Weekend days (e.g., Saturday,Sunday)',
  `fiscal_year_start_month` INT DEFAULT 4 COMMENT 'Fiscal year start month',
  `auto_calculate_summary` BOOLEAN DEFAULT TRUE COMMENT 'Auto-calculate attendance summary',
  `allow_retroactive_marking` BOOLEAN DEFAULT TRUE COMMENT 'Allow marking past dates',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='School-specific attendance system settings';

-- =====================================================
-- SAMPLE DATA (Optional - for testing)
-- =====================================================

-- Sample departments/designations can be added here
-- INSERT INTO school_staff VALUES
-- (1, 1, 'EMP001', 'John Doe', 'Senior Teacher', 'Teaching', 'john@school.com', '9876543210', '2020-01-15', 'active', NOW(), NOW()),
-- (2, 1, 'EMP002', 'Jane Smith', 'Librarian', 'Library', 'jane@school.com', '9876543211', '2019-06-01', 'active', NOW(), NOW()),
-- (3, 1, 'EMP003', 'Mike Johnson', 'Admin Staff', 'Admin', 'mike@school.com', '9876543212', '2021-03-20', 'active', NOW(), NOW());

-- =====================================================
-- VIEWS FOR EASY REPORTING
-- =====================================================

-- View: Current Month Attendance
CREATE OR REPLACE VIEW `v_current_month_attendance` AS
SELECT 
    s.id as staff_id,
    s.employee_id,
    s.name,
    s.designation,
    s.department,
    sa.attendance_date,
    sa.status,
    YEAR(sa.attendance_date) as year,
    MONTH(sa.attendance_date) as month
FROM school_staff s
LEFT JOIN staff_attendance sa ON s.id = sa.staff_id 
    AND YEAR(sa.attendance_date) = YEAR(CURDATE())
    AND MONTH(sa.attendance_date) = MONTH(CURDATE())
ORDER BY s.name, sa.attendance_date;

-- View: Monthly Summary Report
CREATE OR REPLACE VIEW `v_attendance_summary_report` AS
SELECT 
    s.id,
    s.employee_id,
    s.name,
    s.designation,
    s.department,
    ats.year,
    ats.month,
    ats.total_days,
    ats.present_days,
    ats.absent_days,
    ats.leave_days,
    ats.halfday_days,
    ats.attendance_percentage,
    CASE 
        WHEN ats.attendance_percentage >= 90 THEN 'Excellent'
        WHEN ats.attendance_percentage >= 80 THEN 'Good'
        WHEN ats.attendance_percentage >= 70 THEN 'Average'
        ELSE 'Poor'
    END as performance
FROM school_staff s
LEFT JOIN staff_attendance_summary ats ON s.id = ats.staff_id
ORDER BY s.name, ats.year, ats.month;

-- =====================================================
-- INDEX NOTES
-- =====================================================
-- Primary indexes are created on:
-- - staff_id (for attendance lookups)
-- - attendance_date (for date range queries)
-- - status (for filtering by attendance status)
-- - Combined indexes for common query patterns
-- These ensure fast queries for calendar views and reports
