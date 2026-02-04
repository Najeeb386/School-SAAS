-- Diagnostic Queries for Concession Matching Issues
-- Use these to debug why concessions are not being applied

-- 1. Check if concessions exist for your school
SELECT id, school_id, admission_no, session_id, value, value_type, applies_to, start_month, end_month, status
FROM school_student_fees_concessions
WHERE school_id = 10  -- Replace with your school_id
ORDER BY admission_no;

-- 2. Check what admission_no values exist in students table
SELECT DISTINCT s.id, s.admission_no, s.first_name, s.last_name
FROM school_students s
WHERE s.school_id = 10  -- Replace with your school_id
ORDER BY s.admission_no;

-- 3. Cross-reference: Find students WITH concessions
SELECT DISTINCT 
    ss.id as student_id,
    ss.admission_no,
    ss.first_name,
    ss.last_name,
    sfc.id as concession_id,
    sfc.value,
    sfc.value_type,
    sfc.applies_to
FROM school_students ss
INNER JOIN school_student_fees_concessions sfc 
    ON ss.school_id = sfc.school_id 
    AND ss.admission_no = sfc.admission_no
WHERE ss.school_id = 10  -- Replace with your school_id
AND sfc.status = 1;

-- 4. Check invoice_counters to see if they're being created
SELECT * FROM invoice_counters
WHERE school_id = 10  -- Replace with your school_id
ORDER BY session_id;

-- 5. Check generated invoices to see what amounts are stored
SELECT 
    id, invoice_no, student_id, 
    gross_amount, concession_amount, net_payable, total_amount,
    billing_month, status
FROM schoo_fee_invoices
WHERE school_id = 10  -- Replace with your school_id
ORDER BY created_at DESC
LIMIT 20;

-- 6. Check invoice line items to see if concession line is created
SELECT 
    ii.id, ii.invoice_id, ii.description, ii.amount,
    sfi.invoice_no
FROM schoo_fee_invoice_items ii
INNER JOIN schoo_fee_invoices sfi ON ii.invoice_id = sfi.id
WHERE sfi.school_id = 10  -- Replace with your school_id
AND sfi.student_id = 1  -- Replace with specific student_id
ORDER BY sfi.created_at DESC, ii.id;

-- 7. Verify fee assignments exist
SELECT 
    a.id, a.fee_item_id, a.amount as assignment_amount,
    fi.name as fee_name, fi.category_id,
    fc.name as category_name
FROM schoo_fee_assignments a
LEFT JOIN schoo_fee_items fi ON fi.id = a.fee_item_id
LEFT JOIN schoo_fee_categories fc ON fc.id = fi.category_id
WHERE a.school_id = 10  -- Replace with school_id
AND a.session_id = 1  -- Replace with session_id
ORDER BY a.class_id;

-- 8. Check enrollment to ensure students are active
SELECT 
    se.id, se.student_id, se.class_id, se.session_id,
    ss.admission_no, ss.first_name, ss.last_name
FROM school_student_enrollments se
INNER JOIN school_students ss ON se.student_id = ss.id
WHERE se.school_id = 10  -- Replace with school_id
AND se.session_id = 1  -- Replace with session_id
ORDER BY ss.admission_no;
