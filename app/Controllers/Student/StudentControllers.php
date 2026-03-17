<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

// ── SubjectsController ────────────────────────────────────────────
class SubjectsController extends BaseController
{
    private SupabaseService $sb;
    public function __construct() { $this->sb = new SupabaseService(); }

    private function getSubject(int $id): ?array
    {
        $s = $this->sb->selectOne('subjects', ['id' => 'eq.' . $id]);
        if (!$s || $s['class_id'] !== session()->get('class_id')) return null;
        $cls = $this->sb->selectOne('classes', ['id' => 'eq.' . $s['class_id']], 'name');
        $t   = $this->sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
        $s['class_name']   = $cls['name'] ?? '';
        $s['teacher_name'] = $t['full_name'] ?? '';
        return $s;
    }

    public function index()
    {
        $subjects = $this->sb->select('subjects', ['class_id' => 'eq.' . session()->get('class_id')]);
        foreach ($subjects as &$s) {
            $t = $this->sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
            $s['teacher_name'] = $t['full_name'] ?? '';
        }
        return view('student/subjects/index', ['title' => 'Mata Pelajaran', 'subjects' => $subjects]);
    }

    public function materi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');
        $materials = $this->sb->select('materials', ['subject_id' => 'eq.' . $id]);
        usort($materials, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return view('student/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'materi', 'materials' => $materials,
        ]);
    }

    public function tugas(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');
        $userId      = session()->get('user_id');
        $assignments = $this->sb->select('assignments', ['subject_id' => 'eq.' . $id]);
        usort($assignments, fn($a, $b) => strcmp($a['deadline'] ?? '', $b['deadline'] ?? ''));
        foreach ($assignments as &$a) {
            $a['my_submission'] = $this->sb->selectOne('submissions', [
                'assignment_id' => 'eq.' . $a['id'],
                'student_id'    => 'eq.' . $userId,
            ]);
        }
        return view('student/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'tugas', 'assignments' => $assignments,
        ]);
    }

    public function absensi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');
        $userId  = session()->get('user_id');
        $history = $this->sb->select('attendances', [
            'subject_id' => 'eq.' . $id,
            'student_id' => 'eq.' . $userId,
        ]);
        usort($history, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        $total = count($history);
        $hadir = count(array_filter($history, fn($r) => $r['status'] === 'Hadir'));
        $sakit = count(array_filter($history, fn($r) => $r['status'] === 'Sakit'));
        $izin  = count(array_filter($history, fn($r) => $r['status'] === 'Izin'));
        $alfa  = count(array_filter($history, fn($r) => $r['status'] === 'Alfa'));
        return view('student/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'absensi', 'history' => $history,
            'total' => $total, 'hadir' => $hadir, 'sakit' => $sakit,
            'izin' => $izin, 'alfa' => $alfa,
            'persen' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
        ]);
    }

    public function diskusi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');
        $msgs = $this->sb->select('class_discussions', ['class_id' => 'eq.' . $subject['class_id']], '*', 60);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        foreach ($msgs as &$m) {
            $p = $this->sb->selectOne('profiles', ['id' => 'eq.' . $m['sender_id']], 'full_name,role');
            $m['sender_name'] = $p['full_name'] ?? '';
            $m['sender_role'] = $p['role'] ?? '';
        }
        return view('student/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'diskusi', 'messages' => $msgs,
        ]);
    }
}

// ── AssignmentsController ─────────────────────────────────────────
class AssignmentsController extends BaseController
{
    public function submit()
    {
        $sb           = new SupabaseService();
        $assignmentId = (int) $this->request->getPost('assignment_id');
        $subjectId    = (int) $this->request->getPost('subject_id');
        $userId       = session()->get('user_id');
        $fileUrl      = null;

        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'File maksimal 10MB.');
            }
            $path    = "assignment_{$assignmentId}/{$userId}_" . uniqid() . '_' . $file->getClientName();
            $fileUrl = $sb->uploadFile('submissions', $path, $file->getTempName(), $file->getMimeType());
            if (!$fileUrl) return redirect()->back()->with('error', 'Gagal upload ke Supabase Storage.');
        }

        // Upsert: update if exists, insert if not
        $existing = $sb->selectOne('submissions', [
            'assignment_id' => 'eq.' . $assignmentId,
            'student_id'    => 'eq.' . $userId,
        ], 'id');

        if ($existing) {
            $sb->update('submissions', ['id' => 'eq.' . $existing['id']], [
                'file_url'     => $fileUrl,
                'submitted_at' => date('c'),
            ]);
        } else {
            $sb->insert('submissions', [
                'assignment_id' => $assignmentId,
                'student_id'    => $userId,
                'file_url'      => $fileUrl,
                'submitted_at'  => date('c'),
            ]);
        }

        return redirect()->to("/student/subjects/{$subjectId}/tugas")->with('success', 'Tugas berhasil dikumpulkan! ✓');
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
