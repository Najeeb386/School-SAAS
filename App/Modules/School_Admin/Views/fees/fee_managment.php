<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    echo 'Unauthorized'; exit;
}
?><!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Fees Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background:#f6f7fb; }
        .card-hero { border-radius:10px; }
        .muted-small { color:#6c757d; font-size:0.95rem }
        .left-col { max-width:420px; }
        .table-sm th, .table-sm td { vertical-align: middle }
    </style>
</head>
<body>
<div class="container-fluid my-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Fees Management</h3>
        <a href="fees.php" class="btn btn-secondary"><i class="fas fa-chevron-left"></i> Back</a>
    </div>

    <!-- Global quick actions (always visible) -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-primary m-1" id="btnAddItem">
                <i class="fas fa-plus"></i> Add Fee Item
            </button>
            <button class="btn btn-sm btn-success m-1" id="btnAssignFee">
                <i class="fas fa-project-diagram"></i> Assign Fee
            </button>
        </div>
    </div>

    <!-- Tabs for Categories / Fee Items / Assignments -->
    <ul class="nav nav-tabs mb-3" id="feesTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-categories" data-toggle="tab" href="#pane-categories" role="tab" aria-controls="pane-categories" aria-selected="true">
                <i class="fas fa-folder-open mr-1"></i>Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-items" data-toggle="tab" href="#pane-items" role="tab" aria-controls="pane-items" aria-selected="false">
                <i class="fas fa-list mr-1"></i>Fee Items
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-assign" data-toggle="tab" href="#pane-assign" role="tab" aria-controls="pane-assign" aria-selected="false">
                <i class="fas fa-Sitemap mr-1"></i>Fees Assigning
            </a>
        </li>
    </ul>

    <div class="tab-content" id="feesTabContent">
        <!-- Categories Tab -->
        <div class="tab-pane fade show active" id="pane-categories" role="tabpanel" aria-labelledby="tab-categories">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Fee Categories</strong>
                            <button class="btn btn-sm btn-primary" id="btnAddCategory"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light"><tr><th>#</th><th>Name</th><th>Actions</th></tr></thead>
                                    <tbody id="feeCategoriesList">
                                        <tr><td colspan="2" class="text-muted">No categories yet.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Items Tab -->
        <div class="tab-pane fade" id="pane-items" role="tabpanel" aria-labelledby="tab-items">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Fee Items</strong>
                    <div>
                        <button class="btn btn-sm btn-primary" id="newFeeItem"><i class="fas fa-plus"></i> New Item</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light"><tr><th>#</th><th>Item</th><th>Category</th><th>Amount</th><th>Recurring</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody id="feeItemsList">
                                <tr><td colspan="7" class="text-muted">No fee items created.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Tab -->
        <div class="tab-pane fade" id="pane-assign" role="tabpanel" aria-labelledby="tab-assign">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Fees Assigning</strong>
                        <small class="text-muted d-block">Map fees to classes / sections / students</small>
                    </div>
                    <div style="min-width:320px; display:flex; gap:8px; align-items:center;">
                        <input id="assign_search" class="form-control form-control-sm" placeholder="Search assignments..." style="max-width:220px;" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light"><tr><th>#</th><th>Fee Item</th><th>Assigned To</th><th>Amount</th><th>Session</th><th>Actions</th></tr></thead>
                            <tbody id="feeAssignmentsList">
                                <tr><td colspan="6" class="text-muted">No assignments yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modals -->
<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Fee Category</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <form id="formAddCategory" onsubmit="return false;">
                    <input type="hidden" id="cat_edit_id" value="">
                    <div class="form-group"><label>Name</label><input class="form-control" id="cat_name" required></div>
                    <div class="form-group"><label>Description</label><textarea class="form-control" id="cat_desc"></textarea></div>
                </form>
            </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveCategory" class="btn btn-primary">Save</button></div>
    </div>
  </div>
</div>

<!-- Add Fee Item Modal -->
<div class="modal fade" id="addFeeItemModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Fee Item</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <form id="formAddFeeItem" onsubmit="return false;">
                    <input type="hidden" id="fee_edit_id" value="">
                    <div class="form-group"><label>Title</label><input class="form-control" id="fee_title" required></div>
                    <div class="form-group"><label>Category</label><select id="fee_category" class="form-control"><option value="">-- choose --</option></select></div>
                    <div class="form-row"><div class="form-group col-md-6"><label>Amount</label><input class="form-control" id="fee_amount" type="number" step="0.01" required></div><div class="form-group col-md-6"><label>Billing Cycle</label><select id="fee_recurring" class="form-control"><option value="once_per_session">Once per session</option><option value="one_time">One-time</option><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select></div></div>
                </form>
            </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveFeeItem" class="btn btn-primary">Save Item</button></div>
    </div>
  </div>
</div>

<!-- Assign Fee Modal -->
<div class="modal fade" id="assignFeeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Assign Fee</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
                <form id="formAssignFee" onsubmit="return false;">
                    <input type="hidden" id="assign_session_id" value="<?php echo htmlspecialchars($_SESSION['current_session_id'] ?? $_SESSION['active_session_id'] ?? 0); ?>">
                    <input type="hidden" id="assign_edit_id" value="">
                    <div class="form-group"><label>Fee Item</label><select class="form-control" id="assign_fee_item"><option value="">-- choose --</option></select></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Assign To</label><select class="form-control" id="assign_to"><option value="class">Class</option><option value="section">Section</option><option value="student">Student</option></select></div>
                        <div class="form-group col-md-6"><label>Session</label><input type="text" class="form-control" id="assign_session_display" value="<?php echo htmlspecialchars($_SESSION['current_session_name'] ?? $_SESSION['active_session_name'] ?? ''); ?>" readonly></div>
                    </div>
                    <div class="form-group" id="assign_target_container"><label>Target</label><select class="form-control" id="assign_target"><option value="">-- choose --</option></select></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Amount (optional)</label><input class="form-control" id="assign_amount" type="number" step="0.01"></div>
                        <div class="form-group col-md-6"><label>Due Day</label><input class="form-control" id="assign_due_day" type="number" min="1" max="28" value="10"></div>
                    </div>
                </form>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveAssignment" class="btn btn-primary">Assign</button></div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
// Placeholder JS: will be wired to real endpoints later
$(function(){
    // show modals
    $('#btnAddCategory, #newFeeItem, #btnAddItem').on('click', function(){
        var target = $(this).attr('id');
        if (target === 'btnAddCategory') $('#addCategoryModal').modal('show');
        else $('#addFeeItemModal').modal('show');
    });

    // load categories and populate lists
    function loadCategories() {
        fetch('list_fee_categories.php').then(r=>r.json()).then(json=>{
            if (!json.success) return;
            var rows = json.data || [];
            var $list = $('#feeCategoriesList');
            $list.empty();
            var $select = $('#fee_category');
            $select.empty().append('<option value="">-- choose --</option>');
            if (!rows.length) {
                $list.append('<tr><td colspan="3" class="text-muted">No categories yet.</td></tr>');
            } else {
                rows.forEach(function(r,i){
                    var nameEnc = encodeURIComponent(r.name||'');
                    var descEnc = encodeURIComponent(r.description||'');
                    var row = '<tr>'+
                        '<td>'+(i+1)+'</td>'+
                        '<td>'+escapeHtml(r.name)+'</td>'+
                        '<td>'+
                          '<button class="btn btn-sm btn-secondary btn-edit-cat mr-1" data-id="'+r.id+'" data-name="'+nameEnc+'" data-desc="'+descEnc+'">Edit</button>'+
                          '<button class="btn btn-sm btn-danger btn-delete-cat" data-id="'+r.id+'">Delete</button>'+
                        '</td>'+
                      '</tr>';
                    $list.append(row);
                    $select.append('<option value="'+r.id+'">'+escapeHtml(r.name)+'</option>');
                });
            }
        }).catch(err=>console.error(err));
    }

    // escape helper
    function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    loadCategories();

    // save category
    $('#saveCategory').on('click', function(){
        var name = $('#cat_name').val().trim();
        if (!name) return alert('Enter category name');
        var fd = new FormData();
        fd.append('name', name);
        fd.append('description', $('#cat_desc').val());
        var editId = $('#cat_edit_id').val(); if (editId) fd.append('id', editId);
        fetch('save_fee_category.php', { method:'POST', body: fd })
            .then(r=>r.json()).then(j=>{
                if (j.success) {
                    $('#addCategoryModal').modal('hide');
                    $('#cat_name').val(''); $('#cat_desc').val(''); $('#cat_edit_id').val('');
                    loadCategories(); loadFeeItems();
                } else alert(j.message || 'Save failed');
            }).catch(e=>{ alert('Request failed'); console.error(e); });
    });

    // category edit / delete handlers
    $('#feeCategoriesList').on('click', '.btn-edit-cat', function(){
        var $btn = $(this);
        var id = $btn.data('id');
        var name = decodeURIComponent($btn.data('name')||'');
        var desc = decodeURIComponent($btn.data('desc')||'');
        $('#cat_edit_id').val(id); $('#cat_name').val(name); $('#cat_desc').val(desc); $('#addCategoryModal').modal('show');
    });
    $('#feeCategoriesList').on('click', '.btn-delete-cat', function(){
        if (!confirm('Delete this category? This cannot be undone.')) return;
        var id = $(this).data('id'); var fd = new FormData(); fd.append('action','delete'); fd.append('id', id);
        fetch('save_fee_category.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{ if (j.success) { loadCategories(); loadFeeItems(); } else alert(j.message||'Delete failed'); }).catch(e=>{ alert('Request failed'); console.error(e); });
    });

    // load fee items
    function loadFeeItems(){
        fetch('list_fee_items.php').then(r=>r.json()).then(json=>{
            if (!json.success) return;
            var rows = json.data || [];
            var $list = $('#feeItemsList'); $list.empty();
            if (!rows.length) $list.append('<tr><td colspan="7" class="text-muted">No fee items created.</td></tr>');
            else rows.forEach(function(r,i){
                var editBtn = '<button class="btn btn-sm btn-secondary btn-edit-fee mr-1" data-id="'+r.id+'" data-name="'+encodeURIComponent(r.name||'')+'" data-category="'+(r.category_id||'')+'" data-amount="'+r.amount+'" data-billing_cycle="'+(r.billing_cycle||'one_time')+'">Edit</button>';
                var delBtn = '<button class="btn btn-sm btn-danger btn-delete-fee" data-id="'+r.id+'">Delete</button>';
                $list.append('<tr><td>'+(i+1)+'</td><td>'+escapeHtml(r.name)+'</td><td>'+escapeHtml(r.category_name||'-')+'</td><td>'+parseFloat(r.amount).toFixed(2)+'</td><td>'+escapeHtml(r.billing_cycle)+'</td><td>'+(r.status==1?'active':'inactive')+'</td><td>'+editBtn+delBtn+'</td></tr>');
            });
        }).catch(console.error);
    }
    loadFeeItems();

    // load fee items for assign modal
    function loadAssignFeeItems() {
        fetch('list_fee_items.php').then(r=>r.json()).then(json=>{
            var $sel = $('#assign_fee_item'); $sel.empty().append('<option value="">-- choose --</option>');
            if (json.success && Array.isArray(json.data)) {
                json.data.forEach(function(it){ $sel.append('<option value="'+it.id+'">'+escapeHtml(it.name) + ' (' + (it.category_name||'-') + ')</option>'); });
            }
        }).catch(console.error);
    }

    // load targets (classes/sections). caches classes data
    var _classesCache = null;
    function loadAssignTargets(force){
        return new Promise(function(resolve, reject){
            if (_classesCache && !force) { populateTargets(); resolve(); return; }
            fetch('list_classes.php').then(r=>r.json()).then(json=>{
                if (!json.success) { resolve(); return; }
                _classesCache = json.data || [];
                populateTargets();
                resolve();
            }).catch(err=>{ console.error(err); resolve(); });
        });
    }

    function populateTargets(){
        var mode = $('#assign_to').val();
        var $t = $('#assign_target'); $t.empty().append('<option value="">-- choose --</option>');
        if (!Array.isArray(_classesCache)) return;
        if (mode === 'class') {
            _classesCache.forEach(function(c){ $t.append('<option value="'+c.id+'">'+escapeHtml(c.class_name || c.class_name || c.class_name) + '</option>'); });
        } else if (mode === 'section') {
            _classesCache.forEach(function(c){
                var secs = c.sections || [];
                if (secs.length) {
                    var group = document.createElement('optgroup'); group.label = c.class_name || '';
                    secs.forEach(function(s){ var opt = document.createElement('option'); opt.value = s.id; opt.text = s.section_name; group.appendChild(opt); });
                    $t.append(group);
                }
            });
        } else if (mode === 'student') {
            $t.append('<option value="0">(Select student from student list - not implemented)</option>');
        }
    }

    $('#assign_to').on('change', function(){ populateTargets(); });

    // open assign modal and prepare lists; fetch active session first
    $('#btnAssignFee, #btnAssignFeeTab').on('click', function(){
        // fetch current session
        fetch('get_current_session.php').then(r=>r.json()).then(js=>{
            if (js && js.success) {
                $('#assign_session_id').val(js.id);
                $('#assign_session_display').val(js.name || '');
            }
            // reset edit state
            $('#assign_edit_id').val(''); $('#assign_amount').val(''); $('#assign_due_day').val('10');
            loadAssignFeeItems();
            loadAssignTargets(true).then(function(){ $('#assignFeeModal').modal('show'); });
        }).catch(err=>{
            console.error(err);
            $('#assign_edit_id').val(''); $('#assign_amount').val(''); $('#assign_due_day').val('10');
            loadAssignFeeItems();
            loadAssignTargets(true).then(function(){ $('#assignFeeModal').modal('show'); });
        });
    });

    // save assignment
    $('#saveAssignment').on('click', function(){
        var fee = $('#assign_fee_item').val(); if (!fee) return alert('Choose fee item');
        var assign_to = $('#assign_to').val(); var target = $('#assign_target').val(); if (!target) return alert('Choose target');
        var fd = new FormData(); fd.append('fee_item_id', fee); fd.append('assign_to', assign_to); fd.append('target', target);
        fd.append('session_id', $('#assign_session_id').val()); fd.append('amount', $('#assign_amount').val()); fd.append('due_day', $('#assign_due_day').val());
        var editId = $('#assign_edit_id').val(); if (editId) fd.append('id', editId);
        fetch('save_fee_assignment.php', { method: 'POST', body: fd }).then(r=>r.json()).then(j=>{
            if (j.success) { $('#assignFeeModal').modal('hide'); $('#assign_edit_id').val(''); alert(editId ? 'Updated' : 'Assigned'); loadFeeAssignments(); }
            else alert(j.message||'Assign failed');
        }).catch(e=>{ alert('Request failed'); console.error(e); });
    });

    // load assignments list
    function loadFeeAssignments(){
        fetch('list_fee_assignments.php').then(r=>r.json()).then(json=>{
            var $list = $('#feeAssignmentsList'); $list.empty();
            if (!json.success) { $list.append('<tr><td colspan="6" class="text-muted">Failed to load assignments.</td></tr>'); return; }
            var rows = json.data || [];
            if (!rows.length) { $list.append('<tr><td colspan="6" class="text-muted">No assignments yet.</td></tr>'); return; }
            rows.forEach(function(r,i){
                var assignedTo = '-';
                if (r.student_id) assignedTo = 'Student #' + r.student_id;
                else if (r.section_id) assignedTo = (r.class_name? r.class_name + ' - ' : '') + (r.section_name || ('Section ' + r.section_id));
                else if (r.class_id) assignedTo = r.class_name || ('Class ' + r.class_id);
                var amount = r.amount !== null ? parseFloat(r.amount).toFixed(2) : (r.fee_item_default_amount ? parseFloat(r.fee_item_default_amount).toFixed(2) : '0.00');
                var session = r.session_name || ('#' + (r.session_id||''));
                var actions = '<button class="btn btn-sm btn-secondary btn-edit-assign mr-1" data-id="'+r.id+'" data-fee="'+(r.fee_item_id||'')+'" data-class="'+(r.class_id||'')+'" data-section="'+(r.section_id||'')+'" data-student="'+(r.student_id||'')+'" data-amount="'+(r.amount||'')+'" data-due="'+(r.due_day||'')+'" data-session="'+(r.session_id||'')+'" data-session-name="'+(r.session_name||'')+'">Edit</button>'+
                              '<button class="btn btn-sm btn-danger btn-delete-assign" data-id="'+r.id+'">Delete</button>';

                var searchText = (String(r.fee_item_name||'') + ' ' + assignedTo + ' ' + String(session) + ' ' + String(amount)).toLowerCase();
                $list.append('<tr data-search="'+encodeURIComponent(searchText)+'"><td>'+(i+1)+'</td><td>'+escapeHtml(r.fee_item_name||'')+'</td><td>'+escapeHtml(assignedTo)+'</td><td>'+amount+'</td><td>'+escapeHtml(session)+'</td><td>'+actions+'</td></tr>');
            });
            // apply current filter after rendering
            applyAssignmentFilter();
        }).catch(err=>{ console.error(err); $('#feeAssignmentsList').empty().append('<tr><td colspan="6" class="text-muted">Error loading assignments</td></tr>'); });
    }

    // initial load
    loadFeeAssignments();

    // search / filter for assignments
    function applyAssignmentFilter(){
        var q = ($('#assign_search').val() || '').trim().toLowerCase();
        var anyVisible = false;
        $('#feeAssignmentsList tr').each(function(){
            var ds = $(this).attr('data-search') || '';
            var text = ds ? decodeURIComponent(ds) : '';
            if (!q) { $(this).show(); anyVisible = true; return; }
            if (text.indexOf(q) !== -1) { $(this).show(); anyVisible = true; } else { $(this).hide(); }
        });
        if (!anyVisible) {
            $('#feeAssignmentsList').append('<tr class="no-match"><td colspan="6" class="text-muted">No matching assignments.</td></tr>');
        } else {
            $('#feeAssignmentsList tr.no-match').remove();
        }
    }

    $('#assign_search').on('input', function(){ applyAssignmentFilter(); });
    $('#refreshAssignments').on('click', function(){ loadFeeAssignments(); });

    // assignment edit / delete handlers
    $('#feeAssignmentsList').on('click', '.btn-delete-assign', function(){
        if (!confirm('Delete this assignment?')) return;
        var id = $(this).data('id'); var fd = new FormData(); fd.append('action','delete'); fd.append('id', id);
        fetch('save_fee_assignment.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{ if (j.success) loadFeeAssignments(); else alert(j.message||'Delete failed'); }).catch(e=>{ alert('Request failed'); console.error(e); });
    });

    $('#feeAssignmentsList').on('click', '.btn-edit-assign', function(){
        var $b = $(this);
        var id = $b.data('id');
        var fee = $b.data('fee');
        var cls = $b.data('class');
        var sec = $b.data('section');
        var stu = $b.data('student');
        var amt = $b.data('amount');
        var due = $b.data('due');
        var sess = $b.data('session');

        $('#assign_edit_id').val(id);
        $('#assign_fee_item').val(fee);
        if (sess) { $('#assign_session_id').val(sess); }
        var sessName = $b.data('session-name') || '';
        $('#assign_session_display').val(sessName);
        $('#assign_amount').val(amt || '');
        $('#assign_due_day').val(due || 10);

        // determine assign_to and target
        var targetVal = '';
        if (stu) { $('#assign_to').val('student'); targetVal = stu; }
        else if (sec) { $('#assign_to').val('section'); targetVal = sec; }
        else if (cls) { $('#assign_to').val('class'); targetVal = cls; }

        loadAssignTargets(true).then(function(){
            // set the target after populate
            if (targetVal) $('#assign_target').val(targetVal);
            $('#assignFeeModal').modal('show');
        });
    });

    // save fee item
    $('#saveFeeItem').on('click', function(){
        var title = $('#fee_title').val().trim(); if (!title) return alert('Enter title');
        var fd = new FormData();
        fd.append('name', title);
        fd.append('category_id', $('#fee_category').val());
        fd.append('amount', $('#fee_amount').val());
        fd.append('billing_cycle', $('#fee_recurring').val());
        var editId = $('#fee_edit_id').val(); if (editId) fd.append('id', editId);
        fetch('save_fee_item.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
            if (j.success){ $('#addFeeItemModal').modal('hide'); $('#fee_title').val(''); $('#fee_amount').val(''); $('#fee_edit_id').val(''); loadFeeItems(); }
            else alert(j.message||'Failed');
        }).catch(e=>{ alert('Request failed'); console.error(e); });
    });

    // fee item edit / delete
    $('#feeItemsList').on('click', '.btn-edit-fee', function(){
        var $b = $(this);
        var id = $b.data('id');
        var name = decodeURIComponent($b.data('name')||'');
        var cat = $b.data('category')||'';
        var amount = $b.data('amount')||'';
        var cycle = $b.data('billing_cycle')||'one_time';
        $('#fee_edit_id').val(id);
        $('#fee_title').val(name);
        $('#fee_category').val(cat);
        $('#fee_amount').val(amount);
        $('#fee_recurring').val(cycle);
        $('#addFeeItemModal').modal('show');
    });
    $('#feeItemsList').on('click', '.btn-delete-fee', function(){
        if (!confirm('Delete this fee item?')) return;
        var id = $(this).data('id'); var fd = new FormData(); fd.append('action','delete'); fd.append('id', id);
        fetch('save_fee_item.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{ if (j.success) loadFeeItems(); else alert(j.message||'Delete failed'); }).catch(e=>{ alert('Request failed'); console.error(e); });
    });
});
</script>
</body>
</html>
