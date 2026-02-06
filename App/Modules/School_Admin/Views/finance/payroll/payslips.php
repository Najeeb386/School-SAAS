<?php
/**
 * Staff Payslips List View
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$ctrl = new PayrunController($DB_con);

// Get all employees for this school
$empStmt = $DB_con->prepare("
    SELECT id, school_id, name, email, role_id, phone FROM employees 
    WHERE school_id = ? AND status = 1
    ORDER BY name ASC
");
$empStmt->execute([$school_id]);
$employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teachers for this school
$teachStmt = $DB_con->prepare("
    SELECT id, school_id, name, email, role, phone FROM school_teachers 
    WHERE school_id = ? AND status = 1
    ORDER BY name ASC
");
$teachStmt->execute([$school_id]);
$teachers = $teachStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all latest payslips in one optimized query
$latestPayslipsStmt = $DB_con->prepare("
    SELECT ppi.staff_id, ppi.staff_type, ppi.id, ppi.net_salary, pr.pay_month, pr.pay_year
    FROM school_payrun_items ppi
    JOIN school_payruns pr ON ppi.payrun_id = pr.id
    WHERE ppi.school_id = ?
    ORDER BY ppi.staff_id ASC, ppi.staff_type ASC, pr.pay_year DESC, pr.pay_month DESC
");
$latestPayslipsStmt->execute([$school_id]);
$allPayslips = $latestPayslipsStmt->fetchAll(PDO::FETCH_ASSOC);

// Create an indexed array for quick lookup (keep only latest per staff)
$payslipMap = [];
foreach ($allPayslips as $payslip) {
    $key = $payslip['staff_type'] . '_' . $payslip['staff_id'];
    if (!isset($payslipMap[$key])) {
        $payslipMap[$key] = $payslip;
    }
}

// Merge both arrays
$staff_list = [];
foreach ($employees as $emp) {
    $staff_list[] = [
        'id' => $emp['id'],
        'type' => 'employee',
        'name' => $emp['name'],
        'designation' => $emp['role_id'] ?? 'Employee',
        'email' => $emp['email']
    ];
}
foreach ($teachers as $teacher) {
    $staff_list[] = [
        'id' => $teacher['id'],
        'type' => 'teacher',
        'name' => $teacher['name'],
        'designation' => $teacher['role'] ?? 'Teacher',
        'email' => $teacher['email']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Staff Payslips - Payroll</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #000; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h3 { color: #000; margin-bottom: 10px; font-size: 28px; }
        .breadcrumb { background: transparent; padding: 0; margin-bottom: 20px; }
        .breadcrumb-item { color: #000; }
        .breadcrumb-item a { color: #007bff; text-decoration: none; }
        .breadcrumb-item a:hover { text-decoration: underline; }
        
        .controls { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .controls input, .controls select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        
        .card { background: white; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
        
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; color: #000; }
        table thead { background: #007bff; color: white; }
        table th { padding: 12px; text-align: left; font-weight: 600; }
        table td { padding: 12px; border-bottom: 1px solid #ddd; color: #000; }
        table tbody tr:hover { background: #f9f9f9; }
        
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        
        .btn { padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-outline-secondary { background: white; border: 1px solid #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background: #f8f9fa; }
        .btn-outline-info { background: white; border: 1px solid #17a2b8; color: #17a2b8; }
        .btn-outline-info:hover { background: #f8f9fa; }
        
        .btn-group { display: flex; gap: 10px; }
        .text-muted { color: #666; }
        .text-center { text-align: center; }
        
        @media (max-width: 768px) {
            .controls { flex-direction: column; }
            .controls input, .controls select { width: 100%; }
        }
        
        @media print {
            .controls, .btn-group { display: none !important; }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3>Staff Payslips</h3>
                <div class="breadcrumb">
                    <span class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></span>
                    <span> / </span>
                    <span class="breadcrumb-item"><a href="../finance.php">Finance</a></span>
                    <span> / </span>
                    <span class="breadcrumb-item">Payslips</span>
                </div>
            </div>
            <div class="btn-group">
                <button onclick="history.back()" class="btn btn-outline-secondary">← Back</button>
                <button onclick="window.print()" class="btn btn-outline-info">🖨 Print</button>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="controls">
            <input type="search" id="searchInput" placeholder="Search by name or email..." style="flex: 1; min-width: 200px;">
            <select id="typeFilter">
                <option value="">All Types</option>
                <option value="employee">Employees</option>
                <option value="teacher">Teachers</option>
            </select>
        </div>

        <!-- Staff Payslips Table -->
        <div class="card">
            <div class="table-wrapper">
                <table id="payslipsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Designation</th>
                            <th>Latest Payslip</th>
                            <th>Net Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                                        <?php
                                            $i = 1;
                                            foreach ($staff_list as $staff):
                                                $key = $staff['type'] . '_' . $staff['id'];
                                                $latest = $payslipMap[$key] ?? null;
                                        ?>
                                        <tr data-type="<?php echo $staff['type']; ?>" data-name="<?php echo strtolower($staff['name']); ?>" data-email="<?php echo strtolower($staff['email']); ?>">
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo $staff['type'] === 'employee' ? 'EMP' : 'TCH'; ?>-<?php echo str_pad($staff['id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                                            <td><span class="badge badge-info"><?php echo ucfirst($staff['type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($staff['designation']); ?></td>
                                            <td>
                                                <?php 
                                                    if ($latest) {
                                                        echo date('F Y', mktime(0, 0, 0, $latest['pay_month'], 1, $latest['pay_year']));
                                                    } else {
                                                        echo '<span class="badge badge-secondary">N/A</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($latest) {
                                                        echo '<strong>' . number_format($latest['net_salary'], 2) . '</strong>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($latest): ?>
                                                    <a href="payslip.php?id=<?php echo $latest['id']; ?>" class="btn btn-xs btn-primary">
                                                        <i class="fa fa-file-pdf-o"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($staff_list)): ?>
                                        <tr><td colspan="8" class="text-center text-muted">No staff found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
    </div>

    <script>
        // Search and Filter Functionality
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const table = document.getElementById('payslipsTable');
        const rows = table.querySelectorAll('tbody tr');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const typeValue = typeFilter.value;

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                const type = row.getAttribute('data-type');

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesType = typeValue === '' || type === typeValue;

                row.style.display = matchesSearch && matchesType ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        typeFilter.addEventListener('change', filterTable);
    </script>
</body>

</html>
