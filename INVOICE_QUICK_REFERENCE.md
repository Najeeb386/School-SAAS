# Invoice System - Quick Reference Card

## 🚀 Quick Start (5 Minutes)

### 1. Create Database Tables
```sql
-- Copy-paste these into your database:
CREATE TABLE IF NOT EXISTS `schoo_fee_invoices` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `school_id` int, `student_id` int, `session_id` int,
  `invoice_no` varchar(50) UNIQUE,
  `billing_month` varchar(7), `total_amount` decimal(10,2),
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `due_date` date, `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_school_id` (`school_id`), KEY `idx_month` (`billing_month`)
);

CREATE TABLE IF NOT EXISTS `schoo_fee_invoice_items` (
  `id` int AUTO_INCREMENT PRIMARY KEY, `invoice_id` int,
  `fee_item_id` int, `description` varchar(255), `amount` decimal(10,2),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `schoo_fee_invoices`(`id`) ON DELETE CASCADE,
  KEY `idx_invoice_id` (`invoice_id`)
);
```

### 2. Add Menu Links
```html
<a href="?module=School_Admin&page=fees/invoices/fees_invoice">
  <i class="fas fa-file-invoice"></i> Bulk Invoice Generator
</a>
<a href="?module=School_Admin&page=fees/invoices/invoice_list">
  <i class="fas fa-list"></i> View Invoices
</a>
```

### 3. Files Are Ready
All PHP files are in:
```
App/Modules/School_Admin/Views/fees/invoices/
App/Modules/School_Admin/Models/InvoiceModel.php
```

**Done!** Navigate to the URLs above.

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| **QUICK_INVOICE_SETUP.md** | 5-minute setup ⭐ START HERE |
| **BULK_INVOICE_SUMMARY.md** | Feature overview |
| **INVOICE_SYSTEM_GUIDE.md** | Complete documentation |
| **INVOICE_SETUP_TESTING.md** | Testing & troubleshooting |
| **INVOICE_FLOW_DIAGRAMS.md** | Architecture & flows |
| **IMPLEMENTATION_CHECKLIST.md** | What was built checklist |

---

## 🎯 Main Features

### Generate Invoices
1. Select **Session** (e.g., 2025-2026)
2. Select **Month** (e.g., Feb 2026)
3. Choose **All Classes** or **Specific Class**
4. (Optional) Add fees (Exam, Library, etc.)
5. Click **Preview** → **Generate**

### Manage Invoices
- **View**: See breakdown of fees
- **Mark Paid**: Change status to paid
- **Delete**: Remove invoice
- **Filter**: By month or status

---

## 💻 Technical Details

### Files Structure
```
fees_invoice.php             ← Main form (UI)
bulk_generate_invoices.php   ← AJAX processor (backend)
invoice_list.php             ← List & filter (UI)
invoice_detail.php           ← Details popup (UI)
invoice_action.php           ← AJAX actions (backend)
InvoiceModel.php             ← Database layer
```

### AJAX Endpoints
```
POST bulk_generate_invoices.php
  action: 'preview' or 'generate'
  Returns: JSON with preview_html / invoice_count

POST invoice_action.php
  action: 'mark_paid' or 'delete'
  Returns: JSON success response
```

### Fee Calculation
```
Total = Base Fees - Concessions + Additional Fees

Base Fees:     From school_fee_assignment
Concessions:   From school_student_fees_concessions
Additional:    User-selected (exam, vacation, library, advance)
```

---

## 🔍 Common Tasks

### Generate Invoices for All Students
```
1. Session → Any
2. Month → Any future month
3. Apply To → All Classes
4. Additional Fees → (optional)
5. Generate
```

### Generate with Exam Fee
```
1. Check "Examination Fee"
2. Enter amount (e.g., 500)
3. Select other filters
4. Generate
```

### View Single Student Invoice
```
1. Go to Invoice List
2. Find student
3. Click "View"
4. See breakdown in modal
```

### Mark Invoice as Paid
```
1. Invoice List
2. Find invoice
3. Click dropdown menu
4. Click "Mark Paid"
5. Confirm
```

---

## ✅ Prerequisites Check

Before using, verify:
- [ ] `school_fee_assignment` has data
- [ ] `school_students` has active students
- [ ] `school_sessions` has session records
- [ ] `school_classes` has class records
- [ ] Database tables created (SQL provided)
- [ ] Menu links added to dashboard

**Issue?** See **INVOICE_SETUP_TESTING.md** troubleshooting section.

