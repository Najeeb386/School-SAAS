<?php
require_once '../../models/school_model.php';

class SchoolController
{
    private $school;

    public function __construct($db)
    {
        $this->school = new School($db);
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

    // Store school
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->school->create($_POST);
        }
        return false;
    }

    // Update school
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
            return $this->school->update($_POST['id'], $_POST);
        }
        return false;
    }

    // Delete school
    public function delete($id)
    {
        if (!empty($id)) {
            return $this->school->delete($id);
        }
        return false;
    }
}
?>
