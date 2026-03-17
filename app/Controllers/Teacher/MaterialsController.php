<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class MaterialsController extends BaseController
{
    public function store()
    {
        $sb        = new SupabaseService();
        $subjectId = (int) $this->request->getPost('subject_id');
        $type      = $this->request->getPost('type');
        $fileUrl   = null;

        if (in_array($type, ['PDF', 'Video', 'Dokumen', 'Lainnya'])) {
            $file    = $this->request->getFile('file');
            $maxSize = ($type === 'Video') ? 100 * 1024 * 1024 : 10 * 1024 * 1024;
            $maxDesc = ($type === 'Video') ? '100MB' : '10MB';
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if ($file->getSize() > $maxSize) {
                    return redirect()->back()->with('error', "File maksimal {$maxDesc}.");
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

        return redirect()->to(base_url("teacher/subjects/{$subjectId}/materi"))->with('success', 'Materi ditambahkan.');
    }

    public function delete(int $id)
    {
        $sb = new SupabaseService();
        $m  = $sb->selectOne('materials', ['id' => 'eq.' . $id]);
        if ($m) {
            $sb->delete('materials', ['id' => 'eq.' . $id]);
            return redirect()->to(base_url("teacher/subjects/{$m['subject_id']}/materi"))->with('success', 'Materi dihapus.');
        }
        return redirect()->back();
    }
}