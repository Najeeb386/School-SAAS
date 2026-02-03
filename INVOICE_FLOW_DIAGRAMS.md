# Bulk Invoice Generator - System Flow Diagrams

## 1. User Flow - Generate Invoices

```
┌─────────────────────────────────────────────────────────────────┐
│                    BULK INVOICE GENERATOR                       │
└─────────────────────────────────────────────────────────────────┘

User navigates to fees_invoice.php
         │
         ▼
┌─────────────────────────────────────┐
│  Fill Generator Form:              │
│  - Session (dropdown)              │
│  - Billing Month (YYYY-MM input)  │
│  - Apply To (all/specific)        │
│  - Additional Fees (checkboxes)   │
│  - Due Date (date input)          │
└─────────────────────────────────────┘
         │
         ├─────────────────────┬─────────────────────┐
         │                     │                     │
         ▼                     ▼                     ▼
   [PREVIEW BUTTON]     [PREVIEW BUTTON]     [GENERATE BUTTON]
         │                     │                     │
         ▼                     ▼                     ▼
  AJAX POST to           AJAX POST to        AJAX POST to
  bulk_generate_         bulk_generate_      bulk_generate_
  invoices.php           invoices.php        invoices.php
  action='preview'       action='preview'    action='generate'
         │                     │                     │
         ▼                     ▼                     ▼
  Calculate fees      Show preview HTML    Create invoices
  for all students    in modal dialog      in database
         │                     │                     │
         ▼                     ▼                     ▼
  Return JSON with  ┌──────────────────┐   Database transaction:
  preview HTML      │ Preview Modal:   │   - Insert schoo_fee_invoices
  and count         │ • Student names  │   - Insert schoo_fee_invoice_items
         │          │ • Amounts        │   
         ▼          │ • Totals         │   ▼
  Display in        │ • Accept/Cancel  │
  bootstrap modal   └──────────────────┘   Return success count
         │                     │                     │
         │          [User confirms]                 ▼
         │                     │            Redirect to
         └─────────────────────┴────────→  invoice_list.php
                                                   │
                                                   ▼
                                          Show success message
                                          with count
```

## 2. Fee Calculation Flow

```
┌────────────────────────────────────────────────────────────┐
│           INVOICE AMOUNT CALCULATION LOGIC                  │
└────────────────────────────────────────────────────────────┘

For each student in selection:
         │
         ▼
    GET BASE FEES
         │
    SELECT FROM school_fee_assignment
    WHERE class_id = :cid AND session_id = :sid
         │
         ├─────────────┬─────────────┬─────────────┐
         │             │             │             │
      Fee 1         Fee 2         Fee 3         Fee N
      5000          1000           500          ....
         │             │             │             │
         └─────────────┴─────────────┴─────────────┘
                       │
                  SUM = Base Total
                       │
                       ▼
              CHECK FOR CONCESSION
                       │
    SELECT FROM school_student_fees_concessions
    WHERE student_id = :sid AND status = 1
    AND end_month IS NULL OR end_month >= current
                       │
                  ┌─────┴─────┐
                  │           │
              Has concession? No concession
                  │           │
                  ▼           ▼
          Apply discount    Concession = 0
          (% or fixed)
                  │           │
                  └─────┬─────┘
                        │
                        ▼
              ADD ADDITIONAL FEES
                        │
          User selected fees with amounts:
          - Examination: 500 (optional)
          - Vacation: 200 (optional)
          - Library: 100 (optional)
          - Advance: 1000 (optional)
                        │
                   SUM = Additional Total
                        │
                        ▼
         ┌──────────────────────────────┐
         │   TOTAL INVOICE AMOUNT       │
         │   = Base - Concession +      │
         │     Additional Fees          │
         │                              │
         │   Example:                   │
         │   = 6500 - 650 + 500         │
         │   = 6350                     │
         └──────────────────────────────┘
```

## 3. Database Transaction Flow

