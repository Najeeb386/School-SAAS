<?php
/**
 * API Endpoint to get holidays for attendance calendar
 * Returns holidays based on month, year, and applies_to filter
 */
header('Content-Type: application/json');
session_start();

try {
    if (empty($_SESSION['school_id'])) {
        throw new Exception('Unauthorized');
    }

    $school_id = $_SESSION['school_id'];
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $applies_to = $_GET['applies_to'] ?? 'ALL'; // ALL, STUDENTS, or STAFF
    
    require_once __DIR__ . '/../../../../Core/database.php';
    $db = \Database::connect();
    
    // Get holidays for this month/year
    $stmt = $db->prepare("
        SELECT 
            id,
            title,
            description,
            event_type,
            day_of_week,
            start_date,
            end_date,
            applies_to
        FROM school_holliday_calendar
        WHERE school_id = ?
            AND (applies_to = ? OR applies_to = 'ALL')
            AND (
                -- For WEEKLY_OFF: always included if it's the right applies_to
                (event_type = 'WEEKLY_OFF')
                OR
                -- For date-based events: check if they fall in this month
                (event_type != 'WEEKLY_OFF' 
                    AND (
                        (YEAR(start_date) = ? AND MONTH(start_date) = ?)
                        OR
                        (YEAR(end_date) = ? AND MONTH(end_date) = ?)
                        OR
                        (start_date <= DATE_FORMAT(?, '%Y-%m-01') AND end_date >= DATE_FORMAT(?, '%Y-%m-t') )
                    )
                )
            )
        ORDER BY start_date ASC, day_of_week ASC
    ");
    
    $stmt->execute([
        $school_id,
        $applies_to,
        $year,
        $month,
        $year,
        $month,
        "$year-$month-01",
        "$year-$month-01"
    ]);
    
    $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process holidays to calculate which dates they apply to
    $holidaysByDate = [];
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
    foreach ($holidays as $holiday) {
        if ($holiday['event_type'] === 'WEEKLY_OFF') {
            // Add to every occurrence of this day in the month
            $targetDay = (int)$holiday['day_of_week'];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = new DateTime("$year-$month-$day");
                // day_of_week: 1=Monday, 7=Sunday
                // PHP: 0=Sunday, 1=Monday...6=Saturday
                $phpDayOfWeek = $date->format('w');
                $phpDayOfWeek = $phpDayOfWeek === '0' ? 7 : (int)$phpDayOfWeek;
                
                if ($phpDayOfWeek === $targetDay) {
                    $dateStr = $date->format('Y-m-d');
                    if (!isset($holidaysByDate[$dateStr])) {
                        $holidaysByDate[$dateStr] = [];
                    }
                    $holidaysByDate[$dateStr][] = $holiday;
                }
            }
        } else {
            // Date-based holiday: add for each day in the range
            $startDate = new DateTime($holiday['start_date']);
            $endDate = new DateTime($holiday['end_date']);
            
            // Only process if within the requested month
            $currentDate = $startDate;
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                // Only include if within the month
                if ($currentDate->format('Y-m') === "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT)) {
                    if (!isset($holidaysByDate[$dateStr])) {
                        $holidaysByDate[$dateStr] = [];
                    }
                    $holidaysByDate[$dateStr][] = $holiday;
                }
                $currentDate->modify('+1 day');
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $holidaysByDate,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
