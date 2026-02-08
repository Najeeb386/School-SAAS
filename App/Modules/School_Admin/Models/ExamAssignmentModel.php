<?php

// Make class available in both namespace and global scope
if (!class_exists('App\\Models\\ExamAssignmentModel', false)) {
    class_alias('ExamAssignmentModel', 'App\\Models\\ExamAssignmentModel');
}

class ExamAssignmentModel {
    
    private $pdo;
    private $school_id;
    
    public function __construct($pdo, $school_id) {
        $this->pdo = $pdo;
        $this->school_id = $school_id;
    }
    
    /**
     * Get exam details by ID
     */
    public function getExamById($exam_id) {
        try {
            $query = "SELECT * FROM school_exams 
                      WHERE id = :id AND school_id = :school_id";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':id' => $exam_id,
                ':school_id' => $this->school_id
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all classes for current school
     */
    public function getClasses() {
        try {
            // First try without status filter to see if data exists
            $query = "SELECT DISTINCT 
                        sc.id,
                        sc.class_name,
                        sc.class_code,
                        sc.grade_level
                      FROM school_classes sc
                      WHERE sc.school_id = :school_id 
                      ORDER BY sc.grade_level ASC, sc.class_name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':school_id' => $this->school_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get sections by class ID - Fixed to use school_class_sections table
     */
    public function getSectionsByClass($class_id) {
        try {
            // First try without status filter
            $query = "SELECT 
                        id,
                        section_name as name,
                        section_code as code
                      FROM school_class_sections
                      WHERE class_id = :class_id 
                      ORDER BY section_name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':class_id' => $class_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all subjects for current school - Fixed to use correct column names
     */
    public function getSubjects() {
        try {
            // First try without status filter to see if data exists
            $query = "SELECT 
                        id,
                        name,
                        name as code
                      FROM school_subjects
                      WHERE school_id = :school_id 
                      ORDER BY name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':school_id' => $this->school_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get subjects for a specific class and section from school_subject_assignments
     */
    public function getSubjectsByClassAndSection($class_id, $section_id) {
        try {
            $query = "SELECT DISTINCT 
                        ss.id,
                        ss.name,
                        ss.name as code
                      FROM school_subject_assignments ssa
                      JOIN school_subjects ss ON ssa.subject_id = ss.id
                      WHERE ssa.school_id = :school_id 
                        AND ssa.class_id = :class_id
                        AND ssa.section_id = :section_id
                      ORDER BY ss.name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':school_id' => $this->school_id,
                ':class_id' => $class_id,
                ':section_id' => $section_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Save exam class assignment
     */
    public function saveExamClass($data) {
        try {
            // Cast to integers to prevent type errors
            $exam_id = (int)$data['exam_id'];
            $class_id = (int)$data['class_id'];
            $section_id = (int)$data['section_id'];
            
            // The table doesn't have school_id column, so we don't include it
            $query = "INSERT INTO school_exam_classes 
                      (exam_id, class_id, section_id, status)
                      VALUES 
                      (:exam_id, :class_id, :section_id, :status)";
            
            $stmt = $this->pdo->prepare($query);
            
            $params = [
                ':exam_id' => $exam_id,
                ':class_id' => $class_id,
                ':section_id' => $section_id,
                ':status' => $data['status'] ?? 'active'
            ];
            
            $result = $stmt->execute($params);
            
            if ($result) {
                $insert_id = $this->pdo->lastInsertId();
                return $insert_id;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Save exam subjects
     */
    public function saveExamSubjects($exam_class_id, $subjects) {
        try {
            $exam_class_id = (int)$exam_class_id;
            
            // Table doesn't have school_id, so we don't include it
            $query = "INSERT INTO school_exam_subjects 
                      (exam_class_id, subject_id, total_marks, passing_marks, exam_date, exam_time, status)
                      VALUES 
                      (:exam_class_id, :subject_id, :total_marks, :passing_marks, :exam_date, :exam_time, :status)";
            
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            foreach ($subjects as $index => $subject) {
                $subject_id = (int)$subject['subject_id'];
                $total_marks = (int)$subject['total_marks'];
                $passing_marks = (int)$subject['passing_marks'];
                
                $params = [
                    ':exam_class_id' => $exam_class_id,
                    ':subject_id' => $subject_id,
                    ':total_marks' => $total_marks,
                    ':passing_marks' => $passing_marks,
                    ':exam_date' => $subject['exam_date'],
                    ':exam_time' => $subject['exam_time'],
                    ':status' => $subject['status'] ?? 'active'
                ];
                
                $result = $stmt->execute($params);
                
                if (!$result) {
                    return false;
                } else {
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update exam class assignment
     */
    public function updateExamClass($exam_class_id, $data) {
        try {
            $query = "UPDATE school_exam_classes 
                      SET exam_id = :exam_id,
                          class_id = :class_id,
                          section_id = :section_id,
                          status = :status
                      WHERE id = :id";
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                ':id' => $exam_class_id,
                ':exam_id' => $data['exam_id'],
                ':class_id' => $data['class_id'],
                ':section_id' => $data['section_id'],
                ':status' => $data['status'] ?? 'active'
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete exam subjects for a specific exam class
     */
    public function deleteExamSubjects($exam_class_id) {
        try {
            $query = "DELETE FROM school_exam_subjects 
                      WHERE exam_class_id = :exam_class_id";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([':exam_class_id' => $exam_class_id]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all assignments for current school (with subject details) - Fixed table/column names
     */
    public function getAssignments() {
        try {
            $query = "SELECT 
                        sub.id,
                        sec.id as exam_class_id,
                        e.id as exam_id,
                        e.exam_name,
                        cl.class_name,
                        s.section_name,
                        subj.name as subject_name,
                        sub.total_marks,
                        sub.passing_marks,
                        sub.exam_date,
                        sub.exam_time,
                        sub.status
                      FROM school_exam_classes sec
                      JOIN school_exams e ON sec.exam_id = e.id
                      JOIN school_classes cl ON sec.class_id = cl.id
                      JOIN school_class_sections s ON sec.section_id = s.id
                      JOIN school_exam_subjects sub ON sec.id = sub.exam_class_id
                      JOIN school_subjects subj ON sub.subject_id = subj.id
                      WHERE e.school_id = :school_id
                      ORDER BY e.exam_name ASC, cl.grade_level ASC, s.section_name ASC, subj.name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':school_id' => $this->school_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get assignments by exam ID - Fixed table/column names
     */
    public function getAssignmentsByExamId($exam_id) {
        try {
            $query = "SELECT 
                        sub.id,
                        sec.id as exam_class_id,
                        e.id as exam_id,
                        e.exam_name,
                        cl.class_name,
                        s.section_name,
                        subj.name as subject_name,
                        sub.total_marks,
                        sub.passing_marks,
                        sub.exam_date,
                        sub.exam_time,
                        sub.status
                      FROM school_exam_classes sec
                      JOIN school_exams e ON sec.exam_id = e.id
                      JOIN school_classes cl ON sec.class_id = cl.id
                      JOIN school_class_sections s ON sec.section_id = s.id
                      JOIN school_exam_subjects sub ON sec.id = sub.exam_class_id
                      JOIN school_subjects subj ON sub.subject_id = subj.id
                      WHERE e.id = :exam_id AND e.school_id = :school_id
                      ORDER BY cl.grade_level ASC, s.section_name ASC, subj.name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':exam_id' => $exam_id,
                ':school_id' => $this->school_id
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Delete exam class assignment
     */
    public function deleteExamClass($exam_class_id) {
        try {
            // First delete all subjects for this exam class
            $deleteSubjectsQuery = "DELETE FROM school_exam_subjects 
                                    WHERE exam_class_id = :exam_class_id";
            $stmt = $this->pdo->prepare($deleteSubjectsQuery);
            $stmt->execute([':exam_class_id' => $exam_class_id]);
            
            // Then delete the exam class
            $deleteClassQuery = "DELETE FROM school_exam_classes 
                                 WHERE id = :id";
            $stmt = $this->pdo->prepare($deleteClassQuery);
            
            return $stmt->execute([':id' => $exam_class_id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update single exam subject (exam_date, exam_time, marks, status only)
     */
    public function updateExamSubject($subject_data) {
        try {
            $query = "UPDATE school_exam_subjects 
                      SET exam_date = :exam_date,
                          exam_time = :exam_time,
                          total_marks = :total_marks,
                          passing_marks = :passing_marks,
                          status = :status
                      WHERE id = :id";
            
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute([
                ':id' => $subject_data['subject_id'],
                ':exam_date' => $subject_data['exam_date'],
                ':exam_time' => $subject_data['exam_time'],
                ':total_marks' => (int)$subject_data['total_marks'],
                ':passing_marks' => (int)$subject_data['passing_marks'],
                ':status' => $subject_data['status']
            ]);

            if (!$result) {
                return false;
            }

            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete single exam subject by ID
     */
    public function deleteExamSubjectById($subject_id) {
        try {
            $query = "DELETE FROM school_exam_subjects 
                      WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([':id' => $subject_id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get subjects for a specific class (all sections)
     */
    public function getSubjectsByClass($class_id) {
        try {
            $query = "SELECT DISTINCT 
                        ss.id,
                        ss.name,
                        ss.name as code
                      FROM school_subject_assignments ssa
                      JOIN school_subjects ss ON ssa.subject_id = ss.id
                      WHERE ssa.school_id = :school_id 
                        AND ssa.class_id = :class_id
                      ORDER BY ss.name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':school_id' => $this->school_id,
                ':class_id' => $class_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Save assignment to all sections of a class
     */
    public function saveAssignmentToAllSections($exam_id, $class_id, $subjects) {
        try {
            // Get all sections for this class
            $sections = $this->getSectionsByClass($class_id);
            
            if (empty($sections)) {
                return [
                    'success' => false,
                    'message' => 'No sections found for this class'
                ];
            }
            
            $exam_id = (int)$exam_id;
            $class_id = (int)$class_id;
            $section_count = 0;
            
            // Save assignment for each section
            foreach ($sections as $section) {
                $section_id = (int)$section['id'];
                
                // Save exam class
                $exam_class_data = [
                    'exam_id' => $exam_id,
                    'class_id' => $class_id,
                    'section_id' => $section_id,
                    'status' => 'active'
                ];
                
                $exam_class_id = $this->saveExamClass($exam_class_data);
                
                if (!$exam_class_id) {
                    // Continue with next section even if one fails
                    continue;
                }
                
                // Save subjects for this section
                $subjects_saved = $this->saveExamSubjects($exam_class_id, $subjects);
                
                if ($subjects_saved) {
                    $section_count++;
                } else {
                    // Delete the exam class if subjects couldn't be saved
                    $this->deleteExamClass($exam_class_id);
                }
            }
            
            if ($section_count === 0) {
                return [
                    'success' => false,
                    'message' => 'Failed to save assignment to any sections'
                ];
            }
            
            return [
                'success' => true,
                'message' => "Exam assignment saved successfully for {$section_count} section(s)",
                'sections_count' => $section_count
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>
