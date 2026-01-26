<?php
require_once '../../models/school_model.php';
require_once '../../models/school_subscription_model.php';
require_once '../../models/plain_model.php';
require_once __DIR__ . '/../Models/billing_cycles_model.php';

class SchoolController
{
    private $school;
    private $subscription;
    private $db;
    private $billingCycles;

    public function __construct($db)
    {
        $this->school = new School($db);
        $this->subscription = new SchoolSubscription($db);
        $this->db = $db;
        $this->billingCycles = new BillingCycles($db);
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
                        // Get subscription ID
                        $subscription_id = $this->db->lastInsertId();
                        
                        // Handle billing if "Start Billing" is checked
                        if (isset($_POST['start_billing']) && $_POST['start_billing'] == 'on') {
                            $totalAmount = floatval($_POST['total_amount'] ?? 0);
                            $finalTotalAmount = floatval($_POST['final_total_amount'] ?? $totalAmount);
                            $paidAmount = floatval($_POST['paid_amount'] ?? 0);
                            $discountType = $_POST['discount_type'] ?? '';
                            $discountValue = floatval($_POST['discount_value'] ?? 0);
                            
                            // Calculate the discounted amount
                            $discountedAmount = $totalAmount - $finalTotalAmount;
                            
                            if ($finalTotalAmount > 0) {
                                // Calculate billing periods
                                $startDate = $_POST['start_date'] ?? date('Y-m-d');
                                $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
                                
                                // Calculate period end and due date
                                $periodEnd = $this->calculatePeriodEnd($startDate, $billingCycle);
                                $dueDate = $this->calculateDueDate($startDate, $billingCycle);
                                
                                // Create billing cycle record with original amount and discounted amount
                                $billingData = [
                                    'school_id' => $school_id,
                                    'subscription_id' => $subscription_id,
                                    'period_start' => $startDate,
                                    'period_end' => $periodEnd,
                                    'due_date' => $dueDate,
                                    'total_amount' => $totalAmount,
                                    'discounted_amount' => $discountedAmount,
                                    'paid_amount' => $paidAmount,
                                    'status' => $paidAmount > 0 ? 'partial' : 'due'
                                ];
                                
                                $billingResult = $this->billingCycles->create($billingData);
                                
                                // If payment was made, create payment record
                                if ($paidAmount > 0 && $billingResult) {
                                    $billing_id = $this->db->lastInsertId();
                                    $this->createPaymentRecord(
                                        $billing_id,
                                        $school_id,
                                        $finalTotalAmount,
                                        $paidAmount,
                                        $_POST
                                    );
                                }
                                
                                if (!$billingResult) {
                                    $this->db->rollBack();
                                    return false;
                                }
                            }
                        }
                        
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

    // Calculate period end date based on billing cycle
    private function calculatePeriodEnd($startDate, $billingCycle)
    {
        $date = new DateTime($startDate);
        switch ($billingCycle) {
            case 'monthly':
                $date->add(new DateInterval('P1M'));
                break;
            case 'quarterly':
                $date->add(new DateInterval('P3M'));
                break;
            case 'semi-annual':
                $date->add(new DateInterval('P6M'));
                break;
            case 'yearly':
                $date->add(new DateInterval('P1Y'));
                break;
            default:
                $date->add(new DateInterval('P1M'));
        }
        return $date->format('Y-m-d');
    }

    // Calculate due date based on billing cycle
    private function calculateDueDate($startDate, $billingCycle)
    {
        $date = new DateTime($startDate);
        // Due date is 7 days after start date
        $date->add(new DateInterval('P7D'));
        return $date->format('Y-m-d');
    }

    // Create payment record
    private function createPaymentRecord($billing_id, $school_id, $totalAmount, $paidAmount, $postData)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO saas_payments 
            (billing_id, school_id, total_amount, paid_amount, payment_date, payment_method, reference_no, received_by)
            VALUES (:billing_id, :school_id, :total_amount, :paid_amount, :payment_date, :payment_method, :reference_no, :received_by)"
        );
        
        $params = [
            ':billing_id' => $billing_id,
            ':school_id' => $school_id,
            ':total_amount' => $totalAmount,
            ':paid_amount' => $paidAmount,
            ':payment_date' => date('Y-m-d'),
            ':payment_method' => $postData['payment_method'] ?? null,
            ':reference_no' => $postData['reference_no'] ?? null,
            ':received_by' => $postData['received_by'] ?? null
        ];
        
        try {
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Payment creation error: " . $e->getMessage());
            return false;
        }
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
