<?php

namespace App\Models;

use CodeIgniter\Model;

class SubmissionModel extends Model
{
    protected $table         = 'submissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['assignment_id', 'student_id', 'file_url', 'grade', 'feedback', 'submitted_at'];

    public function getByAssignment(int $assignmentId): array
    {
        return $this->db->table('submissions s')
            ->select('s.*, p.full_name AS student_name')
            ->join('profiles p', 'p.id = s.student_id', 'left')
            ->where('s.assignment_id', $assignmentId)
            ->orderBy('p.full_name')
            ->get()->getResultArray();
    }

    public function getRecentByTeacher(string $teacherId, int $limit = 6): array
    {
        return $this->db->table('submissions s')
            ->select('s.*, p.full_name AS student_name, a.title AS assignment_title, sub.name AS subject_name')
            ->join('profiles p', 'p.id = s.student_id', 'left')
            ->join('assignments a', 'a.id = s.assignment_id', 'left')
            ->join('subjects sub', 'sub.id = a.subject_id', 'left')
            ->where('sub.teacher_id', $teacherId)
            ->orderBy('s.submitted_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function upsert(array $data): bool
    {
        $existing = $this->where('assignment_id', $data['assignment_id'])
            ->where('student_id', $data['student_id'])
            ->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        // PostgreSQL: INSERT ... ON CONFLICT handled manually
        return $this->insert($data) !== false;
    }
}
