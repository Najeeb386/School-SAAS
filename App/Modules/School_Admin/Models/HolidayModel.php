<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class HolidayModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all holidays for a school
     */
    public function getHolidaysBySchool(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                title,
                description,
                event_type,
                day_of_week,
                start_date,
                end_date,
                applies_to,
                created_by,
                created_at,
                updated_at
            FROM school_holliday_calendar
            WHERE school_id = ?
            ORDER BY 
                CASE 
                    WHEN event_type = 'WEEKLY_OFF' THEN day_of_week
                    ELSE DAYOFYEAR(start_date)
                END ASC
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get holidays for current month
     */
    public function getHolidaysThisMonth(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM school_holliday_calendar
            WHERE school_id = ?
                AND (
                    (event_type != 'WEEKLY_OFF' AND MONTH(start_date) = MONTH(NOW()) AND YEAR(start_date) = YEAR(NOW()))
                    OR
                    (event_type = 'WEEKLY_OFF')
                )
        ");
        $stmt->execute([$school_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get holidays for current week
     */
    public function getHolidaysThisWeek(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM school_holliday_calendar
            WHERE school_id = ?
                AND (
                    (event_type != 'WEEKLY_OFF' AND WEEK(start_date) = WEEK(NOW()) AND YEAR(start_date) = YEAR(NOW()))
                    OR
                    (event_type = 'WEEKLY_OFF')
                )
        ");
        $stmt->execute([$school_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get all holidays for this session
     */
    public function getHolidaysThisSession(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM school_holliday_calendar
            WHERE school_id = ?
        ");
        $stmt->execute([$school_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Add new holiday
     */
    public function addHoliday(int $school_id, string $title, string $event_type, ?string $description = null, ?int $day_of_week = null, ?string $start_date = null, ?string $end_date = null, string $applies_to = 'ALL', ?int $created_by = null) {
        $stmt = $this->db->prepare("
            INSERT INTO school_holliday_calendar 
            (school_id, title, event_type, description, day_of_week, start_date, end_date, applies_to, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        return $stmt->execute([
            $school_id,
            $title,
            $event_type,
            $description,
            $day_of_week,
            $start_date,
            $end_date,
            $applies_to,
            $created_by
        ]);
    }

    /**
     * Update holiday
     */
    public function updateHoliday(int $id, int $school_id, string $title, string $event_type, ?string $description = null, ?int $day_of_week = null, ?string $start_date = null, ?string $end_date = null, string $applies_to = 'ALL') {
        $stmt = $this->db->prepare("
            UPDATE school_holliday_calendar 
            SET title = ?, event_type = ?, description = ?, day_of_week = ?, start_date = ?, end_date = ?, applies_to = ?, updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");
        
        return $stmt->execute([
            $title,
            $event_type,
            $description,
            $day_of_week,
            $start_date,
            $end_date,
            $applies_to,
            $id,
            $school_id
        ]);
    }

    /**
     * Delete holiday
     */
    public function deleteHoliday(int $id, int $school_id) {
        $stmt = $this->db->prepare("
            DELETE FROM school_holliday_calendar 
            WHERE id = ? AND school_id = ?
        ");
        
        return $stmt->execute([$id, $school_id]);
    }

    /**
     * Get single holiday
     */
    public function getHolidayById(int $id, int $school_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM school_holliday_calendar 
            WHERE id = ? AND school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
