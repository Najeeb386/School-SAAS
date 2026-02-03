# Bulk Invoice Generator System

## Overview
The bulk invoice generator allows school administrators to automatically generate fee invoices for multiple students in one action. The system:

- Scans all students from selected class(es)
- Fetches fee structures from `school_fee_assignment`
- Applies scholarships/concessions from `school_student_fees_concessions`
- Adds optional additional fees (examination, vacation, library, etc.)
- Generates invoices with automatic line items
- Stores data in `schoo_fee_invoices` and `schoo_fee_invoice_items` tables

## Features

### 1. **Bulk Generation**
- Generate invoices for all classes or a specific class
- Select billing month
- Choose due date
- Preview before generating
- Automatic duplicate prevention (skips if invoice already exists for month)

### 2. **Additional Fees**
- Examination Fee
- Vacation/Sports Fee
- Advance Payment / Other
- Library Fee
- Each with configurable amount

### 3. **Fee Calculation Logic**
```
Total Invoice Amount = Base Fees - Concessions + Additional Fees
```

Where:
- **Base Fees**: From `school_fee_assignment` (class-based fee structure)
- **Concessions**: From `school_student_fees_concessions` (scholarships, discounts)
  - Supports both percentage and fixed amount
  - Only active concessions are applied
  - Applies if end_month is NULL, "0000-00-00", or >= current month
- **Additional Fees**: Manually selected fees with custom amounts

### 4. **Invoice Management**
- View all invoices with filters
- Filter by month and status
- Mark invoices as paid
- Delete invoices
- View detailed invoice items

## Database Tables

### `schoo_fee_invoices`
```sql
CREATE TABLE `schoo_fee_invoices` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `school_id` int NOT NULL,
  `student_id` int NOT NULL,
  `session_id` int NOT NULL,
  `invoice_no` varchar(50) UNIQUE NOT NULL,
  `billing_month` varchar(7), -- YYYY-MM
  `total_amount` decimal(10,2),
  `status` enum('pending','paid','overdue'),
  `due_date` date,
  `created_at` timestamp,
  `updated_at` timestamp,
  KEY `idx_school_student` (`school_id`, `student_id`),
  KEY `idx_month` (`billing_month`),
  KEY `idx_status` (`status`)
);
```

### `schoo_fee_invoice_items`
```sql
CREATE TABLE `schoo_fee_invoice_items` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `fee_item_id` int,
  `description` varchar(255),
  `amount` decimal(10,2),
  `created_at` timestamp,
  FOREIGN KEY (`invoice_id`) REFERENCES `schoo_fee_invoices`(`id`) ON DELETE CASCADE,
  KEY `idx_invoice` (`invoice_id`)
);
```

## Files

### Views
- **`fees_invoice.php`**: Main bulk invoice generator form
- **`invoice_list.php`**: List and manage all invoices
- **`invoice_detail.php`**: Modal popup for invoice details
- **`bulk_generate_invoices.php`**: AJAX handler for preview and generation
- **`invoice_action.php`**: AJAX handler for mark paid/delete actions

### Models
- **`App/Modules/School_Admin/Models/InvoiceModel.php`**: Invoice database operations

## Usage Guide

### Generating Invoices

1. Navigate to **Finances > Bulk Invoice Generator**
2. Select **Session** (e.g., 2025-2026)
3. Enter **Billing Month** (e.g., February 2026)
4. Choose **Apply To**:
   - **All Classes**: Invoices all students across all classes
   - **Specific Class**: Invoices students only from selected class
5. (Optional) Select **Additional Fees** and enter amounts
6. Set **Due Date** (defaults to today)
7. Click **Preview** to see calculation breakdown
8. Click **Generate Invoices** to create

### Invoice Preview
The preview shows:
- Student name and admission number
- Base amount (from fee structure)
- Concessions applied (scholarships/discounts)
- Additional fees total
- **Total invoice amount**
- Summary totals for all selected students

### Viewing Invoices

1. Go to **Finances > Fee Invoices** (or link from generator)
2. Use filters to find invoices:
   - By **Billing Month**
   - By **Status** (Pending, Paid, Overdue)
3. Click **View** to see breakdown
4. Click dropdown menu to:
   - **Mark Paid**: Change status to "paid"
   - **Delete**: Remove invoice (and its items)

## Technical Details

### Invoice Number Generation
```
Format: INV-{school_id}-{year}-{sequence}
Example: INV-5-2026-00001
```

### Fee Calculation Query
```php
// Base fees from school_fee_assignment
SELECT amount FROM school_fee_assignment 
WHERE school_id = :sid AND class_id = :cid AND session_id = :ssid

// Concessions from school_student_fees_concessions
SELECT discount_value, discount_type FROM school_student_fees_concessions
WHERE student_id = :stid AND status = 1 AND 
  (end_month IS NULL OR end_month >= current_month)
```

### AJAX Endpoints

#### `bulk_generate_invoices.php` (POST)
**Parameters:**
- `action`: 'preview' or 'generate'
- `session_id`: int
- `billing_month`: YYYY-MM
- `apply_to`: 'all' or 'specific'
- `class_id`: int (if apply_to='specific')
- `additional_fees[]`: array of fee types
- `fee_{type}_amount`: decimal (for each additional fee)
- `due_date`: YYYY-MM-DD

**Response (Preview):**
```json
{
  "success": true,
  "student_count": 25,
  "preview_html": "<table>...</table>"
}
```

**Response (Generate):**
```json
{
  "success": true,
  "message": "Invoices generated successfully",
  "invoice_count": 25,
  "errors": []
}
```

#### `invoice_action.php` (POST)
**Parameters:**
- `action`: 'mark_paid' or 'delete'
- `id`: invoice_id

**Response:**
```json
{
  "success": true,
  "message": "Invoice marked as paid"
}
```

## Validation & Error Handling

### On Preview
- Verifies session and month are selected
- Checks for active students in selected class(es)
- Calculates amounts for each student
- Shows preview with totals

### On Generation
- Prevents duplicate invoices (skips if already exists for month)
- Validates all student records
- Uses database transaction for atomicity
- Returns count of generated invoices
- Lists any failed generations with reasons

## Security

- All operations require `auth_check_school_admin.php` authentication
- School ID from session is enforced in all queries
- Invoices can only be accessed by the school that created them
- Prepared statements protect against SQL injection
- CSRF protection via session validation

## Future Enhancements

- [ ] Bulk payment recording
- [ ] Invoice PDF generation and download
- [ ] Email invoices to parents/guardians
- [ ] Recurring invoice scheduling
- [ ] Payment reminders for overdue invoices
- [ ] Custom fee structure templates
- [ ] Late fees automation
- [ ] Invoice approval workflow
