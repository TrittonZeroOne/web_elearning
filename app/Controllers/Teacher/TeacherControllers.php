<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

// ── MaterialsController ───────────────────────────────────────────
class MaterialsController extends BaseController
{
    public function store()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $type      = $this->request->getPost('type');
        $fileUrl   = null;

        if (in_array($type, ['PDF', 'Dokumen', 'Lainnya'])) {
            $file = $this->request->getFile('file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if ($file->getSize() > 10 * 1024 * 1024) {
                    return redirect()->back()->with('error', 'File maksimal 10MB.');
                }
                $path    = "subject_{$subjectId}/" . uniqid() . '_' . $file->getClientName();
                $fileUrl = $sb->uploadFile('materials', $path, $file->getTempName(), $file->getMimeType());
                if (!$fileUrl) return redirect()->back()->with('error', 'Gagal upload ke Supabase Storage.');
            }
        } else {
            $fileUrl = trim((string) $this->request->getPost('content_url'));
        }

        $sb->insert('materials', [
            'subject_id'  => $subjectId,
            'title'       => trim((string) $this->request->getPost('title')),
            'type'        => $type,
            'content_url' => $fileUrl,
            'description' => trim((string) $this->request->getPost('description')),
        ]);

        return redirect()->to("/teacher/subjects/{$subjectId}/materi")->with('success', 'Materi ditambahkan.');
    }

    public function delete(int $id)
    {
        $sb = new SupabaseService();
        $m  = $sb->selectOne('materials', ['id' => 'eq.' . $id]);
        if ($m) {
            $sb->delete('materials', ['id' => 'eq.' . $id]);
            return redirect()->to("/teacher/subjects/{$m['subject_id']}/materi")->with('success', 'Materi dihapus.');
        }
        return redirect()->back();
    }
}

// ── AssignmentsController ─────────────────────────────────────────
class AssignmentsController extends BaseController
{
    public function store()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $fileUrl   = null;

        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'File maksimal 10MB.');
            }
            $path    = "subject_{$subjectId}/" . uniqid() . '_' . $file->getClientName();
            $fileUrl = $sb->uploadFile('assignments', $path, $file->getTempName(), $file->getMimeType());
        }

        $deadline = $this->request->getPost('deadline');
        $sb->insert('assignments', [
            'subject_id'  => $subjectId,
            'title'       => trim((string) $this->request->getPost('title')),
            'description' => trim((string) $this->request->getPost('description')),
            'deadline'    => $deadline ? date('c', strtotime($deadline)) : null,
            'file_url'    => $fileUrl,
        ]);

        return redirect()->to("/teacher/subjects/{$subjectId}/tugas")->with('success', 'Tugas dibuat.');
    }

    public function delete(int $id)
    {
        $sb = new SupabaseService();
        $a  = $sb->selectOne('assignments', ['id' => 'eq.' . $id]);
        if ($a) {
            $sb->delete('assignments', ['id' => 'eq.' . $id]);
            return redirect()->to("/teacher/subjects/{$a['subject_id']}/tugas")->with('success', 'Tugas dihapus.');
        }
        return redirect()->back();
    }

    public function grade(int $submissionId)
    {
        $sb    = new SupabaseService();
        $grade = min(100, max(0, (int) $this->request->getPost('grade')));
        $sb->update('submissions', ['id' => 'eq.' . $submissionId], [
            'grade'    => $grade,
            'feedback' => trim((string) $this->request->getPost('feedback')),
        ]);
        return redirect()->back()->with('success', "Nilai {$grade} disimpan.");
    }
}

// ── AttendanceController ──────────────────────────────────────────
class AttendanceController extends BaseController
{
    public function save()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $date      = $this->request->getPost('date');
        $statuses  = (array) ($this->request->getPost('status') ?? []);

        foreach ($statuses as $studentId => $status) {
            // Cek existing
            $existing = $sb->selectOne('attendances', [
                'subject_id' => 'eq.' . $subjectId,
                'student_id' => 'eq.' . $studentId,
                'date'       => 'eq.' . $date,
            ], 'id');

            if ($existing) {
                $sb->update('attendances', ['id' => 'eq.' . $existing['id']], ['status' => $status]);
            } else {
                $sb->insert('attendances', [
                    'subject_id' => $subjectId,
                    'student_id' => $studentId,
                    'date'       => $date,
                    'status'     => $status,
                ]);
            }
        }

        return redirect()->to("/teacher/subjects/{$subjectId}/absensi?date={$date}")
            ->with('success', 'Absensi disimpan.');
    }

    public function export(int $subjectId)
    {
        $sb   = new SupabaseService();
        $data = $sb->select('attendances', ['subject_id' => 'eq.' . $subjectId], '*', 9999);

        // Enrich names
        foreach ($data as &$r) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $r['student_id']], 'full_name');
            $r['student_name'] = $p['full_name'] ?? '';
        }
        usort($data, fn($a, $b) => strcmp($a['student_name'], $b['student_name']) ?: strcmp($a['date'], $b['date']));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="absensi_' . $subjectId . '_' . date('Ymd') . '.csv"');
        $f = fopen('php://output', 'w');
        fputs($f, "\xEF\xBB\xBF");
        fputcsv($f, ['Nama Siswa', 'Tanggal', 'Status']);
        foreach ($data as $r) fputcsv($f, [$r['student_name'], $r['date'], $r['status']]);
        fclose($f);
        exit;
    }
}

// ── DiscussionController ──────────────────────────────────────────
class DiscussionController extends BaseController
{
    public function send()
    {
        $sb      = new SupabaseService();
        $classId = $this->request->getPost('class_id');
        $message = trim((string) $this->request->getPost('message'));

        if (!$message) return $this->response->setStatusCode(400)->setJSON(['error' => 'Pesan kosong']);

        $inserted = $sb->insert('class_discussions', [
            'class_id'  => $classId,
            'sender_id' => session()->get('user_id'),
            'message'   => $message,
        ]);

        if ($inserted) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $inserted['sender_id']], 'full_name,role');
            $inserted['sender_name'] = $p['full_name'] ?? session()->get('full_name');
            $inserted['sender_role'] = $p['role'] ?? session()->get('role');
        }

        return $this->response->setJSON(['message' => $inserted]);
    }

    public function poll(string $classId, int $lastId)
    {
        $sb   = new SupabaseService();
        $msgs = $sb->select('class_discussions', [
            'class_id' => 'eq.' . $classId,
            'id'       => 'gt.' . $lastId,
        ], '*', 50);

        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));

        foreach ($msgs as &$m) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $m['sender_id']], 'full_name,role');
            $m['sender_name'] = $p['full_name'] ?? '';
            $m['sender_role'] = $p['role'] ?? '';
        }

        return $this->response->setJSON(['messages' => $msgs]);
    }
}
