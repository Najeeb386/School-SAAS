<?php
require_once __DIR__ . '/../Models/billing_cycles_model.php';

class BillingCyclesController
{
    private $billingCycles;

    public function __construct($db)
    {
        $this->billingCycles = new BillingCycles($db);
    }

    /**
     * Get all billing cycles
     */
    public function index()
    {
        try {
            return $this->billingCycles->getAll();
        } catch (Exception $ex) {
            error_log("BillingCyclesController::index error: " . $ex->getMessage());
            return [];
        }
    }

    /**
     * Get unpaid billing cycles
     */
    public function getUnpaid()
    {
        try {
            return $this->billingCycles->getUnpaid();
        } catch (Exception $ex) {
            error_log("BillingCyclesController::getUnpaid error: " . $ex->getMessage());
            return [];
        }
    }

    /**
     * Get a single billing cycle
     */
    public function show($billing_id)
    {
        try {
            return $this->billingCycles->getById($billing_id);
        } catch (Exception $ex) {
            error_log("BillingCyclesController::show error: " . $ex->getMessage());
            return null;
        }
    }

    /**
     * Create a new billing cycle
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                return $this->billingCycles->create($_POST);
            } catch (Exception $ex) {
                error_log("BillingCyclesController::store error: " . $ex->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Update billing cycle
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['billing_id'])) {
            try {
                return $this->billingCycles->update($_POST['billing_id'], $_POST);
            } catch (Exception $ex) {
                error_log("BillingCyclesController::update error: " . $ex->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Delete billing cycle
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['billing_id'])) {
            try {
                return $this->billingCycles->delete($_POST['billing_id']);
            } catch (Exception $ex) {
                error_log("BillingCyclesController::delete error: " . $ex->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Get total unpaid amount
     */
    public function getTotalUnpaidAmount()
    {
        try {
            return $this->billingCycles->getTotalUnpaidAmount();
        } catch (Exception $ex) {
            error_log("BillingCyclesController::getTotalUnpaidAmount error: " . $ex->getMessage());
            return 0;
        }
    }

    /**
     * Get count of unpaid billing cycles
     */
    public function getUnpaidCount()
    {
        try {
            return $this->billingCycles->getUnpaidCount();
        } catch (Exception $ex) {
            error_log("BillingCyclesController::getUnpaidCount error: " . $ex->getMessage());
            return 0;
        }
    }
}
?>
