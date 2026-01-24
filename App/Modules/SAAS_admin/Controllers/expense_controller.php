<?php
require_once __DIR__ . '/../Models/expense_model.php';

class ExpenseController
{
    private $expense;

    public function __construct($db)
    {
        $this->expense = new Expense($db);
    }

    public function index()
    {
        try {
            return $this->expense->getAll();
        } catch (Exception $ex) {
            error_log("ExpenseController::index error: " . $ex->getMessage());
            return [];
        }
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ensure created_by set from session if available
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            if (isset($_SESSION['user_id'])) {
                $_POST['created_by'] = $_SESSION['user_id'];
            }
            return $this->expense->create($_POST);
        }
        return false;
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['expense_id'])) {
            // Do not allow changing created_by via form; keep original or use session
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            if (isset($_SESSION['user_id'])) {
                $_POST['created_by'] = $_SESSION['user_id'];
            }
            return $this->expense->update($_POST['expense_id'], $_POST);
        }
        return false;
    }

    public function delete($id)
    {
        if (!empty($id)) {
            return $this->expense->delete($id);
        }
        return false;
    }
}

?>
