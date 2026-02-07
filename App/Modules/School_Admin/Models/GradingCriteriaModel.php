<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class GradingCriteriaModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all grading criteria for a school
     */
    public function getGradingCriteriaBySchool(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                school_id,
                grade_name,
                min_percentage,
                max_percentage,
                gpa,
                remarks,
                is_pass,
                grading_system,
                status,
                created_at,
                updated_at
            FROM school_grading_criteria
            WHERE school_id = ?
            ORDER BY min_percentage DESC
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single grading criteria by ID
     */
    public function getGradingCriteriaById(int $id, int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                school_id,
                grade_name,
                min_percentage,
                max_percentage,
                gpa,
                remarks,
                is_pass,
                grading_system,
                status,
                created_at,
                updated_at
            FROM school_grading_criteria
            WHERE id = ? AND school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Add new grading criteria
     */
    public function addGradingCriteria(
        int $school_id,
        string $grade_name,
        float $min_percentage,
        float $max_percentage,
        ?float $gpa = null,
        ?string $remarks = null,
        int $is_pass = 1,
        string $grading_system = 'percentage',
        int $status = 1
    ) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO school_grading_criteria (
                    school_id,
                    grade_name,
                    min_percentage,
                    max_percentage,
                    gpa,
                    remarks,
                    is_pass,
                    grading_system,
                    status,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            return $stmt->execute([
                $school_id,
                $grade_name,
                $min_percentage,
                $max_percentage,
                $gpa,
                $remarks,
                $is_pass,
                $grading_system,
                $status
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Update grading criteria
     */
    public function updateGradingCriteria(
        int $id,
        int $school_id,
        string $grade_name,
        float $min_percentage,
        float $max_percentage,
        ?float $gpa = null,
        ?string $remarks = null,
        int $is_pass = 1,
        string $grading_system = 'percentage',
        int $status = 1
    ) {
        try {
            $stmt = $this->db->prepare("
                UPDATE school_grading_criteria
                SET 
                    grade_name = ?,
                    min_percentage = ?,
                    max_percentage = ?,
                    gpa = ?,
                    remarks = ?,
                    is_pass = ?,
                    grading_system = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ? AND school_id = ?
            ");

            return $stmt->execute([
                $grade_name,
                $min_percentage,
                $max_percentage,
                $gpa,
                $remarks,
                $is_pass,
                $grading_system,
                $status,
                $id,
                $school_id
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Delete grading criteria
     */
    public function deleteGradingCriteria(int $id, int $school_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM school_grading_criteria
                WHERE id = ? AND school_id = ?
            ");
            return $stmt->execute([$id, $school_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Check if grade name already exists
     */
    public function gradeNameExists(string $grade_name, int $school_id, ?int $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM school_grading_criteria WHERE school_id = ? AND grade_name = ?";
        $params = [$school_id, $grade_name];

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check for percentage range overlap
     */
    public function percentageRangeExists(float $min_pct, float $max_pct, int $school_id, ?int $exclude_id = null) {
        $query = "
            SELECT COUNT(*) FROM school_grading_criteria 
            WHERE school_id = ? 
            AND (
                (min_percentage <= ? AND max_percentage >= ?)
                OR 
                (min_percentage <= ? AND max_percentage >= ?)
                OR 
                (min_percentage >= ? AND max_percentage <= ?)
            )
        ";
        $params = [$school_id, $max_pct, $min_pct, $max_pct, $min_pct, $min_pct, $max_pct];

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
