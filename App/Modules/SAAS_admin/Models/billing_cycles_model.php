<?php
class BillingCycles
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get all unpaid billing cycles
     */
    public function getUnpaid()
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, 
                    s.name AS school_name,
                    s.email AS school_email,
                    s.contact_no AS contact_phone,
                    ss.plan_name AS subscription_plan
             FROM saas_billing_cycles bc
             LEFT JOIN schools s ON bc.school_id = s.id
             LEFT JOIN saas_school_subscriptions ss ON bc.subscription_id = ss.subscription_id
             WHERE bc.status = 'due'
             ORDER BY bc.due_date ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all billing cycles
     */
    public function getAll()
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, 
                    s.name AS school_name,
                    s.email AS school_email,
                    s.contact_no AS contact_phone,
                    ss.plan_name AS subscription_plan
             FROM saas_billing_cycles bc
             LEFT JOIN schools s ON bc.school_id = s.id
             LEFT JOIN saas_school_subscriptions ss ON bc.subscription_id = ss.subscription_id
             ORDER BY bc.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single billing cycle by ID
     */
    public function getById($billing_id)
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, 
                    s.name AS school_name,
                    s.email AS school_email,
                    s.contact_no AS contact_phone,
                    ss.plan_name AS subscription_plan
             FROM saas_billing_cycles bc
             LEFT JOIN schools s ON bc.school_id = s.id
             LEFT JOIN saas_school_subscriptions ss ON bc.subscription_id = ss.subscription_id
             WHERE bc.billing_id = ?"
        );
        $stmt->execute([$billing_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get unpaid billing cycles by school ID
     */
    public function getUnpaidBySchoolId($school_id)
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, 
                    s.name AS school_name,
                    ss.plan_name AS subscription_plan
             FROM saas_billing_cycles bc
             LEFT JOIN schools s ON bc.school_id = s.id
             LEFT JOIN saas_school_subscriptions ss ON bc.subscription_id = ss.id
             WHERE bc.school_id = ? AND bc.status = 'due'
             ORDER BY bc.due_date ASC"
        );
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new billing cycle
     */
    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO saas_billing_cycles
            (school_id, subscription_id, period_start, period_end, due_date, total_amount, paid_amount, status, created_at)
            VALUES
            (:school_id, :subscription_id, :period_start, :period_end, :due_date, :total_amount, :paid_amount, :status, :created_at)"
        );

        $params = [
            ':school_id' => $data['school_id'] ?? null,
            ':subscription_id' => $data['subscription_id'] ?? null,
            ':period_start' => $data['period_start'] ?? null,
            ':period_end' => $data['period_end'] ?? null,
            ':due_date' => $data['due_date'] ?? null,
            ':total_amount' => $data['total_amount'] ?? 0,
            ':paid_amount' => $data['paid_amount'] ?? 0,
            ':status' => $data['status'] ?? 'unpaid',
            ':created_at' => date('Y-m-d H:i:s')
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("BillingCycles create error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    /**
     * Update billing cycle
     */
    public function update($billing_id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE saas_billing_cycles SET
                school_id = :school_id,
                subscription_id = :subscription_id,
                period_start = :period_start,
                period_end = :period_end,
                due_date = :due_date,
                total_amount = :total_amount,
                paid_amount = :paid_amount,
                status = :status
            WHERE billing_id = :billing_id"
        );

        $params = [
            ':billing_id' => $billing_id,
            ':school_id' => $data['school_id'] ?? null,
            ':subscription_id' => $data['subscription_id'] ?? null,
            ':period_start' => $data['period_start'] ?? null,
            ':period_end' => $data['period_end'] ?? null,
            ':due_date' => $data['due_date'] ?? null,
            ':total_amount' => $data['total_amount'] ?? 0,
            ':paid_amount' => $data['paid_amount'] ?? 0,
            ':status' => $data['status'] ?? 'unpaid'
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("BillingCycles update error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    /**
     * Delete billing cycle
     */
    public function delete($billing_id)
    {
        $stmt = $this->db->prepare("DELETE FROM saas_billing_cycles WHERE billing_id = ?");
        return $stmt->execute([$billing_id]);
    }

    /**
     * Get total unpaid amount
     */
    public function getTotalUnpaidAmount()
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total_amount - paid_amount), 0) as total_unpaid
             FROM saas_billing_cycles
             WHERE status = 'due'"
        );
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_unpaid'] ?? 0;
    }

    /**
     * Get count of unpaid billing cycles
     */
    public function getUnpaidCount()
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM saas_billing_cycles WHERE status = 'due'"
        );
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
?>
