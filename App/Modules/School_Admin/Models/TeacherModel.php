<?php
namespace App\Modules\School_Admin\Models;

class TeacherModel {
    protected $db;

    public function __construct($DB_con) {
        $this->db = $DB_con;
    }

    public function getAll($school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_teachers WHERE school_id = :sid ORDER BY id DESC');
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id, $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_teachers WHERE id = :id AND school_id = :sid LIMIT 1');
        $stmt->execute([':id' => $id, ':sid' => $school_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($school_id, $data) {
        $stmt = $this->db->prepare('INSERT INTO school_teachers (school_id,name,email,phone,id_no,photo_path,role,permissions,status,created_at,updated_at) VALUES (:school_id,:name,:email,:phone,:id_no,:photo,:role,:perms,:status,NOW(),NOW())');
        $stmt->execute([
            ':school_id'=>$school_id,
            ':name'=>$data['name'] ?? null,
            ':email'=>$data['email'] ?? null,
            ':phone'=>$data['phone'] ?? null,
            ':id_no'=>$data['id_no'] ?? null,
            ':photo'=>$data['photo_path'] ?? null,
            ':role'=>$data['role'] ?? 'teacher',
            ':perms'=>$data['permissions'] ?? null,
            ':status'=>isset($data['status']) ? intval($data['status']) : 1,
        ]);
        return intval($this->db->lastInsertId());
    }

    public function update($id, $school_id, $data) {
        $fields = [];
        $params = [':id'=>$id,':sid'=>$school_id];
        if (isset($data['name'])) { $fields[]='name=:name'; $params[':name']=$data['name']; }
        if (isset($data['email'])) { $fields[]='email=:email'; $params[':email']=$data['email']; }
        if (isset($data['phone'])) { $fields[]='phone=:phone'; $params[':phone']=$data['phone']; }
        if (isset($data['id_no'])) { $fields[]='id_no=:id_no'; $params[':id_no']=$data['id_no']; }
        if (isset($data['photo_path'])) { $fields[]='photo_path=:photo'; $params[':photo']=$data['photo_path']; }
        if (isset($data['role'])) { $fields[]='role=:role'; $params[':role']=$data['role']; }
        if (isset($data['permissions'])) { $fields[]='permissions=:perms'; $params[':perms']=$data['permissions']; }
        if (isset($data['status'])) { $fields[]='status=:status'; $params[':status']=intval($data['status']); }
        if (empty($fields)) return false;
        $sql = 'UPDATE school_teachers SET ' . implode(',', $fields) . ', updated_at=NOW() WHERE id=:id AND school_id=:sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $school_id) {
        $stmt = $this->db->prepare('DELETE FROM school_teachers WHERE id=:id AND school_id=:sid');
        return $stmt->execute([':id'=>$id,':sid'=>$school_id]);
    }
}
