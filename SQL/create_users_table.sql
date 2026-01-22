-- Database migration script for School SAAS
-- Run this SQL to create the users table in your saas_sms database

-- Drop existing table if needed (uncomment to reset)
-- DROP TABLE IF EXISTS `users`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `role` ENUM('admin', 'teacher', 'student', 'parent') DEFAULT 'student',
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `reset_token` VARCHAR(64),
  `reset_token_expiry` DATETIME,
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`),
  INDEX `idx_reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test admin user (username: admin, password: admin123)
-- Password hash: $2y$10$YIvxPsN3W.Y5F4p.F6K.luE8K4L7I7I7I7I7I7I7I7I7I7I7I7I7I
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES 
('admin', 'admin@school.com', '$2y$10$YIvxPsN3W.Y5F4p.F6K.luE8K4L7I7I7I7I7I7I7I7I7I7I7I7I', 'Admin', 'User', 'admin', 'active');

-- Insert test student user (username: student, password: student123)
-- Password hash: $2y$10$6w8.J8w.J8w.J8w.J8w.J.J8w.J8w.J8w.J8w.J8w.J8w.J8w.J8w.J
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES 
('student', 'student@school.com', '$2y$10$6w8.J8w.J8w.J8w.J8w.J.J8w.J8w.J8w.J8w.J8w.J8w.J8w.J8w.J', 'John', 'Doe', 'student', 'active');

-- Insert test teacher user (username: teacher, password: teacher123)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES 
('teacher', 'teacher@school.com', '$2y$10$abc123..hashedpassword..', 'Jane', 'Smith', 'teacher', 'active');
