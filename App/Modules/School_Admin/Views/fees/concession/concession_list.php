<?php
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Models/ConcessionModel.php';
    require_once __DIR__ . '/../../../Controllers/ConcessionController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\ConcessionController($db);
    
    // Get filter parameters
    $search = trim($_GET['search'] ?? '');
    $filterMonth = trim($_GET['filter_month'] ?? ''); // YYYY-MM format
    $filterType = trim($_GET['filter_type'] ?? ''); // active, expiring, expired, all
    $filterEndMonth = trim($_GET['filter_end_month'] ?? ''); // YYYY-MM format

    // Build filters array for model
    $filters = [];
    
    // Get all concessions for the school
    $allConcessions = $controller->listConcessions($school_id);
    
    // Apply filters
    $filteredConcessions = [];
    $nowDate = date('Y-m-d');
    $nowMonth = date('Y-m');
    
    foreach ($allConcessions as $c) {
        $matches = true;
        
        // Search filter
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $name = strtolower(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
            $admNo = strtolower($c['admission_no'] ?? '');
            if (strpos($name, $searchLower) === false && strpos($admNo, $searchLower) === false) {
                $matches = false;
            }
        }
        
        // Month filter (start month)
        if (!empty($filterMonth) && $matches) {
            $startMonth = substr($c['start_month'] ?? '', 0, 7);
            if ($startMonth !== $filterMonth) {
                $matches = false;
            }
        }
        
        // End month filter
        if (!empty($filterEndMonth) && $matches) {
            $endMonth = substr($c['end_month'] ?? '', 0, 7);
            if ($endMonth !== $filterEndMonth) {
                $matches = false;
            }
        }
        
        // Status filter (active, expiring, expired, all)
        if (!empty($filterType) && $matches) {
            $endRaw = $c['end_month'] ?? null;
            $endMonthNorm = null;
            if (!empty($endRaw) && $endRaw !== '0000-00-00') {
                $endMonthNorm = substr($endRaw, 0, 7);
            }
            
            if ($filterType === 'active' && ($c['status'] ?? 0) != 1) {
                $matches = false;
            } elseif ($filterType === 'expiring' && (($c['status'] ?? 0) != 1 || $endMonthNorm !== $nowMonth)) {
                $matches = false;
            } elseif ($filterType === 'expired' && (!empty($endRaw) && $endRaw < $nowDate)) {
                $matches = false;
            }
        }
        
        if ($matches) {
            $filteredConcessions[] = $c;
        }
    }
    
    $totalCount = count($filteredConcessions);

} catch (Exception $e) {
    error_log('Error fetching concessions: ' . $e->getMessage());
    $filteredConcessions = [];
    $totalCount = 0;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Concessions List</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>

<style>
body{
  background:#f5f7fb;
  font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto;
}
.page-title{font-weight:700;}
.card{
  border:none;
  border-radius:12px;
  box-shadow:0 8px 22px rgba(20,30,70,.06);
}
.badge-scholarship{background:#e0f2fe;color:#0369a1;}
.badge-discount{background:#ecfeff;color:#0891b2;}
.badge-concession{background:#fef3c7;color:#92400e;}
.small-muted{font-size:.8rem;color:#6c757d;}
.filter-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 12px rgba(20,30,70,.06);margin-bottom:20px;}
.filter-label{font-size:.85rem;font-weight:600;color:#495057;display:block;margin-bottom:6px;}
.action-btn{font-size:.75rem;padding:4px 10px;}
</style>
</head>

<body>

<div class="container-fluid py-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="page-title mb-1">Concessions List</h3>
      <div class="small-muted">Manage and monitor all fee concessions — extend or cancel as needed</div>
    </div>
    <div>
      <a href="concession.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="filter-card">
    <form method="GET" class="form-inline flex-wrap gap-3">
      <div class="form-group mr-3">
        <label class="filter-label mb-0">Search Student</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or admission" value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <div class="form-group mr-3">
        <label class="filter-label mb-0">Start Month</label>
        <input type="month" name="filter_month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterMonth); ?>">
      </div>

      <div class="form-group mr-3">
        <label class="filter-label mb-0">End Month</label>
        <input type="month" name="filter_end_month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterEndMonth); ?>">
      </div>

      <div class="form-group mr-3">
        <label class="filter-label mb-0">Status</label>
        <select name="filter_type" class="form-control form-control-sm">
          <option value="">All</option>
          <option value="active" <?php echo $filterType === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="expiring" <?php echo $filterType === 'expiring' ? 'selected' : ''; ?>>Expiring This Month</option>
          <option value="expired" <?php echo $filterType === 'expired' ? 'selected' : ''; ?>>Expired</option>
        </select>
      </div>

      <div class="form-group">
        <label class="filter-label mb-0">&nbsp;</label>
        <button type="submit" class="btn btn-sm btn-outline-primary">Apply Filters</button>
        <a href="concession_list.php" class="btn btn-sm btn-outline-secondary ml-2">Clear</a>
      </div>
    </form>
  </div>

  <!-- RESULTS COUNT -->
  <div class="alert alert-info mb-3">
    <i class="fas fa-info-circle"></i> Showing <strong><?php echo $totalCount; ?></strong> concession(s)
  </div>

  <!-- TABLE -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Student</th>
              <th>Type</th>
              <th>Value</th>
              <th>Period</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($filteredConcessions)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No concessions found matching filters.</td></tr>
            <?php else: ?>
                <?php foreach ($filteredConcessions as $c): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')); ?></strong><br>
                            <span class="small-muted"><?php echo htmlspecialchars($c['admission_no'] ?? ''); ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo htmlspecialchars(strtolower($c['type'] ?? 'discount')); ?>">
                                <?php echo htmlspecialchars(ucfirst($c['type'] ?? 'discount')); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            if (($c['value_type'] ?? '') === 'percentage') {
                                echo htmlspecialchars($c['value'] ?? '0') . '%';
                            } else {
                                echo '₨ ' . number_format($c['value'] ?? 0, 2);
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $start = '';
                            $end = '—';
                            if (!empty($c['start_month']) && $c['start_month'] !== '0000-00-00') {
                                $start = date('M Y', strtotime($c['start_month']));
                            }
                            if (!empty($c['end_month']) && $c['end_month'] !== '0000-00-00') {
                                $end = date('M Y', strtotime($c['end_month']));
                            }
                            echo htmlspecialchars($start) . ' → ' . htmlspecialchars($end);
                            ?>
                        </td>
                        <td>
                            <?php
                            $nowDate = date('Y-m-d');
                            $nowMonth = date('Y-m');
                            $status = $c['status'] ?? 0;
                            $endMonth = $c['end_month'] ?? null;
                            
                            if ($status == 0) {
                                // Cancelled
                                echo '<span class="badge badge-secondary">Cancelled</span>';
                            } elseif (empty($endMonth) || $endMonth === '0000-00-00') {
                                // No end date set, show as Active
                                echo '<span class="badge badge-success">Active</span>';
                            } elseif ($endMonth < $nowDate) {
                                // End date has passed, show as Expired
                                echo '<span class="badge badge-danger">Expired</span>';
                            } else {
                                // End date is in future, check if expiring this month
                                $endMonthNorm = substr($endMonth, 0, 7);
                                if ($endMonthNorm === $nowMonth) {
                                    echo '<span class="badge badge-warning">Expiring This Month</span>';
                                } else {
                                    echo '<span class="badge badge-success">Active</span>';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (($c['status'] ?? 0) == 1): ?>
                                <button class="btn btn-sm btn-outline-success action-btn btn-extend" data-id="<?php echo intval($c['id'] ?? 0); ?>" data-current-end="<?php echo htmlspecialchars($c['end_month'] ?? ''); ?>">
                                    <i class="fas fa-calendar-plus"></i> Extend
                                </button>
                                <button class="btn btn-sm btn-outline-danger action-btn btn-cancel" data-id="<?php echo intval($c['id'] ?? 0); ?>">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            <?php else: ?>
                                <span class="small-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Extend Concession Modal -->
<div class="modal fade" id="extendModal" tabindex="-1" role="dialog" aria-labelledby="extendModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="extendModalLabel">
          <i class="fas fa-calendar-plus mr-2"></i>Extend Concession
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="extendCurrentEnd" class="font-weight-bold">Current End Date</label>
          <input type="text" id="extendCurrentEnd" class="form-control" readonly style="background-color:#f0f0f0;">
        </div>
        <div class="form-group">
          <label for="extendNewEnd" class="font-weight-bold">New End Date <span class="text-danger">*</span></label>
          <input type="month" id="extendNewEnd" class="form-control" required>
          <small class="form-text text-muted">Select the new month for when this concession should end</small>
        </div>
        <div id="extendError" class="alert alert-danger" role="alert" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnConfirmExtend">
          <i class="fas fa-check mr-2"></i>Extend
        </button>
      </div>
    </div>
  </div>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function(){
  let extendingId = null;
  
  // Extend button: show professional modal
  document.querySelectorAll('.btn-extend').forEach(btn => {
    btn.addEventListener('click', function(){
      extendingId = this.getAttribute('data-id');
      const currentEnd = this.getAttribute('data-current-end');
      
      // Populate and show modal
      document.getElementById('extendCurrentEnd').value = currentEnd ? currentEnd.substring(0, 7) : 'Not set';
      document.getElementById('extendNewEnd').value = '';
      document.getElementById('extendError').style.display = 'none';
      document.getElementById('extendError').textContent = '';
      
      jQuery('#extendModal').modal('show');
    });
  });

  // Confirm extend button
  document.getElementById('btnConfirmExtend').addEventListener('click', function(){
    const newEnd = document.getElementById('extendNewEnd').value;
    const errorDiv = document.getElementById('extendError');
    
    // Validate
    if (!newEnd) {
      errorDiv.textContent = 'Please select a new end date';
      errorDiv.style.display = 'block';
      return;
    }
    
    // Validate format
    if (!/^\d{4}-\d{2}$/.test(newEnd)) {
      errorDiv.textContent = 'Invalid month format. Please use YYYY-MM format.';
      errorDiv.style.display = 'block';
      return;
    }
    
    // Convert to DATE format (YYYY-MM-01)
    const newEndDate = newEnd + '-01';
    
    // Disable button and show loading
    const btn = this;
    btn.disabled = true;
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Extending...';
    
    // Send AJAX request
    const fd = new FormData();
    fd.set('action', 'extend');
    fd.set('id', extendingId);
    fd.set('end_month', newEndDate);
    
    fetch('concession_action.php', { method:'POST', credentials:'same-origin', body: fd })
      .then(r => {
        if (!r.ok) {
          return r.json().then(data => {
            throw new Error(data.message || ('HTTP ' + r.status));
          });
        }
        return r.json();
      })
      .then(res => {
        if (res && res.success) {
          jQuery('#extendModal').modal('hide');
          alert('✓ Concession extended successfully!');
          window.location.reload();
        } else {
          throw new Error(res.message || 'Unknown error');
        }
      })
      .catch(e => {
        console.error(e);
        errorDiv.textContent = 'Error: ' + e.message;
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = oldText;
      });
  });

  // Cancel button: update status to 0
  document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.getAttribute('data-id');
      
      if (!confirm('Are you sure you want to cancel this concession? This action cannot be undone.')) return;
      
      const fd = new FormData();
      fd.set('action', 'cancel');
      fd.set('id', id);
      
      fetch('concession_action.php', { method:'POST', credentials:'same-origin', body: fd })
        .then(r => {
          if (!r.ok) {
            return r.json().then(data => {
              throw new Error(data.message || ('HTTP ' + r.status));
            });
          }
          return r.json();
        })
        .then(res => {
          if (res && res.success) {
            alert('✓ Concession cancelled successfully!');
            window.location.reload();
          } else {
            throw new Error(res.message || 'Unknown error');
          }
        })
        .catch(e => {
          console.error(e);
          alert('✗ Failed to cancel: ' + e.message);
        });
    });
  });
});
</script>
