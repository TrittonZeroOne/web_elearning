<?php namespace App\Models;
use CodeIgniter\Model;

class DiscussionModel extends Model {
    protected $table         = 'class_discussions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['class_id', 'sender_id', 'message'];

    public function getMessages(string $classId, int $limit = 60): array {
        return $this->db->table('class_discussions d')
            ->select('d.*, p.full_name AS sender_name, p.role AS sender_role')
            ->join('profiles p', 'p.id = d.sender_id', 'left')
            ->where('d.class_id', $classId)
            ->orderBy('d.sent_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getNewMessages(string $classId, int $lastId): array {
        return $this->db->table('class_discussions d')
            ->select('d.*, p.full_name AS sender_name, p.role AS sender_role')
            ->join('profiles p', 'p.id = d.sender_id', 'left')
            ->where('d.class_id', $classId)
            ->where('d.id >', $lastId)
            ->orderBy('d.sent_at', 'ASC')
            ->get()->getResultArray();
    }
}
