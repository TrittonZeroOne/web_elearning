<?php

namespace App\Services;

/**
 * SupabaseService
 *
 * Semua komunikasi ke Supabase:
 * - Auth API  → login, logout, buat/hapus user
 * - REST API  → query tabel via PostgREST (TIDAK butuh pdo_pgsql/pgsql extension!)
 * - Storage   → upload / hapus file
 */
class SupabaseService
{
    private string $url;
    private string $anonKey;
    private string $serviceKey;

    public function __construct()
    {
        $this->url        = rtrim(env('SUPABASE_URL', ''), '/');
        $this->anonKey    = env('SUPABASE_ANON_KEY', '');
        $this->serviceKey = env('SUPABASE_SERVICE_KEY', '');
    }

    // ══════════════════════════════════════════════════
    // AUTH
    // ══════════════════════════════════════════════════

    /** Login → return ['access_token'=>..., 'user'=>[...]] | null */
    public function signIn(string $email, string $password): ?array
    {
        $result = $this->authReq('POST', '/auth/v1/token?grant_type=password', [
            'email'    => $email,
            'password' => $password,
        ], $this->anonKey);

        if (!$result || isset($result['error_code']) || isset($result['error'])) {
            log_message('error', 'Supabase signIn: ' . json_encode($result));
            return null;
        }
        return $result;
    }

    public function signOut(string $token): void
    {
        $this->authReq('POST', '/auth/v1/logout', [], $token);
    }

    public function createUser(string $email, string $password, array $meta = []): ?array
    {
        return $this->authReq('POST', '/auth/v1/admin/users', [
            'email'         => $email,
            'password'      => $password,
            'email_confirm' => true,
            'user_metadata' => $meta,
        ], $this->serviceKey);
    }

    /**
     * Hapus user dari Supabase Auth via Admin API.
     * Return: ['success'=>bool, 'status'=>int, 'message'=>string]
     */
    public function deleteUser(string $uid): array
    {
        $url = $this->url . "/auth/v1/admin/users/{$uid}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $resp    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', "deleteUser cURL error: {$curlErr}");
            return ['success' => false, 'status' => 0, 'message' => "cURL error: {$curlErr}"];
        }

        if ($code >= 200 && $code < 300) {
            log_message('info', "deleteUser success [{$code}] uid={$uid}");
            return ['success' => true, 'status' => $code, 'message' => 'Deleted'];
        }

