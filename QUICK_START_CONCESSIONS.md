# QUICK START - Concession Deduction System

## What Was Fixed

The invoice system now properly deducts concessions (scholarships/discounts) from student fee invoices.

## How It Works

1. **Gross Amount** = Sum of all base fees from fee assignments
2. **Concession Amount** = Fixed amount OR percentage of gross (based on concession record)
3. **Additional Fees** = Any extra fees manually added
4. **Net Payable** = Gross - Concession + Additional Fees (never negative)
5. **Total Amount** = Net Payable (what student pays)

## Database Setup (Required)

Run this SQL once to enable full tracking:

```sql
ALTER TABLE schoo_fee_invoices 
ADD COLUMN gross_amount DECIMAL(12,2) DEFAULT 0.00 AFTER billing_month,
ADD COLUMN concession_amount DECIMAL(12,2) DEFAULT 0.00 AFTER gross_amount,
ADD COLUMN net_payable DECIMAL(12,2) DEFAULT 0.00 AFTER concession_amount;
```

**Note**: The code works even without these columns (backward compatible).

## How to Test

### 1. Create a Test Concession

```sql
INSERT INTO school_student_fees_concessions 
(school_id, admission_no, value_type, value, applies_to, status, start_month, end_month)
VALUES 
(10, 'aams-2026-000001', 'percentage', 10, 'all', 1, '2026-01-01', NULL);
```

This creates a 10% scholarship for the student.

### 2. Generate Invoice

1. Go to Fee Management → Generate Invoices
2. Select Session, Billing Month, Class
3. Click "Preview"
4. Check that the preview shows:
   - **Base**: Gross fee amount
   - **Concessions**: Negative value (e.g., -100.00)
   - **Total**: Base - Concessions
5. Click "Generate Invoices"
6. Check database:

```sql
SELECT invoice_no, gross_amount, concession_amount, net_payable, total_amount
FROM schoo_fee_invoices
ORDER BY created_at DESC LIMIT 1;
```

Expected output:
```
invoice_no          | gross_amount | concession_amount | net_payable | total_amount
INV-10-2026-00001   | 1000.00      | 100.00           | 900.00      | 900.00
```

### 3. Verify Line Items

```sql
SELECT description, amount
FROM schoo_fee_invoice_items ii
WHERE ii.invoice_id = (SELECT MAX(id) FROM schoo_fee_invoices)
ORDER BY id;
```

Expected output includes:
```
description               | amount
Tuition                   | 1000.00
Concession/Scholarship    | -100.00
```

## Troubleshooting

### Concession Not Applied?

**Check 1: Does concession record exist?**
```sql
SELECT * FROM school_student_fees_concessions
WHERE school_id = 10
AND admission_no = 'aams-2026-000001'
AND status = 1;
```

**Check 2: Is admission_no matching?**
- Compare exactly (case-sensitive, no spaces)
- Log shows: `[InvoiceCalc] Searching concession for... admission_no=...`
- If NULL returned, admission_no didn't match

**Check 3: Is date range active?**
- `start_month <= billing_month <= end_month` (or NULL end_month)
- For January 2026 billing: start_month <= 2026-01-01 AND end_month >= 2026-01-01

**Check 4: Check error logs**
```
[InvoiceCalc] CONCESSION APPLIED: ...
[InvoiceCalc] NO CONCESSION FOUND: ...
[InvoiceGenerate] invoice_no=... gross=... concession=... net=...
```

### Still Not Working?

Check the PHP error log:
```bash
tail -f /xampp/apache/logs/error.log | grep InvoiceCalc
```

You'll see exactly what's happening:
- Concession being searched
- Result (found or not found)
- Amount calculated
- Invoice generated with final amounts

## Code Locations

| Component | File |
|-----------|------|
| Invoice Calculation | `App/Modules/School_Admin/Views/fees/invoices/bulk_generate_invoices.php` |
| Function: `calculateInvoiceAmount()` | Line ~330 |
| Function: `getNextInvoiceNumber()` | Line ~260 |
| Invoice INSERT logic | Line ~180 |
| Concession query | Line ~360 |

## Key Variables

In preview/generate response:
- `base_amount` - Gross fees
- `concessions` - Deduction amount
- `additional_fees_total` - Extra fees added
- `total_amount` - Net payable (base - concession + additional)

In database (after migration):
- `gross_amount` - Total base fees
- `concession_amount` - Scholarship/discount deducted
- `net_payable` - Final amount due
- `total_amount` - Same as net_payable (for backward compatibility)

## Common Scenarios

### Scenario A: No Concession
- Gross: 1000
- Concession: 0
- Net: 1000

### Scenario B: Fixed Concession
- Gross: 1000
- Concession: 500 (fixed)
- Net: 500

### Scenario C: Percentage Concession
- Gross: 1000
- Concession: 10% = 100
- Net: 900

### Scenario D: Tuition-Only Concession
- Tuition: 800
- Other fees: 200
- Gross: 1000
- Concession: 10% of tuition = 80
- Net: 920 (tuition reduced, other fees unchanged)

## Files Modified

1. **bulk_generate_invoices.php** - Core calculation and insertion logic
2. **add_invoice_tracking_columns.sql** - Database schema migration (NEW)
3. **CONCESSION_DEDUCTION_FIXES.md** - Detailed explanation (NEW)
4. **INVOICE_CONCESSION_GUIDE.md** - Implementation guide (NEW)
5. **CONCESSION_DEBUG_QUERIES.sql** - SQL queries for debugging (NEW)

## Support

If concessions still aren't working:
1. Run the SQL queries in `CONCESSION_DEBUG_QUERIES.sql`
2. Check PHP error log for `[InvoiceCalc]` entries
3. Verify admission_no matches exactly between student and concession table
4. Verify concession `status = 1`
5. Verify date range (`start_month`, `end_month`) includes billing month

---
**System**: Multi-School SaaS Fee Management  
**Last Updated**: 2026-02-04  
**Status**: Production Ready ✅
