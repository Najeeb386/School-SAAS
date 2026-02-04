<?php
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    
    // DEBUG: Log the school_id value
    error_log("========== INVOICE PAGE DEBUG ==========");
    error_log("School ID from session: " . var_export($school_id, true));
    
    // Fetch classes - PROGRESSIVE APPROACH
    $classes = [];
    try {
        // Step 1: Try with school_id filter AND status = 1
        error_log("CLASSES STEP 1: SELECT id, class_name FROM school_classes WHERE school_id = {$school_id} AND status = 1");
        $stmtClasses = $db->prepare('SELECT id, class_name FROM school_classes WHERE school_id = :sid AND status = 1 ORDER BY class_name');
        $stmtClasses->execute([':sid' => $school_id]);
        $classes = $stmtClasses->fetchAll(\PDO::FETCH_ASSOC);
        error_log("CLASSES STEP 1 Result: " . count($classes) . " classes found");
        
        // Step 2: If empty, fetch without status filter
        if (empty($classes)) {
            error_log("CLASSES STEP 2: No active classes. Fetching ALL classes for school_id = {$school_id}");
            $stmtAllClasses = $db->prepare('SELECT id, class_name FROM school_classes WHERE school_id = :sid ORDER BY class_name');
            $stmtAllClasses->execute([':sid' => $school_id]);
            $classes = $stmtAllClasses->fetchAll(\PDO::FETCH_ASSOC);
            error_log("CLASSES STEP 2 Result: " . count($classes) . " total classes found");
        }
        
        // Step 3: Debug if still empty
        if (empty($classes)) {
            error_log("CLASSES STEP 3: No classes found. Checking table...");
            $stmtDebug = $db->prepare('SELECT * FROM school_classes WHERE school_id = :sid LIMIT 3');
            $stmtDebug->execute([':sid' => $school_id]);
            $debugClasses = $stmtDebug->fetchAll(\PDO::FETCH_ASSOC);
            error_log("CLASSES STEP 3 Sample data: " . json_encode($debugClasses));
        }
    } catch (Exception $e) {
        error_log('ERROR FETCHING CLASSES: ' . $e->getMessage());
        $classes = [];
    }
    
    // Fetch sessions - PROGRESSIVE APPROACH
    $sessions = [];
    try {
        // Step 1: Fetch ACTIVE sessions only
        error_log("SESSIONS STEP 1: SELECT id, name FROM school_sessions WHERE school_id = {$school_id} AND is_active = 1");
        $stmtActive = $db->prepare('SELECT id, name FROM school_sessions WHERE school_id = :sid AND is_active = 1 ORDER BY id DESC');
        $stmtActive->execute([':sid' => $school_id]);
        $sessions = $stmtActive->fetchAll(\PDO::FETCH_ASSOC);
        error_log("SESSIONS STEP 1 Result: " . count($sessions) . " active sessions found");
        
        // Step 2: If no active sessions, fetch all for this school
        if (empty($sessions)) {
            error_log("SESSIONS STEP 2: No active sessions. Fetching ALL sessions for school_id = {$school_id}");
            $stmtAll = $db->prepare('SELECT id, name FROM school_sessions WHERE school_id = :sid ORDER BY id DESC');
            $stmtAll->execute([':sid' => $school_id]);
            $sessions = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);
            error_log("SESSIONS STEP 2 Result: " . count($sessions) . " total sessions found");
        }
        
        // Step 3: If still empty, check what's in the table and what values is_active has
        if (empty($sessions)) {
            error_log("SESSIONS STEP 3: No sessions for this school. Checking table structure and sample data...");
            
            // Check total count in table
            $stmtCount = $db->prepare('SELECT COUNT(*) as total FROM school_sessions');
            $stmtCount->execute();
            $countResult = $stmtCount->fetch(\PDO::FETCH_ASSOC);
            error_log("SESSIONS STEP 3a: Total sessions in table: " . $countResult['total']);
            
            // Get sample record to see actual column values
            $stmtSample = $db->prepare('SELECT * FROM school_sessions LIMIT 1');
            $stmtSample->execute();
            $sample = $stmtSample->fetch(\PDO::FETCH_ASSOC);
            error_log("SESSIONS STEP 3b: Sample session: " . json_encode($sample));
            
            // Check what is_active values exist
            $stmtIsActive = $db->prepare('SELECT DISTINCT is_active FROM school_sessions');
            $stmtIsActive->execute();
            $isActiveValues = $stmtIsActive->fetchAll(\PDO::FETCH_ASSOC);
            error_log("SESSIONS STEP 3c: Distinct is_active values: " . json_encode($isActiveValues));
            
            // Check sessions for THIS school specifically
            $stmtThisSchool = $db->prepare('SELECT * FROM school_sessions WHERE school_id = :sid');
            $stmtThisSchool->execute([':sid' => $school_id]);
            $thisSchoolSessions = $stmtThisSchool->fetchAll(\PDO::FETCH_ASSOC);
            error_log("SESSIONS STEP 3d: Sessions for school_id = {$school_id}: " . json_encode($thisSchoolSessions));
        }
        
        error_log("========== FINAL RESULT: " . count($sessions) . " sessions, " . count($classes) . " classes ==========");
        
    } catch (Exception $e) {
        error_log('ERROR FETCHING SESSIONS: ' . $e->getMessage());
        error_log('Exception trace: ' . $e->getTraceAsString());
        $sessions = [];
    }
    
} catch (Exception $e) {
    error_log('Error loading invoice page: ' . $e->getMessage());
    $classes = [];
    $sessions = [];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bulk Invoice Generator</title>

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
.form-label{
  font-size:.85rem;
  font-weight:600;
  color:#495057;
}
.fee-item-check{margin-bottom:15px;padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#f9f9f9;}
.fee-item-check input{margin-top:3px;}
.small-muted{font-size:.8rem;color:#6c757d;}
</style>
</head>

<body>

<div class="container-fluid py-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="page-title mb-1">Bulk Invoice Generator</h3>
      <div class="small-muted">Generate fee invoices for students in bulk</div>
    </div>
    <div>
      <a href="invoice_list.php" class="btn btn-outline-secondary">
        <i class="fas fa-list"></i> View Invoices
      </a>
    </div>
  </div>

  <!-- DEBUG INFO (REMOVE IN PRODUCTION) -->
  <div class="alert alert-info" role="alert">
    <strong>Debug Info:</strong> School ID: <code><?php echo htmlspecialchars($school_id); ?></code> | 
    Sessions Loaded: <code><?php echo count($sessions); ?></code> | 
    Classes Loaded: <code><?php echo count($classes); ?></code>
  </div>

  <div class="row">
    <!-- LEFT: GENERATOR FORM -->
    <div class="col-lg-8 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="mb-4">Generate Invoices</h5>

          <form id="invoiceGeneratorForm">
            <!-- Session & Month -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="form-label">Session</label>
                <select id="sessionId" name="session_id" class="form-control" required>
                  <option value="">-- Select Session --</option>
                  <?php foreach ($sessions as $s): ?>
                    <option value="<?php echo intval($s['id']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label class="form-label">Billing Month</label>
                <input type="month" id="billingMonth" name="billing_month" class="form-control" required>
              </div>
            </div>

            <!-- Class Selection -->
            <div class="form-group">
              <label class="form-label">Apply To</label>
              <div>
                <div class="custom-control custom-radio mb-2">
                  <input type="radio" id="applyAll" name="apply_to" value="all" class="custom-control-input" checked>
                  <label class="custom-control-label" for="applyAll">All Classes</label>
                </div>
                <div class="custom-control custom-radio">
                  <input type="radio" id="applySpecific" name="apply_to" value="specific" class="custom-control-input">
                  <label class="custom-control-label" for="applySpecific">Specific Class</label>
                </div>
              </div>
            </div>

            <!-- Class Dropdown (hidden by default) -->
            <div class="form-group" id="classSelectWrap" style="display:none;">
              <label class="form-label">Select Class</label>
              <select id="classId" name="class_id" class="form-control">
                <option value="">-- Select Class --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo intval($c['id']); ?>"><?php echo htmlspecialchars($c['class_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Additional Fees -->
            <div class="form-group">
              <label class="form-label">Additional Fees (Optional)</label>
              <small class="form-text text-muted d-block mb-2">Select any additional fees to include in invoices</small>
              
              <!-- <div class="fee-item-check">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" id="fee_examination" name="additional_fees[]" value="examination" class="custom-control-input">
                  <label class="custom-control-label" for="fee_examination">
                    <strong>Examination Fee</strong>
                    <input type="number" name="fee_examination_amount" class="form-control form-control-sm mt-1" placeholder="Amount" style="max-width:150px;">
                  </label>
                </div>
              </div> -->

              <div class="fee-item-check">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" id="fee_vacation" name="additional_fees[]" value="vacation" class="custom-control-input">
                  <label class="custom-control-label" for="sports_fees">
                    <strong>Sports Fee</strong>
                    <input type="number" name="fee_vacation_amount" class="form-control form-control-sm mt-1" placeholder="Amount" style="max-width:150px;">
                  </label>
                </div>
              </div>

              <!-- <div class="fee-item-check">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" id="fee_advance" name="additional_fees[]" value="advance" class="custom-control-input">
                  <label class="custom-control-label" for="fee_advance">
                    <strong>Advance Payment / Other</strong>
                    <input type="number" name="fee_advance_amount" class="form-control form-control-sm mt-1" placeholder="Amount" style="max-width:150px;">
                  </label>
                </div>
              </div> -->

              <div class="fee-item-check">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" id="fee_library" name="additional_fees[]" value="library" class="custom-control-input">
                  <label class="custom-control-label" for="fee_library">
                    <strong>Library Fee</strong>
                    <input type="number" name="fee_library_amount" class="form-control form-control-sm mt-1" placeholder="Amount" style="max-width:150px;">
                  </label>
                </div>
              </div>
            </div>

            <!-- Due Date -->
            <div class="form-group">
              <label class="form-label">Due Date</label>
              <input type="date" id="dueDate" name="due_date" class="form-control" required>
            </div>

            <!-- Submit -->
            <div class="text-right mt-4">
              <button type="button" id="btnPreview" class="btn btn-outline-primary mr-2">
                <i class="fas fa-eye mr-2"></i>Preview
              </button>
              <button type="submit" id="btnGenerate" class="btn btn-success">
                <i class="fas fa-file-invoice mr-2"></i>Generate Invoices
              </button>
            </div>

            <div id="formMessage" class="alert mt-3" role="alert" style="display:none;"></div>
          </form>

        </div>
      </div>
    </div>

    <!-- RIGHT: INFO -->
    <div class="col-lg-4 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="mb-3"><i class="fas fa-info-circle mr-2"></i>About Invoice Generation</h5>
          <p class="small">The system will:</p>
          <ul class="small">
            <li>Fetch all active students from selected class(es)</li>
            <li>Apply fee structure from school_fee_assignment</li>
            <li>Apply scholarships/concessions from school_student_fees_concessions</li>
            <li>Add any additional fees selected above</li>
            <li>Generate invoice with invoice number (auto-increment)</li>
            <li>Create line items for each fee component</li>
          </ul>
          <hr>
          <p class="small"><strong>Note:</strong> Only active students will be included. Invoices are marked as "pending" by default.</p>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5 class="mb-3"><i class="fas fa-cogs mr-2"></i>Total Students to Invoice</h5>
          <h2 id="totalStudents" class="text-primary mb-0">—</h2>
          <small class="text-muted">Will be calculated after you select filters</small>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="previewModalLabel">
          <i class="fas fa-eye mr-2"></i>Invoice Preview
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="previewContent">
        Loading preview...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // Toggle class select based on radio button
  const applyAll = document.getElementById('applyAll');
  const applySpecific = document.getElementById('applySpecific');
  const classSelectWrap = document.getElementById('classSelectWrap');
  const classId = document.getElementById('classId');

  function toggleClassSelect() {
    if (applySpecific.checked) {
      classSelectWrap.style.display = 'block';
      classId.required = true;
    } else {
      classSelectWrap.style.display = 'none';
      classId.required = false;
      classId.value = '';
    }
  }

  applyAll.addEventListener('change', toggleClassSelect);
  applySpecific.addEventListener('change', toggleClassSelect);

  // Set today's date in due date if not set
  const dueDate = document.getElementById('dueDate');
  if (!dueDate.value) {
    const today = new Date();
    dueDate.value = today.toISOString().split('T')[0];
  }

  // Preview button
  document.getElementById('btnPreview').addEventListener('click', function(){
    const form = document.getElementById('invoiceGeneratorForm');
    const formData = new FormData(form);
    formData.set('action', 'preview');

    fetch('bulk_generate_invoices.php', { method:'POST', credentials:'same-origin', body: formData })
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(res => {
        if (res && res.success) {
          document.getElementById('previewContent').innerHTML = res.preview_html;
          document.getElementById('totalStudents').textContent = res.student_count;
          jQuery('#previewModal').modal('show');
        } else {
          alert('Error: ' + (res.message || 'Unknown error'));
        }
      })
      .catch(e => {
        console.error(e);
        alert('Preview failed: ' + e.message);
      });
  });

  // Generate button
  document.getElementById('invoiceGeneratorForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    if (!confirm('Generate invoices for all selected students? This action cannot be undone.')) return;

    const form = this;
    const formData = new FormData(form);
    formData.set('action', 'generate');

    const btn = document.getElementById('btnGenerate');
    btn.disabled = true;
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';

    fetch('bulk_generate_invoices.php', { method:'POST', credentials:'same-origin', body: formData })
      .then(r => {
        if (!r.ok) {
          return r.json().then(data => {
            throw new Error(data.message || 'HTTP ' + r.status);
          });
        }
        return r.json();
      })
      .then(res => {
        if (res && res.success) {
          alert('✓ ' + res.message + '\n' + res.invoice_count + ' invoices generated successfully!');
          window.location.href = 'invoice_list.php';
        } else {
          throw new Error(res.message || 'Unknown error');
        }
      })
      .catch(e => {
        console.error(e);
        const msgDiv = document.getElementById('formMessage');
        msgDiv.className = 'alert alert-danger';
        msgDiv.textContent = 'Error: ' + e.message;
        msgDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = oldText;
      });
  });
});
</script>
