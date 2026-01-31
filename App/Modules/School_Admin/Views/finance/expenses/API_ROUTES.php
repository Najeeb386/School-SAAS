<?php
/**
 * Expense Management System - API Routes
 * 
 * All requests go through save_expense.php
 * 
 * Usage:
 *   POST /finance/expenses/save_expense.php
 *   Body: FormData or JSON
 */

// ============ CREATE EXPENSE ============
/*
POST /finance/expenses/save_expense.php

Form Data:
- action: "create"
- title: "Internet Bill" (required)
- expense_date: "2024-01-15" (required)
- amount: "5000.00" (required)
- expense_category_id: "1" (required)
- vendor_name: "Fibernet"
- invoice_no: "INV-001"
- payment_date: "2024-01-15"
- payment_method: "bank" (cash|bank|online|cheque)
- description: "Monthly internet"
- expense_category_id: "1" (FK to expense_categories)

Response: 
{
  "success": true,
  "message": "Expense created successfully",
  "id": 1
}
*/

// ============ UPDATE EXPENSE ============
/*
POST /finance/expenses/save_expense.php

Form Data:
- action: "update"
- id: "1" (required)
- title: "Internet Bill"
- expense_date: "2024-01-15"
- amount: "5000.00"
- expense_category_id: "1"
- vendor_name: "Fibernet"
- invoice_no: "INV-001"
- payment_date: "2024-01-15"
- payment_method: "bank"
- description: "Monthly internet"

Response:
{
  "success": true,
  "message": "Expense updated successfully"
}
*/

// ============ DELETE EXPENSE ============
/*
POST /finance/expenses/save_expense.php

Form Data:
- action: "delete"
- id: "1" (required)

Response:
{
  "success": true,
  "message": "Expense deleted successfully"
}
*/

// ============ APPROVE EXPENSE ============
/*
POST /finance/expenses/save_expense.php

Form Data:
- action: "approve"
- id: "1" (required)
- approval_notes: "Approved for payment" (optional)

Response:
{
  "success": true,
  "message": "Expense approved"
}
*/

// ============ REJECT EXPENSE ============
/*
POST /finance/expenses/save_expense.php

Form Data:
- action: "reject"
- id: "1" (required)
- approval_notes: "Missing invoice" (optional)

Response:
{
  "success": true,
  "message": "Expense rejected"
}
*/

// ============ FETCH EXPENSES (via Controller) ============
/*
In PHP code:
$controller = new ExpenseController($db);

// Get all expenses
$expenses = $controller->list($school_id, $session_id);

// Get single expense
$expense = $controller->get($expense_id, $school_id);

// Filter by category
$expenses = $controller->getByCategory($school_id, $category_id, $session_id);

// Filter by status
$expenses = $controller->getByStatus($school_id, 'pending', $session_id);

// Get summary statistics
$summary = $controller->getSummary($school_id, $session_id);

// Get categories
$categories = $controller->getCategories($school_id);
*/

// ============ EXPENSE STATUS WORKFLOW ============
/*
Status Values:
- pending: Awaiting approval
- approved: Approved and can be paid
- rejected: Rejected by admin (can be re-edited)

Status Transitions:
pending -> approved
pending -> rejected
rejected -> approved (after re-editing and re-submission)
*/

// ============ REQUIRED FIELDS ============
/*
When Creating/Updating:
- title (string, required)
- expense_date (DATE format YYYY-MM-DD, required)
- amount (decimal, required)
- expense_category_id (int, required, FK)
- vendor_name (string, optional)
- invoice_no (string, optional)
- payment_date (DATE, optional)
- payment_method (enum: cash|bank|online|cheque)
- description (text, optional)

Auto-populated:
- school_id (from session)
- session_id (from session)
- created_by (from session user_id)
- created_at (auto timestamp)
- updated_at (auto timestamp on update)
*/

// ============ CATEGORY IDS ============
/*
Query: SELECT id, name FROM expense_categories WHERE school_id = ?

Common Categories:
- Utilities (electricity, water, internet, etc.)
- Maintenance (repairs, cleaning, etc.)
- Office Supplies (stationery, equipment, etc.)
- Salaries (staff compensation)
- Transportation (fuel, vehicle maintenance)
- Food & Beverages
- Software & Licenses
- Miscellaneous
*/

// ============ EXAMPLE JAVASCRIPT USAGE ============
/*
// Create expense
const formData = new FormData();
formData.append('action', 'create');
formData.append('title', 'Internet Bill');
formData.append('expense_date', '2024-01-15');
formData.append('amount', '5000.00');
formData.append('expense_category_id', '3');
formData.append('vendor_name', 'Fibernet');
formData.append('payment_method', 'bank');

fetch('save_expense.php', {
    method: 'POST',
    body: formData
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        console.log('Expense created:', data.id);
    }
});

// Approve expense
const approveData = new FormData();
approveData.append('action', 'approve');
approveData.append('id', '1');
approveData.append('approval_notes', 'Approved for payment');

fetch('save_expense.php', {
    method: 'POST',
    body: approveData
})
.then(res => res.json())
.then(data => console.log(data));
*/
?>