```
┌───────────────────────────────────────────────────────────┐
│              GENERATION (Database Transaction)             │
└───────────────────────────────────────────────────────────┘

START TRANSACTION
         │
         ▼
    For each student:
         │
         ├─► CHECK if invoice already exists for month
         │        │
         │        ├─ YES: SKIP student (avoid duplicates)
         │        │
         │        └─ NO: Continue
         │
         ├─► CALCULATE amounts (as above)
         │
         ├─► INSERT INTO schoo_fee_invoices
         │   (school_id, student_id, session_id, invoice_no,
         │    billing_month, total_amount, status, due_date)
         │
         ├─► GET last_insert_id() = invoice_id
         │
         ├─► For each fee item:
         │   INSERT INTO schoo_fee_invoice_items
         │   (invoice_id, fee_item_id, description, amount)
         │   └─ Base fees (multiple rows)
         │   └─ Concession (if applicable)
         │   └─ Additional fees (if selected)
         │
         ├─► Increment invoice_count++
         │
         └─► Next student...
         
         ▼
    COMMIT TRANSACTION
    (All or nothing - if error, all rolled back)
         │
         ▼
    Return invoice_count & any errors
```

## 4. Invoice Management Flow

```
┌──────────────────────────────────────────────────────┐
│           INVOICE LIST & MANAGEMENT                   │
└──────────────────────────────────────────────────────┘

User navigates to invoice_list.php
         │
         ▼
    LOAD ALL INVOICES
         │
    SELECT FROM schoo_fee_invoices i
    LEFT JOIN school_students s
    WHERE school_id = :sid
         │
         ├──────────────────┬────────────────┐
         │                  │                │
    APPLY FILTERS      APPLY FILTERS    APPLY FILTERS
    by Month           by Status       by Student
         │                  │                │
         └──────────────────┴────────────────┘
                       │
                       ▼
           RENDER INVOICE TABLE
           with DataTables (sortable)
                       │
    ┌──────────────────┼──────────────────┐
    │                  │                  │
   CLICK             CLICK              CLICK
   [VIEW]           [MARK PAID]        [DELETE]
    │                  │                  │
    ▼                  ▼                  ▼
Open invoice_  AJAX POST to      AJAX POST to
detail.php      invoice_action.php invoice_action.php
modal popup     action=mark_paid   action=delete
    │                  │                  │
    ▼                  ▼                  ▼
Show invoice    UPDATE status=paid  DELETE FROM
breakdown       in database        schoo_fee_invoices
in modal                            DELETE FROM
    │                  │           schoo_fee_invoice_items
    │                  │                  │
    └──────────────────┴──────────────────┘
                       │
                       ▼
                 [CLOSE MODAL]
                       │
                       ▼
              REFRESH INVOICE LIST
```

## 5. Data Flow - From Selection to Database

```
┌─────────────────────────────────────────────────────────────┐
│              COMPLETE DATA FLOW DIAGRAM                      │
└─────────────────────────────────────────────────────────────┘

USER INPUT (fees_invoice.php form)
    │
    ├─ session_id → school_sessions table
    ├─ billing_month → YYYY-MM format
    ├─ apply_to → 'all' or 'specific'
    ├─ class_id → school_classes table (if specific)
    ├─ additional_fees[] → Array of fee types
    ├─ fee_*_amount → Decimal amounts
    └─ due_date → YYYY-MM-DD
    
    │
    ▼
[AJAX to bulk_generate_invoices.php]
    
    │
    ├─► SELECT students from school_students
    │   WHERE school_id = :sid AND status = 1
    │   AND class_id IN (:cids) [based on apply_to]
    │
    ├─► For each student:
    │   │
    │   ├─► SELECT fees from school_fee_assignment
    │   │
    │   ├─► SELECT concession from 
    │   │   school_student_fees_concessions
    │   │
    │   └─► Calculate = Base - Concession + Additional
    │
    ▼
[If Preview action]
    │
    ├─► Build HTML table with breakdown
    ├─► Return preview HTML + student count
    └─► Display in modal
    
[If Generate action]
    │
    ├─► Begin DB transaction
    │
    ├─► For each student:
    │   │
    │   ├─► Check for existing invoice
    │   │
    │   ├─► INSERT INTO schoo_fee_invoices
    │   │   with calculated total_amount
    │   │
    │   └─► INSERT line items INTO
    │       schoo_fee_invoice_items
    │       (base fees, concession, additional)
    │
    ├─► Commit transaction
    │
    └─► Return success + invoice_count
    
    ▼
DATABASE STATE
    │
    ├─► schoo_fee_invoices
    │   └─ New rows created for each student
    │
    └─► schoo_fee_invoice_items
        └─ Line item rows for each component
```

