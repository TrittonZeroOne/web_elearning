<?php

namespace App\Models;

use CodeIgniter\Model;

class SubjectModel extends Model
{
    protected $table         = 'subjects';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'teacher_id', 'class_id', 'schedule_day', 'schedule_time'];

    private function baseJoin()
    {
        return $this->db->table('subjects s')
            ->select('s.*, c.name AS class_name, p.full_name AS teacher_name')
            ->join('classes c', 'c.id = s.class_id', 'left')
            ->join('profiles p', 'p.id = s.teacher_id', 'left');
    }

    public function getAll(): array
    {
        return $this->baseJoin()->orderBy('s.name')->get()->getResultArray();
    }

    public function getByTeacher(string $teacherId): array
    {
        return $this->baseJoin()
            ->where('s.teacher_id', $teacherId)
            ->orderBy('s.schedule_day')
            ->get()->getResultArray();
    }

    public function getByClass(string $classId): array
    {
        return $this->baseJoin()
            ->where('s.class_id', $classId)
            ->orderBy('s.schedule_day')
            ->get()->getResultArray();
    }

    public function getDetail(int $id): ?array
    {
        return $this->baseJoin()
            ->where('s.id', $id)
            ->get()->getRowArray();
    }
}
