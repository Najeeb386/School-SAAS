# Quick Integration Guide

## Step 1: Add Database Tables

Run these SQL queries in your database:

```sql
-- Invoices Table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS `schoo_fee_invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` int NOT NULL,
  `fee_item_id` int,
  `description` varchar(255),
  `amount` decimal(10,2) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `schoo_fee_invoices`(`id`) ON DELETE CASCADE,
  KEY `idx_invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Step 2: Verify Prerequisites

Your database should have:
- ✓ `school_fee_assignment` - Fee structure per class
- ✓ `school_student_fees_concessions` - Scholarships/discounts
- ✓ `school_students` - Active students list
- ✓ `school_sessions` - Session records
- ✓ `school_classes` - Class list

**Check with:**
```sql
SHOW TABLES LIKE 'school_fee_assignment';
SHOW TABLES LIKE 'school_student_fees_concessions';
SHOW TABLES LIKE 'school_students';
SHOW TABLES LIKE 'school_sessions';
SHOW TABLES LIKE 'school_classes';
```

## Step 3: Update Navigation Menu

Add these links to your admin dashboard navigation:

```html
<!-- In your Finances/Fees section menu -->

<li class="nav-item">
  <a href="?module=School_Admin&page=fees/invoices/fees_invoice" class="nav-link">
    <i class="fas fa-file-invoice-dollar"></i>
    <p>Bulk Invoice Generator</p>
  </a>
</li>

<li class="nav-item">
  <a href="?module=School_Admin&page=fees/invoices/invoice_list" class="nav-link">
    <i class="fas fa-list"></i>
    <p>View Invoices</p>
  </a>
</li>
```

## Step 4: Verify File Permissions

Ensure files are readable by your web server:

```bash
# Linux/Mac
chmod 644 App/Modules/School_Admin/Views/fees/invoices/*.php
chmod 644 App/Modules/School_Admin/Models/InvoiceModel.php

# Or set proper ownership
chown www-data:www-data App/Modules/School_Admin/Views/fees/invoices/
chown www-data:www-data App/Modules/School_Admin/Models/InvoiceModel.php
```

## Step 5: Test the System

### Quick Test
1. Create a test session with ID=1 (or note existing)
2. Create a test class with ID=1 (or note existing)
3. Add 3-5 test students to the class with status=1
4. Add fee assignments for the class:
   ```sql
   INSERT INTO school_fee_assignment (school_id, class_id, session_id, fee_item_id, amount, status)
   VALUES (1, 1, 1, 1, 5000, 1);  -- Tuition
   INSERT INTO school_fee_assignment (school_id, class_id, session_id, fee_item_id, amount, status)
   VALUES (1, 1, 1, 2, 1000, 1);  -- Exam
   ```
5. Navigate to **Bulk Invoice Generator**
6. Select the test session and a future month
7. Leave "All Classes" selected
8. Click **Preview** - should show 5 students
9. Click **Generate Invoices** - should create 5 invoices
10. Go to **View Invoices** - verify invoices appear

## File Structure

```
School-SAAS/
├── App/Modules/School_Admin/
│   ├── Views/fees/invoices/
│   │   ├── fees_invoice.php                 ← Main form
│   │   ├── bulk_generate_invoices.php       ← AJAX handler
│   │   ├── invoice_list.php                 ← List view
│   │   ├── invoice_detail.php               ← Details modal
│   │   └── invoice_action.php               ← Actions handler
│   └── Models/
│       └── InvoiceModel.php                 ← Database layer
├── BULK_INVOICE_SUMMARY.md                  ← This summary
├── INVOICE_SYSTEM_GUIDE.md                  ← Full documentation
└── INVOICE_SETUP_TESTING.md                 ← Testing guide
```

## Key URLs

```
/App/Modules/School_Admin/Views/fees/invoices/fees_invoice.php
  → Bulk invoice generator form

/App/Modules/School_Admin/Views/fees/invoices/invoice_list.php
  → View and manage invoices

/App/Modules/School_Admin/Views/fees/invoices/invoice_detail.php
  → Invoice details (called via AJAX)

/App/Modules/School_Admin/Views/fees/invoices/bulk_generate_invoices.php
  → Preview and generate invoices (POST AJAX)

/App/Modules/School_Admin/Views/fees/invoices/invoice_action.php
  → Mark paid / delete invoices (POST AJAX)
```

## Error Troubleshooting

### "Page not found"
- Ensure files are in correct directory
- Check file names exactly match

### "Unauthorized"
- Verify `auth_check_school_admin.php` is detecting session
- Check `$_SESSION['school_id']` is set
- Test: Create a simple PHP file that echoes $_SESSION

### "No students found"
- Verify students have `status=1`
- Check students are assigned to a class
- Confirm `school_students` table exists

### "Undefined table error"
- Run the CREATE TABLE SQL scripts above
- Verify table names exactly match (case-sensitive on Linux)

### "AJAX returning 400 Bad Request"
- Check browser Console (F12) for JavaScript errors
- Verify form data is being submitted correctly
- Check server error log for PHP errors

### "Preview shows 0 total amount"
- Verify `school_fee_assignment` has data for the class
- Check fee amount values are > 0
- Confirm fee items are marked status=1

## Configuration Points

**In fees_invoice.php** - Change default due date:
```php
// Line ~200 - Default due date setting
const DAYS_UNTIL_DUE = 15; // Change to your default
```

**In bulk_generate_invoices.php** - Customize fees:
```php
// Line ~35 - Define which additional fees are available
$additional_fees = [
    'examination' => 'Examination Fee',
    'vacation' => 'Vacation/Sports Fee',
    'advance' => 'Advance Payment',
    'library' => 'Library Fee'
];
// Add or remove as needed
```

## Common Use Cases

### Use Case 1: Monthly Invoice for All Classes
1. Select session
2. Select any month
3. Leave "All Classes" selected
4. Click Generate
→ Creates invoice for every student in school

### Use Case 2: Additional Fee for Exam
1. Select session and month
2. Select all classes (or specific)
3. Check "Examination Fee" → Enter 500
4. Click Generate
→ Each invoice = Base Fees - Concessions + 500 exam fee

### Use Case 3: Specific Class Invoicing
1. Select session and month
2. Select "Specific Class" radio
3. Choose class from dropdown
4. Click Generate
→ Only students in that class are invoiced

### Use Case 4: Record Student Payment
1. Go to Invoice List
2. Find student's invoice
3. Click dropdown → "Mark Paid"
4. Confirm
→ Status changes to "paid" in database

## Important Notes

1. **Duplicate Prevention**: Invoices won't be created if one already exists for that student+month
2. **Concessions**: Only applied if marked active (status=1) and end_month is null or current/future
3. **Additional Fees**: Optional - can generate without any
4. **Transactions**: Multi-step operations use database transactions for data safety
5. **Permissions**: All operations require school admin session authentication

## Support

For complete documentation, see:
- **INVOICE_SYSTEM_GUIDE.md** - Full system documentation
- **INVOICE_SETUP_TESTING.md** - Detailed setup & testing steps
- Browser Console (F12) for JavaScript errors
- Server error logs for PHP errors

---

**Next Step**: Run the quick test in Step 5 above to verify everything works!
