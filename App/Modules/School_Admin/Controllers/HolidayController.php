<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\HolidayModel;

class HolidayController {
    private HolidayModel $model;
    private int $school_id;

    public function __construct($db, int $school_id) {
        $this->model = new HolidayModel($db);
        $this->school_id = $school_id;
    }

    /**
     * Get all holidays
     */
    public function getHolidays() {
        return $this->model->getHolidaysBySchool($this->school_id);
    }

    /**
     * Get holidays this month
     */
    public function getHolidaysThisMonth() {
        return $this->model->getHolidaysThisMonth($this->school_id);
    }

    /**
     * Get holidays this week
     */
    public function getHolidaysThisWeek() {
        return $this->model->getHolidaysThisWeek($this->school_id);
    }

    /**
     * Get total holidays this session
     */
    public function getTotalHolidays() {
        return $this->model->getHolidaysThisSession($this->school_id);
    }

    /**
     * Add new holiday
     */
    public function addHoliday($data) {
        return $this->model->addHoliday(
            $this->school_id,
            $data['title'] ?? null,
            $data['event_type'] ?? null,
            $data['description'] ?? null,
            $data['day_of_week'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['applies_to'] ?? 'ALL',
            $data['created_by'] ?? null
        );
    }

    /**
     * Update holiday
     */
    public function updateHoliday(int $id, $data) {
        return $this->model->updateHoliday(
            $id,
            $this->school_id,
            $data['title'] ?? null,
            $data['event_type'] ?? null,
            $data['description'] ?? null,
            $data['day_of_week'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['applies_to'] ?? 'ALL'
        );
    }

    /**
     * Delete holiday
     */
    public function deleteHoliday(int $id) {
        return $this->model->deleteHoliday($id, $this->school_id);
    }

    /**
     * Get holiday by ID
     */
    public function getHolidayById(int $id) {
        return $this->model->getHolidayById($id, $this->school_id);
    }

    /**
     * Format event type for display
     */
    public static function formatEventType(string $type) {
        $types = [
            'WEEKLY_OFF' => 'Weekly Off',
            'HOLIDAY' => 'Holiday',
            'VACATION' => 'Vacation',
            'EVENT' => 'Event'
        ];
        return $types[$type] ?? $type;
    }

    /**
     * Format applies to for display
     */
    public static function formatAppliesto(string $type) {
        $types = [
            'ALL' => 'All',
            'STUDENTS' => 'Students',
            'STAFF' => 'Staff'
        ];
        return $types[$type] ?? $type;
    }

    /**
     * Get day name from day_of_week
     */
    public static function getDayName(int $day) {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];
        return $days[$day] ?? '';
    }
}
