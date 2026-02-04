# CONCESSION DEDUCTION - VERIFICATION CHECKLIST

## Pre-Implementation Checklist

- [ ] Backup database
- [ ] Review CONCESSION_DEDUCTION_FIXES.md
- [ ] Review INVOICE_CONCESSION_GUIDE.md
- [ ] Check PHP error log location
- [ ] Ensure school has fee assignments for test session/class
- [ ] Ensure school has at least one student with concession record

## Database Setup

- [ ] Run SQL migration to add columns (optional):
```sql
ALTER TABLE schoo_fee_invoices 
ADD COLUMN gross_amount DECIMAL(12,2) DEFAULT 0.00 AFTER billing_month,
ADD COLUMN concession_amount DECIMAL(12,2) DEFAULT 0.00 AFTER gross_amount,
ADD COLUMN net_payable DECIMAL(12,2) DEFAULT 0.00 AFTER concession_amount;
```
- [ ] Verify migration completed without errors
- [ ] Check that new columns are visible:
```sql
DESCRIBE schoo_fee_invoices;
```

## Code Verification

- [ ] Verify `bulk_generate_invoices.php` is updated:
  - [ ] Contains `getNextInvoiceNumber()` function
  - [ ] Contains enhanced concession logic
  - [ ] Contains INSERT with gross/concession/net columns
  - [ ] No syntax errors (check with PHP linter)

- [ ] Check that file has no PHP errors:
```bash
php -l App/Modules/School_Admin/Views/fees/invoices/bulk_generate_invoices.php
```

## Data Verification

- [ ] Concession records exist:
```sql
SELECT COUNT(*) FROM school_student_fees_concessions 
WHERE school_id = YOUR_SCHOOL_ID AND status = 1;
```
- [ ] Fee assignments exist for test class:
```sql
SELECT COUNT(*) FROM schoo_fee_assignments 
WHERE school_id = YOUR_SCHOOL_ID AND session_id = YOUR_SESSION_ID;
```
- [ ] Students are enrolled:
```sql
SELECT COUNT(*) FROM school_student_enrollments 
WHERE school_id = YOUR_SCHOOL_ID AND session_id = YOUR_SESSION_ID;
```

## Functional Testing

### Test 1: Preview Generation
- [ ] Open Fee Management → Generate Invoices
- [ ] Select Session, Billing Month, Class
- [ ] Click "Preview"
- [ ] Check that preview table shows:
  - [ ] Base amounts (gross fees)
  - [ ] Concessions column (should show negative values for students with concessions)
  - [ ] Total column (should be base - concessions)
- [ ] Record expected values for verification

### Test 2: Single Student Debug
- [ ] Go to: `bulk_generate_invoices.php?action=debug&admission_no=STUDENT_ADMISSION&session_id=1&billing_month=2026-01`
- [ ] Check JSON response contains:
  - [ ] `base_amount` > 0
  - [ ] `concessions` value (0 if no concession, otherwise concession amount)
  - [ ] `total_amount` = base_amount - concessions
  - [ ] `fee_items` array with individual line items

### Test 3: Invoice Generation
- [ ] Back to Fee Management → Generate Invoices
- [ ] Fill in form (Session, Billing Month, Class)
- [ ] Click "Generate Invoices"
- [ ] Check response:
  - [ ] `success: true`
  - [ ] `invoice_count > 0`
  - [ ] No errors or empty errors array
- [ ] Record invoice numbers shown in success message

