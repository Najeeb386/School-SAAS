# Invoice Generation & Concession Deduction - Implementation Guide

## Overview
This document explains how the invoice system calculates and applies concessions (scholarships/discounts) to student fee invoices.

## Invoice Amount Calculation Flow

### Step 1: Fetch Base Fees
- Query `schoo_fee_assignments` for the student's class/session
- Join with `schoo_fee_items` and `schoo_fee_categories` to get names and categories
- Sum all fee amounts: **GROSS_AMOUNT**

### Step 2: Fetch and Apply Concessions
- Query `school_student_fees_concessions` for the student's admission_no
- Filter by:
  - `school_id` = student's school
  - `status = 1` (active)
  - `start_month <= billing_month <= end_month` (date range validation)
  - `admission_no = student_admission_no` (EXACT MATCH - case-sensitive)
- If found:
  - Determine which fees apply: `applies_to` (all | tuition_only)
  - Calculate concession: 
    - If `value_type = 'percentage'`: `concession = gross * value / 100`
    - If `value_type = 'fixed'`: `concession = value`
  - Cap concession: `concession = min(concession, gross_amount)`
  - Set **CONCESSION_AMOUNT** = calculated concession

### Step 3: Add Additional Fees
- Parse any manually selected additional fees
- Sum them: **ADDITIONAL_FEES_TOTAL**

### Step 4: Calculate Final Amount
```
NET_PAYABLE = GROSS_AMOUNT - CONCESSION_AMOUNT + ADDITIONAL_FEES_TOTAL
(Never allow NET_PAYABLE < 0)

TOTAL_AMOUNT = NET_PAYABLE  (stored in invoices table)
```

### Step 5: Create Invoice Line Items
- Insert each base fee as a line item
- Insert concession as a negative amount line item (if applied)
- Insert additional fees as line items
- Sum of all line items = NET_PAYABLE

## Database Schema Changes Required

### 1. Add tracking columns to schoo_fee_invoices

```sql
ALTER TABLE schoo_fee_invoices 
ADD COLUMN gross_amount DECIMAL(12,2) DEFAULT 0.00 AFTER billing_month,
ADD COLUMN concession_amount DECIMAL(12,2) DEFAULT 0.00 AFTER gross_amount,
ADD COLUMN net_payable DECIMAL(12,2) DEFAULT 0.00 AFTER concession_amount;
```

**Why?**
- `gross_amount`: Total before any deductions
- `concession_amount`: Amount deducted due to scholarship/discount
- `net_payable`: Final amount student must pay
- `total_amount`: Kept for backward compatibility (equals net_payable)

### 2. Verify school_student_fees_concessions columns

Required columns:
- `school_id` - REQUIRED for filtering
- `admission_no` - REQUIRED for student matching (NOT student_id)
- `value` - Amount or percentage
- `value_type` - 'fixed' or 'percentage'
- `applies_to` - 'all' or 'tuition_only'
- `start_month` - Date range start
- `end_month` - Date range end
- `status` - 1 for active, 0 for inactive

## Common Issues & Solutions

### Issue 1: Concession Not Found
**Symptom**: Concession query returns NULL, amount not deducted

**Causes**:
1. `admission_no` mismatch between student and concession records
   - Check case sensitivity: "AAMS-2026-000001" vs "aams-2026-000001"
   - Check whitespace: trailing/leading spaces
   - Use: `TRIM(LOWER(admission_no))` when comparing
   
2. Concession date range not active for billing month
   - Verify `start_month <= billing_month`
   - Verify `end_month >= billing_month` (or NULL for no end date)
   
3. Concession not active
   - Verify `status = 1`
   - Verify `school_id` matches invoice school_id

**Debug**:
```php
// Log what admission_no is being searched
error_log("Searching concession for admission_no: '{$admission_no}'");

// Run the query manually with TRIM/LOWER
SELECT * FROM school_student_fees_concessions
WHERE school_id = 10
AND TRIM(LOWER(admission_no)) = TRIM(LOWER('aams-2026-000001'))
AND status = 1;
```

### Issue 2: Concession Found But Not Applied
**Symptom**: Concession record exists but still not deducted

**Causes**:
1. Invoice line items created but concession line not showing
   - Check `schoo_fee_invoice_items` - should have negative amount item
   
2. Concession amount calculated but net_payable still gross
   - Check if `applies_to = 'tuition_only'` but student has no tuition items

**Debug**:
```sql
-- Check if concession line item was created
SELECT * FROM schoo_fee_invoice_items 
WHERE invoice_id = 123
AND description LIKE '%Concession%';

-- Check invoice amounts
SELECT id, gross_amount, concession_amount, net_payable, total_amount
FROM schoo_fee_invoices
WHERE id = 123;
```

### Issue 3: Amount Mismatch
**Symptom**: Concession shows in preview but different amount in saved invoice

**Causes**:
1. Calculation happens twice (preview vs generate) with different data
2. Float precision issues (use DECIMAL in DB, not FLOAT)

**Solution**:
- Use same `calculateInvoiceAmount()` function for both preview and generate
- Cast all amounts to float before calculations
- Round to 2 decimals before saving to DB

## Testing Checklist

Before deployment, verify:

- [ ] Create invoice with student who has NO concession
  - Expected: Concession amount = 0, Total = Gross
  
- [ ] Create invoice with student who has FIXED concession
  - Expected: Total = Gross - Fixed Amount
  
- [ ] Create invoice with student who has PERCENTAGE concession
  - Expected: Total = Gross - (Gross * Percentage / 100)
  
- [ ] Create invoice with student who has tuition_only concession + multiple fee types
  - Expected: Concession applied only to tuition fees, not other fees
  
- [ ] Create invoice for expired concession (end_month < billing_month)
  - Expected: Concession not applied
  
- [ ] Check schoo_fee_invoice_items table
  - Expected: Has concession line item with negative amount
  
- [ ] Compare preview vs generated invoice
  - Expected: Same amounts

## PHP Code Location

**Main Invoice Calculation**: 
`App/Modules/School_Admin/Views/fees/invoices/bulk_generate_invoices.php`

**Functions**:
- `calculateInvoiceAmount()` - Core calculation logic
- `getNextInvoiceNumber()` - Atomic counter generation
- `buildPreviewHTML()` - Preview rendering

## Log Monitoring

Check PHP error log for:
```
[InvoiceCalc] Searching concession for school_id=10 admission_no=aams-2026-000001
[InvoiceCalc] Concession result: {...}
[InvoiceCalc] NO CONCESSION FOUND: admission_no=...
[InvoiceCalc] CONCESSION APPLIED: admission_no=... concession_amount=27.50
[InvoiceGenerate] invoice_no=INV-10-2026-00001 gross=900 concession=27 additional=0 net=873
```

These logs help trace exactly what happened during calculation.
