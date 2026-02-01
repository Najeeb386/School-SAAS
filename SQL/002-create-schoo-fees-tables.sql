CREATE TABLE schoo_fee_categories (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
school_id INT UNSIGNED NOT NULL,
name VARCHAR(191) NOT NULL,
code VARCHAR(64) DEFAULT NULL,
description TEXT DEFAULT NULL,
status TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
UNIQUE KEY uq_school_category (school_id, name),
INDEX (school_id)
) ENGINE=InnoDB;

CREATE TABLE schoo_fee_items (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
school_id INT UNSIGNED NOT NULL,
category_id INT UNSIGNED,
name VARCHAR(191) NOT NULL,
code VARCHAR(64) DEFAULT NULL,
amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
billing_cycle ENUM('monthly','quarterly','yearly','one_time') DEFAULT 'one_time',
status TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
UNIQUE KEY uq_school_item_code (school_id, code),
INDEX (school_id),
INDEX (category_id)
) ENGINE=InnoDB;

CREATE TABLE schoo_fee_assignments (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
school_id INT UNSIGNED NOT NULL,
fee_item_id INT UNSIGNED NOT NULL,
class_id INT UNSIGNED DEFAULT NULL,
section_id INT UNSIGNED DEFAULT NULL,
student_id INT UNSIGNED DEFAULT NULL,
session_id INT UNSIGNED NOT NULL,
amount DECIMAL(10,2) DEFAULT NULL,
due_day TINYINT DEFAULT 10, -- e.g. every month 10th
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX (school_id),
INDEX (fee_item_id),
INDEX (class_id),
INDEX (student_id),
UNIQUE KEY uq_assign (
school_id,
fee_item_id,
class_id,
section_id,
student_id,
session_id
)
) ENGINE=InnoDB;

CREATE TABLE schoo_fee_invoices (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
school_id INT UNSIGNED NOT NULL,
student_id INT UNSIGNED NOT NULL,
session_id INT UNSIGNED NOT NULL,
invoice_no VARCHAR(64) NOT NULL,
billing_month DATE NOT NULL,
total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
status ENUM('draft','issued','partially_paid','paid','cancelled') DEFAULT 'draft',
due_date DATE DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
UNIQUE KEY uq_school_invoice (school_id, invoice_no),
INDEX (school_id),
INDEX (student_id)
) ENGINE=InnoDB;

CREATE TABLE schoo_fee_invoice_items (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
invoice_id INT UNSIGNED NOT NULL,
fee_item_id INT UNSIGNED NOT NULL,
description VARCHAR(191),
amount DECIMAL(10,2) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX (invoice_id),
INDEX (fee_item_id)
) ENGINE=InnoDB;

CREATE TABLE schoo_fee_payments (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
school_id INT UNSIGNED NOT NULL,
invoice_id INT UNSIGNED NOT NULL,
student_id INT UNSIGNED NOT NULL,
amount DECIMAL(12,2) NOT NULL,
method VARCHAR(50) DEFAULT 'cash',
reference VARCHAR(128) DEFAULT NULL,
payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
note TEXT DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX (school_id),
INDEX (invoice_id),
INDEX (student_id)
) ENGINE=InnoDB;
