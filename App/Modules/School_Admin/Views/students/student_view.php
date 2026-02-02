<?php
// Student view page - read-only display of full student profile (styled)
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Student Profile</title>
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link rel="stylesheet" href="../../../../../public/assets/css/vendors.css">
    <link rel="stylesheet" href="../../../../../public/assets/css/style.css">
    <link rel="stylesheet" href="../../../../../public/assets/css/font-fix.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
      /* Responsive profile styles */
      .student-photo { width:100%; max-width:220px; height:auto; object-fit:cover; border-radius:8px; }
      .student-photo-placeholder { width:100%; max-width:220px; height:220px; display:flex; align-items:center; justify-content:center; color:#9aa0a6; border-radius:8px; border:1px solid #eee; }
      .doc-thumb { max-height:120px; object-fit:cover; }
      .kv { font-weight:600; }
      .meta-row { gap:12px; display:flex; flex-wrap:wrap; align-items:center; }
      .student-profile-wrapper { writing-mode: horizontal-tb !important; }

      /* Documents responsive grid */
      .docs-row { display:flex; flex-wrap:wrap; gap:12px; }

      @media (max-width: 576px) {
        .meta-row { flex-direction:column; align-items:flex-start; }
        .student-photo-placeholder, .student-photo { margin:0 auto 12px; }
        .student-photo-placeholder { height:180px; }
      }
    </style>
</head>
<body>
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Student Profile</h3>
      <div>
        <a href="student_list.php" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        <a id="editBtn" href="#" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
      </div>
    </div>

    <div id="studentContainer">
      <div class="text-center py-5" id="loading">Loading student details...</div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="../../../../../public/assets/js/vendors.js"></script>
  <script>
  const STUDENT_ID = <?php echo json_encode($student_id); ?>;
  document.addEventListener('DOMContentLoaded', ()=>{
    if (!STUDENT_ID) {
      document.getElementById('studentContainer').innerHTML = '<div class="alert alert-warning">No student selected.</div>';
      return;
    }

    fetch('get_student.php?id=' + STUDENT_ID, { credentials: 'same-origin' })
      .then(r => {
        const ct = r.headers.get('content-type') || '';
        if (!r.ok) {
          // attempt to read body for message
          return r.text().then(t => { throw new Error('Server error: ' + (t||r.statusText)); });
        }
        if (!ct.includes('application/json')) {
          return r.text().then(t => { console.error('Non-JSON response from get_student.php:', t); throw new Error('Invalid server response (not JSON). You may be logged out.'); });
        }
        return r.json();
      })
      .then(data => {
        if (!data || data.success === false) {
          document.getElementById('studentContainer').innerHTML = '<div class="alert alert-danger">Failed to load student.</div>';
          return;
        }
        const student = data.student || {};
        const guardians = data.guardians || [];
        const academic = data.academic || null;
        const documents = data.documents || [];

        // if get_student returned enrollment already, use it; otherwise fetch it
        if (data.enrollment) {
          renderAll(student, guardians, academic, documents, data.enrollment);
        } else {
          fetch('get_enrollment.php?student_id=' + STUDENT_ID, { credentials: 'same-origin' }).then(er => er.json()).then(enData => {
            const enrollment = (enData && enData.success) ? enData.enrollment : null;
            renderAll(student, guardians, academic, documents, enrollment);
          }).catch(errEnroll => {
            console.warn('Failed to load enrollment', errEnroll);
            renderAll(student, guardians, academic, documents, null);
          });
        }
        return; // render will be done in renderAll

        // placeholder; actual rendering moved to renderAll to allow enrollment fetch
        let html = '';
        html += '<div class="card mb-3"><div class="card-body">';
        html += '<div class="row">';
        html += '<div class="col-md-3 text-center">';
        // site root used to build absolute urls for Storage paths
        const SITE_ROOT = '/School-SAAS/';
        function normalizePath(p){
          if (!p) return p;
          if (/^https?:\/\//i.test(p)) return p;
          if (p.charAt(0) === '/') return p; // already absolute from host
          // common stored values start with 'Storage/' or 'public/' - build absolute path from site root
          if (p.match(/^(Storage|public)\//)) return SITE_ROOT + p.replace(/^\/+/, '');
          // fallback: assume path relative to site root
          return SITE_ROOT + p.replace(/^\/+/, '');
        }

        // find photo document (DB column is doc_type and file_path/original_name)
        const photoDoc = documents.find(d => (d.doc_type && d.doc_type.toLowerCase().includes('photo')) || (d.original_name && d.original_name.toLowerCase().match(/photo|pic|image/)) );
        const photo = normalizePath((photoDoc && (photoDoc.file_path || photoDoc.file || '')) || (student.photo ? student.photo : null));
          if (photo) html += '<img src="'+photo+'" class="student-photo mb-2 img-fluid">';
          else html += '<div class="student-photo-placeholder mb-2">No photo</div>';
          html += '<div class="small text-muted">Admission No</div><div class="h6">'+(student.admission_no||'-')+'</div>';
        html += '</div>';

        html += '<div class="col-12 col-md-9">';
        html += '<h4 class="mb-1">'+(student.first_name||'')+' '+(student.last_name||'')+'</h4>';
        html += '<div class="meta-row mb-2">';
        html += '<div><span class="kv">DOB:</span> '+(student.dob||'-')+'</div>';
        html += '<div><span class="kv">Gender:</span> '+(student.gender||'-')+'</div>';
        html += '<div><span class="kv">Admission Date:</span> '+(student.admission_date||'-')+'</div>';
        html += '<div><span class="kv">Religion:</span> '+(student.religion||'-')+'</div>';
        html += '</div>';
        html += '<p class="text-muted">Additional personal information can be added here.</p>';
        html += '</div>';
        html += '</div></div></div>';
          html += '<div class="col-12 col-sm-8 col-md-9">';
          html += '<h4 class="mb-1">'+(student.first_name||'')+' '+(student.last_name||'')+'</h4>';
          html += '<div class="meta-row mb-2">';
          html += '<div><span class="kv">DOB:</span> '+(student.dob||'-')+'</div>';
          html += '<div><span class="kv">Gender:</span> '+(student.gender||'-')+'</div>';
          html += '<div><span class="kv">Admission Date:</span> '+(student.admission_date||'-')+'</div>';
          html += '<div><span class="kv">Religion:</span> '+(student.religion||'-')+'</div>';
          html += '</div>';
          html += '<p class="text-muted">Additional personal information can be added here.</p>';
          html += '</div>';
          html += '</div></div></div>';

        // Guardians
        html += '<div class="card mb-3"><div class="card-header">Guardians</div><div class="card-body">';
        if (guardians.length === 0) {
          html += '<div class="text-muted">No guardians recorded.</div>';
        } else {
          html += '<div class="table-responsive"><table class="table table-sm mb-0">';
          html += '<thead><tr><th>Name</th><th>Relation</th><th>Mobile</th><th>CNIC</th><th>Primary</th></tr></thead><tbody>';
          guardians.forEach(g=>{
            html += '<tr>';
            html += '<td>'+(g.name||'')+'</td>';
            html += '<td>'+(g.relation||'')+'</td>';
            html += '<td>'+(g.mobile||'')+'</td>';
            html += '<td>'+(g.cnic_passport||'')+'</td>';
            html += '<td>'+((g.is_primary==1||g.is_primary=='1')?'<span class="badge badge-success">Primary</span>':'')+'</td>';
            html += '</tr>';
          });
          html += '</tbody></table></div>';
        }
        html += '</div></div>';

        // Academic section is rendered in renderAll

        // Documents
        html += '<div class="card mb-3"><div class="card-header">Documents</div><div class="card-body">';
        if (documents.length === 0) {
          html += '<div class="text-muted">No documents uploaded.</div>';
        } else {
          html += '<div class="row">';
          documents.forEach(d=>{
            const fp = normalizePath(d.file_path || d.file || '');
            const fname = d.original_name || d.file_name || fp.split('/').pop() || 'Document';
              // derive friendly label from type or file name
              function docLabel(doc){
                if (!doc) return 'Document';
                const t = (doc.doc_type||'').toLowerCase();
                const n = (doc.original_name||doc.file_name||'').toLowerCase();
                if (t.includes('photo') || n.match(/photo|pic|image/)) return 'Photo';
                if (t.includes('nic') || t.includes('cnic') || n.match(/nic|cnic/)) return 'NIC/Passport';
                if (t.includes('birth') || n.match(/birth|form b|form_b|form-b/)) return 'Birth Certificate (Form B)';
                if (t.includes('passport')) return 'Passport';
                return doc.type || 'Document';
              }
              const label = docLabel(d);
              html += '<div class="col-6 col-md-3 mb-3">';
              html += '<div class="border rounded p-2 text-center">';
              html += '<a href="'+fp+'" target="_blank" class="d-block mb-2">';
              if ((fp||'').match(/\.(jpg|jpeg|png|gif|webp)$/i)) html += '<img src="'+fp+'" class="img-fluid doc-thumb">';
              else html += '<div class="py-4"><i class="fa fa-file fa-3x"></i></div>';
              html += '</a>';
              html += '<div class="small text-truncate mb-1"><strong>'+label+':</strong> '+(fname||'')+'</div>';
              html += '<a href="'+fp+'" download class="btn btn-sm btn-outline-primary">Download</a>';
              html += '</div></div>';
            });
          html += '</div>';
        }
        html += '</div></div>';

        // renderAll will set innerHTML and edit link
        const eb = document.getElementById('editBtn'); if (eb) eb.href = 'edit_student.php?id='+STUDENT_ID;
      }).catch(err => { console.error(err); document.getElementById('studentContainer').innerHTML = '<div class="alert alert-danger">Error loading student.</div>'; });

      // render function that receives enrollment as well
      function renderAll(student, guardians, academic, documents, enrollment) {
        let html = '';
        html += '<div class="card mb-3"><div class="card-body">';
        html += '<div class="row">';
        html += '<div class="col-md-3 text-center">';
        const SITE_ROOT = '/School-SAAS/';
        function normalizePath(p){ if (!p) return p; if (/^https?:\/\//i.test(p)) return p; if (p.charAt(0) === '/') return p; if (p.match(/^(Storage|public)\//)) return SITE_ROOT + p.replace(/^\/+/, ''); return SITE_ROOT + p.replace(/^\/+/, ''); }
        const photoDoc = documents.find(d => (d.doc_type && d.doc_type.toLowerCase().includes('photo')) || (d.original_name && d.original_name.toLowerCase().match(/photo|pic|image/)) );
        const photo = normalizePath((photoDoc && (photoDoc.file_path || photoDoc.file || '')) || (student.photo ? student.photo : null));
        if (photo) html += '<img src="'+photo+'" class="student-photo mb-2 img-fluid">';
        else html += '<div class="student-photo-placeholder mb-2">No photo</div>';
        html += '<div class="small text-muted">Admission No</div><div class="h6">'+(student.admission_no||'-')+'</div>';
        html += '</div>';

        html += '<div class="col-12 col-md-9">';
        html += '<h4 class="mb-1">'+(student.first_name||'')+' '+(student.last_name||'')+'</h4>';
        html += '<div class="meta-row mb-2">';
        html += '<div><span class="kv">DOB:</span> '+(student.dob||'-')+'</div>';
        html += '<div><span class="kv">Gender:</span> '+(student.gender||'-')+'</div>';
        html += '<div><span class="kv">Admission Date:</span> '+(student.admission_date||'-')+'</div>';
        html += '<div><span class="kv">Religion:</span> '+(student.religion||'-')+'</div>';
        html += '</div>';
        html += '<p class="text-muted">Additional personal information can be added here.</p>';
        html += '</div>';
        html += '</div></div></div>';

        // Guardians
        html += '<div class="card mb-3"><div class="card-header">Guardians</div><div class="card-body">';
        if (guardians.length === 0) {
          html += '<div class="text-muted">No guardians recorded.</div>';
        } else {
          html += '<div class="table-responsive"><table class="table table-sm mb-0">';
          html += '<thead><tr><th>Name</th><th>Relation</th><th>Mobile</th><th>CNIC</th><th>Primary</th></tr></thead><tbody>';
          guardians.forEach(g=>{
            html += '<tr>';
            html += '<td>'+(g.name||'')+'</td>';
            html += '<td>'+(g.relation||'')+'</td>';
            html += '<td>'+(g.mobile||'')+'</td>';
            html += '<td>'+(g.cnic_passport||'')+'</td>';
            html += '<td>'+((g.is_primary==1||g.is_primary=='1')?'<span class="badge badge-success">Primary</span>':'')+'</td>';
            html += '</tr>';
          });
          html += '</tbody></table></div>';
        }
        html += '</div></div>';

        // Academic / Enrollment
        html += '<div class="card mb-3"><div class="card-header">Academic</div><div class="card-body">';
        if (!academic && !enrollment) {
          html += '<div class="text-muted">No academic record.</div>';
        } else {
          // prefer enrollment for roll/session/class/section details
          const source = enrollment || academic || {};
          html += '<div class="row">';
          html += '<div class="col-md-3"><div class="kv">Session</div><div>'+((enrollment && (enrollment.session_name||enrollment.session_label)) || (academic && (academic.session_label||academic.session_id)) || '-')+'</div></div>';
          html += '<div class="col-md-3"><div class="kv">Class</div><div>'+((enrollment && enrollment.class_name) || (academic && (academic.class_name||academic.class_id)) || '-')+'</div></div>';
          html += '<div class="col-md-3"><div class="kv">Section</div><div>'+((enrollment && enrollment.section_name) || (academic && (academic.section_name||academic.section_id)) || '-')+'</div></div>';
          html += '<div class="col-md-3"><div class="kv">Roll No</div><div>'+(enrollment && enrollment.roll_no ? enrollment.roll_no : (academic && academic.roll_no ? academic.roll_no : '-'))+'</div></div>';
          html += '</div>';
          if (enrollment && enrollment.admission_date) html += '<div class="mt-2"><strong>Admission Date:</strong> '+enrollment.admission_date+'</div>';
          if (enrollment && enrollment.status) html += '<div class="mt-1"><strong>Status:</strong> '+enrollment.status+'</div>';
        }
        html += '</div></div>';

        // Documents
        html += '<div class="card mb-3"><div class="card-header">Documents</div><div class="card-body">';
        if (documents.length === 0) {
          html += '<div class="text-muted">No documents uploaded.</div>';
        } else {
          html += '<div class="row">';
          documents.forEach(d=>{
            const fp = normalizePath(d.file_path || d.file || '');
            const fname = d.original_name || d.file_name || fp.split('/').pop() || 'Document';
            function docLabel(doc){ if (!doc) return 'Document'; const t = (doc.doc_type||'').toLowerCase(); const n = (doc.original_name||doc.file_name||'').toLowerCase(); if (t.includes('photo') || n.match(/photo|pic|image/)) return 'Photo'; if (t.includes('nic') || t.includes('cnic') || n.match(/nic|cnic/)) return 'NIC/Passport'; if (t.includes('birth') || n.match(/birth|form b|form_b|form-b/)) return 'Birth Certificate (Form B)'; if (t.includes('passport')) return 'Passport'; return doc.type || 'Document'; }
            const label = docLabel(d);
            html += '<div class="col-6 col-md-3 mb-3">';
            html += '<div class="border rounded p-2 text-center">';
            html += '<a href="'+fp+'" target="_blank" class="d-block mb-2">';
            if ((fp||'').match(/\.(jpg|jpeg|png|gif|webp)$/i)) html += '<img src="'+fp+'" class="img-fluid doc-thumb">';
            else html += '<div class="py-4"><i class="fa fa-file fa-3x"></i></div>';
            html += '</a>';
            html += '<div class="small text-truncate mb-1"><strong>'+label+':</strong> '+(fname||'')+'</div>';
            html += '<a href="'+fp+'" download class="btn btn-sm btn-outline-primary">Download</a>';
            html += '</div></div>';
          });
          html += '</div>';
        }
        html += '</div></div>';

        document.getElementById('studentContainer').innerHTML = html;
      }
      
  });
  </script>
</body>
</html>
