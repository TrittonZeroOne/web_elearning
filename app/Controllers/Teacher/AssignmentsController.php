<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class AssignmentsController extends BaseController
{
    public function store()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $fileUrl   = null;

        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return redirect()->back()->with('error', 'File maksimal 10MB.');
            }
            // Bersihkan nama file: hapus spasi & karakter aneh agar URL aman
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientName());
            $path     = "subject_{$subjectId}/" . uniqid() . '_' . $safeName;
            $fileUrl  = $sb->uploadFile('assignments', $path, $file->getTempName(), $file->getMimeType());
            if (!$fileUrl) {
                return redirect()->back()->with('error', 'Gagal upload lampiran ke Storage. Cek bucket "assignments" sudah PUBLIC.');
            }
        }

        // Deadline dari input type=date: format YYYY-MM-DD, simpan apa adanya
        $deadline = trim((string) $this->request->getPost('deadline'));
        $sb->insert('assignments', [
            'subject_id'  => $subjectId,
            'title'       => trim((string) $this->request->getPost('title')),
            'description' => trim((string) $this->request->getPost('description')),
            'deadline'    => $deadline ?: null,
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