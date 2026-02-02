<?php
namespace App\Models;

class Student
{
    protected $db;

    public function __construct()
    {
        require_once __DIR__ . '/../../../Core/database.php';
        $this->db = \Database::connect();
    }

    /**
     * Return students with status = 0 (dropped) for a school
     */
    public function getDroppedBySchool(int $school_id)
    {
        $sql = "SELECT s.id, s.admission_no, s.first_name, s.last_name, s.father_names, s.father_contact, s.status, s.updated_at,
            sc.class_name, sec.section_name,
            (SELECT g.name FROM school_student_guardians g WHERE g.student_id = s.id AND g.is_primary = 1 AND g.deleted_at IS NULL LIMIT 1) AS guardian_name
            FROM school_students s
            LEFT JOIN school_student_academics a ON a.student_id = s.id AND a.deleted_at IS NULL
            LEFT JOIN school_classes sc ON sc.id = a.class_id
            LEFT JOIN school_class_sections sec ON sec.id = a.section_id
            WHERE s.school_id = :sid AND s.status = 0 AND s.deleted_at IS NULL
            ORDER BY s.id DESC LIMIT 1000";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mark student as dropped (status = 0) by admission number
     * Returns number of affected rows
     */
    public function dropByAdmissionNo(int $school_id, string $admission_no)
    {
        $stmt = $this->db->prepare("UPDATE school_students SET status = 0, updated_at = NOW() WHERE school_id = :sid AND admission_no = :adm AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':sid' => $school_id, ':adm' => $admission_no]);
        return $stmt->rowCount();
    }

    /**
     * Admit a dropped student by id (set status = 1)
     */
    public function admitById(int $student_id)
    {
        $stmt = $this->db->prepare("UPDATE school_students SET status = 1, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => $student_id]);
        return $stmt->rowCount();
    }
}
