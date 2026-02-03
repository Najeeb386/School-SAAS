<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fees — Concessions</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
.badge-soft{
  background:#eef2ff;
  color:#4f46e5;
  font-weight:600;
}
.badge-scholarship{background:#e0f2fe;color:#0369a1;}
.badge-discount{background:#ecfeff;color:#0891b2;}
.badge-concession{background:#fef3c7;color:#92400e;}
.small-muted{font-size:.8rem;color:#6c757d;}
.list-scroll{max-height:360px;overflow:auto;}
.stat-box{
  background:linear-gradient(135deg,#6366f1,#4f46e5);
  color:#fff;border-radius:12px;padding:18px;
}
.stat-box.light{
  background:#fff;color:#111;border:1px solid #e5e7eb;
}
</style>
</head>

<body>

<div class="container-fluid py-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="page-title mb-1">Concessions & Scholarships</h3>
      <div class="small-muted">Manage discounts, scholarships & special fee concessions</div>
    </div>
    <div>
      <button class="btn btn-outline-secondary mr-2">
        <i class="fas fa-history"></i> Audit
      </button>
      <button class="btn btn-primary">
        <i class="fas fa-plus"></i> New
      </button>
    </div>
  </div>

  <div class="row">

    <!-- LEFT: CREATE FORM -->
    <div class="col-lg-5 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="mb-3">Create / Update Concession</h5>

          <div class="form-group">
            <label class="form-label">Student</label>
            <div class="input-group">
              <input id="studentSearch" class="form-control" placeholder="Search by name / admission / phone">
              <div class="input-group-append">
                <button id="btnPickStudent" class="btn btn-outline-primary" type="button">Pick</button>
              </div>
            </div>
            <input type="hidden" id="admission_no" name="admission_no">
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
              <input id="value" type="number" class="form-control" placeholder="e.g. 500 or 10%">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="form-label">Session</label>
              <select id="sessionId" class="form-control">
                <option value="2025">2025–2026</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label class="form-label">Status</label>
              <select id="status" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="form-label">Start Month</label>
              <input id="startMonth" type="month" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label class="form-label">End Month</label>
              <input id="endMonth" type="month" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Remarks</label>
            <textarea id="remarks" class="form-control" rows="3"></textarea>
          </div>

          <div class="text-right">
            <button id="btnReset" class="btn btn-outline-secondary mr-2" type="button">Reset</button>
            <button id="btnSaveConcession" class="btn btn-success" type="button">Save</button>
          </div>

        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="col-lg-7">

      <!-- STATS -->
      <div class="row mb-3">
          <div class="col-md-6 mb-3">
            <div id="statActive" class="stat-box">
              <div class="small-muted text-white">Active Concessions</div>
              <h3 class="mb-0">0</h3>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div id="statExpiring" class="stat-box light">
              <div class="small-muted">Expiring This Month</div>
              <h3 class="mb-0">0</h3>
            </div>
          </div>
      </div>

      <!-- RECENT -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="mb-3">Recent Concessions</h5>
          <div class="list-scroll">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Type</th>
                  <th>Value</th>
                  <th>Period</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    John Doe<br>
                    <span class="small-muted">ADM-2026-003</span>
                  </td>
                  <td><span class="badge badge-discount">Discount</span></td>
                  <td>₨ 500</td>
                  <td>Feb → Jun</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
                <tr>
                  <td>
                    Mary Ali<br>
                    <span class="small-muted">ADM-2025-112</span>
                  </td>
                  <td><span class="badge badge-scholarship">Scholarship</span></td>
                  <td>10%</td>
                  <td>Sep → —</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function(){
  function qs(id){ return document.getElementById(id); }
  function escapeHtml(s){ if (s===null||s===undefined) return ''; return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; }); }

  function loadRecent(){
    fetch('list_concessions.php', { credentials: 'same-origin' })
      .then(r=>r.json()).then(data=>{
        if (!data || !data.success) return;
        const tb = document.querySelector('tbody'); tb.innerHTML='';
        const list = (data.concessions||[]);
        let activeCount = 0, expiringCount = 0;
        const now = new Date();
        const nowMonth = now.getMonth()+1; const nowYear = now.getFullYear();
        list.forEach(c=>{
          const tr = document.createElement('tr');
          const student = (c.first_name||'') + (c.last_name?(' '+c.last_name):'');
          const period = (c.start_month||'') + (c.end_month?(' → '+c.end_month):' → —');
          const value = (c.value_type==='percentage') ? (c.value+'%') : ('₨ '+Number(c.value).toLocaleString());
          const typeBadge = '<span class="badge badge-discount">'+escapeHtml(c.type||'')+'</span>';
          const statusBadge = c.status==1?'<span class="badge badge-success">Active</span>':'<span class="badge badge-secondary">Inactive</span>';
          tr.innerHTML = '<td>'+escapeHtml(student)+'<br><span class="small-muted">'+escapeHtml(c.admission_no||'')+'</span></td><td>'+typeBadge+'</td><td>'+escapeHtml(value)+'</td><td>'+escapeHtml(period)+'</td><td>'+statusBadge+'</td>';
          tb.appendChild(tr);
          if (parseInt(c.status)==1) activeCount++;
          // expiring this month: check end_month in format YYYY-MM or similar
          if (c.end_month) {
            const m = c.end_month.split('-');
            if (m.length>=2) {
              const y = parseInt(m[0]), mo = parseInt(m[1]);
              if (y===nowYear && mo===nowMonth) expiringCount++;
            }
          }
        });
        const elActive = document.querySelector('#statActive h3'); if (elActive) elActive.innerText = String(activeCount);
        const elExp = document.querySelector('#statExpiring h3'); if (elExp) elExp.innerText = String(expiringCount);
      }).catch(err=>console.error(err));
  }

  // safe reset helper
  qs('btnReset').addEventListener('click', function(){ const form = document.querySelector('form'); if (form) form.reset(); qs('admission_no').value=''; });

  // prevent double submissions
  let saving = false;
  qs('btnSaveConcession').addEventListener('click', function(){
    if (saving) return; // ignore double clicks
    saving = true; qs('btnSaveConcession').disabled = true; const oldText = qs('btnSaveConcession').innerText; qs('btnSaveConcession').innerText = 'Saving...';

    const fd = new FormData();
    fd.set('admission_no', qs('admission_no').value || '');
    fd.set('session_id', qs('sessionId').value || '');
    fd.set('type', qs('type').value || '');
    fd.set('value_type', qs('valueType').value || '');
    fd.set('value', qs('value').value || '');
    fd.set('applies_to', qs('appliesTo').value || '');
    fd.set('start_month', qs('startMonth').value || '');
    fd.set('end_month', qs('endMonth').value || '');
    fd.set('status', qs('status').value || '1');
    fd.set('remarks', qs('remarks').value || '');

    fetch('save_concession.php', { method:'POST', credentials:'same-origin', body: fd })
      .then(r=>{
        if (!r.ok) throw new Error('Server returned '+r.status);
        return r.json();
      })
      .then(res=>{
        if (res && res.success) {
          // reload the page so the UI reflects the new data
          window.location.reload();
        } else {
          alert('Save failed: '+(res.message||''));
        }
      })
      .catch(e=>{ console.error(e); alert('Save error: '+(e.message||'')); })
      .finally(()=>{ saving = false; qs('btnSaveConcession').disabled = false; qs('btnSaveConcession').innerText = oldText; });
  });

  qs('btnPickStudent').addEventListener('click', function(){
    const ad = prompt('Enter admission number (e.g. aams-2026-000003)'); if (!ad) return;
    fetch('find_student_by_admission.php?admission_no='+encodeURIComponent(ad), { credentials:'same-origin' }).then(r=>r.json()).then(res=>{
      if (res && res.success) {
        const s = res.student; qs('studentSearch').value = (s.first_name||'')+' '+(s.last_name||'')+' ('+s.admission_no+')'; qs('admission_no').value = s.admission_no;
      } else alert('Not found');
    }).catch(e=>{console.error(e); alert('Lookup error');});
  });

  // bulk apply
  const bulkBtn = document.querySelector('.card .btn-outline-primary');
  if (bulkBtn) bulkBtn.addEventListener('click', function(){ alert('Bulk apply is available in the Bulk section.'); });

  loadRecent();
});
</script>
