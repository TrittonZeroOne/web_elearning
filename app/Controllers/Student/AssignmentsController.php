<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

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
        if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'File maksimal 10MB.');
            }
            // Sanitasi nama file agar aman di URL
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientName());
            // Bucket "submissions" tidak ada → pakai bucket "assignments" dengan prefix submissions/
            $path    = "submissions/assignment_{$assignmentId}/{$userId}_" . uniqid() . '_' . $safeName;
            $fileUrl = $sb->uploadFile('assignments', $path, $file->getTempName(), $file->getMimeType());
            if (!$fileUrl) {
                return redirect()->back()->with('error', 'Gagal upload file. Cek koneksi atau ukuran file.');
            }
        }

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

        return redirect()->to("/student/subjects/{$subjectId}/tugas")->with('success', 'Tugas berhasil dikumpulkan!');
    }
}