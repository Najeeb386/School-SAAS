<?php
/**
 * Examination Schedule / Datesheet Display
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Examination Schedule / Datesheet</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Examination Schedule and Datesheet" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
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
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->

            <!-- begin app-header -->
            <header class="app-header top-bar">
                <!-- begin navbar -->
                <!-- end navbar -->
            </header>
            <!-- end app-header -->

            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-navbar -->
                <!-- end app-navbar -->

                <!-- begin app-main -->
                <div class="" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="mb-0" style="color: #000; font-weight: 700;">Examination Schedule / Datesheet</h3>
                                    <div>
                                        <button class="btn btn-primary btn-sm" onclick="printDatesheet()">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="downloadDatesheet()">
                                            <i class="fa fa-download"></i> Download PDF
                                        </button>
                                        <a href="./assign_to_class.php" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-arrow-left"></i> Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datesheet Display -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card" id="datesheetCard" style="display: none;">
                                    <div class="card-body">
                                        <!-- Header Section -->
                                        <div class="text-center mb-4" id="datesheetHeader">
                                            <!-- School Name -->
                                            <h2 id="schoolName" style="color: #000; font-weight: 700; margin: 0; font-size: 24px;"></h2>

                                            <!-- Exam Name -->
                                            <h4 id="examName" style="color: #333; font-weight: 600; margin: 10px 0 0 0;">DATE SHEET FOR EXAMINATION - 2024</h4>

                                            <hr style="border-top: 2px solid #000; margin: 15px 0;">
                                        </div>

                                        <!-- Schedule Grid Table -->
                                        <div id="scheduleGridContainer" style="overflow-x: auto;">
                                            <!-- Schedule grid will be generated here -->
                                        </div>

                                        <!-- Footer Notes -->
                                        <div class="mt-4 pt-3 border-top">
                                            <p style="color: #666; font-size: 12px; margin-bottom: 5px;">
                                                <strong>Important Notes:</strong>
                                            </p>
                                            <ul style="color: #666; font-size: 11px; margin: 5px 0; padding-left: 20px;">
                                                <li>Students must report 15 minutes before the exam time.</li>
                                                <li>Entry will be closed 10 minutes after the exam starts.</li>
                                                <li>Students must carry their admit card and ID proof.</li>
                                                <li>Mobile phones are strictly prohibited in the examination hall.</li>
                                            </ul>
                                        </div>

                                        <!-- Signature Section -->
                                        <div class="row mt-4">
                                            <div class="col-md-4 text-center">
                                                <div style="height: 50px; border-top: 1px solid #000; margin-bottom: 5px;"></div>
                                                <p style="color: #000; font-weight: 600; font-size: 12px; margin: 0;">Principal Signature</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div style="height: 50px; border-top: 1px solid #000; margin-bottom: 5px;"></div>
                                                <p style="color: #000; font-weight: 600; font-size: 12px; margin: 0;">Exam Coordinator</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div style="height: 50px; border-top: 1px solid #000; margin-bottom: 5px;"></div>
                                                <p style="color: #000; font-weight: 600; font-size: 12px; margin: 0;">Date</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div class="card" id="emptyState">
                                    <div class="card-body text-center py-5">
                                        <i class="fa fa-calendar" style="font-size: 48px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                        <p style="color: #666; font-size: 16px;" id="emptyMessage">Loading datesheet...</p>
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
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../../public/assets/js/app.js"></script>

    <style>
    <style>
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .schedule-table thead {
            background-color: #f8f9fa;
        }

        .schedule-table th {
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
            color: #000;
            font-weight: 700;
            font-size: 13px;
        }

        .schedule-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            color: #333;
            font-size: 13px;
            vertical-align: top;
        }

        .schedule-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .schedule-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .schedule-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .exam-date {
            font-weight: 600;
            text-align: center;
        }

        .exam-day {
            text-align: center;
        }

        .subject-name {
            font-weight: 500;
        }

        .exam-time {
            text-align: center;
            font-weight: 600;
        }

        .marks-info {
            text-align: center;
        }

        @media print {
            .app-header,
            .app-navbar,
            .loader,
            .btn,
            .breadcrumb,
            #filterExam,
            .card > .card-body > .row:first-child {
                display: none !important;
            }

            .schedule-table {
                page-break-inside: avoid;
            }

            body {
                background: white;
            }

            .card {
                border: none;
                box-shadow: none;
            }
        }
    </style>

    <script>
        let allScheduleData = [];
        let selectedExamId = null;

        // Load page
        document.addEventListener('DOMContentLoaded', function () {
            // Get exam_id from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            selectedExamId = urlParams.get('exam_id');

            if (!selectedExamId) {
                document.getElementById('emptyMessage').textContent = 'No exam selected. Please go back and select an exam.';
                document.getElementById('datesheetCard').style.display = 'none';
                document.getElementById('emptyState').style.display = 'block';
                return;
            }

            loadDatesheet();

            setTimeout(function () {
                var loader = document.querySelector('.loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 500);
        });

        /**
         * Load schedule data for the exam
         */
        function loadDatesheet() {
            fetch('./manage_exam_assignments.php?action=get_assignments&exam_id=' + selectedExamId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        allScheduleData = data.data;
                        displayDatesheet();
                    } else {
                        document.getElementById('emptyMessage').textContent = 'No schedule data available for this exam.';
                        document.getElementById('datesheetCard').style.display = 'none';
                        document.getElementById('emptyState').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading schedule:', error);
                    document.getElementById('emptyMessage').textContent = 'Error loading schedule. Please try again.';
                    document.getElementById('datesheetCard').style.display = 'none';
                    document.getElementById('emptyState').style.display = 'block';
                });
        }

        /**
         * Display the datesheet in grid format (dates × classes matrix)
         */
        function displayDatesheet() {
            if (allScheduleData.length === 0) {
                return;
            }

            // Get exam info from first record
            const examInfo = allScheduleData[0];

            // Update header information
            document.getElementById('schoolName').textContent = 'SHRI RAM GLOBAL SCHOOL';
            document.getElementById('examName').textContent = 'DATE SHEET FOR ' + (examInfo.exam_name || 'EXAMINATION') + ' - 2024';

            // Get unique dates and classes
            const uniqueDates = [...new Set(allScheduleData.map(item => item.exam_date))].sort();
            const uniqueClasses = [...new Set(allScheduleData.map(item => item.class_name))].sort();

            // Create grid data structure: { date: { class: [{subject, time}] } }
            const gridData = {};
            uniqueDates.forEach(date => {
                gridData[date] = {};
                uniqueClasses.forEach(className => {
                    gridData[date][className] = [];
                });
            });

            // Populate grid with schedule data
            allScheduleData.forEach(item => {
                if (!gridData[item.exam_date][item.class_name]) {
                    gridData[item.exam_date][item.class_name] = [];
                }
                gridData[item.exam_date][item.class_name].push({
                    subject: item.subject_name,
                    time: item.exam_time,
                    marks: item.total_marks,
                    passing_marks: item.passing_marks
                });
            });

            // Generate grid table
            generateGridTable(gridData, uniqueDates, uniqueClasses);

            document.getElementById('datesheetCard').style.display = 'block';
            document.getElementById('emptyState').style.display = 'none';
        }

        /**
         * Generate grid table HTML
         */
        function generateGridTable(gridData, dates, classes) {
            let html = `
                <div style="overflow-x: auto;">
                    <table class="schedule-table" style="border-collapse: collapse; width: 100%; margin: 20px 0; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="border: 1px solid #000; padding: 12px; font-weight: 700; width: 120px; text-align: center;">DATE</th>
            `;

            // Add class columns
            classes.forEach(className => {
                html += `<th style="border: 1px solid #000; padding: 12px; font-weight: 700; text-align: center; min-width: 150px;">${className}</th>`;
            });

            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;

            // Add rows for each date
            dates.forEach(date => {
                const dateObj = new Date(date);
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
                const formattedDate = dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: '2-digit' });
                const dateDisplay = formattedDate + ' (' + dayName + ')';

                html += `<tr style="height: 80px;">`;
                html += `<td style="border: 1px solid #000; padding: 10px; font-weight: 600; text-align: center; vertical-align: top;">${dateDisplay}</td>`;

                // Add cells for each class
                classes.forEach(className => {
                    const subjects = gridData[date][className] || [];
                    let cellContent = '';

                    subjects.forEach((item, index) => {
                        cellContent += `<div style="margin-bottom: 4px; font-size: 12px;">
                            <strong>${item.subject}</strong><br>
                            <small style="color: #666;">Time: ${item.time}</small>
                        </div>`;
                    });

                    html += `<td style="border: 1px solid #000; padding: 10px; vertical-align: top;">${cellContent || '-'}</td>`;
                });

                html += `</tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('scheduleGridContainer').innerHTML = html;
        }

        /**
         * Print datesheet
         */
        function printDatesheet() {
            if (!selectedExamId) {
                alert('Please go back and select an exam first');
                return;
            }
            window.print();
        }

        /**
         * Download datesheet as PDF
         */
        function downloadDatesheet() {
            if (!selectedExamId) {
                alert('Please go back and select an exam first');
                return;
            }
            alert('PDF download functionality will be implemented soon');
        }
    </script>
</body>

</html>
