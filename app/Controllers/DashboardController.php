<?php

namespace App\Controllers;

use App\Services\SupabaseService;

class DashboardController extends BaseController
{
    private SupabaseService $sb;

    public function __construct()
    {
        $this->sb = new SupabaseService();
    }

    public function index()
    {
        switch (session()->get('role')) {
            case 'admin':   return $this->adminDash();
            case 'teacher': return $this->teacherDash();
            case 'student': return $this->studentDash();
            default:        return redirect()->to('/login');
        }
    }

    // ══════════════════════════════════════════════════
    // ADMIN
    // ══════════════════════════════════════════════════
    private function adminDash()
    {
        $sb = $this->sb;
        return view('dashboard/admin', [
            'title'          => 'Dashboard Admin',
            'total_students' => count($sb->select('profiles', ['role' => 'eq.student'], 'id')),
            'total_teachers' => count($sb->select('profiles', ['role' => 'eq.teacher'], 'id')),
            'total_subjects' => count($sb->select('subjects', [], 'id')),
            'total_classes'  => count($sb->select('classes',  [], 'id')),
            'announcements'  => $this->announcementsWithSender([], 5),
        ]);
    }

    // ══════════════════════════════════════════════════
    // TEACHER
    // ══════════════════════════════════════════════════
    private function teacherDash()
    {
        $sb        = $this->sb;
        $teacherId = session()->get('user_id');

        // Semua mapel guru + class_name
        $subjects  = $sb->select('subjects', ['teacher_id' => 'eq.' . $teacherId]);
        $classIds  = array_unique(array_column($subjects, 'class_id'));
        $classMap  = [];
        foreach ($classIds as $cid) {
            $c = $sb->selectOne('classes', ['id' => 'eq.' . $cid], 'name');
            if ($c) $classMap[$cid] = $c['name'];
        }
        foreach ($subjects as &$s) {
            $s['class_name']   = $classMap[$s['class_id']] ?? '';
            $s['teacher_name'] = session()->get('full_name');
        }
        unset($s);

        // ── Jadwal mengajar HARI INI ──
        $dayMap      = [0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
        $todayName   = $dayMap[(int)date('w')];
        $todaySubjects = array_values(array_filter($subjects, fn($s) => $s['schedule_day'] === $todayName));
        usort($todaySubjects, fn($a, $b) => strcmp($a['schedule_time'] ?? '', $b['schedule_time'] ?? ''));

        // ── Tugas BELUM dinilai (grade = null) ──
        $ungradedSubmit = $this->ungradedSubmissionsForTeacher($teacherId, $classMap);

        return view('dashboard/teacher', [
            'title'           => 'Dashboard',
            'subjects'        => $subjects,
            'total_subjects'  => count($subjects),
            'today_subjects'  => $todaySubjects,
            'today_name'      => $todayName,
            'ungraded_submit' => $ungradedSubmit,
            'announcements'   => $this->announcementsWithSender(['all', 'teacher'], 3),
        ]);
    }

    // ══════════════════════════════════════════════════
    // STUDENT
    // ══════════════════════════════════════════════════
    private function studentDash()
    {
        $sb      = $this->sb;
        $userId  = session()->get('user_id');
        $classId = session()->get('class_id');

        // Semua mapel di kelas + teacher_name
        $subjects = $sb->select('subjects', ['class_id' => 'eq.' . $classId]);
        foreach ($subjects as &$s) {
            $t = $sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
            $s['teacher_name'] = $t['full_name'] ?? '';
        }
        unset($s);

        // ── Mapel HARI INI ──
        $dayMap      = [0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
        $todayName   = $dayMap[(int)date('w')];
        $todaySubjects = array_values(array_filter($subjects, fn($s) => $s['schedule_day'] === $todayName));
        usort($todaySubjects, fn($a, $b) => strcmp($a['schedule_time'] ?? '', $b['schedule_time'] ?? ''));

        // ── Tugas menunggu & tugas dinilai ──
        [$pendingTasks, $gradedTasks] = $this->tasksForStudent($userId, $classId);

        return view('dashboard/student', [
            'title'          => 'Beranda',
            'subjects'       => $subjects,
            'today_subjects' => $todaySubjects,
            'today_name'     => $todayName,
            'pending_tasks'  => $pendingTasks,
            'graded_tasks'   => $gradedTasks,
            'announcements'  => $this->announcementsWithSender(['all', 'student'], 3),
        ]);
    }

    // ══════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════

    private function announcementsWithSender(array $targets, int $limit): array
    {
        $rows = $this->sb->select('announcements', [], '*', 100);
        if ($targets) {
            $rows = array_values(array_filter($rows, fn($a) => in_array($a['target'], $targets)));
        }
        usort($rows, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $rows = array_slice($rows, 0, $limit);
        foreach ($rows as &$r) {
            $sender = $this->sb->selectOne('profiles', ['id' => 'eq.' . $r['sender_id']], 'full_name');
            $r['sender_name'] = $sender['full_name'] ?? '';
        }
        return $rows;
    }

    /**
     * Ambil submissions yang BELUM dinilai (grade = null) milik guru:
     * setiap baris berisi student_name, class_name, subject_name, assignment_title
     */
    private function ungradedSubmissionsForTeacher(string $teacherId, array $classMap): array
    {
        $subjects = $this->sb->select('subjects', ['teacher_id' => 'eq.' . $teacherId], 'id,name,class_id');
        if (empty($subjects)) return [];

        $result = [];
        foreach ($subjects as $subj) {
            $assignments = $this->sb->select('assignments', ['subject_id' => 'eq.' . $subj['id']], 'id,title');
            foreach ($assignments as $a) {
                // Hanya submission yang grade masih null
                $subs = $this->sb->select('submissions', [
                    'assignment_id' => 'eq.' . $a['id'],
                    'grade'         => 'is.null',
                ], '*', 20);
                foreach ($subs as $sub) {
                    $student        = $this->sb->selectOne('profiles', ['id' => 'eq.' . $sub['student_id']], 'full_name,class_id');
                    $studentClassId = $student['class_id'] ?? '';
                    $result[] = [
                        'student_name'     => $student['full_name'] ?? '-',
                        'class_name'       => $classMap[$studentClassId] ?? ($classMap[$subj['class_id']] ?? '-'),
                        'subject_name'     => $subj['name'],
                        'assignment_title' => $a['title'],
                        'submitted_at'     => $sub['submitted_at'],
                        'submission_id'    => $sub['id'],
                    ];
                }
            }
        }

        usort($result, fn($a, $b) => strcmp($b['submitted_at'] ?? '', $a['submitted_at'] ?? ''));
        return array_slice($result, 0, 8);
    }

    /**
     * Return [pendingTasks, gradedTasks] untuk student
     */
    private function tasksForStudent(string $userId, ?string $classId): array
    {
        if (!$classId) return [[], []];

        $subjects = $this->sb->select('subjects', ['class_id' => 'eq.' . $classId], 'id,name');
        $pending  = [];
        $graded   = [];

        foreach ($subjects as $s) {
            $assignments = $this->sb->select('assignments', ['subject_id' => 'eq.' . $s['id']]);
            foreach ($assignments as $a) {
                $sub = $this->sb->selectOne('submissions', [
                    'assignment_id' => 'eq.' . $a['id'],
                    'student_id'    => 'eq.' . $userId,
                ]);
                $a['subject_name'] = $s['name'];

                if (!$sub) {
                    // Belum dikumpulkan & deadline belum lewat
                    if (!$a['deadline'] || strtotime($a['deadline']) >= strtotime('today')) {
                        $pending[] = $a;
                    }
                } elseif ($sub['grade'] !== null) {
                    // Sudah dinilai
                    $graded[] = array_merge($a, [
                        'grade'        => $sub['grade'],
                        'feedback'     => $sub['feedback'] ?? '',
                        'submitted_at' => $sub['submitted_at'],
                    ]);
                }
            }
        }

        usort($pending, fn($a, $b) => strcmp($a['deadline'] ?? 'Z', $b['deadline'] ?? 'Z'));
        usort($graded,  fn($a, $b) => strcmp($b['submitted_at'] ?? '', $a['submitted_at'] ?? ''));

        return [array_slice($pending, 0, 5), array_slice($graded, 0, 5)];
    }
}