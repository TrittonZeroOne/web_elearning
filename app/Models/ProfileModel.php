<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ProfileModel
 * Tabel: public.profiles (dari Supabase)
 * Kolom: id (uuid), email, full_name, role, class_id, avatar_url
 *
 * Auth ditangani Supabase Auth API — tidak ada kolom password di sini.
 * Tabel ini di-sync dengan auth.users via Supabase trigger/RLS.
 */
class ProfileModel extends Model
{
    protected $table         = 'profiles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['id', 'email', 'full_name', 'role', 'class_id', 'avatar_url'];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function getStudentsForClass(string $classId): array
    {
        return $this->where('class_id', $classId)
            ->where('role', 'student')
            ->orderBy('full_name', 'ASC')
            ->findAll();
    }

    public function getTeachers(): array
    {
        return $this->where('role', 'teacher')->orderBy('full_name')->findAll();
    }

    public function searchUsers(string $role = '', string $keyword = ''): array
    {
        $builder = $this->db->table('profiles p')
            ->select('p.*, c.name AS class_name')
            ->join('classes c', 'c.id = p.class_id', 'left')
            ->orderBy('p.full_name');

        if ($role) {
            $builder->where('p.role', $role);
        }
        if ($keyword) {
            $builder->groupStart()
                ->like('p.full_name', $keyword)
                ->orLike('p.email', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Buat user via Supabase Admin Auth API + insert profile.
     * Dipanggil dari Admin UsersController.
     */
    public function insertProfile(array $data): bool
    {
        return $this->insert($data) !== false;
    }
}
