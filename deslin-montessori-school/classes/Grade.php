<?php
/**
 * Grade Model Class
 * Handles grade and result data operations
 */

require_once __DIR__ . '/../config/database.php';

class Grade {
    private $db;
    private $table = 'grades';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a grade record
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (student_id, subject_id, class_id, term_id, academic_year, test_score, 
             exam_score, assignment_score, class_work_score, total_score, 
             grade_letter, grade_point, remarks, teacher_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        // Calculate total score
        $totalScore = ($data['test_score'] ?? 0) + ($data['exam_score'] ?? 0) + 
                      ($data['assignment_score'] ?? 0) + ($data['class_work_score'] ?? 0);
        
        // Calculate grade letter and point
        $gradeInfo = $this->calculateGrade($totalScore);
        $gradeLetter = $gradeInfo['letter'];
        $gradePoint = $gradeInfo['point'];

        $stmt->bind_param(
            'iiiiidddddddsi',
            $data['student_id'],
            $data['subject_id'],
            $data['class_id'],
            $data['term_id'],
            $data['academic_year'],
            $data['test_score'],
            $data['exam_score'],
            $data['assignment_score'],
            $data['class_work_score'],
            $totalScore,
            $gradeLetter,
            $gradePoint,
            $data['remarks'],
            $data['teacher_id']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'grade_id' => $this->db->insert_id, 'message' => 'Grade recorded successfully'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Get grade by ID
     */
    public function getById($gradeId) {
        $stmt = $this->db->prepare(
            "SELECT g.*, sub.subject_name, s.first_name, s.last_name, t.term_name 
             FROM {$this->table} g 
             LEFT JOIN subjects sub ON g.subject_id = sub.subject_id 
             LEFT JOIN students s ON g.student_id = s.student_id 
             LEFT JOIN examination_terms t ON g.term_id = t.term_id 
             WHERE g.grade_id = ?"
        );

        $stmt->bind_param('i', $gradeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get grades for a student in a term
     */
    public function getStudentTermGrades($studentId, $termId) {
        $stmt = $this->db->prepare(
            "SELECT g.*, sub.subject_name, sub.subject_code 
             FROM {$this->table} g 
             LEFT JOIN subjects sub ON g.subject_id = sub.subject_id 
             WHERE g.student_id = ? AND g.term_id = ? 
             ORDER BY sub.subject_name"
        );

        $stmt->bind_param('ii', $studentId, $termId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get grades for a class in a term
     */
    public function getClassTermGrades($classId, $termId, $subjectId = null) {
        $query = "SELECT g.*, s.student_reg_no, s.first_name, s.last_name, sub.subject_name 
                  FROM {$this->table} g 
                  JOIN students s ON g.student_id = s.student_id 
                  LEFT JOIN subjects sub ON g.subject_id = sub.subject_id 
                  WHERE g.class_id = ? AND g.term_id = ?";

        if ($subjectId) {
            $query .= " AND g.subject_id = ?";
            $stmt = $this->db->prepare($query . " ORDER BY s.first_name, s.last_name");
            $stmt->bind_param('iii', $classId, $termId, $subjectId);
        } else {
            $stmt = $this->db->prepare($query . " ORDER BY s.first_name, s.last_name, sub.subject_name");
            $stmt->bind_param('ii', $classId, $termId);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update grade
     */
    public function update($gradeId, $data) {
        $updateFields = [];
        $types = '';
        $values = [];

        if (isset($data['test_score'])) {
            $updateFields[] = 'test_score = ?';
            $types .= 'd';
            $values[] = $data['test_score'];
        }
        if (isset($data['exam_score'])) {
            $updateFields[] = 'exam_score = ?';
            $types .= 'd';
            $values[] = $data['exam_score'];
        }
        if (isset($data['assignment_score'])) {
            $updateFields[] = 'assignment_score = ?';
            $types .= 'd';
            $values[] = $data['assignment_score'];
        }
        if (isset($data['class_work_score'])) {
            $updateFields[] = 'class_work_score = ?';
            $types .= 'd';
            $values[] = $data['class_work_score'];
        }
        if (isset($data['remarks'])) {
            $updateFields[] = 'remarks = ?';
            $types .= 's';
            $values[] = $data['remarks'];
        }

        // Recalculate total score and grade
        $grade = $this->getById($gradeId);
        $totalScore = ($data['test_score'] ?? $grade['test_score']) + 
                      ($data['exam_score'] ?? $grade['exam_score']) + 
                      ($data['assignment_score'] ?? $grade['assignment_score']) + 
                      ($data['class_work_score'] ?? $grade['class_work_score']);
        
        $gradeInfo = $this->calculateGrade($totalScore);
        $updateFields[] = 'total_score = ?';
        $updateFields[] = 'grade_letter = ?';
        $updateFields[] = 'grade_point = ?';
        $types .= 'dsd';
        $values[] = $totalScore;
        $values[] = $gradeInfo['letter'];
        $values[] = $gradeInfo['point'];

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $types .= 'i';
        $values[] = $gradeId;

        $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE grade_id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grade updated successfully'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Delete grade record
     */
    public function delete($gradeId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE grade_id = ?");
        $stmt->bind_param('i', $gradeId);

        return $stmt->execute() ? 
            ['success' => true, 'message' => 'Grade deleted successfully'] : 
            ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Calculate grade letter and point based on score
     */
    public function calculateGrade($totalScore) {
        if ($totalScore >= 90) {
            return ['letter' => 'A', 'point' => 4.0];
        } elseif ($totalScore >= 80) {
            return ['letter' => 'B', 'point' => 3.0];
        } elseif ($totalScore >= 70) {
            return ['letter' => 'C', 'point' => 2.0];
        } elseif ($totalScore >= 60) {
            return ['letter' => 'D', 'point' => 1.0];
        } else {
            return ['letter' => 'F', 'point' => 0.0];
        }
    }

    /**
     * Get student average score for a term
     */
    public function getStudentTermAverage($studentId, $termId) {
        $stmt = $this->db->prepare(
            "SELECT AVG(total_score) as average, COUNT(grade_id) as subject_count 
             FROM {$this->table} 
             WHERE student_id = ? AND term_id = ?"
        );

        $stmt->bind_param('ii', $studentId, $termId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get class rankings for a term
     */
    public function getClassRankings($classId, $termId) {
        $query = "SELECT 
                    s.student_id,
                    s.student_reg_no,
                    s.first_name,
                    s.last_name,
                    AVG(g.total_score) as average_score,
                    SUM(g.total_score) as total_marks,
                    COUNT(g.grade_id) as subject_count,
                    ROW_NUMBER() OVER (ORDER BY AVG(g.total_score) DESC) as position
                  FROM students s
                  LEFT JOIN {$this->table} g ON s.student_id = g.student_id 
                    AND g.term_id = ? AND g.class_id = ?
                  WHERE s.current_class_id = ? AND s.student_status = 'Active'
                  GROUP BY s.student_id
                  ORDER BY average_score DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('iii', $termId, $classId, $classId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>