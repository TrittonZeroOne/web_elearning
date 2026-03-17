<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class SubjectsController extends BaseController
{
    private SupabaseService $sb;

    public function __construct()
    {
        $this->sb = new SupabaseService();
    }

    private function getSubject(int $id): ?array
    {
        $s = $this->sb->selectOne('subjects', ['id' => 'eq.' . $id]);
        if (!$s || $s['class_id'] !== session()->get('class_id')) return null;
        $cls = $this->sb->selectOne('classes',  ['id' => 'eq.' . $s['class_id']],   'name');
        $t   = $this->sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
        $s['class_name']   = $cls['name']      ?? '';
        $s['teacher_name'] = $t['full_name']   ?? '';
        return $s;
    }

    public function index()
    {
        $subjects = $this->sb->select('subjects', ['class_id' => 'eq.' . session()->get('class_id')]);
        foreach ($subjects as &$s) {
            $t = $this->sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
            $s['teacher_name'] = $t['full_name'] ?? '';
        }
        return view('student/subjects/index', [
            'title'    => 'Mata Pelajaran',
            'subjects' => $subjects,
        ]);
    }

    public function materi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');

        $materials = $this->sb->select('materials', ['subject_id' => 'eq.' . $id], '*', 100);
        usort($materials, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return view('student/subjects/detail', [
            'title'      => $subject['name'],
            'subject'    => $subject,
            'active_tab' => 'materi',
            'materials'  => $materials,
        ]);
    }

    public function tugas(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');

        $userId      = session()->get('user_id');
        $assignments = $this->sb->select('assignments', ['subject_id' => 'eq.' . $id], '*', 100);
        usort($assignments, fn($a, $b) => strcmp($a['deadline'] ?? '', $b['deadline'] ?? ''));

        foreach ($assignments as &$a) {
            $a['my_submission'] = $this->sb->selectOne('submissions', [
                'assignment_id' => 'eq.' . $a['id'],
                'student_id'    => 'eq.' . $userId,
            ]);
        }

        return view('student/subjects/detail', [
            'title'       => $subject['name'],
            'subject'     => $subject,
            'active_tab'  => 'tugas',
            'assignments' => $assignments,
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
        ], '*', 500);
        usort($history, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        $total = count($history);
        $hadir = count(array_filter($history, fn($r) => $r['status'] === 'Hadir'));
        $sakit = count(array_filter($history, fn($r) => $r['status'] === 'Sakit'));
        $izin  = count(array_filter($history, fn($r) => $r['status'] === 'Izin'));
        $alfa  = count(array_filter($history, fn($r) => $r['status'] === 'Alfa'));

        return view('student/subjects/detail', [
            'title'      => $subject['name'],
            'subject'    => $subject,
            'active_tab' => 'absensi',
            'history'    => $history,
            'total'      => $total,
            'hadir'      => $hadir,
            'sakit'      => $sakit,
            'izin'       => $izin,
            'alfa'       => $alfa,
            'persen'     => $total > 0 ? round($hadir / $total * 100, 1) : 0,
        ]);
    }

    public function diskusi(int $id)
    {
        $subject = $this->getSubject($id);
        if (!$subject) return redirect()->to('/student/subjects')->with('error', 'Akses ditolak.');

        $msgs = $this->sb->select('class_discussions', [
            'class_id' => 'eq.' . $subject['class_id'],
        ], '*', 60);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));

        foreach ($msgs as &$m) {
            $p = $this->sb->selectOne('profiles', ['id' => 'eq.' . $m['sender_id']], 'full_name,role');
            $m['sender_name'] = $p['full_name'] ?? '';
            $m['sender_role'] = $p['role']      ?? '';
        }

        return view('student/subjects/detail', [
            'title'      => $subject['name'],
            'subject'    => $subject,
            'active_tab' => 'diskusi',
            'messages'   => $msgs,
        ]);
    }
}