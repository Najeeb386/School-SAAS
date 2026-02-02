<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
$school_id = $_SESSION['school_id'] ?? null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Reports</title>
  <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
  <link rel="stylesheet" href="../../../../../public/assets/css/vendors.css">
  <link rel="stylesheet" href="../../../../../public/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .report-card { min-height:150px }
    .student-photo { width:96px; height:96px; object-fit:cover; border-radius:8px }
  </style>
</head>
<body>
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center">
        <button class="btn btn-light mr-3" onclick="history.back()"><i class="fas fa-arrow-left"></i> Back</button>
        <h3 class="mb-0">Student Reports</h3>
      </div>
      <div>
        <button id="printReport" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i> Print</button>
      </div>
    </div>

    <div class="card mb-3"><div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center">
          <input id="student_id_input" class="form-control mr-2" placeholder="Enter student ID or admission no" />
          <button id="loadBtn" class="btn btn-primary">Load</button>
        </div>
        <div class="col-md-6 text-right text-muted">Select a student to view reports</div>
      </div>
    </div></div>

    <div id="reportsArea">
      <!-- Student Summary -->
      <div class="card mb-3">
        <div class="card-header">Student Summary</div>
        <div class="card-body report-card" id="summaryArea">Please select a student and click Load.</div>
      </div>

      <!-- Fee Status -->
      <div class="card mb-3">
        <div class="card-header">Fee Status</div>
        <div class="card-body report-card" id="feeArea">Fee information will appear here.</div>
      </div>

      <!-- Attendance -->
      <div class="card mb-3">
        <div class="card-header">Attendance</div>
        <div class="card-body report-card" id="attendanceArea">Attendance summary will appear here.</div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="../../../../../public/assets/js/vendors.js"></script>
  <script>
  (function(){
    const input = document.getElementById('student_id_input');
    const loadBtn = document.getElementById('loadBtn');
    const summaryArea = document.getElementById('summaryArea');
    const feeArea = document.getElementById('feeArea');
    const attendanceArea = document.getElementById('attendanceArea');

    function renderSummary(data){
      if (!data || !data.student) { summaryArea.innerHTML = '<div class="alert alert-warning">Student not found.</div>'; return; }
      const s = data.student;
      const docs = data.documents || [];
      const photoDoc = docs.find(d=> (d.doc_type||'').toLowerCase().includes('photo') || (d.original_name||'').toLowerCase().match(/photo|pic|image/)) || null;
      const photo = photoDoc ? (photoDoc.file_path||photoDoc.file||'') : (s.photo||'');
      const photoTag = photo ? '<img src="'+photo+'" class="student-photo mr-3">' : '<div class="student-photo-placeholder mr-3" style="width:96px;height:96px;border:1px solid #eee;display:flex;align-items:center;justify-content:center;color:#999">No photo</div>';
      let html = '<div class="d-flex">'+photoTag+'<div>';
      html += '<h5>'+ (s.first_name||'') + ' ' + (s.last_name||'') +'</h5>';
      html += '<div><strong>Admission:</strong> '+(s.admission_no||'-')+'</div>';
      html += '<div><strong>Class/Section:</strong> '+((data.academics && data.academics[0]) ? (data.academics[0].class_name+' / '+data.academics[0].section_name) : '-')+'</div>';
      html += '<div><strong>Father:</strong> '+(s.father_names||'-') + ' <small>'+(s.father_contact||'')+'</small></div>';
      html += '<div><strong>Guardian:</strong> '+(data.guardians && data.guardians[0] ? data.guardians[0].name : '-')+'</div>';
      html += '<div><strong>Last Updated:</strong> '+(s.updated_at||'-')+'</div>';
      html += '</div></div>';
      summaryArea.innerHTML = html;
    }

    function renderFee(data){
      // If server returns fee structure, render table; otherwise show placeholder
      if (!data || !data.fees) { feeArea.innerHTML = '<div class="text-muted">No fee data available for this student.</div>'; return; }
      let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Due</th><th>Paid</th><th>Balance</th><th>Last Payment</th></tr></thead><tbody>';
      html += '<tr><td>'+ (data.fees.total_due||'-') +'</td><td>'+ (data.fees.total_paid||'-') +'</td><td>'+ (data.fees.balance||'-') +'</td><td>'+ (data.fees.last_payment_date||'-') +'</td></tr>';
      html += '</tbody></table></div>';
      feeArea.innerHTML = html;
    }

    function renderAttendance(data){
      if (!data || !data.attendance) { attendanceArea.innerHTML = '<div class="text-muted">No attendance data available.</div>'; return; }
      const a = data.attendance;
      let html = '<div class="row"><div class="col-md-4"><div><strong>Present</strong><div>'+ (a.present || 0) +'</div></div></div>';
      html += '<div class="col-md-4"><div><strong>Absent</strong><div>'+ (a.absent || 0) +'</div></div></div>';
      html += '<div class="col-md-4"><div><strong>Percentage</strong><div>'+ (a.percentage ? a.percentage+'%' : '-') +'</div></div></div></div>';
      attendanceArea.innerHTML = html;
    }

    function loadStudent(identifier){
      summaryArea.innerHTML = 'Loading...'; feeArea.innerHTML = ''; attendanceArea.innerHTML = '';
      // first try to interpret identifier as id param, else admission no via search endpoint
      // prefer existing endpoint get_student.php?id=
      const isNumeric = /^[0-9]+$/.test(identifier);
      const url = isNumeric ? ('get_student.php?id='+identifier) : ('get_student.php?admission_no='+encodeURIComponent(identifier));
      fetch(url, { credentials: 'same-origin' }).then(r=>r.json()).then(j=>{
        if (!j || j.success === false) { summaryArea.innerHTML = '<div class="alert alert-warning">Student not found.</div>'; return; }
        renderSummary(j);
      }).catch(()=>{ summaryArea.innerHTML = '<div class="alert alert-danger">Failed to load student summary.</div>'; });

      // try fee and attendance endpoints (they may not exist yet) - use POST-free endpoints
      fetch('get_fee_status.php?student=' + encodeURIComponent(identifier), { credentials: 'same-origin' }).then(r=>r.json()).then(renderFee).catch(()=>{ feeArea.innerHTML = '<div class="text-muted">Fee data not available.</div>'; });
      fetch('get_attendance.php?student=' + encodeURIComponent(identifier), { credentials: 'same-origin' }).then(r=>r.json()).then(renderAttendance).catch(()=>{ attendanceArea.innerHTML = '<div class="text-muted">Attendance data not available.</div>'; });
    }

    loadBtn.addEventListener('click', function(){
      const v = input.value.trim(); if (!v) return alert('Enter student id or admission no');
      loadStudent(v);
    });

    document.getElementById('printReport').addEventListener('click', function(){ window.print(); });
  })();
  </script>
</body>
</html>
