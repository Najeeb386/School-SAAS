<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Fees — Concessions</title>
  <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
  <link rel="stylesheet" href="../../../../../../public/assets/css/vendors.css">
  <link rel="stylesheet" href="../../../../../../public/assets/css/font-fix.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    body { background:#f6f7fb; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
    .page-header { padding:22px 0; }
    .card-ghost { background:transparent; border:none; }
    .muted { color:#6c757d; }
    .form-label { font-weight:600; font-size:0.9rem; }
    .concession-list { max-height:420px; overflow:auto; }
    .badge-type { font-size:0.8rem; padding:0.35em 0.6em; border-radius:6px; }
  </style>
</head>
<body>
  <div class="container my-4">
    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h2 class="mb-1">Concessions & Discounts</h2>
        <div class="muted">Create and manage discounts, scholarships and concessions for students.</div>
      </div>
      <div>
        <button class="btn btn-outline-secondary mr-2"><i class="fas fa-history"></i> Audit</button>
        <button class="btn btn-primary"><i class="fas fa-plus"></i> New Concession</button>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="mb-3">Create Concession</h5>
            <form id="concessionForm" class="">
              <div class="form-group">
                <label class="form-label">Student</label>
                <div class="input-group">
                  <input id="studentSearch" class="form-control" placeholder="Search student by name / admission no / mobile">
                  <div class="input-group-append">
                    <button id="btnPickStudent" class="btn btn-outline-primary" type="button">Pick</button>
                  </div>
                </div>
                <small class="text-muted">Select the student to whom this concession will apply.</small>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label class="form-label">Type</label>
                  <select id="type" class="form-control">
                    <option value="discount">Discount</option>
                    <option value="scholarship">Scholarship</option>
                    <option value="concession">Concession</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label class="form-label">Applies To</label>
                  <select id="appliesTo" class="form-control">
                    <option value="tuition_only">Tuition Only</option>
                    <option value="all">All Fees</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label class="form-label">Value Type</label>
                  <select id="valueType" class="form-control">
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label class="form-label">Value</label>
                  <input id="value" type="number" step="0.01" class="form-control" placeholder="e.g. 500 or 10 for 10%">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label class="form-label">Session</label>
                  <select id="sessionId" class="form-control"><option value="2025">2025-2026</option></select>
                </div>
                <div class="form-group col-md-6">
                  <label class="form-label">Status</label>
                  <select id="status" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label class="form-label">Start Month</label>
                  <input id="startMonth" type="month" class="form-control">
                </div>
                <div class="form-group col-md-6">
                  <label class="form-label">End Month (optional)</label>
                  <input id="endMonth" type="month" class="form-control">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Notes / Remarks</label>
                <textarea id="remarks" class="form-control" rows="3" placeholder="Optional notes"></textarea>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <div class="muted small">Concession records are stored per session and can be time-bound.</div>
                <div>
                  <button id="btnSaveConcession" class="btn btn-success">Save Concession</button>
                  <button id="btnReset" type="button" class="btn btn-outline-secondary">Reset</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="mb-3">Recent Concessions</h5>
            <div class="concession-list">
              <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Student</th><th>Type</th><th>Value</th><th>Period</th><th>Status</th></tr></thead>
                <tbody id="recentList">
                  <tr><td>John Doe<br><small class="muted">aams-2026-000003</small></td><td><span class="badge badge-info badge-type">Discount</span></td><td>₨ 500</td><td>2026-02 → 2026-06</td><td><span class="badge badge-success">Active</span></td></tr>
                  <tr><td>Mary Ali<br><small class="muted">aams-2025-000112</small></td><td><span class="badge badge-primary badge-type">Scholarship</span></td><td>10%</td><td>2025-09 → —</td><td><span class="badge badge-success">Active</span></td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="mb-3">Bulk Concessions & Reports</h5>
            <p class="muted">Apply concessions in bulk to a class or session, or review current concessions.</p>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="form-label">Apply To Class</label>
                <select id="bulkClass" class="form-control"><option value="">Select class</option><option>Class 1</option><option>Class 2</option></select>
              </div>
              <div class="form-group col-md-6">
                <label class="form-label">Apply Session</label>
                <select id="bulkSession" class="form-control"><option value="">Select session</option><option>2025-2026</option></select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="form-label">Concession Template</label>
                <select id="template" class="form-control"><option>10% Tuition Discount</option><option>₨ 1000 Scholarship</option></select>
              </div>
              <div class="form-group col-md-6 align-self-end text-right">
                <button id="btnApplyBulk" class="btn btn-outline-primary">Apply to Class</button>
              </div>
            </div>

            <hr>
            <h6>Reports</h6>
            <div class="row">
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="muted">Total Active</div>
                  <div id="reportTotal" class="h4">24</div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="muted">Expiring This Month</div>
                  <div id="reportExpiring" class="h4">3</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="../../../../../public/assets/js/vendors.js"></script>
  <script>
    // UI-only interactions
    document.getElementById('btnReset').addEventListener('click', function(){ document.getElementById('concessionForm').reset(); });
    document.getElementById('btnSaveConcession').addEventListener('click', function(ev){ ev.preventDefault(); alert('Concession saved (UI demo).'); });
    document.getElementById('btnApplyBulk').addEventListener('click', function(){ alert('Bulk apply (UI demo).'); });
    document.getElementById('btnPickStudent').addEventListener('click', function(){ alert('Open student picker (UI demo).'); });
  </script>
</body>
</html>
