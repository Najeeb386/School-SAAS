<?php
namespace App\Controllers;

use App\Models\Student;

class StudentController
{
    protected $studentModel;
    protected $school_id;

    public function __construct(int $school_id)
    {
        $this->school_id = $school_id;
        $this->studentModel = new Student();
    }

    public function listDropped()
    {
        return $this->studentModel->getDroppedBySchool($this->school_id);
    }

    public function dropByAdmissionNo(string $admission_no)
    {
        if (empty($admission_no)) throw new \Exception('Admission number is required');
        return $this->studentModel->dropByAdmissionNo($this->school_id, $admission_no);
    }

    public function admitById(int $student_id)
    {
        if (empty($student_id)) throw new \Exception('Student id is required');
        return $this->studentModel->admitById($student_id);
    }
}
