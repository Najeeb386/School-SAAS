<?php
namespace App\Modules\School_Admin\Models;

class SessionModel {
    protected $db;

    public function __construct($DB_con) {
        $this->db = $DB_con;
    }

    public function getAll($school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_sessions WHERE school_id = :sid AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute([':sid'=>$school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id, $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_sessions WHERE id = :id AND school_id = :sid AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id'=>$id,':sid'=>$school_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($school_id, $data) {
        // if is_active is set, clear other active sessions for this school
        if (!empty($data['is_active'])) {
            $this->clearActiveForSchool($school_id);
        }

        $stmt = $this->db->prepare('INSERT INTO school_sessions (school_id,name,start_date,end_date,is_active,created_by,updated_by,created_at,updated_at) VALUES (:school_id,:name,:start,:end,:active,:created_by,:updated_by,NOW(),NOW())');
        $stmt->execute([
            ':school_id'=>$school_id,
            ':name'=>$data['name'] ?? null,
            ':start'=>$data['start_date'] ?? null,
            ':end'=>$data['end_date'] ?? null,
            ':active'=>isset($data['is_active']) ? intval($data['is_active']) : 0,
            ':created_by'=>isset($data['created_by']) ? $data['created_by'] : null,
            ':updated_by'=>isset($data['updated_by']) ? $data['updated_by'] : null,
        ]);
        return intval($this->db->lastInsertId());
    }

    public function update($id, $school_id, $data) {
        if (isset($data['is_active']) && $data['is_active']) {
            $this->clearActiveForSchool($school_id, $id);
        }

        $fields = [];
        $params = [':id'=>$id,':sid'=>$school_id];
        if (isset($data['name'])) { $fields[] = 'name = :name'; $params[':name'] = $data['name']; }
        if (isset($data['start_date'])) { $fields[] = 'start_date = :start'; $params[':start'] = $data['start_date']; }
        if (isset($data['end_date'])) { $fields[] = 'end_date = :end'; $params[':end'] = $data['end_date']; }
        if (isset($data['is_active'])) { $fields[] = 'is_active = :active'; $params[':active'] = intval($data['is_active']); }
        if (isset($data['updated_by'])) { $fields[] = 'updated_by = :updated_by'; $params[':updated_by'] = $data['updated_by']; }

        if (empty($fields)) return false;
        $sql = 'UPDATE school_sessions SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND school_id = :sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $school_id) {
        // soft delete
        $stmt = $this->db->prepare('UPDATE school_sessions SET deleted_at = NOW() WHERE id = :id AND school_id = :sid');
        return $stmt->execute([':id'=>$id,':sid'=>$school_id]);
    }

    protected function clearActiveForSchool($school_id, $except_id = null) {
        if ($except_id) {
            $stmt = $this->db->prepare('UPDATE school_sessions SET is_active = 0 WHERE school_id = :sid AND id <> :eid AND is_active = 1');
            return $stmt->execute([':sid'=>$school_id,':eid'=>$except_id]);
        }
        $stmt = $this->db->prepare('UPDATE school_sessions SET is_active = 0 WHERE school_id = :sid AND is_active = 1');
        return $stmt->execute([':sid'=>$school_id]);
    }
}
