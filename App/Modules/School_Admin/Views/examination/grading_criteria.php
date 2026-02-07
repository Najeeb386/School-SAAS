<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>School Admin Dashboard</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School Admin Dashboard - Manage your school" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
</head>

<body>
    <!-- begin app -->
    <div class="app">
        <!-- begin app-wrap -->
        <div class="app-wrap">
            <!-- begin pre-loader -->
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->
            <!-- begin app-header -->
            <header class="app-header top-bar">
                <!-- begin navbar -->
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
                <!-- end navbar -->
            </header>
            <!-- end app-header -->
            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-navbar -->
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <!-- end app-navbar -->
                <!-- begin app-main -->
                <div class="app-main" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="mb-0">Grading Criteria</h3>
                                    <button class="btn btn-primary" onclick="openGradingCriteriaModal()">
                                        <i class="fa fa-plus"></i> Add New Grade
                                    </button>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Grading Criteria</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-2">School Grading Scale</h5>
                                        <p class="text-muted" style="font-size: 12px; margin-bottom: 20px; color: #666;">
                                            <i class="fa fa-info-circle"></i> Grades are sorted from highest to lowest (by minimum percentage)
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered" id="gradingCriteriaTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Grade</th>
                                                        <th>Min %</th>
                                                        <th>Max %</th>
                                                        <th>GPA</th>
                                                        <th>Remarks</th>
                                                        <th>Pass Status</th>
                                                        <th>Grading System</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="gradingCriteriaBody">
                                                    <!-- Will be populated by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="text-muted text-center mt-3" id="noDataMsg">Loading grading criteria...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>
            <!-- end app-container -->
            <!-- begin footer -->
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
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- Grading Criteria Modal -->
    <div class="modal fade" id="gradingCriteriaModal" tabindex="-1" role="dialog" aria-labelledby="gradingCriteriaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="gradingCriteriaModalLabel">
                        <i class="fa fa-graduation-cap"></i> Add/Edit Grading Criteria
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gradingCriteriaForm" class="needs-validation">
                        <input type="hidden" id="criteriaId" name="id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gradeName" class="form-label fw-bold" style="color: #000; font-size: 15px;">Grade Name</label>
                                <input type="text" class="form-control form-control-lg" id="gradeName" name="grade_name" 
                                       placeholder="e.g., A+, A, B, C, D, F" required style="border: 2px solid #e9ecef; color: #000;">
                                <small class="text-muted" style="color: #666; font-size: 12px;">Enter the grade symbol (e.g., A+, A, B, C, D, F)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gradingSystem" class="form-label fw-bold" style="color: #000; font-size: 15px;">Grading System</label>
                                <select class="form-select form-control-lg" id="gradingSystem" name="grading_system" 
                                        onchange="toggleGradeInputs()" required style="border: 2px solid #e9ecef; color: #000;">
                                    <option value="">Select Grading System</option>
                                    <option value="percentage">Percentage Based</option>
                                    <option value="gpa">GPA Based</option>
                                    <option value="both">Both (Percentage & GPA)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="minPercentage" class="form-label fw-bold" style="color: #000; font-size: 15px;">Minimum Percentage</label>
                                <input type="number" class="form-control form-control-lg" id="minPercentage" name="min_percentage" 
                                       min="0" max="100" step="0.01" placeholder="0" required style="border: 2px solid #e9ecef; color: #000;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="maxPercentage" class="form-label fw-bold" style="color: #000; font-size: 15px;">Maximum Percentage</label>
                                <input type="number" class="form-control form-control-lg" id="maxPercentage" name="max_percentage" 
                                       min="0" max="100" step="0.01" placeholder="100" required style="border: 2px solid #e9ecef; color: #000;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gpa" class="form-label fw-bold" style="color: #000; font-size: 15px;">GPA <span class="text-muted">(Optional)</span></label>
                                <input type="number" class="form-control form-control-lg" id="gpa" name="gpa" 
                                       min="0" max="4" step="0.01" placeholder="e.g., 4.0" style="border: 2px solid #e9ecef; color: #000;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="remarks" class="form-label fw-bold" style="color: #000; font-size: 15px;">Remarks <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control form-control-lg" id="remarks" name="remarks" 
                                       placeholder="e.g., Excellent, Good, Pass, Fail" style="border: 2px solid #e9ecef; color: #000;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch" style="padding-top: 40px;">
                                    <input class="form-check-input" type="checkbox" id="isPass" name="is_pass" value="1" checked>
                                    <label class="form-check-label fw-bold" for="isPass" style="color: #000; font-size: 15px; margin-left: 8px;">Passing Grade</label>
                                    <small class="d-block text-muted mt-1" style="color: #666; font-size: 12px;">Check if this is a passing grade</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch" style="padding-top: 40px;">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                                    <label class="form-check-label fw-bold" for="status" style="color: #000; font-size: 15px; margin-left: 8px;">Active Status</label>
                                    <small class="d-block text-muted mt-1" style="color: #666; font-size: 12px;">Check to keep this grade active</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveGradingCriteria()">
                        <i class="fa fa-save"></i> Save Grade
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <style>
        .table-responsive {
            border-radius: 0.25rem;
        }

        #gradingCriteriaTable {
            color: #000;
        }

        #gradingCriteriaTable thead {
            background-color: #f8f9fa;
            color: #000;
            font-weight: 600;
        }

        #gradingCriteriaTable thead th {
            color: #000;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6;
        }

        #gradingCriteriaTable tbody td {
            color: #000;
            font-weight: 500;
            vertical-align: middle;
        }

        #gradingCriteriaTable tbody strong {
            color: #000;
            font-weight: 700;
        }

        .badge-passing {
            background-color: #28a745;
            color: #fff;
            font-weight: 600;
        }

        .badge-failing {
            background-color: #dc3545;
            color: #fff;
            font-weight: 600;
        }

        .grading-system-percentage {
            background-color: #e7f3ff;
            color: #0056b3;
            font-weight: 600;
        }

        .grading-system-gpa {
            background-color: #f0f0f0;
            color: #333;
            font-weight: 600;
        }

        .grading-system-both {
            background-color: #fff3cd;
            color: #664d03;
            font-weight: 600;
        }

        #gradingCriteriaTable tbody tr {
            transition: background-color 0.2s;
        }

        #gradingCriteriaTable tbody tr:hover {
            background-color: #e8f4f8;
            color: #000;
        }

        .form-control, .form-select {
            color: #000 !important;
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-label {
            color: #000;
            font-weight: 700;
        }

        .modal-body {
            color: #000;
        }

        .card-title {
            color: #000;
            font-weight: 700;
        }

        .text-muted {
            color: #666 !important;
        }
    </style>

    <script>
        // Load grading criteria on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadGradingCriteria();
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 500);
        });

        /**
         * Load all grading criteria from the server
         */
        function loadGradingCriteria() {
            fetch('./manage_grading_criteria.php?action=get')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayGradingCriteria(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error loading grading criteria:', error);
                    document.getElementById('noDataMsg').innerHTML = 'Error loading grading criteria. Please refresh the page.';
                });
        }

        /**
         * Display grading criteria in table
         */
        function displayGradingCriteria(criteria) {
            const tbody = document.getElementById('gradingCriteriaBody');
            const noDataMsg = document.getElementById('noDataMsg');

            if (criteria.length === 0) {
                tbody.innerHTML = '';
                noDataMsg.style.display = 'block';
                noDataMsg.innerHTML = 'No grading criteria found. <a href="#" onclick="openGradingCriteriaModal(); return false;">Add one now</a>';
                return;
            }

            noDataMsg.style.display = 'none';
            tbody.innerHTML = '';

            criteria.forEach(item => {
                const row = document.createElement('tr');
                const passClass = item.is_pass ? 'badge-passing' : 'badge-failing';
                const passText = item.is_pass ? 'Passing' : 'Failing';
                const systemClass = 'grading-system-' + item.grading_system;
                const statusBadge = item.status ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-secondary">Inactive</span>';

                row.innerHTML = `
                    <td><strong>${escapeHtml(item.grade_name)}</strong></td>
                    <td>${parseFloat(item.min_percentage).toFixed(2)}%</td>
                    <td>${parseFloat(item.max_percentage).toFixed(2)}%</td>
                    <td>${item.gpa ? parseFloat(item.gpa).toFixed(2) : '-'}</td>
                    <td>${item.remarks ? escapeHtml(item.remarks) : '-'}</td>
                    <td><span class="badge ${passClass}">${passText}</span></td>
                    <td>
                        <span class="badge ${systemClass}">
                            ${item.grading_system.charAt(0).toUpperCase() + item.grading_system.slice(1)}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editGradingCriteria(${item.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteGradingCriteria(${item.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        /**
         * Open modal for adding new grading criteria
         */
        function openGradingCriteriaModal() {
            document.getElementById('criteriaId').value = '';
            document.getElementById('gradingCriteriaForm').reset();
            document.getElementById('gradingCriteriaModalLabel').innerHTML = 
                '<i class="fa fa-graduation-cap"></i> Add New Grading Criteria';
            
            const modal = new bootstrap.Modal(document.getElementById('gradingCriteriaModal'));
            modal.show();
        }

        /**
         * Edit existing grading criteria
         */
        function editGradingCriteria(id) {
            fetch('./manage_grading_criteria.php?action=get')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const criteria = data.data.find(item => item.id == id);
                        if (criteria) {
                            document.getElementById('criteriaId').value = criteria.id;
                            document.getElementById('gradeName').value = criteria.grade_name;
                            document.getElementById('minPercentage').value = criteria.min_percentage;
                            document.getElementById('maxPercentage').value = criteria.max_percentage;
                            document.getElementById('gpa').value = criteria.gpa || '';
                            document.getElementById('remarks').value = criteria.remarks || '';
                            document.getElementById('gradingSystem').value = criteria.grading_system;
                            document.getElementById('isPass').checked = parseInt(criteria.is_pass) === 1;
                            document.getElementById('status').checked = parseInt(criteria.status) === 1;
                            
                            document.getElementById('gradingCriteriaModalLabel').innerHTML = 
                                '<i class="fa fa-graduation-cap"></i> Edit Grading Criteria';
                            
                            const modal = new bootstrap.Modal(document.getElementById('gradingCriteriaModal'));
                            modal.show();
                        }
                    }
                })
                .catch(error => {
                    alert('Error loading grading criteria details');
                    console.error('Error:', error);
                });
        }

        /**
         * Save grading criteria (both add and update)
         */
        function saveGradingCriteria() {
            const form = document.getElementById('gradingCriteriaForm');
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Convert checkboxes to 0/1
            data.is_pass = document.getElementById('isPass').checked ? 1 : 0;
            data.status = document.getElementById('status').checked ? 1 : 0;

            const criteriaId = document.getElementById('criteriaId').value;
            const action = criteriaId ? 'update' : 'add';
            const url = './manage_grading_criteria.php?action=' + action;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        // Reload the page after successful save
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        alert('Error: ' + (result.message || 'Unable to save grading criteria'));
                    }
                })
                .catch(error => {
                    alert('Error saving grading criteria: ' + error);
                    console.error('Error:', error);
                });
        }

        /**
         * Delete grading criteria with confirmation
         */
        function deleteGradingCriteria(id) {
            if (confirm('Are you sure you want to delete this grading criteria? This action cannot be undone.')) {
                const data = { id: id };

                fetch('./manage_grading_criteria.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert(result.message);
                            loadGradingCriteria();
                        } else {
                            alert('Error: ' + (result.message || 'Unable to delete grading criteria'));
                        }
                    })
                    .catch(error => {
                        alert('Error deleting grading criteria: ' + error);
                        console.error('Error:', error);
                    });
            }
        }

        /**
         * Toggle grade inputs based on grading system selection
         */
        function toggleGradeInputs() {
            const system = document.getElementById('gradingSystem').value;
            const percentageInputs = document.getElementById('minPercentage').parentElement;
            const gpaInput = document.getElementById('gpa').parentElement;

            // For now, always show both - but you can customize based on selection
            percentageInputs.style.display = 'block';
            gpaInput.style.display = 'block';
        }

        /**
         * Escape HTML special characters
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>

</html>
