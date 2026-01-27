<?php
/**
 * Billing History view for School Admin
 * Displays `saas_billing_cycles` for the current school only
 */
require_once __DIR__ . '/../../../../Config/auth_check.php';
require_once __DIR__ . '/../../../../../autoloader.php';

use App\Modules\School_Admin\Models\SchoolModel;

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    die('School ID not found in session');
}

$model = new SchoolModel();
$cycles = $model->getBillingCycles($school_id);

$printMode = isset($_GET['print']) && $_GET['print'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Billing History</title>
    <?php if (!$printMode): ?>
    <link rel="stylesheet" href="../../../../../public/assets/css/vendors.css">
    <link rel="stylesheet" href="../../../../../public/assets/css/style.css">
    <?php endif; ?>
    <style>
        body { font-family: Roboto, Arial, sans-serif; }
        .billing-table { width: 100%; border-collapse: collapse; }
        .billing-table th, .billing-table td { padding: 10px; border: 1px solid #eaeaea; }
        .actions { white-space: nowrap; }
        <?php if ($printMode): ?>
        /* print-only minimal styles */
        body { margin: 20px; }
        .no-print { display: none !important; }
        <?php endif; ?>
    </style>
</head>
<body>

<?php if (!$printMode): ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3>Billing History</h3>
            <p class="text-muted">Showing billing cycles for your school only.</p>
            <div class="mb-3">
                <a href="?print=1" target="_blank" class="btn btn-outline-primary">Open Print View</a>
                <button class="btn btn-primary" id="printBtn">Print / Save as PDF</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid">
    <table class="billing-table">
        <thead>
            <tr>
                <th>Billing ID</th>
                <th>Period Start</th>
                <th>Period End</th>
                <th>Due Date</th>
                <th>Total Amount</th>
                <th>Discount</th>
                <th>Paid Amount</th>
                <th>Status</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cycles)): ?>
                <tr><td colspan="9">No billing records found.</td></tr>
            <?php else: ?>
                <?php foreach ($cycles as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['billing_id']); ?></td>
                        <td><?php echo htmlspecialchars($c['period_start']); ?></td>
                        <td><?php echo htmlspecialchars($c['period_end']); ?></td>
                        <td><?php echo htmlspecialchars($c['due_date']); ?></td>
                        <td><?php echo number_format((float)$c['total_amount'],2); ?></td>
                        <td><?php echo number_format((float)$c['discounted_amount'],2); ?></td>
                        <td><?php echo number_format((float)$c['paid_amount'],2); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($c['status'])); ?></td>
                        <td class="actions no-print">
                            <button class="btn btn-sm btn-outline-secondary" onclick="openDetails(<?php echo (int)$c['billing_id']; ?>)">View</button>
                            <a href="?print=1&billing_id=<?php echo (int)$c['billing_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">Print</a>
                        </td>
                    </tr>
                    <tr id="details-<?php echo (int)$c['billing_id']; ?>" style="display:none;">
                        <td colspan="9">
                            <?php
                                $payments = $model->getPaymentsByBilling($c['billing_id']);
                            ?>
                            <strong>Payments</strong>
                            <?php if (empty($payments)): ?>
                                <p>No payments recorded.</p>
                            <?php else: ?>
                                <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                                    <thead>
                                        <tr>
                                            <th style="padding:6px; border:1px solid #ddd;">Payment ID</th>
                                            <th style="padding:6px; border:1px solid #ddd;">Date</th>
                                            <th style="padding:6px; border:1px solid #ddd;">Paid Amount</th>
                                            <th style="padding:6px; border:1px solid #ddd;">Method</th>
                                            <th style="padding:6px; border:1px solid #ddd;">Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
                                                <td style="padding:6px; border:1px solid #ddd;"><?php echo htmlspecialchars($p['payment_id']); ?></td>
                                                <td style="padding:6px; border:1px solid #ddd;"><?php echo htmlspecialchars($p['payment_date']); ?></td>
                                                <td style="padding:6px; border:1px solid #ddd;"><?php echo number_format((float)$p['paid_amount'],2); ?></td>
                                                <td style="padding:6px; border:1px solid #ddd;"><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                                <td style="padding:6px; border:1px solid #ddd;"><?php echo htmlspecialchars($p['reference_no']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!$printMode): ?>
<script>
    function openDetails(billingId) {
        const el = document.getElementById('details-' + billingId);
        if (!el) return;
        el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
    }

    document.getElementById('printBtn').addEventListener('click', function() {
        const w = window.open('?print=1', '_blank');
        // allow new window to load then trigger print
        w.onload = function() { w.print(); };
    });
</script>
<?php else: ?>
<script>
    // If opened in print mode auto print
    window.onload = function() {
        window.print();
    };
</script>
<?php endif; ?>

</body>
</html>
