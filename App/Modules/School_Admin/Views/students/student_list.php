<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Students List</title>
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link rel="stylesheet" href="../../../../../public/assets/css/vendors.css">
    <link rel="stylesheet" href="../../../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .filters-row { gap:8px; display:flex; flex-wrap:wrap; align-items:center }
        .filters-row .form-group { margin-bottom:0 }
        .table-actions .btn { margin-right:6px }
    </style>
</head>
<body>
    <div class="app">
        <div class="app-wrap">
            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>
            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <div class="app-main" id="main">
                    <div class="container-fluid my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Students</h3>
                            <div>
                                <a href="new_student.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Student</a>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="filters-row">
                                    <div class="form-group">
                                        <label class="mb-0">Class</label>
                                        <select id="filter_class" class="form-control form-control-sm"><option value="">All classes</option></select>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-0">Section</label>
                                        <select id="filter_section" class="form-control form-control-sm"><option value="">All sections</option></select>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-0">Session</label>
                                        <select id="filter_session" class="form-control form-control-sm"><option value="">All sessions</option></select>
                                    </div>
                                    <div class="form-group" style="flex:1; min-width:220px;">
                                        <label class="mb-0">Search</label>
                                        <input id="filter_search" class="form-control form-control-sm" placeholder="Search by name, admission no, id">
                                    </div>
                                    <div class="ml-auto d-flex" style="gap:8px;">
                                        <button id="btnFilter" class="btn btn-sm btn-outline-primary">Filter</button>
                                        <button id="btnReset" class="btn btn-sm btn-outline-secondary">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="studentsTable">
                                        <thead class="thead-light"><tr><th>#</th><th>Photo</th><th>Admission</th><th>Name</th><th>Class</th><th>Section</th><th>Guardian</th><th>Status</th><th>Actions</th></tr></thead>
                                        <tbody id="studentsTbody"><tr><td colspan="8" class="text-muted">No students loaded.</td></tr></tbody>
                                    </table>
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
    $(function(){
        // load classes & sessions
        var classes = [];
        function loadClasses(){
            return fetch('../fees/list_classes.php').then(r=>r.json()).then(j=>{ if (!j.success) return; classes = j.data||[]; var $c = $('#filter_class'); $c.empty().append('<option value="">All classes</option>'); classes.forEach(function(cl){ $c.append('<option value="'+cl.id+'">'+(cl.class_name||'')+'</option>'); }); });
        }
        function loadSessions(){
            return fetch('../fees/get_current_session.php').then(r=>r.json()).then(j=>{ var $s = $('#filter_session'); $s.empty().append('<option value="">All sessions</option>'); if (j && j.success) $s.append('<option value="'+j.id+'">'+(j.name||'')+'</option>'); }).catch(()=>{});
        }

        // load students endpoint (to implement) - placeholder call to students list endpoint
        function loadStudents(){
            var params = { class_id: $('#filter_class').val(), section_id: $('#filter_section').val(), session_id: $('#filter_session').val(), q: $('#filter_search').val() };
            var qs = Object.keys(params).filter(k=>params[k]).map(k=>encodeURIComponent(k)+'='+encodeURIComponent(params[k])).join('&');
            var url = 'list_students.php' + (qs?('?'+qs):'');
            $('#studentsTbody').html('<tr><td colspan="8" class="text-muted">Loading...</td></tr>');
            fetch(url).then(r=>r.json()).then(j=>{
                var $b = $('#studentsTbody'); $b.empty();
                if (!j.success || !j.data || !j.data.length) { $b.append('<tr><td colspan="8" class="text-muted">No students found.</td></tr>'); return; }
                j.data.forEach(function(s,i){
                    var photo = s.photo || s.photo_path || '';
                    var thumb = photo ? '<img src="'+photo+'" style="width:48px;height:48px;object-fit:cover;border-radius:6px">' : '<img src="../../../../../public/assets/img/avtar/02.jpg" style="width:48px;height:48px;object-fit:cover;border-radius:6px">';
                    var actions = '<div class="table-actions">'+
                        '<a href="view_student.php?id='+s.id+'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a> '+
                        '<a href="student_documents.php?id='+s.id+'" class="btn btn-sm btn-outline-info" title="Documents"><i class="fas fa-file-alt"></i></a> '+
                        '<a href="edit_student.php?id='+s.id+'" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a> '+
                        '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="'+s.id+'" title="Delete"><i class="fas fa-trash"></i></button>'+
                        '</div>';
                    $b.append('<tr><td>'+(i+1)+'</td><td>'+thumb+'</td><td>'+(s.admission_no||'') +'</td><td>'+ (s.first_name+' '+(s.last_name||'')) +'</td><td>'+(s.class_name||'')+'</td><td>'+(s.section_name||'')+'</td><td>'+(s.guardian_name||'')+'</td><td>'+(s.status==1?'Active':'Inactive')+'</td><td>'+actions+'</td></tr>');
                });
            }).catch(err=>{ console.error(err); $('#studentsTbody').html('<tr><td colspan="8" class="text-danger">Error loading students</td></tr>'); });
        }

        // when class filter changes populate section filter
        $('#filter_class').on('change', function(){ var cid = $(this).val(); var $s = $('#filter_section'); $s.empty().append('<option value="">All sections</option>'); if (!cid) return; var cl = classes.find(c=>String(c.id)===String(cid)); if (!cl) return; (cl.sections||[]).forEach(function(sec){ $s.append('<option value="'+sec.id+'">'+(sec.section_name||'')+'</option>'); }); });

        $('#btnFilter').on('click', loadStudents);
        $('#btnReset').on('click', function(){ $('#filter_class').val(''); $('#filter_section').val(''); $('#filter_session').val(''); $('#filter_search').val(''); loadStudents(); });

        // delete handler (placeholder, needs endpoint)
        $('#studentsTbody').on('click', '.btn-delete', function(){ if (!confirm('Delete this student?')) return; var id = $(this).data('id'); fetch('delete_student.php', { method:'POST', body: new URLSearchParams({ id: id }) }).then(r=>r.json()).then(j=>{ if (j.success) loadStudents(); else alert(j.message||'Delete failed'); }).catch(()=>alert('Delete failed')); });

        // initial loads
        loadClasses().then(function(){ return loadSessions(); }).then(function(){ loadStudents(); });
    });
    </script>
</body>
</html>
