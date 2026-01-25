<?php
class SchoolSubscription
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO saas_school_subscriptions
            (school_id, plan_name, price_per_student, students_count, billing_cycle, start_date, end_date, status, created_at)
            VALUES
            (:school_id, :plan_name, :price_per_student, :students_count, :billing_cycle, :start_date, :end_date, :status, :created_at)"
        );

        $params = [
            ':school_id' => $data['school_id'] ?? null,
            ':plan_name' => $data['plan_name'] ?? null,
            ':price_per_student' => $data['price_per_student'] ?? 0,
            ':students_count' => $data['students_count'] ?? 0,
            ':billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':status' => $data['status'] ?? 'active',
            ':created_at' => date('Y-m-d H:i:s')
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("Subscription create error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    public function getBySchoolId($school_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM saas_school_subscriptions WHERE school_id = ? ORDER BY created_at DESC");
        $stmt->execute([$school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($school_id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE saas_school_subscriptions SET
                plan_name = :plan_name,
                price_per_student = :price_per_student,
                students_count = :students_count,
                billing_cycle = :billing_cycle,
                start_date = :start_date,
                end_date = :end_date,
                status = :status
            WHERE school_id = :school_id"
        );

        $params = [
            ':school_id' => $school_id,
            ':plan_name' => $data['plan_name'] ?? null,
            ':price_per_student' => $data['price_per_student'] ?? 0,
            ':students_count' => $data['students_count'] ?? 0,
            ':billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("Subscription update error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    public function delete($school_id)
    {
        $stmt = $this->db->prepare("DELETE FROM saas_school_subscriptions WHERE school_id = ?");
        return $stmt->execute([$school_id]);
    }
}
?>
