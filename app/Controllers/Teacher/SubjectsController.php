<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class SubjectsController extends BaseController
{
    private SupabaseService $sb;

    public function __construct()
    {
        $this->sb = new SupabaseService();
    }

    /** Ambil + validasi subject milik guru ini */
    private function getSubject(int $id): ?array
    {
        $s = $this->sb->selectOne('subjects', ['id' => 'eq.' . $id]);
        if (!$s || $s['teacher_id'] !== session()->get('user_id')) {
            return null;
        }
        // Enrich
        $cls = $this->sb->selectOne('classes', ['id' => 'eq.' . $s['class_id']], 'name');
        $s['class_name']   = $cls['name'] ?? '';
        $s['teacher_name'] = session()->get('full_name');
        return $s;
    }

    public function index()
    {
        $subjects = $this->sb->select('subjects', ['teacher_id' => 'eq.' . session()->get('user_id')]);
        foreach ($subjects as &$s) {
            $cls = $this->sb->selectOne('classes', ['id' => 'eq.' . $s['class_id']], 'name');
            $s['class_name'] = $cls['name'] ?? '';
        }
        return view('teacher/subjects/index', ['title' => 'Mata Pelajaran', 'subjects' => $subjects]);
    }

    public function materi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/teacher/subjects')->with('error', 'Akses ditolak.');

        $materials = $this->sb->select('materials', ['subject_id' => 'eq.' . $id], '*', 100);
        usort($materials, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return view('teacher/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'materi', 'materials' => $materials,
        ]);
    }

    public function tugas(int $id)
    {
        $subject     = $this->getSubject($id);
        if (!$subject) return redirect()->to('/teacher/subjects')->with('error', 'Akses ditolak.');

        $assignments = $this->sb->select('assignments', ['subject_id' => 'eq.' . $id]);
        usort($assignments, fn($a, $b) => strcmp($a['deadline'] ?? '', $b['deadline'] ?? ''));

        foreach ($assignments as &$a) {
            $subs = $this->sb->select('submissions', ['assignment_id' => 'eq.' . $a['id']]);
            foreach ($subs as &$sub) {
                $p = $this->sb->selectOne('profiles', ['id' => 'eq.' . $sub['student_id']], 'full_name');
                $sub['student_name'] = $p['full_name'] ?? '';
            }
            $a['submissions']      = $subs;
            $a['submission_count'] = count($subs);
        }

        return view('teacher/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'tugas', 'assignments' => $assignments,
        ]);
    }

    public function absensi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/teacher/subjects')->with('error', 'Akses ditolak.');

        $date     = $this->request->getGet('date') ?? date('Y-m-d');
        $students = $this->sb->select('profiles', [
            'class_id' => 'eq.' . $subject['class_id'],
            'role'     => 'eq.student',
        ], 'id,full_name');
        usort($students, fn($a, $b) => strcmp($a['full_name'], $b['full_name']));

        $records = $this->sb->select('attendances', [
            'subject_id' => 'eq.' . $id,
            'date'       => 'eq.' . $date,
        ]);
        $statusMap = [];
        foreach ($records as $r) $statusMap[$r['student_id']] = $r['status'];

        $history = $this->sb->select('attendances', ['subject_id' => 'eq.' . $id], '*', 500);
        foreach ($history as &$r) {
            $p = $this->sb->selectOne('profiles', ['id' => 'eq.' . $r['student_id']], 'full_name');
            $r['student_name'] = $p['full_name'] ?? '';
        }
        usort($history, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return view('teacher/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'absensi', 'date' => $date,
            'students' => $students, 'statusMap' => $statusMap, 'history' => $history,
        ]);
    }

    public function diskusi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/teacher/subjects')->with('error', 'Akses ditolak.');

        $msgs = $this->getMessages($subject['class_id'], 60);
        return view('teacher/subjects/detail', [
            'title' => $subject['name'], 'subject' => $subject,
            'active_tab' => 'diskusi', 'messages' => $msgs,
        ]);
    }

    private function getMessages(string $classId, int $limit): array
    {
        $msgs = $this->sb->select('class_discussions', ['class_id' => 'eq.' . $classId], '*', $limit);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        foreach ($msgs as &$m) {
            $p = $this->sb->selectOne('profiles', ['id' => 'eq.' . $m['sender_id']], 'full_name,role');
            $m['sender_name'] = $p['full_name'] ?? '';
            $m['sender_role'] = $p['role'] ?? '';
        }
        return $msgs;
    }
}
