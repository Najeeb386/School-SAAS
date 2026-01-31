<?php
/**
 * Payslip View
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Models/PayrunModel.php';
require_once __DIR__ . '/../../../Models/PayrunItemModel.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$payrun_item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payrun_item_id <= 0) {
    header('Location: payrun.php');
    exit;
}

$ctrl = new PayrunController($DB_con);
$payrunItemModel = new \App\Modules\School_Admin\Models\PayrunItemModel($DB_con);

// Get payslip details
$stmt = $DB_con->prepare("
    SELECT ppi.*, pr.pay_month, pr.pay_year, pr.pay_period_start, pr.pay_period_end, pr.created_at,
           e.name as employee_name, e.email as employee_email, e.phone as employee_phone, e.role_id as staff_role,
           st.name as teacher_name, st.email as teacher_email, st.phone as teacher_phone, st.role as teacher_role
    FROM school_payrun_items ppi
    LEFT JOIN school_payruns pr ON ppi.payrun_id = pr.id
    LEFT JOIN employees e ON ppi.staff_id = e.id AND ppi.staff_type = 'employee'
    LEFT JOIN school_teachers st ON ppi.staff_id = st.id AND ppi.staff_type = 'teacher'
    WHERE ppi.id = ? AND pr.school_id = ?
");
$stmt->execute([$payrun_item_id, $school_id]);
$payslip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payslip) {
    header('Location: payrun.php');
    exit;
}

// Get school info
$schoolStmt = $DB_con->prepare("SELECT * FROM schools WHERE id = ? LIMIT 1");
$schoolStmt->execute([$school_id]);
$school = $schoolStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payslip - <?php echo htmlspecialchars($payslip['employee_name']); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .app, .app-wrap, .app-container { padding-left: 0 !important; }
        .app-main { margin-left: 0 !important; width: 100% !important; }
        .container-fluid { max-width: 1000px; padding-left: 1.5rem; padding-right: 1.5rem; }
        body { overflow-x: hidden; color: #000; background: #f5f5f5; }
        
        .payslip-container {
            background: white;
            padding: 2rem;
            margin: 2rem auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 4px;
            color: #000;
        }

        .payslip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .school-info h2 {
            margin: 0;
            color: #007bff;
            font-weight: 700;
        }

        .school-info p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
            color: #555;
        }

        .payslip-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #000;
        }

        .payslip-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-block h5 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #000;
            font-size: 1rem;
        }

        .detail-block p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
            color: #333;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            display: inline-block;
            width: 140px;
        }

        .detail-value {
            color: #000;
            font-weight: 500;
        }

        .earnings-table, .deductions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .earnings-table th, .deductions-table th {
            background: #007bff;
            color: white;
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            border: 1px solid #0056b3;
        }

        .earnings-table td, .deductions-table td {
            padding: 0.8rem;
            border: 1px solid #ddd;
            color: #000;
        }

        .earnings-table tr:nth-child(even),
        .deductions-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .earnings-table tbody tr:hover,
        .deductions-table tbody tr:hover {
            background: #f0f0f0;
        }

        .amount-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        .section-title {
            background: #007bff;
            color: white;
            padding: 0.8rem 1rem;
            margin-top: 1.5rem;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
            font-weight: 600;
            font-size: 1rem;
        }

        .summary-box {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin: 2rem 0;
        }

        .summary-item {
            background: #f8f9fa;
            padding: 1rem;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .summary-item h6 {
            margin: 0 0 0.5rem 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
        }

        .summary-item .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
        }

        .payment-info {
            background: #e8f4f8;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid #17a2b8;
            margin: 1.5rem 0;
            color: #000;
        }

        .payment-info h6 {
            margin: 0 0 0.8rem 0;
            font-weight: 600;
            color: #000;
        }

        .payment-info p {
            margin: 0.3rem 0;
            font-size: 0.95rem;
            color: #333;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .container-fluid { padding-left: 1rem; padding-right: 1rem; }
            .payslip-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .payslip-details { grid-template-columns: 1fr; gap: 1rem; }
            .summary-box { grid-template-columns: 1fr; }
            .button-group { flex-direction: column; }
            .btn { width: 100%; }
        }

        @media print {
            body { background: white; }
            .payslip-container { margin: 0; padding: 1.5rem; box-shadow: none; }
            .button-group, .breadcrumb { display: none !important; }
            .payslip-container { page-break-after: always; }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="app-container">
                <div class="breadcrumb-wrapper">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../../dashboard/index.php" style="color: #007bff;">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="payrun.php" style="color: #007bff;">Payruns</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Payslip</li>
                        </ol>
                    </nav>
                </div>

                <div class="payslip-container">
                    <!-- Header -->
                    <div class="payslip-header">
                        <div class="school-info">
                            <h2><?php echo htmlspecialchars($school['name'] ?? 'School Name'); ?></h2>
                            <?php if($school): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($school['email'] ?? ''); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($school['phone'] ?? ''); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right; color: #000;">
                            <div class="payslip-title">PAYSLIP</div>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                                <?php echo date('F Y', mktime(0, 0, 0, $payslip['pay_month'], 1, $payslip['pay_year'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Employee & Period Details -->
                    <div class="payslip-details">
                        <div>
                            <h5>Employee Information</h5>
                            <p>
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">
                                    <?php 
                                        if($payslip['staff_type'] == 'employee') {
                                            echo htmlspecialchars($payslip['employee_name']);
                                        } else {
                                            echo htmlspecialchars($payslip['teacher_name']);
                                        }
                                    ?>
                                </span>
                            </p>
                            <p>
                                <span class="detail-label">Staff Type:</span>
                                <span class="detail-value"><?php echo ucfirst(htmlspecialchars($payslip['staff_type'])); ?></span>
                            </p>
                            <p>
                                <span class="detail-label">Position:</span>
                                <span class="detail-value">
                                    <?php 
                                        if($payslip['staff_type'] == 'employee') {
                                            echo htmlspecialchars($payslip['staff_role'] ?? 'N/A');
                                        } else {
                                            echo htmlspecialchars($payslip['teacher_role'] ?? 'Teacher');
                                        }
                                    ?>
                                </span>
                            </p>
                            <p>
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">
                                    <?php 
                                        if($payslip['staff_type'] == 'employee') {
                                            echo htmlspecialchars($payslip['employee_email']);
                                        } else {
                                            echo htmlspecialchars($payslip['teacher_email']);
                                        }
                                    ?>
                                </span>
                            </p>
                            <p>
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value">
                                    <?php 
                                        if($payslip['staff_type'] == 'employee') {
                                            echo htmlspecialchars($payslip['employee_phone'] ?? 'N/A');
                                        } else {
                                            echo htmlspecialchars($payslip['teacher_phone'] ?? 'N/A');
                                        }
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <h5>Payrun Period</h5>
                            <p>
                                <span class="detail-label">Month:</span>
                                <span class="detail-value"><?php echo date('F', mktime(0, 0, 0, $payslip['pay_month'], 1)); ?></span>
                            </p>
                            <p>
                                <span class="detail-label">Year:</span>
                                <span class="detail-value"><?php echo $payslip['pay_year']; ?></span>
                            </p>
                            <p>
                                <span class="detail-label">Period Start:</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($payslip['pay_period_start'])); ?></span>
                            </p>
                            <p>
                                <span class="detail-label">Period End:</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($payslip['pay_period_end'])); ?></span>
                            </p>
                            <p>
                                <span class="detail-label">Payrun Date:</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($payslip['created_at'])); ?></span>
                            </p>
                        </div>
                    </div>

                    <!-- Earnings Section -->
                    <div class="section-title">EARNINGS</div>
                    <table class="earnings-table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Description</th>
                                <th class="amount-cell" style="width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Basic Salary</strong></td>
                                <td class="amount-cell"><strong><?php echo number_format($payslip['basic_salary'] ?? 0, 2); ?></strong></td>
                            </tr>
                            <?php if(($payslip['allowance'] ?? 0) > 0): ?>
                            <tr>
                                <td>Allowances</td>
                                <td class="amount-cell"><?php echo number_format($payslip['allowance'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr style="background: #e8f4f8; font-weight: 600;">
                                <td><strong>Total Earnings</strong></td>
                                <td class="amount-cell"><strong><?php echo number_format(($payslip['basic_salary'] ?? 0) + ($payslip['allowance'] ?? 0), 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Deductions Section -->
                    <div class="section-title">DEDUCTIONS</div>
                    <table class="deductions-table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Description</th>
                                <th class="amount-cell" style="width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(($payslip['deduction'] ?? 0) > 0): ?>
                            <tr>
                                <td>Deductions</td>
                                <td class="amount-cell"><?php echo number_format($payslip['deduction'], 2); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td>No Deductions</td>
                                <td class="amount-cell">0.00</td>
                            </tr>
                            <?php endif; ?>
                            <tr style="background: #ffebee; font-weight: 600;">
                                <td><strong>Total Deductions</strong></td>
                                <td class="amount-cell"><strong><?php echo number_format($payslip['deduction'] ?? 0, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div class="summary-box">
                        <div class="summary-item">
                            <h6>Gross Salary</h6>
                            <div class="value"><?php echo number_format(($payslip['basic_salary'] ?? 0) + ($payslip['allowance'] ?? 0), 2); ?></div>
                        </div>
                        <div class="summary-item" style="border-left-color: #dc3545;">
                            <h6>Total Deductions</h6>
                            <div class="value" style="color: #dc3545;"><?php echo number_format($payslip['deduction'] ?? 0, 2); ?></div>
                        </div>
                        <div class="summary-item" style="border-left-color: #28a745;">
                            <h6>Net Salary</h6>
                            <div class="value" style="color: #28a745;"><?php echo number_format($payslip['net_salary'] ?? 0, 2); ?></div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="payment-info">
                        <h6>Payment Information</h6>
                        <p>
                            <strong>Payment Status:</strong> 
                            <?php
                                $status_class = '';
                                $status_text = ucfirst($payslip['payment_status']);
                                if($payslip['payment_status'] == 'paid') $status_class = 'badge badge-success';
                                elseif($payslip['payment_status'] == 'pending') $status_class = 'badge badge-warning';
                                elseif($payslip['payment_status'] == 'cancelled') $status_class = 'badge badge-danger';
                            ?>
                            <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </p>
                        <?php if($payslip['payment_date']): ?>
                        <p><strong>Payment Date:</strong> <?php echo date('d M Y', strtotime($payslip['payment_date'])); ?></p>
                        <?php endif; ?>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payslip['payment_method'] ?? 'Bank Transfer'); ?></p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="button-group">
                        <button class="btn btn-primary" onclick="printPayslip()">
                            <i class="fa fa-print"></i> Print Payslip
                        </button>
                        <button class="btn btn-success" onclick="downloadPDF()">
                            <i class="fa fa-download"></i> Download PDF
                        </button>
                        <a href="payrun.php" class="btn btn-secondary" style="text-decoration: none;">
                            <i class="fa fa-arrow-left"></i> Back to Payruns
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../../public/assets/js/script.js"></script>
    <script>
        function printPayslip() {
            window.print();
        }

        function downloadPDF() {
            const element = document.querySelector('.payslip-container');
            const filename = 'Payslip_<?php echo htmlspecialchars($payslip['employee_name']); ?>_<?php echo $payslip['pay_month'] . '_' . $payslip['pay_year']; ?>.pdf';
            
            const options = {
                margin: 10,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };

            html2pdf().set(options).from(element).save();
        }
    </script>
</body>

</html>
