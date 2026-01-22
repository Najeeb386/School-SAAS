<?php
require_once '../../models/plain_model.php';

class PlanController
{
    private $plan;

    public function __construct($db)
    {
        $this->plan = new Plan($db);
    }

    // List plans
    public function index()
    {
        return $this->plan->getAll();
    }

    // Store plan
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->plan->create($_POST);
        }
        return false;
    }

    // Update plan
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
            return $this->plan->update($_POST['id'], $_POST);
        }
        return false;
    }

    // Delete plan
    public function delete($id)
    {
        if (!empty($id)) {
            return $this->plan->delete($id);
        }
        return false;
    }
}
?>