## 6. Status Flow

```
┌────────────────────────────────────────────┐
│         INVOICE STATUS LIFECYCLE            │
└────────────────────────────────────────────┘

GENERATED (action='generate')
    │
    ▼
[PENDING] ◄─ Default status
    │
    ├──────────────────────────┐
    │                          │
    │                    [PAID] ◄─ User clicks "Mark Paid"
    │                          │
    │               (Stored in DB, status='paid')
    │                          │
    │                    (No auto-change)
    │
    └──────────────────────────┘
         │
         │ (If due_date passed and status != 'paid')
         │ (Displayed in UI as OVERDUE - not stored)
         │
         ▼
[OVERDUE] ◄─ Calculated in invoice_list.php
                based on due_date comparison
                
         │
         ▼
[DELETE] ◄─ User clicks "Delete"
    │
    ▼
Removed from schoo_fee_invoices
Removed from schoo_fee_invoice_items
```

## 7. Error Handling Flow

```
┌─────────────────────────────────────────────────┐
│            ERROR HANDLING PATHS                  │
└─────────────────────────────────────────────────┘

TRY BLOCK [bulk_generate_invoices.php]
    │
    ├─► Missing session_id?
    │   └─► throw "Session is required"
    │
    ├─► Missing billing_month?
    │   └─► throw "Billing month is required"
    │
    ├─► No students found?
    │   └─► throw "No active students found"
    │
    ├─► Missing fee structure?
    │   └─► Warn but continue (0 base fees)
    │
    ├─► Database error on INSERT?
    │   └─► ROLLBACK transaction
    │   └─► Add to errors[] array
    │   └─► Continue next student
    │
    ├─► Invalid action?
    │   └─► throw "Invalid action"
    │
    └─► Catch exception
        │
        ▼
    CATCH BLOCK
        │
        ├─► Log error with context
        │
        ├─► Set HTTP 400 status
        │
        ├─► Return JSON error response
        │   {
        │     "success": false,
        │     "message": "Error description"
        │   }
        │
        └─► Display error to user
            (AJAX catches and shows alert)
```

## 8. Performance Characteristics

```
┌──────────────────────────────────────────────┐
│      PERFORMANCE & SCALABILITY NOTES          │
└──────────────────────────────────────────────┘

Students per generation:
    1-100:   Instant (< 1 sec)
    100-500: Quick (2-5 secs)
    500+:    Slow, may need optimization

Database queries per student:
    - 1 × SELECT from school_fee_assignment
    - 1 × SELECT from school_student_fees_concessions
    - 1 × INSERT into schoo_fee_invoices
    - N × INSERT into schoo_fee_invoice_items
      (where N = number of fee components)
    
    Total per 100 students ≈ 600 queries

Memory usage:
    - Stores all students in memory
    - For 1000 students ≈ 5MB
    
Optimization tips:
    1. Use database indexes on:
       - school_students(school_id, class_id, status)
       - school_fee_assignment(school_id, class_id, session_id)
       - school_student_fees_concessions(student_id, status)
    
    2. For 500+ students, increase PHP timeout:
       set_time_limit(300); // 5 minutes
    
    3. Consider batch processing:
       Generate 100 students per request
       Return URL for next batch
```

---

These diagrams show the complete flow of the bulk invoice generation system from user input through database storage and management.
