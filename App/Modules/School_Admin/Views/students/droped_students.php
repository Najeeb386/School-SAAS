<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../Controllers/StudentController.php';
require_once __DIR__ . '/../../Models/Student.php';

$school_id = $_SESSION['school_id'] ?? null;
$ctrl = null;
$rows = [];
if ($school_id) {
	$ctrl = new \App\Controllers\StudentController((int)$school_id);
	$rows = $ctrl->listDropped();
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Dropped Students</title>
	<link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
	<link rel="stylesheet" href="../../../../../public/assets/css/vendors.css">
	<link rel="stylesheet" href="../../../../../public/assets/css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<style>.thumb{width:48px;height:48px;object-fit:cover;border-radius:6px}</style>
</head>
<body>
	<div class="app"><div class="app-wrap"><div class="app-container">
		<div class="container-fluid my-4">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div class="d-flex align-items-center">
					<button class="btn btn-light mr-3" onclick="history.back()"><i class="fas fa-arrow-left"></i> Back</button>
					<h3 class="mb-0">Dropped Students</h3>
				</div>
				<div class="d-flex align-items-center">
					<input id="table_search" class="form-control form-control-sm mr-2" placeholder="Search table..." style="width:220px">
					<select id="date_filter" class="form-control form-control-sm mr-2" style="width:150px">
						<option value="all">All dates</option>
						<option value="this_month">This month</option>
						<option value="this_year">This year</option>
						<option value="custom">Custom range</option>
					</select>
					<div id="custom_dates" style="display:none" class="d-flex">
						<input id="from_date" type="date" class="form-control form-control-sm mr-1">
						<input id="to_date" type="date" class="form-control form-control-sm">
					</div>
					<div class="ml-2"><button class="btn btn-danger" data-toggle="modal" data-target="#dropModal"><i class="fas fa-user-slash"></i> Drop by Admission No</button></div>
				</div>
			</div>

			<div class="card"><div class="card-body"><div class="table-responsive">
				<table id="dropped_table" class="table table-sm table-hover"><thead><tr><th>#</th><th>Admission</th><th>Name</th><th>Father</th><th>Father Contact</th><th>Class</th><th>Section</th><th>Guardian</th><th>Dropped At</th><th>Actions</th></tr></thead>
				<tbody>
				<?php if (empty($rows)) { ?>
					<tr><td colspan="8" class="text-muted">No dropped students.</td></tr>
				<?php } else {
					foreach ($rows as $i => $r) { ?>
						<tr>
							<td><?php echo $i+1; ?></td>
							<td><?php echo htmlspecialchars($r['admission_no']); ?></td>
							<td><?php echo htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')); ?></td>
							<td><?php echo htmlspecialchars($r['father_names'] ?? ''); ?></td>
							<td><?php echo htmlspecialchars($r['father_contact'] ?? ''); ?></td>
							<td><?php echo htmlspecialchars($r['class_name'] ?? ''); ?></td>
							<td><?php echo htmlspecialchars($r['section_name'] ?? ''); ?></td>
							<td><?php echo htmlspecialchars($r['guardian_name'] ?? ''); ?></td>
							<td><?php echo htmlspecialchars($r['updated_at'] ?? ''); ?></td>
							<td>
								<button class="btn btn-sm btn-success admitBtn" data-id="<?php echo $r['id']; ?>">Admit</button>
								<a class="btn btn-sm btn-primary" href="student_view.php?student_id=<?php echo $r['id']; ?>">View</a>
							</td>
						</tr>
				<?php }
				} ?>
				</tbody></table>
			</div></div></div>
		</div>
	</div></div></div>

	<!-- Drop Modal -->
	<div class="modal fade" id="dropModal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header"><h5 class="modal-title">Drop Student by Admission No</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
		  <div class="modal-body">
			<div class="form-group"><label>Admission Number</label><input id="modal_adm_no" class="form-control" placeholder="E.g. SCH-2025-000123"></div>
			<div id="drop_alert" style="display:none" class="alert"></div>
		  </div>
		  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancel</button><button id="doDrop" class="btn btn-danger">Drop Student</button></div>
		</div>
	  </div>
	</div>

	<script src="../../../../../public/assets/js/vendors.js"></script>
	<script>
;(function(){
	// Drop student modal logic
	document.getElementById('doDrop').addEventListener('click', function(){
		var btn = this; btn.disabled = true; btn.textContent = 'Processing...';
		var adm = document.getElementById('modal_adm_no').value || '';
		var form = new URLSearchParams(); form.append('admission_no', adm);
		fetch('drop_student.php', { method: 'POST', body: form }).then(r=>r.json()).then(function(j){
			btn.disabled = false; btn.textContent = 'Drop Student';
			var alert = document.getElementById('drop_alert'); alert.style.display = 'block';
			if (j.success) { alert.className = 'alert alert-success'; alert.textContent = j.message||'Dropped'; setTimeout(function(){ location.reload(); },800); }
			else { alert.className = 'alert alert-danger'; alert.textContent = j.message||'Failed'; }
		}).catch(function(err){ btn.disabled = false; btn.textContent = 'Drop Student'; var alert = document.getElementById('drop_alert'); alert.style.display='block'; alert.className='alert alert-danger'; alert.textContent='Request error'; });
	});

	// Search filter
	var search = document.getElementById('table_search');
	var table = document.getElementById('dropped_table');
	search.addEventListener('input', function(){
		var q = this.value.toLowerCase();
		Array.from(table.tBodies[0].rows).forEach(function(row){
			var text = row.textContent.toLowerCase();
			row.style.display = text.indexOf(q) === -1 ? 'none' : '';
		});
	});

	// Date filter handling
	var dateFilter = document.getElementById('date_filter');
	var customDates = document.getElementById('custom_dates');
	dateFilter.addEventListener('change', function(){
		customDates.style.display = this.value === 'custom' ? 'flex' : 'none';
		applyDateFilter();
	});
	document.getElementById('from_date').addEventListener('change', applyDateFilter);
	document.getElementById('to_date').addEventListener('change', applyDateFilter);

	function applyDateFilter(){
		var mode = dateFilter.value;
		var from = null, to = null;
		var now = new Date();
		if (mode === 'this_month'){
			from = new Date(now.getFullYear(), now.getMonth(), 1);
			to = new Date(now.getFullYear(), now.getMonth()+1, 0,23,59,59);
		} else if (mode === 'this_year'){
			from = new Date(now.getFullYear(),0,1);
			to = new Date(now.getFullYear(),11,31,23,59,59);
		} else if (mode === 'custom'){
			var f = document.getElementById('from_date').value;
			var t = document.getElementById('to_date').value;
			if (f) from = new Date(f);
			if (t) { to = new Date(t); to.setHours(23,59,59); }
		}
		Array.from(table.tBodies[0].rows).forEach(function(row){
			var dt = row.getAttribute('data-updated');
			if (!dt){ row.style.display = ''; return; }
			var d = new Date(dt);
			var show = true;
			if (from && d < from) show = false;
			if (to && d > to) show = false;
			row.style.display = show ? '' : 'none';
		});
	}

	// Admit button handlers
	Array.from(document.getElementsByClassName('admitBtn')).forEach(function(b){
		b.addEventListener('click', function(){
			var id = this.getAttribute('data-id');
			if (!confirm('Admit this student back?')) return;
			var btn = this; btn.disabled = true; btn.textContent = 'Processing...';
			var form = new URLSearchParams(); form.append('student_id', id);
			fetch('admit_student.php', { method: 'POST', body: form }).then(r=>r.json()).then(function(j){
				btn.disabled = false; btn.textContent = 'Admit';
				if (j.success) location.reload(); else alert(j.message || 'Failed to admit');
			}).catch(function(){ btn.disabled = false; btn.textContent = 'Admit'; alert('Request error'); });
		});
	});
})();

// mark data-updated attributes on rows for filtering
document.addEventListener('DOMContentLoaded', function(){
	var table = document.getElementById('dropped_table');
	if (!table) return;
	Array.from(table.tBodies[0].rows).forEach(function(row){
		var cell = row.cells[8]; // updated_at is in column index 8 (0-based)
		if (cell) {
			var txt = cell.textContent.trim();
			// try to parse dd-mm-yyyy or yyyy-mm-dd
			var iso = txt;
			// if contains '/', replace with '-'
			iso = iso.replace(/\//g,'-');
			row.setAttribute('data-updated', iso);
		}
	});
});

	</script>
</body>
</html>

