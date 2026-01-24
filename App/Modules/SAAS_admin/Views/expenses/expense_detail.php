<?php
session_start();

// Database connection and model
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Models/expense_model.php';

$expense = null;
$error = null;

// Get expense ID from URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    try {
        $expenseModel = new Expense($DB_con);
        $expense = $expenseModel->getById($_GET['id']);
        
        if (!$expense) {
            $error = 'Expense not found.';
        }
    } catch (Exception $ex) {
        $error = 'Error loading expense: ' . $ex->getMessage();
        error_log($error);
    }
} else {
    $error = 'No expense ID provided.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expense Invoice - <?= htmlspecialchars($expense['expense_id'] ?? 'Detail') ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
            padding: 0;
            margin: 0;
        }

        .page-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .invoice-container {
            width: 210mm;
            height: 297mm;
            background: white;
            padding: 12mm;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8mm;
            margin-bottom: 8mm;
        }

        .invoice-title h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
            font-weight: 700;
        }

        .invoice-title p {
            margin: 3px 0 0 0;
            color: #666;
            font-size: 13px;
            font-weight: 400;
        }

        .invoice-no {
            text-align: right;
            font-size: 12px;
        }

        .invoice-no p {
            margin: 4px 0;
            color: #333;
            line-height: 1.5;
        }

        .invoice-no strong {
            font-weight: 600;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10mm;
            margin-bottom: 8mm;
            padding-bottom: 8mm;
            border-bottom: 1px solid #ddd;
        }

        .invoice-details-col h3 {
            margin: 0 0 4mm 0;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
        }

        .invoice-details-col p {
            margin: 2mm 0;
            color: #333;
            line-height: 1.4;
            font-size: 11px;
        }

        .invoice-details-col strong {
            font-weight: 600;
        }

        .description-section {
            margin-bottom: 8mm;
            padding-bottom: 8mm;
            border-bottom: 1px solid #ddd;
        }

        .description-section h3 {
            margin: 0 0 4mm 0;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
        }

        .description-section p {
            margin: 0;
            color: #333;
            line-height: 1.4;
            font-size: 10px;
            white-space: pre-wrap;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 6mm 0;
        }

        .table th {
            background-color: #f8f9fa;
            padding: 4mm 6mm;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            color: #333;
            font-size: 10px;
        }

        .table td {
            padding: 4mm 6mm;
            border-bottom: 1px solid #dee2e6;
            color: #333;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .table-summary {
            margin-top: 6mm;
            text-align: right;
            margin-right: 0;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 80mm 30mm;
            gap: 10mm;
            margin: 3mm 0;
            padding: 3mm 0;
            font-size: 11px;
        }

        .summary-row.total {
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            font-weight: 700;
            font-size: 12px;
            padding: 6mm 0;
        }

        .summary-label {
            text-align: right;
            color: #333;
            font-weight: 600;
        }

        .summary-value {
            text-align: right;
            color: #007bff;
            font-weight: 700;
        }

        .metadata {
            margin-top: 6mm;
            padding-top: 6mm;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
        }

        .metadata p {
            margin: 2mm 0;
            line-height: 1.3;
        }

        .invoice-footer {
            margin-top: 8mm;
            padding-top: 6mm;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 8px;
        }

        .badge {
            display: inline-block;
            padding: 3mm 6mm;
            border-radius: 3px;
            font-weight: 500;
            font-size: 11px;
        }

        .badge-info {
            background-color: #e7f3ff;
            color: #0066cc;
        }

        .error-container {
            width: 210mm;
            height: 297mm;
            background: white;
            padding: 20mm;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .error-message {
            text-align: center;
            color: #d32f2f;
            font-size: 14px;
        }

        .error-message a {
            display: inline-block;
            margin-top: 10mm;
            padding: 5mm 10mm;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }

        .btn-container {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            z-index: 1000;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .page-container {
                padding: 0;
                background: white;
            }

            .invoice-container {
                width: 100%;
                height: 100%;
                box-shadow: none;
                padding: 12mm;
                margin: 0;
                page-break-after: always;
            }

            .btn-container {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>

<body>
    <?php if ($error) : ?>
        <div class="page-container">
            <div class="error-container">
                <div class="error-message">
                    <p><?= htmlspecialchars($error) ?></p>
                    <a href="expenses.php">&larr; Back to Expenses</a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <!-- Action Buttons -->
        <div class="btn-container">
            <a href="expenses.php" class="btn btn-secondary">
                &larr; Back
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                🖨 Print
            </button>
        </div>

        <!-- Invoice Container -->
        <div class="page-container">
            <div class="invoice-container">
                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="invoice-title">
                        <h1>MENTOR</h1>
                        <p>School Management System</p>
                    </div>
                    <div class="invoice-no">
                        <p><strong>Expense ID:</strong> #<?= htmlspecialchars($expense['expense_id'] ?? '') ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($expense['expense_date'] ?? '') ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-info"><?= htmlspecialchars($expense['status'] ?? 'Pending') ?></span></p>
                    </div>
                </div>

                <!-- Expense Details -->
                <div class="invoice-details">
                    <div class="invoice-details-col">
                        <h3>Expense Information</h3>
                        <p><strong>Title:</strong><br><?= htmlspecialchars($expense['title'] ?? '') ?></p>
                        <p><strong>Category:</strong><br><?= htmlspecialchars($expense['category'] ?? '') ?></p>
                        <p><strong>Vendor Name:</strong><br><?= htmlspecialchars($expense['vendor_name'] ?? 'N/A') ?></p>
                        <p><strong>Invoice Number:</strong><br><?= htmlspecialchars($expense['invoice_no'] ?? 'N/A') ?></p>
                    </div>
                    <div class="invoice-details-col">
                        <h3>Payment Information</h3>
                        <p><strong>Amount:</strong><br><span style="font-size: 16px; color: #007bff; font-weight: 700;">Rs <?= number_format((float)($expense['amount'] ?? 0), 2) ?></span></p>
                        <p><strong>Payment Method:</strong><br><?= htmlspecialchars($expense['payment_method'] ?? 'N/A') ?></p>
                        <p><strong>Recurring:</strong><br><?= ($expense['is_recurring'] == 1 ? 'Yes (' . htmlspecialchars($expense['recurring_cycle'] ?? '') . ')' : 'No') ?></p>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($expense['description'])) : ?>
                    <div class="description-section">
                        <h3>Description</h3>
                        <p><?= htmlspecialchars($expense['description']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Summary Table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($expense['title'] ?? 'Expense') ?></td>
                            <td class="text-right" style="font-weight: 600;">Rs <?= number_format((float)($expense['amount'] ?? 0), 2) ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Total Summary -->
                <div class="table-summary">
                    <div class="summary-row total">
                        <div class="summary-label">Total Amount:</div>
                        <div class="summary-value">Rs <?= number_format((float)($expense['amount'] ?? 0), 2) ?></div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="metadata">
                    <p><strong>Created By:</strong> <?= htmlspecialchars($expense['creator_name'] ?? ($expense['created_by'] ? 'User ID: ' . $expense['created_by'] : 'System')) ?></p>
                    <p><strong>Created At:</strong> <?= htmlspecialchars($expense['created_at'] ?? '') ?></p>
                    <?php if (!empty($expense['updated_at'])) : ?>
                        <p><strong>Updated At:</strong> <?= htmlspecialchars($expense['updated_at']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="invoice-footer">
                    <p>This is an automatically generated expense invoice from Mentor School Management System.</p>
                    <p>&copy; <?= date('Y') ?> Mentor. All rights reserved.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>
    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

</body>
</html>
