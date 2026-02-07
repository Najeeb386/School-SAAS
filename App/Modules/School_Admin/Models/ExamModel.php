<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class ExamModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all exams for a school
     */
    public function getExamsBySchool(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.school_id = ?
            ORDER BY e.start_date DESC
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exams filtered by criteria
     */
    public function getExamsFiltered(int $school_id, ?string $exam_type = null, ?int $session_id = null, ?string $start_date = null, ?string $end_date = null, ?string $status = null) {
        $query = "
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.school_id = ?
        ";
        $params = [$school_id];

        if ($exam_type) {
            $query .= " AND e.exam_type = ?";
            $params[] = $exam_type;
        }

        if ($session_id) {
            $query .= " AND e.session_id = ?";
            $params[] = $session_id;
        }

        if ($start_date) {
            $query .= " AND e.start_date >= ?";
            $params[] = $start_date;
        }

        if ($end_date) {
            $query .= " AND e.end_date <= ?";
            $params[] = $end_date;
        }

        if ($status) {
            $query .= " AND e.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY e.start_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single exam by ID
     */
    public function getExamById(int $id, int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.id = ? AND e.school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Add new exam
     */
    public function addExam(
        int $school_id,
        int $session_id,
        string $exam_name,
        string $exam_type,
        string $start_date,
        string $end_date,
        ?string $description = null,
        string $status = 'draft',
        ?int $created_by = null
    ) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO school_exams (
                    school_id,
                    session_id,
                    exam_name,
                    exam_type,
                    start_date,
                    end_date,
                    description,
                    status,
                    created_by,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            return $stmt->execute([
                $school_id,
                $session_id,
                $exam_name,
                $exam_type,
                $start_date,
                $end_date,
                $description,
                $status,
                $created_by
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Update exam
     */
    public function updateExam(
        int $id,
        int $school_id,
        int $session_id,
        string $exam_name,
        string $exam_type,
        string $start_date,
        string $end_date,
        ?string $description = null,
        string $status = 'draft'
    ) {
        try {
            $stmt = $this->db->prepare("
                UPDATE school_exams
                SET 
                    session_id = ?,
                    exam_name = ?,
                    exam_type = ?,
                    start_date = ?,
                    end_date = ?,
                    description = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ? AND school_id = ?
            ");

            return $stmt->execute([
                $session_id,
                $exam_name,
                $exam_type,
                $start_date,
                $end_date,
                $description,
                $status,
                $id,
                $school_id
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Delete exam
     */
    public function deleteExam(int $id, int $school_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM school_exams
                WHERE id = ? AND school_id = ?
            ");
            return $stmt->execute([$id, $school_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Check if exam exists for school/session/name
     */
    public function examExists(string $exam_name, int $school_id, int $session_id, ?int $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM school_exams WHERE school_id = ? AND session_id = ? AND exam_name = ?";
        $params = [$school_id, $session_id, $exam_name];

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}

