<?php
/**
 * School Admin - Holidays Management
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../Controllers/HolidayController.php';
require_once __DIR__ . '/../../../Models/HolidayModel.php';

$school_id = $_SESSION['school_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$holidays = [];
$totalHolidays = 0;
$holidaysThisMonth = 0;
$holidaysThisWeek = 0;

if ($school_id) {
    require_once __DIR__ . '/../../../../../Core/database.php';
    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\HolidayController($db, (int)$school_id);
    
    $holidays = $controller->getHolidays();
    $totalHolidays = $controller->getTotalHolidays();
    $holidaysThisMonth = $controller->getHolidaysThisMonth();
    $holidaysThisWeek = $controller->getHolidaysThisWeek();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Hollidays</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School Admin Dashboard - Manage your school" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <!-- begin app-container -->
            <div class="app-container">
              
                <!-- begin app-main -->
                <div class="" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-11">
                                <h3 class="mb-3 fw-bold" style="color: #000;">Holidays</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php" style="color: #007bff;">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #000;">Holidays</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-1 mt-3 text-end">
                                <button class="btn btn-success mb-2" onclick="openHolidayModal()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                                <button onclick="window.history.back()" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-primary shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fw-semibold" style="color: #000;">Total Holidays this session</p>
                                                <h5 class="fw-bold" style="color: #000;"><?php echo $totalHolidays; ?></h5>
                                            </div>
                                            <i class="fas fa-calendar fa-2x text-primary opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-success shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fw-semibold" style="color: #000;">Holidays this month</p>
                                                <h5 class="fw-bold" style="color: #000;"><?php echo $holidaysThisMonth; ?></h5>
                                            </div>
                                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-info shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fw-semibold" style="color: #000;">Holidays this week</p>
                                                <h5 class="fw-bold" style="color: #000;"><?php echo $holidaysThisWeek; ?></h5>
                                            </div>
                                            <i class="fas fa-calendar-week fa-2x text-info opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- stats end here  -->
                          <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light border-bottom-2">
                                        <h5 class="mb-0 fw-bold" style="color: #000;">Holiday List</h5>
                                    </div>

                                    <div class="card-body table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="color: #000; font-weight: 600;">#</th>
                                                    <th style="color: #000; font-weight: 600;">Title</th>
                                                    <th style="color: #000; font-weight: 600;">Type</th>
                                                    <th style="color: #000; font-weight: 600;">Date(s)</th>
                                                    <th style="color: #000; font-weight: 600;">Applies To</th>
                                                    <th style="color: #000; font-weight: 600;">Status</th>
                                                    <th width="120" style="color: #000; font-weight: 600;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="holidaysList">
                                                <?php if (empty($holidays)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4" style="color: #000;">
                                                            No holidays found. <a href="javascript:void(0)" onclick="openHolidayModal()" style="color: #007bff;">Add your first holiday</a>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($holidays as $index => $holiday): ?>
                                                        <tr>
                                                            <td style="color: #000; font-weight: 500;"><?php echo $index + 1; ?></td>
                                                            <td style="color: #000;"><strong><?php echo htmlspecialchars($holiday['title']); ?></strong></td>
                                                            <td>
                                                                <?php 
                                                                    $badge_colors = [
                                                                        'WEEKLY_OFF' => 'secondary',
                                                                        'HOLIDAY' => 'danger',
                                                                        'VACATION' => 'warning',
                                                                        'EVENT' => 'info'
                                                                    ];
                                                                    $color = $badge_colors[$holiday['event_type']] ?? 'secondary';
                                                                ?>
                                                                <span class="badge bg-<?php echo $color; ?> text-white"><?php echo \App\Modules\School_Admin\Controllers\HolidayController::formatEventType($holiday['event_type']); ?></span>
                                                            </td>
                                                            <td style="color: #000;">
                                                                <?php 
                                                                    if ($holiday['event_type'] === 'WEEKLY_OFF') {
                                                                        echo \App\Modules\School_Admin\Controllers\HolidayController::getDayName($holiday['day_of_week']);
                                                                    } else {
                                                                        $start = $holiday['start_date'] ? date('d M Y', strtotime($holiday['start_date'])) : '-';
                                                                        $end = $holiday['end_date'] ? date('d M Y', strtotime($holiday['end_date'])) : '-';
                                                                        echo $start !== '-' && $end !== '-' && $start !== $end ? $start . ' → ' . $end : $start;
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td style="color: #000;">
                                                                <span class="badge bg-light text-dark"><?php echo \App\Modules\School_Admin\Controllers\HolidayController::formatAppliesto($holiday['applies_to']); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success text-white">Active</span>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary" onclick="editHoliday(<?php echo $holiday['id']; ?>)" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteHoliday(<?php echo $holiday['id']; ?>)" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
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
            
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../../public/assets/js/app.js"></script>

    <!-- Holiday Management Scripts -->
    <script>
        function openHolidayModal() {
            document.getElementById('holidayForm').reset();
            document.getElementById('dayOfWeekContainer').style.display = 'none';
            document.getElementById('startDateContainer').style.display = 'none';
            document.getElementById('endDateContainer').style.display = 'none';
            const modal = new bootstrap.Modal(document.getElementById('holidayModal'));
            modal.show();
        }

        function toggleDayOfWeek() {
            const eventType = document.getElementById('holidayEventType').value;
            document.getElementById('dayOfWeekContainer').style.display = eventType === 'WEEKLY_OFF' ? 'block' : 'none';
            document.getElementById('startDateContainer').style.display = eventType === 'WEEKLY_OFF' ? 'none' : 'block';
            document.getElementById('endDateContainer').style.display = eventType === 'WEEKLY_OFF' ? 'none' : 'block';
        }

        function saveHoliday() {
            const title = document.getElementById('holidayTitle').value.trim();
            const eventType = document.getElementById('holidayEventType').value;
            const appliesto = document.getElementById('holidayAppliesto').value;
            const description = document.getElementById('holidayDescription').value.trim();
            
            if (!title) {
                alert('Please enter holiday title');
                return;
            }
            if (!eventType) {
                alert('Please select event type');
                return;
            }

            let dayOfWeek = null;
            let startDate = null;
            let endDate = null;

            if (eventType === 'WEEKLY_OFF') {
                dayOfWeek = document.getElementById('holidayDayOfWeek').value;
                if (!dayOfWeek) {
                    alert('Please select a day for weekly off');
                    return;
                }
            } else {
                startDate = document.getElementById('holidayStartDate').value;
                if (!startDate) {
                    alert('Please select start date');
                    return;
                }
                endDate = document.getElementById('holidayEndDate').value || startDate;
            }

            const payload = {
                action: 'add',
                title: title,
                event_type: eventType,
                day_of_week: dayOfWeek,
                start_date: startDate,
                end_date: endDate,
                applies_to: appliesto,
                description: description
            };

            fetch('manage_holidays.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Holiday added successfully');
                    const modalElement = document.getElementById('holidayModal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + (data.message || 'Unable to add holiday'));
                }
            })
            .catch(error => {
                alert('Error adding holiday');
            });
        }

        function editHoliday(holidayId) {
            alert('Edit functionality coming soon - Holiday ID: ' + holidayId);
        }

        function deleteHoliday(holidayId) {
            if (!confirm('Are you sure you want to delete this holiday?')) {
                return;
            }

            const payload = {
                id: holidayId
            };

            fetch('manage_holidays.php?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Holiday deleted successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + (data.message || 'Unable to delete holiday'));
                }
            })
            .catch(error => {
                alert('Error deleting holiday');
            });
        }
    </script>
</body>
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-primary shadow-lg">

            <div class="modal-header bg-light border-bottom-3">
                <h5 class="modal-title fw-bold" style="color: #000;">Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="holidayForm">

                    <div class="mb-4">
                        <label for="holidayTitle" class="form-label fw-semibold" style="color: #000;">Holiday Title <span class="text-danger">*</span></label>
                        <input type="text" id="holidayTitle" class="form-control form-control-lg" placeholder="e.g. Eid, Winter Break" style="color: #000; border: 2px solid #e9ecef;" required>
                    </div>

                    <div class="mb-4">
                        <label for="holidayEventType" class="form-label fw-semibold" style="color: #000;">Event Type <span class="text-danger">*</span></label>
                        <select id="holidayEventType" class="form-select form-select-lg" onchange="toggleDayOfWeek()" style="color: #000; border: 2px solid #e9ecef;" required>
                            <option value="">Select Type</option>
                            <option value="HOLIDAY">Holiday</option>
                            <option value="VACATION">Vacation</option>
                            <option value="WEEKLY_OFF">Weekly Off</option>
                            <option value="EVENT">Event</option>
                        </select>
                    </div>

                    <div id="dayOfWeekContainer" style="display: none;">
                        <div class="mb-4">
                            <label for="holidayDayOfWeek" class="form-label fw-semibold" style="color: #000;">Repeating Day <span class="text-danger">*</span></label>
                            <select id="holidayDayOfWeek" class="form-select form-select-lg" style="color: #000; border: 2px solid #e9ecef;">
                                <option value="">Select Day</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="7">Sunday</option>
                            </select>
                        </div>
                    </div>

                    <div id="startDateContainer" style="display: none;">
                        <div class="mb-4">
                            <label for="holidayStartDate" class="form-label fw-semibold" style="color: #000;">Start Date <span class="text-danger">*</span></label>
                            <input type="date" id="holidayStartDate" class="form-control form-control-lg" style="color: #000; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div id="endDateContainer" style="display: none;">
                        <div class="mb-4">
                            <label for="holidayEndDate" class="form-label fw-semibold" style="color: #000;">End Date</label>
                            <input type="date" id="holidayEndDate" class="form-control form-control-lg" style="color: #000; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="holidayAppliesto" class="form-label fw-semibold" style="color: #000;">Applies To <span class="text-danger">*</span></label>
                        <select id="holidayAppliesto" class="form-select form-select-lg" style="color: #000; border: 2px solid #e9ecef;" required>
                            <option value="">Select</option>
                            <option value="ALL">All</option>
                            <option value="STUDENTS">Students</option>
                            <option value="STAFF">Staff</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="holidayDescription" class="form-label fw-semibold" style="color: #000;">Description / Remarks</label>
                        <textarea id="holidayDescription" class="form-control form-control-lg" rows="3" placeholder="Optional notes" style="color: #000; border: 2px solid #e9ecef;"></textarea>
                    </div>

                </form>
            </div>

            <div class="modal-footer bg-light p-3 border-top">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg fw-semibold" onclick="saveHoliday()">
                    <i class="fas fa-save me-2"></i> Save Holiday
                </button>
            </div>

        </div>
    </div>
</div>


</html>
