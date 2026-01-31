<?php
/**
 * School Admin - Expenses Management
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Expenses - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #000; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h3 { color: #000; margin-bottom: 10px; font-size: 28px; }
        .breadcrumb { margin-bottom: 20px; font-size: 14px; }
        .breadcrumb a { color: #007bff; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 25px; }
        
        .stat-card {
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-card h6 {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-card .amount { font-size: 24px; font-weight: 700; }
        .stat-card.total { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card.paid { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.pending { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

        .controls { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .controls button, .controls select, .controls input { 
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; cursor: pointer;
        }
        .controls input { color: #000; }
        .controls button { background: #007bff; color: white; border: none; }
        .controls button:hover { background: #0056b3; }
        .controls .outline { background: white; color: #007bff; border: 1px solid #007bff; }

        .card { background: white; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
        
        table { width: 100%; border-collapse: collapse; color: #000; margin-top: 10px; }
        table thead { background: #007bff; color: white; }
        table th { padding: 12px; text-align: left; font-weight: 600; border: none; }
        table td { padding: 12px; border-bottom: 1px solid #ddd; color: #000; }
        table tbody tr:hover { background: #f9f9f9; }

        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .badge-hosting { background: #e3f2fd; color: #1976d2; }
        .badge-salary { background: #f3e5f5; color: #7b1fa2; }
        .badge-marketing { background: #fff3e0; color: #e65100; }
        .badge-maintenance { background: #fce4ec; color: #c2185b; }
        .badge-software { background: #e8f5e9; color: #388e3c; }
        .badge-office { background: #f1f8e9; color: #558b2f; }
        .badge-misc { background: #eceff1; color: #455a64; }
        .badge-paid { background: #d4edda; color: #155724; }
        .badge-pending { background: #fff3cd; color: #856404; }

        .btn { padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-outline { background: white; border: 1px solid #ddd; color: #333; }
        .btn-outline:hover { background: #f8f9fa; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 25px; border-radius: 8px; width: 90%; max-width: 600px; color: #000; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 15px; }
        .modal-header h5 { margin: 0; color: #000; }
        .close-btn { font-size: 24px; background: none; border: none; cursor: pointer; color: #999; }
        .close-btn:hover { color: #000; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; color: #000; }
        .form-group textarea { resize: vertical; min-height: 80px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .form-actions button { flex: 1; padding: 10px; }

        .text-center { text-align: center; }
        .text-muted { color: #666; }
        .py-4 { padding: 30px 0; }

        @media (max-width: 768px) {
            .stat-row { grid-template-columns: 1fr; }
            .controls { flex-direction: column; }
            .controls input, .controls select { width: 100%; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3>Expenses</h3>
                <div class="breadcrumb">
                    <a href="../../dashboard/index.php">Overview</a> / 
                    <a href="../finance.php">Finance</a> / 
                    <span>Expenses</span>
                </div>
            </div>
            <button onclick="history.back()" class="btn btn-outline">← Back</button>
        </div>

        <!-- Statistics Cards -->
        <div class="stat-row">
            <div class="stat-card total">
                <h6>Total Expenses</h6>
                <div class="amount">₨0.00</div>
            </div>
            <div class="stat-card paid">
                <h6>Paid Expenses</h6>
                <div class="amount">₨0.00</div>
            </div>
            <div class="stat-card pending">
                <h6>Pending Expenses</h6>
                <div class="amount">₨0.00</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="controls">
            <button id="btnAddExpense" class="btn btn-primary">+ Add Expense</button>
            <button class="btn btn-outline">Import CSV</button>
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <option value="hosting">Hosting</option>
                <option value="salary">Salary</option>
                <option value="marketing">Marketing</option>
                <option value="maintenance">Maintenance</option>
                <option value="software">Software</option>
                <option value="office">Office</option>
                <option value="misc">Miscellaneous</option>
            </select>
            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
            </select>
            <input type="search" id="searchInput" placeholder="Search expenses...">
        </div>

        <!-- Expenses Table -->
        <div class="card">
            <table id="expensesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No expenses found. Click "Add Expense" to create one.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Expense Modal -->
    <div id="expenseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add New Expense</h5>
                <button class="close-btn" onclick="closeExpenseModal()">×</button>
            </div>
            <form id="expenseForm" method="POST" action="save_expense.php">
                <input type="hidden" id="expenseId" name="expense_id" value="">

                <div class="form-group">
                    <label>Expense Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" id="expenseDate" name="expense_date" required>
                    </div>
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" id="amount" name="amount" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="hosting">Hosting</option>
                            <option value="salary">Salary</option>
                            <option value="marketing">Marketing</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="software">Software</option>
                            <option value="office">Office</option>
                            <option value="misc">Miscellaneous</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" id="vendor" name="vendor_name" placeholder="e.g., AWS, Microsoft, etc.">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="description" name="description" placeholder="Additional details..."></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="recurring" name="is_recurring">
                        This is a recurring expense
                    </label>
                </div>

                <div id="recurringOptions" style="display: none;">
                    <div class="form-group">
                        <label>Recurring Cycle *</label>
                        <select id="recurringCycle" name="recurring_cycle">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                    <button type="button" class="btn btn-outline" onclick="closeExpenseModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open/Close Modal
        document.getElementById('btnAddExpense').addEventListener('click', openExpenseModal);
        document.getElementById('recurring').addEventListener('change', toggleRecurringOptions);

        function openExpenseModal() {
            document.getElementById('expenseForm').reset();
            document.getElementById('expenseId').value = '';
            document.querySelector('.modal-header h5').textContent = 'Add New Expense';
            document.getElementById('expenseModal').classList.add('show');
            document.getElementById('expenseDate').valueAsDate = new Date();
        }

        function closeExpenseModal() {
            document.getElementById('expenseModal').classList.remove('show');
        }

        function toggleRecurringOptions() {
            const recurringDiv = document.getElementById('recurringOptions');
            if (document.getElementById('recurring').checked) {
                recurringDiv.style.display = 'block';
            } else {
                recurringDiv.style.display = 'none';
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('expenseModal');
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        }

        // Set today's date by default
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expenseDate').value = today;
        });
    </script>
</body>

</html>
