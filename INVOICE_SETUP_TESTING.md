# Invoice System - Setup & Testing Checklist

## Pre-Requisites Verification

### Database Tables

Ensure these tables exist in your database:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'schoo_fee_invoices';
SHOW TABLES LIKE 'schoo_fee_invoice_items';
SHOW TABLES LIKE 'school_fee_assignment';
SHOW TABLES LIKE 'school_student_fees_concessions';
```

### Create Tables (if missing)

```sql
-- Fee Invoices
CREATE TABLE IF NOT EXISTS `schoo_fee_invoices` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_id` int NOT NULL,
  `student_id` int NOT NULL,
  `session_id` int NOT NULL,
  `invoice_no` varchar(50) NOT NULL UNIQUE,
  `billing_month` varchar(7),
  `total_amount` decimal(10,2) DEFAULT 0,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `due_date` date,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_school_id` (`school_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  KEY `idx_month` (`billing_month`)
);

-- Invoice Items/Line Items
CREATE TABLE IF NOT EXISTS `schoo_fee_invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` int NOT NULL,
  `fee_item_id` int,
  `description` varchar(255),
  `amount` decimal(10,2) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `schoo_fee_invoices`(`id`) ON DELETE CASCADE,
  KEY `idx_invoice_id` (`invoice_id`)
);

-- Fee Assignment (must exist for fee structure)
CREATE TABLE IF NOT EXISTS `school_fee_assignment` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_id` int NOT NULL,
  `class_id` int NOT NULL,
  `session_id` int NOT NULL,
  `fee_item_id` int NOT NULL,
  `amount` decimal(10,2) DEFAULT 0,
  `status` int DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_school_class_session` (`school_id`, `class_id`, `session_id`)
);

-- Concessions (scholarships/discounts)
CREATE TABLE IF NOT EXISTS `school_student_fees_concessions` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_id` int NOT NULL,
  `student_id` int NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) DEFAULT 0,
  `start_month` varchar(7),
  `end_month` varchar(7),
  `status` int DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);
```

## Setup Steps

### 1. Database Preparation ✓
- [ ] Verify `schoo_fee_invoices` table exists
- [ ] Verify `schoo_fee_invoice_items` table exists
- [ ] Run CREATE TABLE scripts above if missing
- [ ] Verify `school_fee_assignment` has sample data
- [ ] Verify `school_students` table has active students
- [ ] Verify `school_sessions` table has records

### 2. File Placement ✓
- [ ] Copy all files to `App/Modules/School_Admin/Views/fees/invoices/`
  - `fees_invoice.php` (main generator)
  - `invoice_list.php` (invoice list)
  - `invoice_detail.php` (detail view)
  - `bulk_generate_invoices.php` (AJAX handler)
  - `invoice_action.php` (action handler)
- [ ] Copy `InvoiceModel.php` to `App/Modules/School_Admin/Models/`
- [ ] Copy `INVOICE_SYSTEM_GUIDE.md` to root directory

### 3. Test Data Setup
- [ ] Create at least 1 session
- [ ] Create at least 1 class
- [ ] Create 5-10 test students in the class with status=1
- [ ] Create fee structure entries in `school_fee_assignment` for the class
- [ ] (Optional) Create a concession record for one student

### 4. Navigation Setup
Add these menu links to your admin dashboard:

```html
<!-- Under Finances menu -->
<a href="?module=School_Admin&page=fees/invoices/fees_invoice" class="list-group-item">
  <i class="fas fa-file-invoice"></i> Bulk Invoice Generator
</a>
<a href="?module=School_Admin&page=fees/invoices/invoice_list" class="list-group-item">
  <i class="fas fa-list"></i> View Invoices