---

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Page not found | Check file paths match exactly |
| Unauthorized | Verify `$_SESSION['school_id']` is set |
| No students shown | Verify students have status=1 |
| 0 total amount | Add data to `school_fee_assignment` |
| AJAX error | Check browser console (F12) for JS errors |
| Database error | Run CREATE TABLE SQL scripts |
| Invoice not created | Check for duplicate (already exists for month) |

**Still stuck?** See **INVOICE_SETUP_TESTING.md** for detailed help.

---

## 📊 Database Tables

### schoo_fee_invoices
```
id          - PK
school_id   - FK (multi-tenant)
student_id  - FK
session_id  - FK
invoice_no  - UNIQUE invoice number
billing_month - YYYY-MM format
total_amount  - Decimal (Base - Concession + Additional)
status      - pending | paid | overdue
due_date    - Date
created_at  - Timestamp
updated_at  - Timestamp
```

### schoo_fee_invoice_items
```
id          - PK
invoice_id  - FK (cascade delete)
fee_item_id - FK (can be 0 for non-standard items)
description - Text (e.g., "Tuition", "Concession", "Exam Fee")
amount      - Decimal (positive or negative)
created_at  - Timestamp
```

### Related Tables (Read Only)
- `school_fee_assignment` - Fee structure
- `school_student_fees_concessions` - Scholarships
- `school_students` - Student data
- `school_sessions` - Session data
- `school_classes` - Class data

---

## 🔐 Security Features

✅ Session authentication required  
✅ School ID isolation (multi-tenant)  
✅ Prepared statements (SQL injection prevention)  
✅ Input validation  
✅ Error logging without sensitive data  
✅ Database transactions (atomicity)  
✅ Cascade delete for data integrity  

---

## ⚡ Performance Tips

- **< 100 students**: Instant (< 1 sec)
- **100-500 students**: Quick (2-5 secs)
- **500+ students**: Use indexes + increase timeout

**Optimize with:**
1. Database indexes on `school_id`, `class_id`, `status`
2. `set_time_limit(300)` for large batches
3. Consider batch processing for 1000+ students

---

## 📞 Support

**Need help?**
1. Check relevant documentation file (see top)
2. See troubleshooting section above
3. Check browser console (F12) for JS errors
4. Check server error log for PHP errors

**Documentation Path:**
```
School-SAAS/
├── QUICK_INVOICE_SETUP.md         ← Start here
├── INVOICE_SYSTEM_GUIDE.md        ← Details
├── INVOICE_SETUP_TESTING.md       ← Testing
├── INVOICE_FLOW_DIAGRAMS.md       ← Architecture
└── IMPLEMENTATION_CHECKLIST.md    ← What was built
```

---

## 🎓 Training Quick Facts

### For Admin
- Generate invoices for students monthly
- Add additional fees (exam, vacation, etc.)
- View and manage invoices
- Record payments (mark as paid)

### For Developer
- View, modify, or extend fee calculation logic
- Customize additional fee types
- Integrate with payment gateway
- Generate PDF invoices
- Email invoices to parents

---

## 📋 Invoice Generation Checklist

Before generating, ensure:
- [ ] Session is selected
- [ ] Billing month is selected
- [ ] Class(es) have students with status=1
- [ ] Fee structure exists for class+session
- [ ] Due date is reasonable
- [ ] No duplicate month invoices exist (system prevents)

---

## 🚀 Production Deployment

### Before Going Live
1. ✅ Test with sample data (see INVOICE_SETUP_TESTING.md)
2. ✅ Backup database
3. ✅ Train staff on UI
4. ✅ Create admin documentation
5. ✅ Monitor first generation
6. ✅ Set up invoice archival policy

### Monthly Routine
1. Generate invoices for current month
2. Review preview before confirming
3. Send to students (manual or email)
4. Track payments
5. Mark as paid when received
6. Archive month-end

---

## 💡 Tips & Tricks

**Pro Tips:**
- Preview shows exact amounts before generating (no surprises!)
- Duplicate invoice prevention = safe re-running
- Use "Specific Class" to stagger generation
- "Mark Paid" to track who has paid
- Delete invoices if mistakes (removes completely)
- Filter by status to see pending amounts

---

## 📈 Next Steps

1. ✅ Setup (QUICK_INVOICE_SETUP.md)
2. ✅ Test (INVOICE_SETUP_TESTING.md)
3. ✅ Train (share relevant docs with staff)
4. ✅ Deploy (go live)
5. ✅ Monitor (watch error logs first week)
6. ✅ Optimize (add indexes if slow)

---

**Last Updated**: February 2026  
**Version**: 1.0  
**Status**: ✅ Ready for Production
