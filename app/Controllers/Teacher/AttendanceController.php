<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class AttendanceController extends BaseController
{
    public function save()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $date      = $this->request->getPost('date');
        $statuses  = (array) ($this->request->getPost('status') ?? []);

        foreach ($statuses as $studentId => $status) {
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
        $sb = new SupabaseService();

        // Ambil info mata pelajaran untuk nama file & header
        $subject     = $sb->selectOne('subjects', ['id' => 'eq.' . $subjectId], 'name,class_id');
        $subjectName = $subject['name'] ?? 'Mapel';

        // Ambil semua siswa di kelas ini (untuk menampilkan yang belum punya record absensi juga)
        $classId  = $subject['class_id'] ?? null;
        $students = $classId
            ? $sb->select('profiles', ['class_id' => 'eq.' . $classId, 'role' => 'eq.student'], 'id,full_name', 500)
            : [];
        // Index by id
        $studentMap = [];
        foreach ($students as $s) $studentMap[$s['id']] = $s['full_name'];

        // Ambil semua data absensi untuk subject ini
        $data = $sb->select('attendances', ['subject_id' => 'eq.' . $subjectId], '*', 9999);

        // Enrich dengan nama siswa
        foreach ($data as &$r) {
            $r['student_name'] = $studentMap[$r['student_id']]
                ?? ($sb->selectOne('profiles', ['id' => 'eq.' . $r['student_id']], 'full_name')['full_name'] ?? 'Unknown');
        }
        unset($r);

        // Sort: nama siswa A-Z, lalu tanggal terlama → terbaru
        usort($data, fn($a, $b) =>
            strcmp($a['student_name'], $b['student_name']) ?: strcmp($a['date'], $b['date'])
        );

        // Hitung rekapitulasi per siswa
        $rekap = [];
        foreach ($data as $r) {
            $n = $r['student_name'];
            if (!isset($rekap[$n])) $rekap[$n] = ['Hadir'=>0,'Sakit'=>0,'Izin'=>0,'Alfa'=>0];
            if (isset($rekap[$n][$r['status']])) $rekap[$n][$r['status']]++;
        }

        // Output CSV
        $filename = 'absensi_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $subjectName) . '_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $f = fopen('php://output', 'w');
        fputs($f, "\xEF\xBB\xBF"); // BOM agar Excel baca UTF-8 dengan benar

        // Header
        fputcsv($f, ['Mata Pelajaran: ' . $subjectName]);
        fputcsv($f, ['Diekspor: ' . date('d/m/Y H:i')]);
        fputcsv($f, []); // baris kosong

        // Data absensi per tanggal
        fputcsv($f, ['No', 'Nama Siswa', 'Tanggal', 'Status']);
        $no = 1;
        foreach ($data as $r) {
            fputcsv($f, [
                $no++,
                $r['student_name'],
                date('d/m/Y', strtotime($r['date'])),
                $r['status'],
            ]);
        }

        // Rekapitulasi
        fputcsv($f, []);
        fputcsv($f, ['REKAPITULASI']);
        fputcsv($f, ['Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Alfa', 'Total']);
        foreach ($rekap as $nama => $counts) {
            $total = array_sum($counts);
            fputcsv($f, [$nama, $counts['Hadir'], $counts['Sakit'], $counts['Izin'], $counts['Alfa'], $total]);
        }

        fclose($f);
        exit;
    }
}