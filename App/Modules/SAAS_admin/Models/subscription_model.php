<?php
class Subscription
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Get all schools with subscription details
    public function getAllSchools()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM schools 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get school by ID
    public function getSchoolById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM schools 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Renew subscription (extend by plan duration)
    public function renewSubscription($schoolId, $plan)
    {
        $school = $this->getSchoolById($schoolId);
        
        if (!$school) {
            return false;
        }

        // Get duration based on plan
        $duration = $this->getPlanDuration($plan);
        
        // Set new expiry date to current date + plan duration
        $newExpiryDate = date('Y-m-d', strtotime('+' . $duration . ' days'));

        $stmt = $this->db->prepare("
            UPDATE schools 
            SET expires_at = :expires_at,
                updated_at = :updated_at
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $schoolId,
            ':expires_at' => $newExpiryDate,
            ':updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Extend subscription (add more days to current expiry)
    public function extendSubscription($schoolId, $days)
    {
        $school = $this->getSchoolById($schoolId);
        
        if (!$school) {
            return false;
        }

        // Add days to current expiry date (or today if already expired)
        $currentExpiryDate = strtotime($school['expires_at']);
        $today = strtotime(date('Y-m-d'));
        
        $baseDate = ($currentExpiryDate > $today) ? $currentExpiryDate : $today;
        $newExpiryDate = date('Y-m-d', strtotime('+' . $days . ' days', $baseDate));

        $stmt = $this->db->prepare("
            UPDATE schools 
            SET expires_at = :expires_at,
                updated_at = :updated_at
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $schoolId,
            ':expires_at' => $newExpiryDate,
            ':updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Get plan duration in days
    private function getPlanDuration($plan)
    {
        $durations = [
            'basic' => 365,      // 1 year
            'standard' => 365,   // 1 year
            'premium' => 365,    // 1 year
            'monthly' => 30,
            'quarterly' => 90,
            'annually' => 365
        ];

        return isset($durations[strtolower($plan)]) ? $durations[strtolower($plan)] : 365;
    }

    // Get subscription status
    public function getSubscriptionStatus($expiryDate)
    {
        $today = strtotime(date('Y-m-d'));
        $expiry = strtotime($expiryDate);
        $daysLeft = floor(($expiry - $today) / (60 * 60 * 24));

        if ($daysLeft < 0) {
            return ['status' => 'expired', 'daysLeft' => 0, 'class' => 'danger'];
        } elseif ($daysLeft <= 30) {
            return ['status' => 'expiring_soon', 'daysLeft' => $daysLeft, 'class' => 'warning'];
        } else {
            return ['status' => 'active', 'daysLeft' => $daysLeft, 'class' => 'success'];
        }
    }
}
?>