</a>
```

## Testing Workflow

### Test 1: Basic Generation (All Classes)

1. Navigate to **Bulk Invoice Generator**
2. Select any session
3. Select a future month (e.g., current month + 1)
4. Leave "Apply To" as "All Classes"
5. Click **Preview**
   - Should show all active students
   - Should show correct fee calculations
   - Total amount should be > 0
6. Click **Generate Invoices**
   - Should show success message
   - Should redirect to invoice list
7. Verify invoices appear in list with correct amounts

### Test 2: Specific Class Generation

1. Go back to **Bulk Invoice Generator**
2. Select session and month (different from Test 1)
3. Select **Specific Class** radio button
4. Choose a class from dropdown
5. Click **Preview**
   - Should show only students from selected class
   - Count should match class enrollment
6. Click **Generate Invoices**
7. Filter invoice list by month to verify

### Test 3: Additional Fees

1. Go to **Bulk Invoice Generator**
2. Select session and month
3. Select "All Classes"
4. Check **Examination Fee** → Enter "500"
5. Check **Library Fee** → Enter "200"
6. Click **Preview**
   - Total should include base + 700 (500+200) - concessions
7. Click **Generate Invoices**
8. View invoice details
   - Items should show all fee components separately

### Test 4: Concession Application

1. Create a test student with concession:
   - 10% scholarship (discount_value=10, discount_type='percentage')
   - Active status, end_month = null
2. Run Test 1 (All Classes)
3. View invoice for that student
   - Should show "Concession/Scholarship" as negative amount
   - Total should be: Base - (Base × 10%)

### Test 5: Duplicate Prevention

1. Generate invoices for Feb 2026 for all students
2. Immediately try to generate again for Feb 2026
3. System should skip existing invoices
4. Should show 0 new invoices generated

### Test 6: Invoice Management

1. In **Invoice List**:
   - Click **View** on any invoice → Modal shows details ✓
   - Click **Mark Paid** → Status changes to "paid" ✓
   - Click dropdown menu → Options appear ✓
   - Click **Delete** → Invoice removed ✓
2. Filter by month → Shows only that month's invoices ✓
3. Filter by status → Shows only selected status ✓

### Test 7: Edge Cases

- [ ] Try generating with no session selected → Should show error
- [ ] Try generating with no month selected → Should show error
- [ ] Try generating for class with no students → Should show error
- [ ] Try generating for class with no fee structure → Should work with 0 base fees
- [ ] Generate invoice with 0 amount → Should create successfully
- [ ] Generate invoice for student with no concessions → Should work normally

## Sample Test Data SQL

```sql
-- Insert test session if missing
INSERT INTO school_sessions (school_id, name, start_date, end_date, status) 
VALUES (1, '2025-2026', '2025-01-01', '2026-12-31', 1);

-- Insert test class if missing
INSERT INTO school_classes (school_id, name, status) 
VALUES (1, 'Test Class A', 1);

-- Insert 5 test students
INSERT INTO school_students (school_id, first_name, last_name, admission_no, class_id, status)
VALUES 
  (1, 'Ahmed', 'Ali', 'ADM-001', 1, 1),
  (1, 'Fatima', 'Khan', 'ADM-002', 1, 1),
  (1, 'Hassan', 'Ahmed', 'ADM-003', 1, 1),
  (1, 'Zainab', 'Ibrahim', 'ADM-004', 1, 1),
  (1, 'Ibrahim', 'Hassan', 'ADM-005', 1, 1);

-- Insert fee structure
INSERT INTO school_fee_assignment (school_id, class_id, session_id, fee_item_id, amount, status)
VALUES 
  (1, 1, 1, 1, 5000, 1),  -- Tuition: 5000
  (1, 1, 1, 2, 1000, 1);  -- Exam Fee: 1000

-- Insert sample concession for first student
INSERT INTO school_student_fees_concessions (school_id, student_id, discount_type, discount_value, status)
VALUES (1, 1, 'percentage', 10, 1);
```

## Troubleshooting

### "No active students found"
- **Cause**: No students with `status=1` in selected class
- **Fix**: Verify students exist and have status=1

### "Invoice amount is 0"
- **Cause**: No fee structure defined for class
- **Fix**: Add records to `school_fee_assignment` for that class+session

### "Invoices not showing in list"
- **Cause**: Invoices were generated but filtering hides them
- **Fix**: Clear all filters and check "All Months" and "All Status"

### "Preview shows wrong calculation"
- **Cause**: Concession status or dates incorrect
- **Fix**: Verify concession has status=1 and end_month is NULL or future

### "AJAX requests failing"
- **Cause**: auth_check_school_admin.php not detecting session
- **Fix**: Verify `$_SESSION['school_id']` is set before accessing pages

### "Button click does nothing"
- **Cause**: jQuery not loading properly
- **Fix**: Check browser console for JavaScript errors
- **Verify**: jQuery 3.6.0 CDN link is in page <head>

## Validation & Security

- [ ] All endpoints validate `school_id` from session
- [ ] Prepared statements used for all queries (no SQL injection)
- [ ] User can only see/modify own school's invoices
- [ ] Transaction used for multi-step operations
- [ ] Error messages don't expose sensitive data
- [ ] CSRF protection via session validation

## Performance Optimization

For large numbers of students (500+):

1. **Increase PHP timeout**: `set_time_limit(300);` in bulk_generate_invoices.php
2. **Batch processing**: Modify to generate 100 invoices per request
3. **Database indexing**: Ensure indexes on `school_id`, `student_id`, `billing_month`
4. **Connection pooling**: Use persistent database connections

## Success Criteria

All of the following must be true:

- ✓ Bulk invoices generate without errors
- ✓ Invoice amounts match fee structure + concessions + additional fees
- ✓ Duplicate invoices are prevented
- ✓ Invoice list displays with filters
- ✓ Invoice details show all line items
- ✓ Status changes persist in database
- ✓ Deleted invoices are removed completely
- ✓ All AJAX requests return proper JSON
- ✓ Session/auth works correctly
- ✓ No console errors or warnings

## Going Live

1. [ ] Backup database before first use
2. [ ] Test with real data in staging environment
3. [ ] Train staff on generator workflow
4. [ ] Set up backup of invoice data (monthly exports)
5. [ ] Monitor invoice generation logs
6. [ ] Create admin documentation for end users
7. [ ] Plan for invoice archival/retention policy