        $body = json_decode($resp, true);
        $msg  = $body['message'] ?? $body['error_description'] ?? $body['msg'] ?? $resp;
        log_message('error', "deleteUser failed [{$code}] uid={$uid}: {$msg}");
        return ['success' => false, 'status' => $code, 'message' => (string)$msg];
    }

    /**
     * Ubah password user via Supabase Auth Admin API.
     * Return: ['success'=>bool, 'message'=>string]
     */
    public function updateUserPassword(string $uid, string $newPassword): array
    {
        $url  = $this->url . "/auth/v1/admin/users/{$uid}";
        $body = json_encode(['password' => $newPassword]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $resp    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'message' => "cURL error: {$curlErr}"];
        }
        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'message' => 'Password updated'];
        }
        $data = json_decode($resp, true);
        $msg  = $data['message'] ?? $data['error_description'] ?? $data['msg'] ?? $resp;
        return ['success' => false, 'message' => "[HTTP {$code}] {$msg}"];
    }

    /**
     * DEBUG: Test koneksi Supabase Auth Admin API.
     */
    public function debugDeleteUser(string $uid): array
    {
        $urlLoaded     = !empty($this->url)        && !str_contains($this->url, 'YOUR_PROJECT');
        $serviceLoaded = !empty($this->serviceKey) && !str_contains($this->serviceKey, 'your_service');

        $info = [
            'uid'              => $uid,
            'supabase_url'     => $urlLoaded ? substr($this->url, 0, 40) . '...' : '❌ TIDAK DISET / masih placeholder',
            'service_key_set'  => $serviceLoaded ? '✓ Set (' . strlen($this->serviceKey) . ' chars)' : '❌ TIDAK DISET / masih placeholder',
            'anon_key_set'     => !empty($this->anonKey) ? '✓ Set' : '❌ TIDAK DISET',
            'endpoint'         => $this->url . "/auth/v1/admin/users/{$uid}",
        ];

        if (!$urlLoaded || !$serviceLoaded) {
            $info['error'] = 'Konfigurasi Supabase belum lengkap di .env!';
            $info['fix']   = 'Isi SUPABASE_URL, SUPABASE_ANON_KEY, SUPABASE_SERVICE_KEY di file .env';
            return $info;
        }

        $url = $this->url . "/auth/v1/admin/users/{$uid}";
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $resp    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $info['test_get_http_code']  = $code;
        $info['test_get_curl_error'] = $curlErr ?: null;
        $info['test_get_response']   = json_decode($resp, true) ?? $resp;

        if ($code === 200) {
            $info['diagnosis'] = '✓ API bisa diakses. DELETE seharusnya bekerja.';
        } elseif ($code === 401) {
            $info['diagnosis'] = '❌ 401 Unauthorized — SUPABASE_SERVICE_KEY salah atau bukan service_role key.';
            $info['fix']       = 'Pastikan SUPABASE_SERVICE_KEY di .env menggunakan SERVICE ROLE key (bukan anon key).';
        } elseif ($code === 403) {
            $info['diagnosis'] = '❌ 403 Forbidden — Tidak ada permission Admin. Pastikan pakai service_role key.';
        } elseif ($code === 404) {
            $info['diagnosis'] = '⚠️ 404 — User tidak ditemukan di Supabase Auth dengan UID ini.';
        } elseif ($code === 0) {
            $info['diagnosis'] = '❌ Koneksi gagal — cURL error: ' . $curlErr;
        } else {
            $info['diagnosis'] = "HTTP {$code} — lihat test_get_response untuk detail.";
        }

        return $info;
    }

    // ══════════════════════════════════════════════════
    // REST API — PostgREST
    // ══════════════════════════════════════════════════

    /**
     * SELECT banyak baris.
     * $filters format: ['col' => 'eq.value', 'col2' => 'gte.10']
     *
     * PENTING: pisahkan `select=` dari http_build_query agar karakter `*`
     * tidak di-encode menjadi `%2A` (PostgREST hanya mengenal literal `*`).
     */
    public function select(string $table, array $filters = [], string $cols = '*', int $limit = 200): array
    {
        $q = $filters;
        if ($limit) $q['limit'] = $limit;
        // Bangun query string: select= ditaruh manual di depan agar * tidak ter-encode
        $qs  = 'select=' . $cols;
        if ($q) $qs .= '&' . http_build_query($q);
        $url = $this->url . '/rest/v1/' . $table . '?' . $qs;
        $result = $this->restGet($url);
        return is_array($result) ? $result : [];
    }

    /** SELECT satu baris */
    public function selectOne(string $table, array $filters = [], string $cols = '*'): ?array
    {
        $rows = $this->select($table, $filters, $cols, 1);
        return $rows[0] ?? null;
    }

    /** INSERT → return inserted row atau null */
    public function insert(string $table, array $data): ?array
    {
        $url    = $this->url . '/rest/v1/' . $table;
        $result = $this->restMutate('POST', $url, $data, ['Prefer: return=representation']);
        if (is_array($result) && isset($result[0])) return $result[0];
        return null;
    }

    /** UPDATE berdasarkan filter → return bool */
    public function update(string $table, array $filters, array $data): bool
    {
        $q   = http_build_query($filters);
        $url = $this->url . '/rest/v1/' . $table . '?' . $q;
        $r   = $this->restMutate('PATCH', $url, $data, ['Prefer: return=minimal']);
        return $r !== null;
    }

    /** UPSERT (insert or update on conflict) */
    public function upsert(string $table, array $data): ?array
    {
        $url    = $this->url . '/rest/v1/' . $table;
        $result = $this->restMutate('POST', $url, $data, [
            'Prefer: return=representation',
            'Prefer: resolution=merge-duplicates',
        ]);
        if (is_array($result) && isset($result[0])) return $result[0];
        return null;
    }

    /** DELETE berdasarkan filter */
    public function delete(string $table, array $filters): bool
    {
        $q   = http_build_query($filters);
        $url = $this->url . '/rest/v1/' . $table . '?' . $q;
        return $this->restMutate('DELETE', $url, []) !== null;
    }

    /** COUNT baris */
    public function count(string $table, array $filters = []): int
    {
        $rows = $this->select($table, $filters, 'id', 9999);
        return count($rows);
    }

    // ══════════════════════════════════════════════════
    // STORAGE
    // ══════════════════════════════════════════════════

    /**
     * Upload file ke Supabase Storage → return public URL | null
     *
     * PENTING: Bucket harus PUBLIC di Supabase Dashboard agar URL bisa diakses.
     * Gunakan service_role key agar bypass RLS storage.
     */
    public function uploadFile(string $bucket, string $path, string $filePath, string $mime): ?string
    {
        if (!file_exists($filePath)) {
            log_message('error', "uploadFile: file tidak ditemukan: {$filePath}");
            return null;
        }

        $ch = curl_init($this->url . "/storage/v1/object/{$bucket}/{$path}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => file_get_contents($filePath),
            CURLOPT_HTTPHEADER     => [
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Content-Type: '         . $mime,
                'x-upsert: true',
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $resp    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', "uploadFile cURL error: {$curlErr}");
            return null;
        }

        if ($code >= 400) {
            log_message('error', "uploadFile HTTP [{$code}] bucket={$bucket} path={$path}: {$resp}");
            return null;
        }

        return $this->publicUrl($bucket, $path);
    }

    /** Hapus file dari storage */
    public function deleteFile(string $bucket, string $path): bool
    {
        $ch = curl_init($this->url . "/storage/v1/object/{$bucket}/{$path}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_exec($ch);
        curl_close($ch);
        return $code < 400;
    }

    /** Generate public URL untuk file di bucket publik */
    public function publicUrl(string $bucket, string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /** Generate signed URL untuk file di bucket private (berlaku $expiresIn detik) */
    public function signedUrl(string $bucket, string $path, int $expiresIn = 3600): ?string
    {
        $url    = $this->url . "/storage/v1/object/sign/{$bucket}/{$path}";
        $result = $this->restMutate('POST', $url, ['expiresIn' => $expiresIn]);
        if (isset($result['signedURL'])) {
            return $this->url . $result['signedURL'];
        }
        return null;
    }

    // ══════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════

    private function authReq(string $method, string $ep, array $data, string $token): ?array
    {
        $ch = curl_init($this->url . $ep);
        $h  = [
            'Content-Type: application/json',
            'apikey: ' . $this->anonKey,
        ];
        if ($token) $h[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $h,
            CURLOPT_TIMEOUT        => 15,
        ]);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', "Auth cURL [{$method} {$ep}]: {$err}");
            return null;
        }
        if ($code >= 400) {
            log_message('error', "Auth API [{$method} {$ep}] HTTP {$code}: {$resp}");
            $d = json_decode($resp, true);
            return is_array($d) ? $d : ['error' => $resp, 'status' => $code];
        }
        $d = json_decode($resp, true);
        return is_array($d) ? $d : null;
    }

    private function restGet(string $url): mixed
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            log_message('error', "REST GET cURL: {$err}");
            return null;
        }
        if ($code >= 400) {
            log_message('error', "REST GET [{$code}] {$url}: {$resp}");
            return null;
        }
        return json_decode($resp, true);
    }

    private function restMutate(string $method, string $url, array $data, array $extra = []): mixed
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => array_merge([
                'apikey: '               . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Content-Type: application/json',
            ], $extra),
            CURLOPT_TIMEOUT => 15,
        ]);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', "REST {$method} cURL: {$err}");
            return null;
        }
        if ($code >= 400) {
            log_message('error', "REST {$method} [{$code}] {$url}: {$resp}");
            return null;
        }
        if (empty($resp) || $resp === 'null') return true;
        $d = json_decode($resp, true);
        return $d ?? true;
    }
}