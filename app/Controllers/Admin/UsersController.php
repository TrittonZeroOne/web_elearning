<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class UsersController extends BaseController
{
    private SupabaseService $sb;

    public function __construct()
    {
        $this->sb = new SupabaseService();
    }

    public function index()
    {
        $role    = $this->request->getGet('role') ?? '';
        $keyword = $this->request->getGet('q') ?? '';
        $filters = [];
        if ($role) $filters['role'] = 'eq.' . $role;

        $users = $this->sb->select('profiles', $filters, '*', 500);

        if ($keyword) {
            $kw    = strtolower($keyword);
            $users = array_filter($users, fn($u) =>
                str_contains(strtolower($u['full_name'] ?? ''), $kw) ||
                str_contains(strtolower($u['email']     ?? ''), $kw)
            );
        }

        foreach ($users as &$u) {
            $u['class_name'] = '';
            if (!empty($u['class_id'])) {
                $cls = $this->sb->selectOne('classes', ['id' => 'eq.' . $u['class_id']], 'name');
                $u['class_name'] = $cls['name'] ?? '';
            }
        }
        usort($users, fn($a, $b) => strcmp($a['full_name'] ?? '', $b['full_name'] ?? ''));

        return view('admin/users/index', [
            'title'  => 'Manajemen Pengguna',
            'users'  => array_values($users),
            'role'   => $role,
            'search' => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin/users/create', [
            'title'   => 'Tambah Pengguna',
            'classes' => $this->sb->select('classes', [], 'id,name', 200),
        ]);
    }

    public function store()
    {
        $email   = trim((string) $this->request->getPost('email'));
        $password= (string) $this->request->getPost('password');
        $name    = trim((string) $this->request->getPost('full_name'));
        $role    = (string) $this->request->getPost('role');
        $classId = $this->request->getPost('class_id') ?: null;

        if (!$email || !$password || !$name || !$role) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi.');
        }

        $authUser = $this->sb->createUser($email, $password, [
            'full_name' => $name,
            'role'      => $role,
        ]);

        if (!$authUser || !isset($authUser['id'])) {
            $msg = $authUser['message'] ?? $authUser['msg'] ?? $authUser['error_description'] ?? 'Gagal membuat akun.';
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $this->sb->upsert('profiles', [
            'id'        => $authUser['id'],
            'email'     => $email,
            'full_name' => $name,
            'role'      => $role,
            'class_id'  => $classId,
        ]);

        return redirect()->to('/admin/users')->with('success', "Pengguna '{$name}' berhasil ditambahkan.");
    }

    public function edit(string $id)
    {
        $user = $this->sb->selectOne('profiles', ['id' => 'eq.' . $id]);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }
        return view('admin/users/edit', [
            'title'   => 'Edit Pengguna',
            'user'    => $user,
            'classes' => $this->sb->select('classes', [], 'id,name', 200),
        ]);
    }

    public function update(string $id)
    {
        $role    = (string) $this->request->getPost('role');
        $classId = $this->request->getPost('class_id') ?: null;

        $this->sb->update('profiles', ['id' => 'eq.' . $id], [
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'role'      => $role,
            'class_id'  => $classId,
        ]);

        return redirect()->to('/admin/users')->with('success', 'Data pengguna diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE — hapus dari Auth + profiles
    // ─────────────────────────────────────────────────────────────────────────

    public function delete(string $id)
    {
        if ($id === session()->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $profile = $this->sb->selectOne('profiles', ['id' => 'eq.' . $id], 'id,full_name,email');

        // ── Step 1: Hapus dari Supabase Auth ─────────────────────────────────
        $authResult = $this->sb->deleteUser($id);

        // ── Step 2: Hapus dari tabel profiles ────────────────────────────────
        // Lakukan SELALU, tidak peduli step 1 berhasil atau tidak
        if ($profile) {
            $this->sb->delete('profiles', ['id' => 'eq.' . $id]);
        }

        // ── Tentukan pesan hasil ──────────────────────────────────────────────
        if ($authResult['success']) {
            $name = $profile['full_name'] ?? $profile['email'] ?? $id;
            return redirect()->to('/admin/users')
                ->with('success', "Pengguna '{$name}' berhasil dihapus dari Auth dan database.");
        }

        // Auth gagal — tampilkan error detail agar bisa di-debug
        $statusCode = $authResult['status'];
        $msg        = $authResult['message'];

        // 404: user tidak ada di Auth (mungkin sudah dihapus manual), anggap OK
        if ($statusCode === 404) {
            $name = $profile['full_name'] ?? $id;
            return redirect()->to('/admin/users')
                ->with('success', "'{$name}' dihapus dari database (tidak ditemukan di Supabase Auth).");
        }

        // 401/403: service_role key salah atau tidak ada permission
        if ($statusCode === 401 || $statusCode === 403) {
            return redirect()->to('/admin/users')
                ->with('error', "❌ Gagal hapus dari Supabase Auth [{$statusCode}]: Service Role Key salah atau tidak ada permission. Cek SUPABASE_SERVICE_KEY di .env. Detail: {$msg}");
        }

        // Error lainnya
        return redirect()->to('/admin/users')
            ->with('error', "❌ Gagal hapus dari Supabase Auth [HTTP {$statusCode}]: {$msg}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHANGE PASSWORD — ubah password user via Supabase Auth Admin API
    // ─────────────────────────────────────────────────────────────────────────

    public function changePassword(string $id)
    {
        $password = (string) $this->request->getPost('new_password');
        $confirm  = (string) $this->request->getPost('confirm_password');

        if (strlen($password) < 6) {
            return redirect()->to('/admin/users/edit/' . $id)
                ->with('error', 'Password minimal 6 karakter.');
        }

        if ($password !== $confirm) {
            return redirect()->to('/admin/users/edit/' . $id)
                ->with('error', 'Konfirmasi password tidak cocok.');
        }

        $result = $this->sb->updateUserPassword($id, $password);

        if ($result['success'] ?? false) {
            $profile = $this->sb->selectOne('profiles', ['id' => 'eq.' . $id], 'full_name');
            $name    = $profile['full_name'] ?? $id;
            return redirect()->to('/admin/users/edit/' . $id)
                ->with('success', "Password '{$name}' berhasil diubah.");
        }

        return redirect()->to('/admin/users/edit/' . $id)
            ->with('error', 'Gagal mengubah password: ' . ($result['message'] ?? 'Unknown error'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEBUG — endpoint sementara untuk test koneksi Supabase Auth Admin API
    // Akses: GET /admin/users/debug-auth/{user_id}
    // HAPUS endpoint ini setelah masalah resolved!
    // ─────────────────────────────────────────────────────────────────────────

    public function debugAuth(string $uid)
    {
        $sb          = new SupabaseService();
        $debugResult = $sb->debugDeleteUser($uid);

        header('Content-Type: application/json');
        echo json_encode($debugResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}