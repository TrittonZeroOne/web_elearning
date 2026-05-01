diff --git a/c:\laragon\www\elearning\README.md b/c:\laragon\www\elearning\README.md
new file mode 100644
--- /dev/null
+++ b/c:\laragon\www\elearning\README.md
@@ -0,0 +1,199 @@
+# E-Learning App
+
+Aplikasi e-learning berbasis CodeIgniter 4 dan Supabase. Sistem ini menyediakan dashboard terpisah untuk admin, guru, dan siswa, lengkap dengan manajemen kelas, mata pelajaran, materi, tugas, absensi, pengumuman, diskusi kelas, dan chat.
+
+## Fitur
+
+- Autentikasi login dan logout menggunakan Supabase Auth.
+- Hak akses berdasarkan role: admin, teacher, dan student.
+- Dashboard ringkasan untuk masing-masing role.
+- Manajemen pengguna, kelas, mata pelajaran, dan pengumuman untuk admin.
+- Upload materi dan lampiran tugas oleh guru.
+- Pengumpulan tugas oleh siswa.
+- Penilaian tugas dan feedback dari guru.
+- Absensi per mata pelajaran.
+- Diskusi kelas berbasis polling.
+- Chat langsung antar role terkait.
+- Integrasi Supabase REST API dan Supabase Storage.
+
+## Teknologi
+
+- PHP 8.2 atau lebih baru
+- CodeIgniter 4
+- Composer
+- Supabase Auth
+- Supabase PostgREST
+- Supabase Storage
+- PHPUnit
+
+## Kebutuhan Sistem
+
+Pastikan extension PHP berikut aktif:
+
+- intl
+- mbstring
+- json
+- curl
+
+Jika menjalankan database lokal CodeIgniter, aktifkan juga driver database yang sesuai. Aplikasi ini menggunakan Supabase REST API untuk operasi utama, sehingga tidak membutuhkan koneksi PostgreSQL langsung untuk alur Supabase yang sudah tersedia.
+
+## Instalasi
+
+1. Clone repository.
+
+```bash
+git clone https://github.com/username/elearning.git
+cd elearning
+```
+
+2. Install dependency PHP.
+
+```bash
+composer install
+```
+
+3. Siapkan file environment.
+
+Jika belum ada file `.env`, salin dari template CodeIgniter:
+
+```bash
+cp env .env
+```
+
+Pada Windows PowerShell:
+
+```powershell
+Copy-Item env .env
+```
+
+4. Atur konfigurasi aplikasi di `.env`.
+
+```ini
+CI_ENVIRONMENT = development
+
+app.baseURL = 'http://localhost/elearning/'
+
+SUPABASE_URL = 'https://your-project-ref.supabase.co'
+SUPABASE_ANON_KEY = 'your-anon-key'
+SUPABASE_SERVICE_KEY = 'your-service-role-key'
+```
+
+> Jangan commit `.env` ke repository karena berisi credential Supabase.
+
+5. Jalankan aplikasi.
+
+Dengan development server CodeIgniter:
+
+```bash
+php spark serve
+```
+
+Lalu buka:
+
+```text
+http://localhost:8080
+```
+
+Jika menggunakan Laragon atau Apache, arahkan document root ke folder `public` atau pastikan konfigurasi virtual host mengikuti standar CodeIgniter 4.
+
+## Konfigurasi Supabase
+
+Aplikasi membutuhkan project Supabase yang memiliki Auth, tabel database, dan bucket storage.
+
+Environment wajib:
+
+- `SUPABASE_URL`: URL project Supabase.
+- `SUPABASE_ANON_KEY`: anon public key untuk proses login.
+- `SUPABASE_SERVICE_KEY`: service role key untuk operasi admin, REST mutation, dan upload storage.
+
+Bucket storage yang digunakan:
+
+- `assignments`: lampiran tugas guru dan file submission siswa.
+
+Pastikan bucket yang digunakan untuk file publik sudah diatur sebagai public bucket, atau sesuaikan implementasi agar memakai signed URL.
+
+## Struktur Modul
+
+```text
+app/
+  Controllers/
+    Admin/       Controller untuk panel admin
+    Teacher/     Controller untuk fitur guru
+    Student/     Controller untuk fitur siswa
+  Filters/       AuthFilter dan RoleFilter
+  Models/        Model untuk tabel aplikasi
+  Services/      SupabaseService
+  Views/         Halaman dashboard dan modul aplikasi
+public/          Entry point web
+tests/           Test PHPUnit
+writable/        Cache, log, session, dan upload runtime
+```
+
+## Role dan Akses
+
+### Admin
+
+- Mengelola user.
+- Mengelola kelas.
+- Mengelola mata pelajaran.
+- Membuat dan menghapus pengumuman.
+- Melihat statistik.
+- Chat dengan guru.
+
+### Teacher
+
+- Melihat mata pelajaran yang diajar.
+- Mengunggah materi.
+- Membuat tugas.
+- Menilai submission siswa.
+- Mengelola absensi.
+- Mengikuti diskusi kelas.
+- Chat dengan admin dan siswa.
+
+### Student
+
+- Melihat mata pelajaran berdasarkan kelas.
+- Mengakses materi.
+- Mengumpulkan tugas.
+- Melihat nilai dan feedback.
+- Melihat riwayat absensi.
+- Mengikuti diskusi kelas.
+- Chat dengan guru.
+
+## Route Utama
+
+- `/login`: halaman login.
+- `/logout`: keluar dari sesi.
+- `/dashboard`: dashboard sesuai role.
+- `/admin/users`: manajemen user.
+- `/admin/classes`: manajemen kelas.
+- `/admin/subjects`: manajemen mata pelajaran.
+- `/admin/announcements`: manajemen pengumuman.
+- `/admin/statistics`: statistik.
+- `/teacher/subjects`: daftar mata pelajaran guru.
+- `/student/subjects`: daftar mata pelajaran siswa.
+
+## Testing
+
+Jalankan test dengan Composer:
+
+```bash
+composer test
+```
+
+Atau langsung melalui PHPUnit:
+
+```bash
+vendor/bin/phpunit
+```
+
+## Catatan Keamanan
+
+- Jangan menyimpan `SUPABASE_SERVICE_KEY` di repository publik.
+- Pastikan `.env`, `writable/logs`, `writable/session`, dan file runtime lain tidak ikut ter-commit.
+- Untuk deployment production, ubah `CI_ENVIRONMENT` menjadi `production`.
+- Batasi akses endpoint debug sebelum aplikasi dipublikasikan.
+
+## Lisensi
+
+Project ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail.
