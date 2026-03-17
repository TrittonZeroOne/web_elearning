<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table         = 'assignments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['subject_id', 'title', 'description', 'deadline', 'file_url'];

    public function getBySubject(int $subjectId): array
    {
        return $this->where('subject_id', $subjectId)
            ->orderBy('deadline', 'ASC')
            ->findAll();
    }

    public function getWithMySubmission(int $subjectId, string $studentId): array
    {
        $assignments = $this->getBySubject($subjectId);
        $subModel    = new SubmissionModel();
        foreach ($assignments as &$a) {
            $a['my_submission'] = $subModel
                ->where('assignment_id', $a['id'])
                ->where('student_id', $studentId)
                ->first();
        }
        return $assignments;
    }

    public function getPendingForStudent(string $studentId, string $classId): array
    {
        return $this->db->table('assignments a')
            ->select('a.*, s.name AS subject_name')
            ->join('subjects s', 's.id = a.subject_id')
            ->join('submissions sub', "sub.assignment_id = a.id AND sub.student_id = '{$studentId}'", 'left')
            ->where('s.class_id', $classId)
            ->where('sub.id IS NULL')
            ->where('a.deadline >', date('Y-m-d H:i:sP'))
            ->orderBy('a.deadline', 'ASC')
            ->limit(5)
            ->get()->getResultArray();
    }
}
