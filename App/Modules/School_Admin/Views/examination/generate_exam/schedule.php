<?php
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';

// Get exam_id from URL - THIS IS THE ONLY INPUT
$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    die('
        <div style="text-align: center; padding: 50px; font-family: Arial;">
            <h2 style="color: red;">Error: Exam ID is required</h2>
            <p>Please access this page with: schedule.php?exam_id=2</p>
            <button style="color: blue; text-decoration: underline;" onclick="window.history.back()">Back</button>
        </div>
    ');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Examination Schedule / Datesheet</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Examination Schedule and Datesheet" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
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

            <header class="app-header top-bar"></header>

            <div class="app-container">
                <div class="" id="main">
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

                        <div class="row">
                            <div class="col-12">
                                <div class="card" id="datesheetCard" style="display: none;">
                                    <div class="card-body">
                                        <div class="text-center mb-4" id="datesheetHeader">
                                            <div class="mb-2" id="logoContainer" style="display: none;">
                                                <img id="schoolLogo" src="" alt="School Logo" style="max-width: 80px; max-height: 80px;" class="mb-2">
                                            </div>

                                            <h2 id="schoolName" style="color: #000; font-weight: 700; margin: 0; font-size: 24px;"></h2>
                                            <h4 id="examName" style="color: #333; font-weight: 600; margin: 10px 0 0 0;"></h4>
                                            <hr style="border-top: 2px solid #000; margin: 15px 0;">
                                        </div>

                                        <div id="scheduleGridContainer" style="overflow-x: auto;"></div>

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

                                <div class="card" id="emptyState">
                                    <div class="card-body text-center py-5">
                                        <i class="fa fa-calendar" style="font-size: 48px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                        <p style="color: #666; font-size: 16px;" id="emptyMessage">Loading datesheet...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../../public/assets/js/app.js"></script>

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

        @media print {
            .app-header,
            .app-navbar,
            .loader,
            .btn,
            .breadcrumb,
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
        const urlParams = new URLSearchParams(window.location.search);
        const examId = urlParams.get('exam_id');
        let datesheetData = null;

        document.addEventListener('DOMContentLoaded', function () {
            if (!examId) {
                document.getElementById('emptyMessage').textContent = 'Error: No exam ID provided in URL';
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

        function loadDatesheet() {
            fetch('./manage_exam_assignments.php?action=get_datesheet_data&exam_id=' + examId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        datesheetData = data.data;
                        displayDatesheet();
                    } else {
                        document.getElementById('emptyMessage').textContent = data.message || 'Failed to load datesheet data';
                        document.getElementById('datesheetCard').style.display = 'none';
                        document.getElementById('emptyState').style.display = 'block';
                    }
                })
                .catch(error => {
                    document.getElementById('emptyMessage').textContent = 'Error loading datesheet. Please try again.';
                    document.getElementById('datesheetCard').style.display = 'none';
                    document.getElementById('emptyState').style.display = 'block';
                });
        }

        function displayDatesheet() {
            if (!datesheetData) return;

            const school = datesheetData.school;
            const exam = datesheetData.exam;
            const scheduleData = datesheetData.schedule || [];

            if (!school || !exam) {
                document.getElementById('emptyMessage').textContent = 'Incomplete data received from server';
                return;
            }

            // Display school logo if available
            if (school.logo_path) {
                document.getElementById('logoContainer').style.display = 'block';
                document.getElementById('schoolLogo').src = school.logo_path;
            }

            // Set dynamic school name
            document.getElementById('schoolName').textContent = school.school_name || 'School Name';

            // Set dynamic exam name with academic year
            const startDate = new Date(exam.start_date);
            const year = startDate.getFullYear();
            const nextYear = year + 1;
            document.getElementById('examName').textContent = 
                'DATE SHEET FOR ' + (exam.exam_name || 'EXAMINATION') + ' - ' + year + '-' + (nextYear % 100);

            // Generate grid if we have schedule data
            if (scheduleData.length > 0) {
                const uniqueDates = [...new Set(scheduleData.map(item => item.exam_date))].sort();
                const uniqueClasses = [...new Set(scheduleData.map(item => item.class_name))].sort();

                const gridData = {};
                uniqueDates.forEach(date => {
                    gridData[date] = {};
                    uniqueClasses.forEach(className => {
                        gridData[date][className] = [];
                    });
                });

                scheduleData.forEach(item => {
                    if (!gridData[item.exam_date][item.class_name]) {
                        gridData[item.exam_date][item.class_name] = [];
                    }
                    gridData[item.exam_date][item.class_name].push({
                        subject: item.subject_name,
                        time: item.exam_time
                    });
                });

                generateGridTable(gridData, uniqueDates, uniqueClasses);
            } else {
                document.getElementById('scheduleGridContainer').innerHTML = '<p class="text-center text-muted">No schedule data available for this exam</p>';
            }

            document.getElementById('datesheetCard').style.display = 'block';
            document.getElementById('emptyState').style.display = 'none';
        }

        function generateGridTable(gridData, dates, classes) {
            let html = `
                <div style="overflow-x: auto;">
                    <table class="schedule-table" style="border-collapse: collapse; width: 100%; margin: 20px 0; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="border: 1px solid #000; padding: 12px; font-weight: 700; width: 120px; text-align: center;">DATE</th>
            `;

            classes.forEach(className => {
                html += `<th style="border: 1px solid #000; padding: 12px; font-weight: 700; text-align: center; min-width: 150px;">${escapeHtml(className)}</th>`;
            });

            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;

            dates.forEach(date => {
                const dateObj = new Date(date);
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
                const formattedDate = dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: '2-digit' });
                const dateDisplay = formattedDate + ' (' + dayName + ')';

                html += `<tr style="height: 80px;">`;
                html += `<td style="border: 1px solid #000; padding: 10px; font-weight: 600; text-align: center; vertical-align: top;">${escapeHtml(dateDisplay)}</td>`;

                classes.forEach(className => {
                    const subjects = gridData[date][className] || [];
                    let cellContent = '';

                    subjects.forEach((item) => {
                        cellContent += `<div style="margin-bottom: 4px; font-size: 12px;">
                            <strong>${escapeHtml(item.subject)}</strong><br>
                            <small style="color: #666;">Time: ${escapeHtml(item.time)}</small>
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

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function printDatesheet() {
            window.print();
        }

        function downloadDatesheet() {
            alert('PDF download functionality will be implemented soon');
        }
    </script>
</body>

</html>
