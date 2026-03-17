<?php

namespace App\Controllers;

use App\Services\SupabaseService;

/**
 * AuthController
 *
 * Login/logout via Supabase Auth API.
 * Profile diambil via Supabase REST API (PostgREST) — tidak butuh koneksi DB PostgreSQL.
 */
class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function doLogin()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if (!$email || !$password) {
            return redirect()->back()->with('error', 'Email dan password harus diisi.')->withInput();
        }

        $sb = new SupabaseService();

        // ── Step 1: Supabase Auth ─────────────────────────────
        $auth = $sb->signIn($email, $password);

        if (!$auth || empty($auth['access_token'])) {
            return redirect()->back()
                ->with('error', 'Email atau password salah. Pastikan akunmu sudah terdaftar')
                ->withInput();
        }

        // ── Step 2: Ambil profil via REST API ─────────────────
        // Tidak perlu pdo_pgsql — murni HTTP ke PostgREST
        $profile = $sb->selectOne('profiles', ['email' => 'eq.' . $email]);

        // ── Step 3: Auto-buat profil jika belum ada ───────────
        if (!$profile) {
            $uid     = $auth['user']['id'] ?? null;
            $meta    = $auth['user']['user_metadata'] ?? [];

            if ($uid) {
                $profile = $sb->upsert('profiles', [
                    'id'        => $uid,
                    'email'     => $email,
                    'full_name' => $meta['full_name'] ?? explode('@', $email)[0],
                    'role'      => $meta['role'] ?? 'student',
                    'class_id'  => $meta['class_id'] ?? null,
                ]);
            }
        }

        if (!$profile) {
            return redirect()->back()->with('error', 'Profil tidak ditemukan. Hubungi administrator.');
        }

        // ── Step 4: Set session ───────────────────────────────
        session()->set([
            'logged_in'    => true,
            'user_id'      => $profile['id'],
            'email'        => $profile['email'],
            'full_name'    => $profile['full_name'] ?? $email,
            'role'         => $profile['role'] ?? 'student',
            'class_id'     => $profile['class_id'] ?? null,
            'avatar_url'   => $profile['avatar_url'] ?? null,
            'access_token' => $auth['access_token'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        $token = session()->get('access_token');
        if ($token) {
            (new SupabaseService())->signOut($token);
        }
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Berhasil keluar.');
    }
}
