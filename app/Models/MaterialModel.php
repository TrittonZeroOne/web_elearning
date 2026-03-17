<?php namespace App\Models;
use CodeIgniter\Model;

class MaterialModel extends Model {
    protected $table         = 'materials';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['subject_id', 'title', 'type', 'content_url', 'description'];

    public function getBySubject(int $subjectId): array {
        return $this->where('subject_id', $subjectId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