### Test 4: Database Verification
- [ ] Check invoices were created:
```sql
SELECT invoice_no, gross_amount, concession_amount, net_payable, total_amount
FROM schoo_fee_invoices
WHERE created_at > NOW() - INTERVAL 1 HOUR
ORDER BY created_at DESC;
```
- [ ] Verify for each invoice:
  - [ ] `gross_amount` is sum of base fees
  - [ ] `concession_amount` is 0 if no concession, or calculated amount if has concession
  - [ ] `net_payable` = gross_amount - concession_amount
  - [ ] `total_amount` = net_payable (or both are NET if columns don't exist)

### Test 5: Line Items Verification
- [ ] Check invoice items:
```sql
SELECT ii.description, ii.amount, ii.invoice_id
FROM schoo_fee_invoice_items ii
WHERE ii.invoice_id IN (
    SELECT id FROM schoo_fee_invoices 
    WHERE created_at > NOW() - INTERVAL 1 HOUR
)
ORDER BY ii.invoice_id, ii.id;
```
- [ ] For each invoice, verify:
  - [ ] Individual fee items are present (e.g., Tuition, Exam Fee)
  - [ ] Amounts match fee assignments
  - [ ] If concession applies: Concession/Scholarship line item exists with negative amount
  - [ ] Sum of all items = net payable (if no additional fees)

### Test 6: Log Verification
- [ ] Check PHP error log:
```bash
tail -200 /xampp/apache/logs/error.log | grep InvoiceCalc
```
- [ ] Look for entries like:
  - [ ] `[InvoiceCalc] Searching concession for school_id=... admission_no=...`
  - [ ] `[InvoiceCalc] CONCESSION APPLIED: ...` or `[InvoiceCalc] NO CONCESSION FOUND: ...`
  - [ ] `[InvoiceGenerate] invoice_no=... gross=... concession=... net=...`
- [ ] Verify no ERROR entries (only INFO/DEBUG)

## Edge Case Testing

### Test 7: No Concession Student
- [ ] Select a class where some students have NO concession
- [ ] Generate invoice for that batch
- [ ] Verify:
  - [ ] Invoices are created
  - [ ] `concession_amount = 0`
  - [ ] `net_payable = gross_amount`
  - [ ] No Concession line item in fee_items

### Test 8: Expired Concession
- [ ] Create a concession with `end_month` = past date (e.g., 2025-12-01)
- [ ] Generate invoice for future month (e.g., 2026-02-01)
- [ ] Verify:
  - [ ] Concession is NOT applied
  - [ ] Log shows `[InvoiceCalc] NO CONCESSION FOUND`

### Test 9: Tuition-Only Concession
- [ ] Create concession with `applies_to = 'tuition_only'`
- [ ] Invoice should have multiple fee types (Tuition + other fees)
- [ ] Verify:
  - [ ] Concession is calculated on tuition portion only
  - [ ] Other fees are not reduced
  - [ ] `net_payable = tuition - concession + other_fees`

### Test 10: Percentage Concession
- [ ] Create concession with `value_type = 'percentage'` and `value = 10`
- [ ] Gross = 1000
- [ ] Verify:
  - [ ] `concession_amount = 100` (10% of 1000)
  - [ ] `net_payable = 900`

### Test 11: Fixed Concession
- [ ] Create concession with `value_type = 'fixed'` and `value = 500`
- [ ] Verify:
  - [ ] `concession_amount = 500`
  - [ ] `net_payable = gross - 500`

### Test 12: Concession Greater Than Gross
- [ ] Create concession with `value_type = 'fixed'` and `value = 2000`
- [ ] Gross = 1000
- [ ] Verify:
  - [ ] Concession is capped: `concession_amount = min(2000, 1000) = 1000`
  - [ ] `net_payable = 1000 - 1000 = 0` (not negative)

### Test 13: Multiple Invoices Batch
- [ ] Generate invoices for entire class (10+ students)
- [ ] Some with concession, some without
- [ ] Verify:
  - [ ] All invoices created successfully
  - [ ] Invoice numbers are unique and sequential
  - [ ] Each invoice has correct concession applied
  - [ ] No duplicate key errors

## Performance Testing

- [ ] Generate 50+ invoices in batch
- [ ] Measure completion time (should be <30 seconds)
- [ ] Check for database locks or slowness
- [ ] Verify invoice counters are accurate after large batch

## Rollback Checklist

If issues occur:
- [ ] Disable new columns feature by reverting code
- [ ] Run on legacy schema (code is backward compatible)
- [ ] Identify root cause in error logs
- [ ] Fix and re-test

## Sign-Off Checklist

- [ ] All tests passed
- [ ] No errors in logs
- [ ] Concessions are applied correctly
- [ ] Amounts match expectations
- [ ] Invoice numbers are unique
- [ ] Database integrity verified
- [ ] Performance is acceptable
- [ ] Code ready for production

## Production Deployment

1. [ ] Backup production database
2. [ ] Apply code changes to production
3. [ ] Run database migration (optional but recommended)
4. [ ] Clear any caches
5. [ ] Generate test batch of invoices
6. [ ] Verify in production database
7. [ ] Monitor logs for 24 hours
8. [ ] Notify users of new feature

## Documentation Update

- [ ] Update user manual with new features
- [ ] Document any customizations made
- [ ] Create FAQ for common issues
- [ ] Add training materials if needed

---

**Completion Date**: _______________  
**Tested By**: _______________  
**Approved By**: _______________  
**Notes**: _______________________________________________
