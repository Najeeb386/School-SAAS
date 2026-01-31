<?php
/**
 * School Admin - Expenses Management
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../../../../autoloader.php';

$db = $DB_con;

use App\Modules\School_Admin\Controllers\ExpenseController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$session_id = isset($_SESSION['current_session_id']) ? intval($_SESSION['current_session_id']) : 0;

// If session_id is not set, fetch the current active session from database
if (!$session_id && $school_id) {
    $stmt = $db->prepare("SELECT id FROM school_sessions WHERE school_id = ? ORDER BY start_date DESC LIMIT 1");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $session_id = $result['id'] ?? 0;
}

// Initialize controller
$controller = new ExpenseController($db);

// Get expenses and categories
$expenses = $controller->list($school_id, $session_id);
$categories = $controller->getCategories($school_id);
$summary = $controller->getSummary($school_id, $session_id);
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
        #customDateRange { display: flex !important; }

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

        @media print {
            body { background: white; }
            .stat-row { display: flex; gap: 15px; margin-bottom: 25px; page-break-inside: avoid; }
            .stat-card { flex: 1; padding: 15px; }
            .stat-card h6, .stat-card .amount { font-weight: bold; }
            .controls, .modal, .close-btn, .btn-outline { display: none !important; }
            table { width: 100%; margin-top: 20px; page-break-inside: avoid; }
            table thead { background: #007bff; color: white; }
            table th, table td { font-weight: bold; }
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
                <div class="amount">₨<?php echo number_format($summary['total'] ?? 0, 2); ?></div>
            </div>
            <div class="stat-card paid">
                <h6>Approved Expenses</h6>
                <div class="amount">₨<?php echo number_format($summary['approved_amount'] ?? 0, 2); ?></div>
            </div>
            <div class="stat-card pending">
                <h6>Pending Expenses</h6>
                <div class="amount">₨<?php echo number_format($summary['pending_amount'] ?? 0, 2); ?></div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="controls">
            <button id="btnAddExpense" class="btn btn-primary">+ Add Expense</button>
            <button class="btn btn-outline" onclick="exportToExcel()">📊 Export Excel</button>
            <button class="btn btn-outline" onclick="window.print()">🖨️ Print</button>
            <select id="dateFilter" onchange="filterExpenses()">
                <option value="">All Dates</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
                <option value="custom">Custom Date Range</option>
            </select>
            <div id="customDateRange" style="display:none; gap: 10px;">
                <input type="date" id="dateFrom" placeholder="From Date" onchange="filterExpenses()">
                <input type="date" id="dateTo" placeholder="To Date" onchange="filterExpenses()">
            </div>
            <select id="categoryFilter" onchange="filterExpenses()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter" onchange="filterExpenses()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
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
                    <?php if (!empty($expenses)): ?>
                        <?php foreach ($expenses as $i => $expense): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?></td>
                                <td><?php echo htmlspecialchars($expense['title']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '', $expense['category_name'] ?? 'misc')); ?>"><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></span></td>
                                <td><?php echo htmlspecialchars($expense['vendor_name'] ?? '-'); ?></td>
                                <td>₨<?php echo number_format($expense['amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $status = $expense['status'] ?? 'pending';
                                    $badgeClass = 'badge-' . $status;
                                    echo '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-primary" onclick="editExpense(<?php echo $expense['id']; ?>)">Edit</button>
                                    <button class="btn btn-outline" onclick="deleteExpense(<?php echo $expense['id']; ?>)" style="color: #d9534f;">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No expenses found. Click "Add Expense" to create one.
                            </td>
                        </tr>
                    <?php endif; ?>
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
            <form id="expenseForm">
                <input type="hidden" id="expenseId" name="id" value="">
                <input type="hidden" name="action" value="create">

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
                        <label>Amount (₨) *</label>
                        <input type="number" id="amount" name="amount" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="expense_category_id" name="expense_category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Or Create New Category</label>
                        <input type="text" id="newCategory" name="new_category" placeholder="Enter new category name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select id="payment_method" name="payment_method">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="online">Online</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="status" name="status">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" id="vendor_name" name="vendor_name" placeholder="e.g., AWS, Microsoft, etc.">
                </div>

                <div class="form-group">
                    <label>Invoice Number</label>
                    <input type="text" id="invoice_no" name="invoice_no" placeholder="Invoice reference">
                </div>

                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" id="payment_date" name="payment_date">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="description" name="description" placeholder="Additional details..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                    <button type="button" class="btn btn-outline" onclick="closeExpenseModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Store expenses data for filtering
        let allExpenses = <?php echo json_encode($expenses); ?>;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expenseDate').value = today;
            document.getElementById('payment_date').value = today;

            // Form submission
            document.getElementById('expenseForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitExpenseForm();
            });

            // Category dropdown - clear new category field when selecting from dropdown
            document.getElementById('expense_category_id').addEventListener('change', function() {
                if (this.value) {
                    document.getElementById('newCategory').value = '';
                }
            });

            // New category field - clear dropdown when typing
            document.getElementById('newCategory').addEventListener('input', function() {
                if (this.value.trim()) {
                    document.getElementById('expense_category_id').value = '';
                }
            });

            // Filter listeners
            document.getElementById('categoryFilter').addEventListener('change', filterExpenses);
            document.getElementById('statusFilter').addEventListener('change', filterExpenses);
            document.getElementById('dateFilter').addEventListener('change', filterExpenses);
            document.getElementById('dateFrom')?.addEventListener('change', filterExpenses);
            document.getElementById('dateTo')?.addEventListener('change', filterExpenses);
            document.getElementById('searchInput').addEventListener('keyup', filterExpenses);
        });

        // Open/Close Modal
        document.getElementById('btnAddExpense').addEventListener('click', openExpenseModal);

        function openExpenseModal() {
            document.getElementById('expenseForm').reset();
            document.getElementById('expenseId').value = '';
            document.querySelector('.modal-header h5').textContent = 'Add New Expense';
            document.querySelector('input[name="action"]').value = 'create';
            document.getElementById('expenseModal').classList.add('show');
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expenseDate').value = today;
            document.getElementById('payment_date').value = today;
        }

        function closeExpenseModal() {
            document.getElementById('expenseModal').classList.remove('show');
        }

        function editExpense(id) {
            const expense = allExpenses.find(e => e.id === id);
            if (!expense) {
                alert('Expense not found');
                return;
            }

            document.getElementById('expenseId').value = id;
            document.getElementById('title').value = expense.title;
            document.getElementById('expenseDate').value = expense.expense_date;
            document.getElementById('amount').value = expense.amount;
            document.getElementById('expense_category_id').value = expense.expense_category_id;
            document.getElementById('vendor_name').value = expense.vendor_name || '';
            document.getElementById('invoice_no').value = expense.invoice_no || '';
            document.getElementById('payment_date').value = expense.payment_date || '';
            document.getElementById('payment_method').value = expense.payment_method || 'cash';
            document.getElementById('status').value = expense.status || 'pending';
            document.getElementById('description').value = expense.description || '';
            
            document.querySelector('.modal-header h5').textContent = 'Edit Expense';
            document.querySelector('input[name="action"]').value = 'update';
            document.getElementById('expenseModal').classList.add('show');
        }

        function deleteExpense(id) {
            if (!confirm('Are you sure you want to delete this expense?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('save_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Expense deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function submitExpenseForm() {
            const categoryId = document.getElementById('expense_category_id').value;
            const newCategory = document.getElementById('newCategory').value.trim();

            // Check if either category selection or new category is filled
            if (!categoryId && !newCategory) {
                alert('Please select a category or enter a new one');
                return;
            }

            // If new category is entered, create it first
            if (newCategory && !categoryId) {
                createNewCategoryAndSaveExpense(newCategory);
            } else {
                saveExpenseDirectly();
            }
        }

        function createNewCategoryAndSaveExpense(categoryName) {
            const formData = new FormData();
            formData.append('action', 'create_category');
            formData.append('name', categoryName);

            fetch('save_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.id) {
                    // Create a new FormData from the form and manually set the category ID
                    const form = document.getElementById('expenseForm');
                    const expenseFormData = new FormData(form);
                    
                    // Explicitly set the category ID to the newly created category
                    expenseFormData.set('expense_category_id', data.id);
                    
                    // Clear the new category input field
                    document.getElementById('newCategory').value = '';
                    
                    // Now submit the expense with the new category
                    submitExpenseWithFormData(expenseFormData);
                } else {
                    alert('Error creating category: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the category');
            });
        }

        function saveExpenseDirectly() {
            const form = document.getElementById('expenseForm');
            const formData = new FormData(form);
            
            // Ensure expense_category_id is in the FormData
            const categoryId = document.getElementById('expense_category_id').value;
            if (categoryId) {
                formData.set('expense_category_id', categoryId);
            }
            
            submitExpenseWithFormData(formData);
        }

        function submitExpenseWithFormData(formData) {
            fetch('save_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Expense saved successfully');
                    closeExpenseModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the expense');
            });
        }

        function filterExpenses() {
            const categoryId = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            const dateFilter = document.getElementById('dateFilter').value;

            // If no filters are applied, don't update stats (keep the PHP-calculated ones)
            const hasFilters = categoryId || status || search || (dateFilter && dateFilter !== '');

            let filtered = allExpenses;

            // Date filtering
            if (dateFilter && dateFilter !== '') {
                filtered = filtered.filter(e => {
                    // Parse the expense date properly
                    const [year, month, day] = e.expense_date.split('-').map(Number);
                    const expenseDate = new Date(year, month - 1, day);
                    
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (dateFilter === 'today') {
                        return expenseDate.toDateString() === today.toDateString();
                    } 
                    else if (dateFilter === 'week') {
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);
                        weekEnd.setHours(23, 59, 59);
                        return expenseDate >= weekStart && expenseDate <= weekEnd;
                    } 
                    else if (dateFilter === 'month') {
                        return expenseDate.getMonth() === today.getMonth() && 
                               expenseDate.getFullYear() === today.getFullYear();
                    } 
                    else if (dateFilter === 'year') {
                        return expenseDate.getFullYear() === today.getFullYear();
                    } 
                    else if (dateFilter === 'custom') {
                        const dateFromVal = document.getElementById('dateFrom').value;
                        const dateToVal = document.getElementById('dateTo').value;
                        if (dateFromVal && dateToVal) {
                            const [fromYear, fromMonth, fromDay] = dateFromVal.split('-').map(Number);
                            const [toYear, toMonth, toDay] = dateToVal.split('-').map(Number);
                            const fromDate = new Date(fromYear, fromMonth - 1, fromDay);
                            const toDate = new Date(toYear, toMonth - 1, toDay);
                            toDate.setHours(23, 59, 59);
                            return expenseDate >= fromDate && expenseDate <= toDate;
                        }
                        return true;
                    }
                    return true;
                });
            }

            if (categoryId) {
                filtered = filtered.filter(e => e.expense_category_id == categoryId);
            }
            if (status) {
                filtered = filtered.filter(e => e.status === status);
            }
            if (search) {
                filtered = filtered.filter(e => 
                    e.title.toLowerCase().includes(search) ||
                    (e.vendor_name && e.vendor_name.toLowerCase().includes(search)) ||
                    (e.category_name && e.category_name.toLowerCase().includes(search))
                );
            }

            displayFilteredExpenses(filtered);
            
            // Only update statistics if filters are actually applied
            if (hasFilters) {
                updateStatistics(filtered);
            }
        }

        function updateStatistics(expenses) {
            let total = 0;
            let approved = 0;
            let pending = 0;

            expenses.forEach(e => {
                const amount = parseFloat(e.amount) || 0;
                total += amount;
                if (e.status === 'approved') {
                    approved += amount;
                } else if (e.status === 'pending') {
                    pending += amount;
                }
            });

            document.querySelector('.stat-card.total .amount').textContent = '₨' + total.toFixed(2);
            document.querySelector('.stat-card.paid .amount').textContent = '₨' + approved.toFixed(2);
            document.querySelector('.stat-card.pending .amount').textContent = '₨' + pending.toFixed(2);
        }

        // Show/hide custom date range
        document.getElementById('dateFilter')?.addEventListener('change', function() {
            const customRange = document.getElementById('customDateRange');
            if (this.value === 'custom') {
                customRange.style.display = 'flex';
            } else {
                customRange.style.display = 'none';
                filterExpenses();
            }
        });

        function displayFilteredExpenses(expenses) {
            const tbody = document.querySelector('#expensesTable tbody');
            
            if (expenses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No expenses found</td></tr>';
                return;
            }

            tbody.innerHTML = expenses.map((e, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${new Date(e.expense_date).toLocaleDateString('en-PK', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                    <td>${e.title}</td>
                    <td><span class="badge badge-${(e.category_name || 'misc').toLowerCase().replace(/\s+/g, '')}">${e.category_name || 'Uncategorized'}</span></td>
                    <td>${e.vendor_name || '-'}</td>
                    <td>₨${parseFloat(e.amount).toFixed(2)}</td>
                    <td><span class="badge badge-${e.status}">${e.status.charAt(0).toUpperCase() + e.status.slice(1)}</span></td>
                    <td>
                        <button class="btn btn-primary" onclick="editExpense(${e.id})">Edit</button>
                        <button class="btn btn-outline" onclick="deleteExpense(${e.id})" style="color: #d9534f;">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function exportToExcel() {
            // Get filtered data from table
            const table = document.getElementById('expensesTable');
            let html = '<table border="1" style="border-collapse: collapse; width: 100%;">';
            
            // Add statistics
            html += '<tr style="background-color: #007bff; color: white;">';
            html += '<td colspan="8" style="padding: 10px; font-weight: bold;">EXPENSE SUMMARY</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Total Expenses: ' + document.querySelector('.stat-card.total .amount').textContent + '</td>';
            html += '<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Approved: ' + document.querySelector('.stat-card.paid .amount').textContent + '</td>';
            html += '<td colspan="6" style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Pending: ' + document.querySelector('.stat-card.pending .amount').textContent + '</td>';
            html += '</tr>';
            
            // Add header
            html += '<tr style="background-color: #007bff; color: white;">';
            table.querySelectorAll('thead th').forEach((th, idx) => {
                if (idx < 7) { // Exclude Action column
                    html += '<th style="padding: 10px; text-align: left; font-weight: bold;">' + th.textContent + '</th>';
                }
            });
            html += '</tr>';
            
            // Add rows
            html += '<tbody>';
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.cells.length === 8) {
                    html += '<tr>';
                    for (let i = 0; i < 7; i++) {
                        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + row.cells[i].textContent.trim() + '</td>';
                    }
                    html += '</tr>';
                }
            });
            html += '</tbody></table>';

            // Create blob and download
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'expenses_' + new Date().toISOString().split('T')[0] + '.xls';
            link.click();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('expenseModal');
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        }
    </script>
</body>

</html>
