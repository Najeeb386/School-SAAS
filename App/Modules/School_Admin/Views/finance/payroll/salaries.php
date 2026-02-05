<?php
/**
 * Staff Salaries UI (no sidebar)
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Controllers/StaffSalaryController.php';
require_once __DIR__ . '/../../../Models/StaffSalaryModel.php';

use App\Modules\School_Admin\Controllers\StaffSalaryController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$ctrl = new StaffSalaryController($DB_con);
$salaries = $ctrl->list();

// Get unfinalised staff (employees and teachers without salary records)
$unfinalised_staff = $ctrl->getUnfinalisedStaff();

$editing = false;
$editRecord = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    if ($eid > 0) {
        $editRecord = $ctrl->get($eid);
        if ($editRecord) $editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Staff Salaries - Payroll</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <style>
        /* Page-specific layout overrides to use full width after removing sidebar */
        .app, .app-wrap, .app-container { padding-left: 0 !important; }
        .app-main { margin-left: 0 !important; width: 100% !important; }
        .container-fluid { max-width: 1400px; padding-left: 1.5rem; padding-right: 1.5rem; }
        body { overflow-x: hidden; color: #000; }
        @media (max-width: 768px) {
            .container-fluid { padding-left: 1rem; padding-right: 1rem; }
        }
        /* tighten table spacing on small screens */
        #salariesTable th, #salariesTable td { white-space: nowrap; color: #000; }
        #unfinalizedTable th, #unfinalizedTable td { white-space: nowrap; color: #000; }
        .table { color: #000; }
        .card-body, .card-title { color: #000; }
        h3, h5, h6 { color: #000; }
        
        /* Pagination Styling */
        .pagination { margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem; }
        .pagination .page-link { color: #007bff; border: 1px solid #dee2e6; padding: 0.5rem 0.75rem; }
        .pagination .page-link:hover { background-color: #e9ecef; }
        .pagination .page-item.active .page-link { background-color: #007bff; border-color: #007bff; color: white; }
        .pagination .page-item.disabled .page-link { color: #6c757d; pointer-events: none; }
        
        /* Print media query to hide Actions column */
        @media print {
            .no-print, #salariesTable th:last-child, #salariesTable td:last-child, .pagination { 
                display: none !important; 
            }
            body { margin: 0; padding: 0; }
            .app, .app-wrap { padding: 0 !important; }
            .container-fluid { max-width: 100%; padding: 0.5rem !important; }
            .card { border: 1px solid #ddd; }
            #salariesTable { font-size: 0.8rem; margin-top: 1rem; }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>

            <!-- main content full-width (no navbar, no sidebar) -->
            <div class="" id="main" style="width: 100%; margin-left: 0;">
                <div class="container-fluid" style="max-width: 100%;">
                    <div class="row mb-4">
                        <div class="col-11">
                            <h3 class="mb-3">Staff Salaries</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb p-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                    <li class="breadcrumb-item"><a href="../finance.php">Finance</a></li>
                                    <li class="breadcrumb-item"><a href="../payroll.php">Payrolls</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staff Salaries</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-1 mt-5 no-print"><button onclick="history.back()" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back</button></div>
                    </div>

                    <div class="row mb-3 no-print">
                        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
                            <div class="mb-2 mb-md-0">
                                <button id="btnAddSalary" class="btn btn-primary">Add Salary</button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Export
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" id="btnExportExcel">Export to Excel</a>
                                        <a class="dropdown-item" href="#" id="btnExportPdf">Export to PDF</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" id="btnPrint">Print</a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-inline flex-wrap">
                                <label class="mr-2 text-muted d-none d-md-inline">Filter:</label>
                                <select id="filterGrade" class="form-control form-control-sm mr-2 mb-2 mb-md-0" style="min-width: 120px;">
                                    <option value="all">All Grades</option>
                                    <option value="grade1">Grade 1</option>
                                    <option value="grade2">Grade 2</option>
                                </select>
                                <input id="searchInput" type="search" class="form-control form-control-sm mb-2 mb-md-0" placeholder="Search staff" style="min-width: 150px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Salaries List</h5>
                                    <div class="table-responsive">
                                        <table id="salariesTable" class="table table-striped table-hover" style="font-size: 0.875rem;">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 30px;">#</th>
                                                    <th style="min-width: 80px;">Staff ID</th>
                                                    <th style="min-width: 100px;">Name</th>
                                                    <th style="min-width: 80px;">Designation</th>
                                                    <th style="min-width: 80px;">Pay Grade</th>
                                                    <th style="min-width: 100px;">Basic Salary</th>
                                                    <th style="min-width: 100px;">Allowances</th>
                                                    <th style="min-width: 100px;">Deductions</th>
                                                    <th style="min-width: 100px;">Net Pay</th>
                                                    <th style="min-width: 120px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($salaries) && is_array($salaries)): ?>
                                                    <?php $i = 1; foreach ($salaries as $sal): ?>
                                                        <?php 
                                                            $net = $sal['basic_salary'] + $sal['allowance'] - $sal['deduction'];
                                                            $sid = isset($sal['id']) ? $sal['id'] : '';
                                                            $stype = isset($sal['staff_type']) ? $sal['staff_type'] : '';
                                                            $staff_id = isset($sal['staff_id']) ? $sal['staff_id'] : '';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo htmlspecialchars($stype . '-' . str_pad($staff_id, 3, '0', STR_PAD_LEFT)); ?></td>
                                                            <td><?php echo htmlspecialchars($sal['staff_name'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($sal['staff_role'] ?? '-'); ?></td>
                                                            <td>-</td>
                                                            <td><?php echo number_format($sal['basic_salary'], 2); ?></td>
                                                            <td><?php echo number_format($sal['allowance'], 2); ?></td>
                                                            <td><?php echo number_format($sal['deduction'], 2); ?></td>
                                                            <td><strong><?php echo number_format($net, 2); ?></strong></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary btn-edit-salary" 
                                                                    data-id="<?php echo $sid; ?>"
                                                                    data-staff-type="<?php echo $stype; ?>"
                                                                    data-staff-id="<?php echo $staff_id; ?>"
                                                                    data-basic="<?php echo $sal['basic_salary']; ?>"
                                                                    data-allowance="<?php echo $sal['allowance']; ?>"
                                                                    data-deduction="<?php echo $sal['deduction']; ?>"
                                                                    data-eff-from="<?php echo $sal['effective_from']; ?>"
                                                                    data-session-id="<?php echo $sal['session_id']; ?>">Edit</button>
                                                                <a href="view_salary.php?id=<?php echo $sid; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="10" class="text-center">No salary records found.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination for Salaries Table -->
                                    <nav aria-label="Salaries pagination">
                                        <ul class="pagination">
                                            <li class="page-item" id="salariesPrevBtn"><a class="page-link" href="#" id="salariesPrev">Previous</a></li>
                                            <li class="page-item active" id="salariesPageInfo"><span class="page-link">Page <span id="salariesCurrentPage">1</span></span></li>
                                            <li class="page-item" id="salariesNextBtn"><a class="page-link" href="#" id="salariesNext">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Salary Finalization</h5>
                                    <small class="text-muted">Staff members who don't have finalized salaries yet</small>
                                    <div class="table-responsive mt-3">
                                        <table id="unfinalizedTable" class="table table-striped table-hover" style="font-size: 0.875rem;">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 30px;">#</th>
                                                    <th style="min-width: 100px;">Staff Type</th>
                                                    <th style="min-width: 80px;">Staff ID</th>
                                                    <th style="min-width: 150px;">Name</th>
                                                    <th style="min-width: 100px;">Email</th>
                                                    <th style="min-width: 120px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($unfinalised_staff) && is_array($unfinalised_staff)): ?>
                                                    <?php $j = 1; foreach ($unfinalised_staff as $staff): ?>
                                                        <tr>
                                                            <td><?php echo $j++; ?></td>
                                                            <td><?php echo htmlspecialchars(ucfirst($staff['staff_type'])); ?></td>
                                                            <td><?php echo htmlspecialchars($staff['staff_type'] . '-' . str_pad($staff['staff_id'], 3, '0', STR_PAD_LEFT)); ?></td>
                                                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                                                            <td><?php echo htmlspecialchars($staff['email'] ?? '-'); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-success btn-add-pending-salary" 
                                                                    data-staff-type="<?php echo $staff['staff_type']; ?>"
                                                                    data-staff-id="<?php echo $staff['staff_id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($staff['name']); ?>">Add Salary</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="6" class="text-center text-muted">All staff have finalized salaries!</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination for Unfinalised Table -->
                                    <nav aria-label="Unfinalised pagination">
                                        <ul class="pagination">
                                            <li class="page-item" id="unfinalPrevBtn"><a class="page-link" href="#" id="unfinalPrev">Previous</a></li>
                                            <li class="page-item active" id="unfinalPageInfo"><span class="page-link">Page <span id="unfinalCurrentPage">1</span></span></li>
                                            <li class="page-item" id="unfinalNextBtn"><a class="page-link" href="#" id="unfinalNext">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="footer">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-left">
                        <p>&copy; Copyright 2019. All rights reserved.</p>
                    </div>
                    <div class="col col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="https://www.templateshub.net">Templates Hub</a></p>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- Add/Edit Salary Modal -->
    <div class="modal fade" id="salaryModal" tabindex="-1" role="dialog" aria-labelledby="salaryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="salaryModalLabel">Add Salary</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form method="post" action="save_salary.php" id="salaryForm">
            <div class="modal-body">
              <input type="hidden" name="id" id="modal_salary_id">
              <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
              
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="modal_staff_type">Staff Type</label>
                  <select class="form-control" id="modal_staff_type" name="staff_type" required>
                    <option value="">Select type</option>
                    <option value="teacher">Teacher</option>
                    <option value="employee">Employee</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="modal_staff_id">Staff ID</label>
                  <input type="number" class="form-control" id="modal_staff_id" name="staff_id" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="modal_session_id">Session</label>
                  <input type="number" class="form-control" id="modal_session_id" name="session_id" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="modal_eff_from">Effective From</label>
                  <input type="date" class="form-control" id="modal_eff_from" name="effective_from" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="modal_basic">Basic Salary</label>
                  <input type="number" class="form-control" id="modal_basic" name="basic_salary" step="0.01" required>
                </div>
                <div class="form-group col-md-4">
                  <label for="modal_allowance">Allowance</label>
                  <input type="number" class="form-control" id="modal_allowance" name="allowance" step="0.01" value="0">
                </div>
                <div class="form-group col-md-4">
                  <label for="modal_deduction">Deduction</label>
                  <input type="number" class="form-control" id="modal_deduction" name="deduction" step="0.01" value="0">
                </div>
              </div>

              <div class="alert alert-info">
                <strong>Net Salary:</strong> <span id="net_salary">0</span>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Salary</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        // Show modal when Add Salary is clicked
        document.getElementById('btnAddSalary').addEventListener('click', function(){
            document.getElementById('salaryForm').reset();
            document.getElementById('modal_salary_id').value = '';
            document.getElementById('salaryModalLabel').textContent = 'Add Salary';
            if (window.jQuery && typeof jQuery('#salaryModal').modal === 'function') {
                jQuery('#salaryModal').modal('show');
            }
        });

        // Edit buttons populate and show modal
        document.querySelectorAll('.btn-edit-salary').forEach(function(btn){
            btn.addEventListener('click', function(){
                var id = this.getAttribute('data-id');
                var stype = this.getAttribute('data-staff-type');
                var stid = this.getAttribute('data-staff-id');
                var basic = this.getAttribute('data-basic');
                var allowance = this.getAttribute('data-allowance');
                var deduction = this.getAttribute('data-deduction');
                var effrom = this.getAttribute('data-eff-from');
                var sessionid = this.getAttribute('data-session-id');

                document.getElementById('modal_salary_id').value = id;
                document.getElementById('modal_staff_type').value = stype;
                document.getElementById('modal_staff_id').value = stid;
                document.getElementById('modal_basic').value = basic;
                document.getElementById('modal_allowance').value = allowance;
                document.getElementById('modal_deduction').value = deduction;
                document.getElementById('modal_eff_from').value = effrom;
                document.getElementById('modal_session_id').value = sessionid;
                document.getElementById('salaryModalLabel').textContent = 'Edit Salary';

                updateNetSalary();

                if (window.jQuery && typeof jQuery('#salaryModal').modal === 'function') {
                    jQuery('#salaryModal').modal('show');
                }
            });
        });

        // Add Salary for unfinalised staff
        document.querySelectorAll('.btn-add-pending-salary').forEach(function(btn){
            btn.addEventListener('click', function(){
                var stype = this.getAttribute('data-staff-type');
                var stid = this.getAttribute('data-staff-id');
                var name = this.getAttribute('data-name');

                document.getElementById('modal_salary_id').value = '';
                document.getElementById('modal_staff_type').value = stype;
                document.getElementById('modal_staff_id').value = stid;
                document.getElementById('modal_basic').value = '';
                document.getElementById('modal_allowance').value = '0';
                document.getElementById('modal_deduction').value = '0';
                document.getElementById('modal_eff_from').value = new Date().toISOString().split('T')[0];
                document.getElementById('modal_session_id').value = '';
                document.getElementById('salaryModalLabel').textContent = 'Add Salary for ' + name;

                updateNetSalary();

                if (window.jQuery && typeof jQuery('#salaryModal').modal === 'function') {
                    jQuery('#salaryModal').modal('show');
                }
            });
        });

        // Calculate net salary in real-time
        function updateNetSalary(){
            var basic = parseFloat(document.getElementById('modal_basic').value) || 0;
            var allowance = parseFloat(document.getElementById('modal_allowance').value) || 0;
            var deduction = parseFloat(document.getElementById('modal_deduction').value) || 0;
            var net = basic + allowance - deduction;
            document.getElementById('net_salary').textContent = net.toFixed(2);
        }

        document.getElementById('modal_basic').addEventListener('input', updateNetSalary);
        document.getElementById('modal_allowance').addEventListener('input', updateNetSalary);
        document.getElementById('modal_deduction').addEventListener('input', updateNetSalary);

        // Search and filter
        document.getElementById('searchInput').addEventListener('input', function(e) {
            var q = e.target.value.toLowerCase();
            var rows = document.querySelectorAll('#salariesTable tbody tr');
            rows.forEach(function(r) {
                var text = r.innerText.toLowerCase();
                r.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
        });

        document.getElementById('filterGrade').addEventListener('change', function(e) {
            var val = e.target.value;
            var rows = document.querySelectorAll('#salariesTable tbody tr');
            if (val === 'all') { rows.forEach(r => r.style.display = ''); return; }
            rows.forEach(function(r) {
                var grade = r.cells[4].innerText.toLowerCase();
                r.style.display = grade.indexOf(val.replace('grade', 'grade ')) > -1 ? '' : 'none';
            });
        });

        // Export to Excel
        document.getElementById('btnExportExcel').addEventListener('click', function(e){
            e.preventDefault();
            var table = document.getElementById('salariesTable');
            var csv = [];
            
            // Get header (exclude last column - Actions)
            var headers = [];
            table.querySelectorAll('thead tr th').forEach(function(th, idx) {
                if (idx < table.querySelectorAll('thead tr th').length - 1) {
                    headers.push(th.innerText);
                }
            });
            csv.push(headers.join(','));
            
            // Get rows (exclude last column - Actions)
            table.querySelectorAll('tbody tr').forEach(function(tr) {
                if (tr.style.display !== 'none') {
                    var row = [];
                    tr.querySelectorAll('td').forEach(function(td, idx) {
                        if (idx < tr.querySelectorAll('td').length - 1) {
                            row.push('"' + td.innerText.replace(/"/g, '""') + '"');
                        }
                    });
                    if (row.length > 0) csv.push(row.join(','));
                }
            });
            
            var csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
            var link = document.createElement('a');
            link.setAttribute('href', csvContent);
            link.setAttribute('download', 'salaries_' + new Date().toISOString().split('T')[0] + '.csv');
            link.click();
        });

        // Export to PDF
        document.getElementById('btnExportPdf').addEventListener('click', function(e){
            e.preventDefault();
            // Load jsPDF and html2canvas libraries from CDN
            var script1 = document.createElement('script');
            script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script1.onload = function() {
                var element = document.getElementById('salariesTable').cloneNode(true);
                // Remove Actions column
                element.querySelectorAll('th:last-child, td:last-child').forEach(function(el) {
                    el.remove();
                });
                
                var opt = {
                    margin: 10,
                    filename: 'salaries_' + new Date().toISOString().split('T')[0] + '.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
                };
                
                html2pdf().set(opt).from(element).save();
            };
            document.head.appendChild(script1);
        });

        // Print
        document.getElementById('btnPrint').addEventListener('click', function(e){
            e.preventDefault();
            window.print();
        });

        // Hide loader on load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 300);

            // Initialize pagination
            initPagination();
        });

        // Pagination Configuration
        const ROWS_PER_PAGE = 5;

        function initPagination() {
            paginateTable('salariesTable', 'salariesPrev', 'salariesNext', 'salariesCurrentPage', 'salariesPrevBtn', 'salariesNextBtn');
            paginateTable('unfinalizedTable', 'unfinalPrev', 'unfinalNext', 'unfinalCurrentPage', 'unfinalPrevBtn', 'unfinalNextBtn');
        }

        function paginateTable(tableId, prevBtnId, nextBtnId, pageSpanId, prevContainerId, nextContainerId) {
            const table = document.getElementById(tableId);
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / ROWS_PER_PAGE);

            const showPage = (page) => {
                rows.forEach((row, idx) => {
                    const isInRange = idx >= (page - 1) * ROWS_PER_PAGE && idx < page * ROWS_PER_PAGE;
                    row.style.display = isInRange ? '' : 'none';
                });
                document.getElementById(pageSpanId).textContent = page;
                document.getElementById(prevContainerId).classList.toggle('disabled', page === 1);
                document.getElementById(nextContainerId).classList.toggle('disabled', page === totalPages);
            };

            document.getElementById(prevBtnId).addEventListener('click', (e) => {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                }
            });

            document.getElementById(nextBtnId).addEventListener('click', (e) => {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage++;
                    showPage(currentPage);
                }
            });

            showPage(currentPage);
        }
    </script>
</body>

</html>
