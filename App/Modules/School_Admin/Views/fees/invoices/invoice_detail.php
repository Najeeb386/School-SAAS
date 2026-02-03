<?php
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    die('Unauthorized');
}

$invoice_id = intval($_GET['id'] ?? 0);
if (!$invoice_id) {
    die('Invalid invoice ID');
}

$db = \Database::connect();

// Fetch invoice
$stmt = $db->prepare('
    SELECT i.*, s.first_name, s.last_name, s.admission_no, s.class_id
    FROM schoo_fee_invoices i
    LEFT JOIN school_students s ON i.student_id = s.id
    WHERE i.id = :id AND i.school_id = :sid
');
$stmt->execute([':id' => $invoice_id, ':sid' => $school_id]);
$invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$invoice) {
    die('Invoice not found');
}

// Fetch line items
$stmtItems = $db->prepare('
    SELECT * FROM schoo_fee_invoice_items WHERE invoice_id = :id
');
$stmtItems->execute([':id' => $invoice_id]);
$items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="invoice-detail">
    <div class="row mb-3">
        <div class="col-6">
            <h6 class="text-muted">Invoice No</h6>
            <p class="h6" style="font-family:monospace;"><?php echo htmlspecialchars($invoice['invoice_no']); ?></p>
        </div>
        <div class="col-6">
            <h6 class="text-muted">Status</h6>
            <p>
                <?php
                  $status_class = 'badge-secondary';
                  if ($invoice['status'] === 'pending') $status_class = 'badge-warning';
                  elseif ($invoice['status'] === 'paid') $status_class = 'badge-success';
                ?>
                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($invoice['status']); ?></span>
            </p>
        </div>
    </div>

    <hr>

    <div class="row mb-3">
        <div class="col-6">
            <h6 class="text-muted">Student</h6>
            <p class="mb-0"><strong><?php echo htmlspecialchars(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? '')); ?></strong></p>
            <p><small class="text-muted">Admission: <?php echo htmlspecialchars($invoice['admission_no'] ?? '—'); ?></small></p>
        </div>
        <div class="col-6">
            <h6 class="text-muted">Billing Period</h6>
            <p><?php echo date('F Y', strtotime($invoice['billing_month'] . '-01')); ?></p>
        </div>
    </div>

    <hr>

    <h6 class="mb-2">Fee Breakdown</h6>
    <table class="table table-sm table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Description</th>
                <th class="text-right" style="width:100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
              $total = 0;
              foreach ($items as $item):
                $total += $item['amount'];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td class="text-right"><?php echo ($item['amount'] < 0 ? '−' : '') . number_format(abs($item['amount']), 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="font-weight-bold bg-light">
                <td>Total</td>
                <td class="text-right"><?php echo number_format($invoice['total_amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <hr>

    <div class="row">
        <div class="col-6">
            <h6 class="text-muted">Due Date</h6>
            <p><?php echo date('d M Y', strtotime($invoice['due_date'])); ?></p>
        </div>
        <div class="col-6">
            <h6 class="text-muted">Created</h6>
            <p><?php echo date('d M Y H:i', strtotime($invoice['created_at'])); ?></p>
        </div>
    </div>

</div>
