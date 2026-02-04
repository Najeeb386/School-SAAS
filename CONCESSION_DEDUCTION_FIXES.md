# CONCESSION DEDUCTION - CRITICAL FIXES APPLIED

## Problem Statement
Concession/scholarship amounts were NOT being deducted from invoice totals during generation, even though they were being calculated.

## Root Causes Identified & Fixed

### 1. ❌ MISSING: Explicit Concession Amount Initialization
**Problem**: If no concession was found, `$result['concessions']` remained uninitialized
**Fix**: Added explicit `$result['concessions'] = 0.0` in the `else` block

### 2. ❌ MISSING: Proper Gross/Net Tracking
**Problem**: Invoice table only had `total_amount` - no way to see gross vs concession breakdown
**Fix**: 
- Created SQL migration to add `gross_amount`, `concession_amount`, `net_payable` columns
- Updated INSERT logic to save all three amounts
- Added fallback for legacy schema (tries new columns, reverts to old if not exist)

### 3. ❌ MISSING: Negative Amount Capping
**Problem**: Concession could theoretically exceed gross amount, making net payable negative
**Fix**: Added validation: `concession = min(concession, gross_amount)`

### 4. ❌ MISSING: Explicit Error Logging
**Problem**: Hard to debug why concessions didn't apply - no detailed logs
**Fix**: Added comprehensive error logging:
```php
error_log("[InvoiceCalc] CONCESSION APPLIED: ...")
error_log("[InvoiceCalc] NO CONCESSION FOUND: ...")
error_log("[InvoiceGenerate] invoice_no=... gross=... concession=... net=...")
```

## Code Changes Made

### File: `bulk_generate_invoices.php`

#### Change 1: Enhanced Concession Logic (Lines ~390-445)
```php
// BEFORE: Only set concession if found
if ($concession) {
    // ... calculation ...
    $result['concessions'] = $concession_amount;
}
// Bug: If no concession, $result['concessions'] = 0 (uninitialized)

// AFTER: Explicitly set in both cases
if ($concession) {
    // ... calculation ...
    $result['concessions'] = (float)$concession_amount;
} else {
    error_log("[InvoiceCalc] NO CONCESSION FOUND: ...");
    $result['concessions'] = 0.0;  // EXPLICIT
}
```

#### Change 2: Enhanced Invoice Insertion (Lines ~180-240)
```php
// BEFORE: Only stored total_amount
INSERT INTO schoo_fee_invoices (..., total_amount, ...)
VALUES (..., :total, ...)
:total => $calc['total_amount']

// AFTER: Store gross, concession, net, total
$gross = (float)$calc['base_amount'];
$concession = (float)($calc['concessions'] ?? 0);
$net = max(0, $gross - $concession + $additional);

INSERT INTO schoo_fee_invoices 
(..., gross_amount, concession_amount, net_payable, total_amount, ...)
VALUES (..., :gross, :concession, :net, :total, ...)
```

## Database Migration Required

Run this SQL to enable full concession tracking:

```sql
ALTER TABLE schoo_fee_invoices 
ADD COLUMN gross_amount DECIMAL(12,2) DEFAULT 0.00 AFTER billing_month,
ADD COLUMN concession_amount DECIMAL(12,2) DEFAULT 0.00 AFTER gross_amount,
ADD COLUMN net_payable DECIMAL(12,2) DEFAULT 0.00 AFTER concession_amount;
```

Note: The code has fallback logic - it will work even if these columns don't exist yet.

## How to Verify the Fix

### 1. Check Generated Invoices
```sql
SELECT invoice_no, gross_amount, concession_amount, net_payable, total_amount
FROM schoo_fee_invoices
WHERE created_at > NOW() - INTERVAL 1 HOUR;
```

Expected:
- `net_payable = gross_amount - concession_amount` (for invoices without additional fees)
- `total_amount = net_payable`
- `concession_amount > 0` if student has concession

### 2. Check Invoice Line Items
```sql
SELECT ii.description, ii.amount
FROM schoo_fee_invoice_items ii
WHERE ii.invoice_id = 123
ORDER BY ii.id;
```

Expected: Should include a line item with:
- `description = 'Concession/Scholarship'`
- `amount < 0` (negative)

### 3. Check Error Logs
Look for entries like:
```
[InvoiceCalc] CONCESSION APPLIED: admission_no=aams-2026-000001 value_type=percentage value=3 concession_amount=27
[InvoiceGenerate] invoice_no=INV-10-2026-00001 gross=900 concession=27 net=873
```

## Testing Scenarios

### Scenario 1: No Concession Student
- Student has NO concession record
- Expected: `concession_amount = 0`, `net_payable = gross_amount`

### Scenario 2: Fixed Concession
- Student has concession with `value = 500`, `value_type = 'fixed'`
- Expected: `concession_amount = 500`, `net_payable = gross_amount - 500`

### Scenario 3: Percentage Concession
- Student has concession with `value = 10`, `value_type = 'percentage'`
- Gross = 1000
- Expected: `concession_amount = 100`, `net_payable = 900`

### Scenario 4: Tuition-Only Concession
- Student has concession with `applies_to = 'tuition_only'`, `value = 5%`
- Gross includes: Tuition (1000) + Exam Fee (200) = 1200
- Expected: `concession_amount = 50` (5% of tuition only = 5% * 1000), `net_payable = 1150`

## Next Steps if Concession Still Not Applied

1. **Check admission_no matching**:
   - Compare student's `admission_no` with concession record's `admission_no`
   - They must match EXACTLY (case-sensitive, no spaces)
   
2. **Check date ranges**:
   - Verify concession `start_month <= billing_month <= end_month`
   - Check if `end_month` is NULL or '0000-00-00' (means no end date)
   
3. **Check concession status**:
   - Verify `status = 1` in concession record
   
4. **Review error logs**:
   - Look for `[InvoiceCalc]` entries - they show exactly what was searched and found
   
5. **Run diagnostic queries**:
   - See `CONCESSION_DEBUG_QUERIES.sql` for queries to verify data integrity

## Files Modified

1. **bulk_generate_invoices.php** - Enhanced concession logic and invoice insertion
2. **add_invoice_tracking_columns.sql** - Migration script (NEW)
3. **CONCESSION_DEBUG_QUERIES.sql** - Diagnostic queries (NEW)
4. **INVOICE_CONCESSION_GUIDE.md** - Implementation guide (NEW)

## Backward Compatibility

✅ Code automatically detects if `gross_amount`/`concession_amount`/`net_payable` columns exist
✅ Falls back to legacy schema if columns not present
✅ All existing invoices continue to work
✅ New invoices will use enhanced schema once migration is run
