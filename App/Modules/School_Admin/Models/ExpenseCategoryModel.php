<?php

namespace App\Modules\School_Admin\Models;

use PDO;

class ExpenseCategoryModel
{
    private $db;

    public function __construct(PDO $connection)
    {
        $this->db = $connection;
    }

    /**
     * Get all categories for a school
     */
    public function getAll($school_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM expense_categories
            WHERE school_id = ? AND status = 1
            ORDER BY name ASC
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category by ID
     */
    public function getById($id, $school_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM expense_categories
            WHERE id = ? AND school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new category
     */
    public function create($school_id, $name, $description = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO expense_categories (school_id, name, description, status)
            VALUES (?, ?, ?, 1)
        ");
        $result = $stmt->execute([$school_id, $name, $description]);
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update category
     */
    public function update($id, $school_id, $name, $description = null, $status = 1)
    {
        $stmt = $this->db->prepare("
            UPDATE expense_categories SET
                name = ?,
                description = ?,
                status = ?,
                updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");
        return $stmt->execute([$name, $description, $status, $id, $school_id]);
    }

    /**
     * Delete category
     */
    public function delete($id, $school_id)
    {
        $stmt = $this->db->prepare("
            UPDATE expense_categories SET status = 0
            WHERE id = ? AND school_id = ?
        ");
        return $stmt->execute([$id, $school_id]);
    }
}
