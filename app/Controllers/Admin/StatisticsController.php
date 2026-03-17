<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class StatisticsController extends BaseController
{
    private SupabaseService $sb;

    public function __construct()
    {
        $this->sb = new SupabaseService();
    }

    public function index()
    {
        $sb = $this->sb;

        // Parallel fetch semua data statistik
        $totalSiswa   = count($sb->select('profiles',    ['role' => 'eq.student'],  'id', 9999));
        $totalGuru    = count($sb->select('profiles',    ['role' => 'eq.teacher'],  'id', 9999));
        $totalKelas   = count($sb->select('classes',     [],                        'id', 9999));
        $totalMapel   = count($sb->select('subjects',    [],                        'id', 9999));
        $totalMateri  = count($sb->select('materials',   [],                        'id', 9999));
        $totalTugas   = count($sb->select('assignments', [],                        'id', 9999));
        $totalSubmisi = count($sb->select('submissions', [],                        'id', 9999));
        $totalAbsensi = count($sb->select('attendances', [],                        'id', 9999));

        // Distribusi siswa per kelas
        $classes  = $sb->select('classes', [], 'id,name', 100);
        $classStats = [];
        foreach ($classes as $c) {
            $count = count($sb->select('profiles', [
                'class_id' => 'eq.' . $c['id'],
                'role'     => 'eq.student',
            ], 'id', 9999));
            $classStats[] = ['name' => $c['name'], 'count' => $count];
        }
        usort($classStats, fn($a, $b) => $b['count'] - $a['count']);

        // Submission per mapel (top 5)
        $subjects = $sb->select('subjects', [], 'id,name', 100);
        $subjectSubmitStats = [];
        foreach ($subjects as $s) {
            $assignments = $sb->select('assignments', ['subject_id' => 'eq.' . $s['id']], 'id', 100);
            $submits = 0;
            foreach ($assignments as $a) {
                $submits += count($sb->select('submissions', ['assignment_id' => 'eq.' . $a['id']], 'id', 9999));
            }
            if ($submits > 0) {
                $subjectSubmitStats[] = ['name' => $s['name'], 'count' => $submits];
            }
        }
        usort($subjectSubmitStats, fn($a, $b) => $b['count'] - $a['count']);
        $subjectSubmitStats = array_slice($subjectSubmitStats, 0, 5);

        return view('admin/statistics/index', [
            'title'               => 'Statistik Aplikasi',
            'total_siswa'         => $totalSiswa,
            'total_guru'          => $totalGuru,
            'total_kelas'         => $totalKelas,
            'total_mapel'         => $totalMapel,
            'total_materi'        => $totalMateri,
            'total_tugas'         => $totalTugas,
            'total_submisi'       => $totalSubmisi,
            'total_absensi'       => $totalAbsensi,
            'class_stats'         => $classStats,
            'subject_submit_stats'=> $subjectSubmitStats,
        ]);
    }
}