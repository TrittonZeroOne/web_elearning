<?php namespace App\Models;
use CodeIgniter\Model;

class AttendanceModel extends Model {
    protected $table         = 'attendances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['subject_id', 'student_id', 'date', 'status'];

    public function getForDate(int $subjectId, string $date): array {
        return $this->where('subject_id', $subjectId)->where('date', $date)->findAll();
    }

    public function getStudentHistory(string $studentId, int $subjectId): array {
        return $this->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->orderBy('date', 'DESC')->findAll();
    }

    public function getSubjectSummary(int $subjectId): array {
        return $this->db->table('attendances a')
            ->select('a.*, p.full_name AS student_name')
            ->join('profiles p', 'p.id = a.student_id', 'left')
            ->where('a.subject_id', $subjectId)
            ->orderBy('a.date', 'DESC')->orderBy('p.full_name')
            ->get()->getResultArray();
    }

    public function upsert(array $data): bool {
        $existing = $this->where('subject_id', $data['subject_id'])
            ->where('student_id', $data['student_id'])
            ->where('date', $data['date'])->first();
        if ($existing) return $this->update($existing['id'], ['status' => $data['status']]);
        return $this->insert($data) !== false;
    }
}
