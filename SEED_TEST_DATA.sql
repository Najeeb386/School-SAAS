-- ============================================================
-- SEED TEST DATA FOR STAFF ATTENDANCE SYSTEM
-- ============================================================

-- Insert test teachers (school_id = 1)
INSERT INTO `school_teachers` 
(`id`, `school_id`, `name`, `email`, `phone`, `id_no`, `photo_path`, `role`, `permissions`, `status`, `last_login`, `created_at`, `updated_at`) 
VALUES 
(1, 1, 'John Smith', 'john@school.com', '1234567890', 'T001', '', 'Mathematics', 'teacher', 1, NOW(), NOW(), NOW()),
(2, 1, 'Sarah Johnson', 'sarah@school.com', '1234567891', 'T002', '', 'English', 'teacher', 1, NOW(), NOW(), NOW()),
(3, 1, 'Michael Brown', 'michael@school.com', '1234567892', 'T003', '', 'Science', 'teacher', 1, NOW(), NOW(), NOW()),
(4, 1, 'Emily Davis', 'emily@school.com', '1234567893', 'T004', '', 'History', 'teacher', 1, NOW(), NOW(), NOW()),
(5, 1, 'Robert Wilson', 'robert@school.com', '1234567894', 'T005', '', 'Physical Education', 'teacher', 1, NOW(), NOW(), NOW());

-- Insert test employees (school_id = 1)
INSERT INTO `employees` 
(`id`, `school_id`, `name`, `email`, `password`, `role_id`, `phone`, `permissions`, `status`, `last_login`, `created_at`, `updated_at`) 
VALUES 
(1, 1, 'Admin User', 'admin@school.com', 'hashed_password_1', 1, '0987654321', 'admin', 1, NOW(), NOW(), NOW()),
(2, 1, 'Office Manager', 'office@school.com', 'hashed_password_2', 2, '0987654322', 'staff', 1, NOW(), NOW(), NOW()),
(3, 1, 'Receptionist', 'reception@school.com', 'hashed_password_3', 3, '0987654323', 'staff', 1, NOW(), NOW(), NOW());

-- Insert sample attendance for today (February 5, 2026)
INSERT INTO `school_staff_attendance` 
(`id`, `school_id`, `staff_type`, `staff_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`, `updated_at`) 
VALUES 
-- Teachers attendance for today
(1, 1, 'teacher', 1, '2026-02-05', 'P', 'Present', 1, NOW(), NOW()),
(2, 1, 'teacher', 2, '2026-02-05', 'P', 'Present', 1, NOW(), NOW()),
(3, 1, 'teacher', 3, '2026-02-05', 'A', 'Absent', 1, NOW(), NOW()),
(4, 1, 'teacher', 4, '2026-02-05', 'L', 'On Leave', 1, NOW(), NOW()),
(5, 1, 'teacher', 5, '2026-02-05', 'HD', 'Half Day', 1, NOW(), NOW()),
-- Employees attendance for today
(6, 1, 'employee', 1, '2026-02-05', 'P', 'Present', 1, NOW(), NOW()),
(7, 1, 'employee', 2, '2026-02-05', 'P', 'Present', 1, NOW(), NOW()),
(8, 1, 'employee', 3, '2026-02-05', 'A', 'Absent', 1, NOW(), NOW());

-- Insert sample attendance for previous days (February 1-4, 2026)
INSERT INTO `school_staff_attendance` 
(`id`, `school_id`, `staff_type`, `staff_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`, `updated_at`) 
VALUES 
-- Feb 4 (Wednesday)
(9, 1, 'teacher', 1, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(10, 1, 'teacher', 2, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(11, 1, 'teacher', 3, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(12, 1, 'teacher', 4, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(13, 1, 'teacher', 5, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(14, 1, 'employee', 1, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(15, 1, 'employee', 2, '2026-02-04', 'P', '', 1, NOW(), NOW()),
(16, 1, 'employee', 3, '2026-02-04', 'P', '', 1, NOW(), NOW()),
-- Feb 3 (Tuesday)
(17, 1, 'teacher', 1, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(18, 1, 'teacher', 2, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(19, 1, 'teacher', 3, '2026-02-03', 'A', '', 1, NOW(), NOW()),
(20, 1, 'teacher', 4, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(21, 1, 'teacher', 5, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(22, 1, 'employee', 1, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(23, 1, 'employee', 2, '2026-02-03', 'P', '', 1, NOW(), NOW()),
(24, 1, 'employee', 3, '2026-02-03', 'L', '', 1, NOW(), NOW()),
-- Feb 2 (Monday)
(25, 1, 'teacher', 1, '2026-02-02', 'P', '', 1, NOW(), NOW()),
(26, 1, 'teacher', 2, '2026-02-02', 'P', '', 1, NOW(), NOW()),
(27, 1, 'teacher', 3, '2026-02-02', 'P', '', 1, NOW(), NOW()),
(28, 1, 'teacher', 4, '2026-02-02', 'A', '', 1, NOW(), NOW()),
(29, 1, 'teacher', 5, '2026-02-02', 'P', '', 1, NOW(), NOW()),
(30, 1, 'employee', 1, '2026-02-02', 'P', '', 1, NOW(), NOW()),
(31, 1, 'employee', 2, '2026-02-02', 'HD', '', 1, NOW(), NOW()),
(32, 1, 'employee', 3, '2026-02-02', 'P', '', 1, NOW(), NOW());
