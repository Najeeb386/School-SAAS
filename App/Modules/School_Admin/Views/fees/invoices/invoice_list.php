<?php
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    die('Unauthorized');
}

$db = \Database::connect();

// Get filter values
$filter_month = $_GET['month'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query
$query = 'SELECT i.*, s.first_name, s.last_name, s.admission_no FROM schoo_fee_invoices i
          LEFT JOIN school_students s ON i.student_id = s.id
          WHERE i.school_id = :sid';

$params = [':sid' => $school_id];

if (!empty($filter_month)) {
    $query .= ' AND i.billing_month = :month';
    $params[':month'] = $filter_month;
}

if (!empty($filter_status)) {
    $query .= ' AND i.status = :status';
    $params[':status'] = $filter_status;
}

$query .= ' ORDER BY i.created_at DESC';

$stmt = $db->prepare($query);
$stmt->execute($params);
$invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// Get unique months for filter
$stmtMonths = $db->prepare('
    SELECT DISTINCT billing_month FROM schoo_fee_invoices
    WHERE school_id = :sid AND billing_month IS NOT NULL
    ORDER BY billing_month DESC
');
$stmtMonths->execute([':sid' => $school_id]);
$months = $stmtMonths->fetchAll(\PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fee Invoices</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<style>
body { background: #f5f7fb; font-family: Inter, system-ui, -apple-system; }
.card { border: none; border-radius: 12px; box-shadow: 0 8px 22px rgba(20,30,70,.06); }
.page-title { font-weight: 700; }
.badge-pending { background-color: #ffc107; }
.badge-paid { background-color: #28a745; }
.badge-overdue { background-color: #dc3545; }
.table td { vertical-align: middle; }
.invoice-no { font-family: monospace; font-weight: 600; }
</style>

</head>

<body>

<div class="container-fluid py-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="page-title mb-1">Fee Invoices</h3>
      <div class="small text-muted">View and manage generated fee invoices</div>
    </div>
    <div>
      <a href="fees_invoice.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Generate Invoices
      </a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row align-items-end">
        <div class="col-md-3">
          <label class="form-label">Billing Month</label>
          <select id="filterMonth" class="form-control">
            <option value="">-- All Months --</option>
            <?php foreach ($months as $m): ?>
              <option value="<?php echo htmlspecialchars($m['billing_month']); ?>"
                <?php echo $filter_month === $m['billing_month'] ? 'selected' : ''; ?>>
                <?php echo date('F Y', strtotime($m['billing_month'] . '-01')); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select id="filterStatus" class="form-control">
            <option value="">-- All Status --</option>
            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="paid" <?php echo $filter_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="overdue" <?php echo $filter_status === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
          </select>
        </div>

        <div class="col-md-3">
          <button class="btn btn-primary btn-block" onclick="applyFilters()">
            <i class="fas fa-filter"></i> Apply Filter
          </button>
        </div>

        <div class="col-md-3">
          <a href="invoice_list.php" class="btn btn-outline-secondary btn-block">
            <i class="fas fa-redo"></i> Clear Filter
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table id="invoiceTable" class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Invoice No</th>
            <th>Student</th>
            <th>Admission</th>
            <th>Month</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($invoices)): ?>
            <?php foreach ($invoices as $inv): ?>
              <tr>
                <td><span class="invoice-no"><?php echo htmlspecialchars($inv['invoice_no']); ?></span></td>
                <td>
                  <strong><?php echo htmlspecialchars(($inv['first_name'] ?? '') . ' ' . ($inv['last_name'] ?? '')); ?></strong>
                </td>
                <td><?php echo htmlspecialchars($inv['admission_no'] ?? '—'); ?></td>
                <td><?php echo date('M Y', strtotime($inv['billing_month'] . '-01')); ?></td>
                <td>
                  <strong><?php echo number_format($inv['total_amount'], 2); ?></strong>
                </td>
                <td>
                  <?php
                    $status_class = 'badge-secondary';
                    if ($inv['status'] === 'pending') $status_class = 'badge-pending';
                    elseif ($inv['status'] === 'paid') $status_class = 'badge-paid';
                    elseif ($inv['status'] === 'overdue') $status_class = 'badge-overdue';
                  ?>
                  <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($inv['status']); ?></span>
                </td>
                <td>
                  <?php
                    $due = new DateTime($inv['due_date']);
                    $now = new DateTime();
                    $is_overdue = ($now > $due && $inv['status'] !== 'paid');
                    echo '<small>' . $due->format('d M Y');
                    if ($is_overdue) echo ' <span class="badge badge-danger">OVERDUE</span>';
                    echo '</small>';
                  ?>
                </td>
                <td>
                  <button class="btn btn-sm btn-info" onclick="viewInvoice(<?php echo intval($inv['id']); ?>)">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <div class="btn-group btn-group-sm ml-1" role="group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-label="More">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item" href="#" onclick="markAsPaid(<?php echo intval($inv['id']); ?>)">
                        <i class="fas fa-check text-success"></i> Mark Paid
                      </a>
                      <a class="dropdown-item" href="#" onclick="deleteInvoice(<?php echo intval($inv['id']); ?>)">
                        <i class="fas fa-trash text-danger"></i> Delete
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No invoices found
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- View Invoice Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">Invoice Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function applyFilters() {
  const month = document.getElementById('filterMonth').value;
  const status = document.getElementById('filterStatus').value;
  
  let url = 'invoice_list.php?';
  if (month) url += 'month=' + encodeURIComponent(month) + '&';
  if (status) url += 'status=' + encodeURIComponent(status);
  
  window.location.href = url;
}

function viewInvoice(invoiceId) {
  fetch('invoice_detail.php?id=' + invoiceId)
    .then(r => r.text())
    .then(html => {
      document.getElementById('viewContent').innerHTML = html;
      jQuery('#viewModal').modal('show');
    })
    .catch(e => alert('Error: ' + e.message));
}

function markAsPaid(invoiceId) {
  if (!confirm('Mark this invoice as paid?')) return;
  
  fetch('invoice_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=mark_paid&id=' + invoiceId
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Invoice marked as paid');
      location.reload();
    } else {
      alert('Error: ' + res.message);
    }
  })
  .catch(e => alert('Error: ' + e.message));
}

function deleteInvoice(invoiceId) {
  if (!confirm('Delete this invoice? This action cannot be undone.')) return;
  
  fetch('invoice_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=delete&id=' + invoiceId
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Invoice deleted');
      location.reload();
    } else {
      alert('Error: ' + res.message);
    }
  })
  .catch(e => alert('Error: ' + e.message));
}

document.addEventListener('DOMContentLoaded', function(){
  $('#invoiceTable').DataTable({
    pageLength: 25,
    order: [[0, 'desc']]
  });
});
</script>

</body>
</html>
