<?php
require_once '../../models/school_model.php';
require_once '../../models/school_subscription_model.php';
require_once '../../models/plain_model.php';

class SchoolController
{
    private $school;
    private $subscription;
    private $db;

    public function __construct($db)
    {
        $this->school = new School($db);
        $this->subscription = new SchoolSubscription($db);
        $this->db = $db;
    }

    // List schools
    public function index()
    {
        return $this->school->getAll();
    }

    // Get single school by ID
    public function getSchoolById($id)
    {
        return $this->school->getById($id);
    }

    // Store school with subscription
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Start transaction
            $this->db->beginTransaction();
            try {
                // Create school
                $schoolResult = $this->school->create($_POST);
                
                if ($schoolResult) {
                    // Get the last inserted school ID
                    $school_id = $this->db->lastInsertId();
                    
                    // Prepare subscription data
                    $subscriptionData = [
                        'school_id' => $school_id,
                        'plan_name' => $_POST['plan'] ?? 'Basic',
                        'price_per_student' => $_POST['price_per_student'] ?? 0,
                        'students_count' => $_POST['estimated_students'] ?? 0,
                        'billing_cycle' => $_POST['billing_cycle'] ?? 'monthly',
                        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                        'end_date' => $_POST['expires_at'] ?? date('Y-m-d', strtotime('+1 year')),
                        'status' => $_POST['status'] ?? 'active'
                    ];
                    
                    // Create subscription
                    $subscriptionResult = $this->subscription->create($subscriptionData);
                    
                    if ($subscriptionResult) {
                        $this->db->commit();
                        return true;
                    } else {
                        $this->db->rollBack();
                        return false;
                    }
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("School creation error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    // Update school with subscription
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
            // Start transaction
            $this->db->beginTransaction();
            try {
                // Update school
                $schoolResult = $this->school->update($_POST['id'], $_POST);
                
                if ($schoolResult) {
                    // Prepare subscription data
                    $subscriptionData = [
                        'plan_name' => $_POST['plan'] ?? 'Basic',
                        'price_per_student' => $_POST['price_per_student'] ?? 0,
                        'students_count' => $_POST['estimated_students'] ?? 0,
                        'billing_cycle' => $_POST['billing_cycle'] ?? 'monthly',
                        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                        'end_date' => $_POST['expires_at'] ?? date('Y-m-d', strtotime('+1 year')),
                        'status' => $_POST['status'] ?? 'active'
                    ];
                    
                    // Update subscription
                    $subscriptionResult = $this->subscription->update($_POST['id'], $subscriptionData);
                    
                    if ($subscriptionResult) {
                        $this->db->commit();
                        return true;
                    } else {
                        $this->db->rollBack();
                        return false;
                    }
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("School update error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    // Delete school (and subscription)
    public function delete($id)
    {
        if (!empty($id)) {
            // Start transaction
            $this->db->beginTransaction();
            try {
                // Delete subscription first
                $this->subscription->delete($id);
                
                // Delete school
                $schoolResult = $this->school->delete($id);
                
                if ($schoolResult) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("School deletion error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}
?>
