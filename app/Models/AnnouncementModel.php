<?php namespace App\Models;
use CodeIgniter\Model;

class AnnouncementModel extends Model {
    protected $table         = 'announcements';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['title', 'body', 'target', 'sender_id'];

    public function getWithSender(array $targetFilter = [], int $limit = 10): array {
        $builder = $this->db->table('announcements a')
            ->select('a.*, p.full_name AS sender_name')
            ->join('profiles p', 'p.id = a.sender_id', 'left')
            ->orderBy('a.created_at', 'DESC');
        if ($targetFilter) {
            $builder->whereIn('a.target', $targetFilter);
        }
        return $builder->limit($limit)->get()->getResultArray();
    }
}
