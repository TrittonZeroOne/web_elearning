<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class AnnouncementsController extends BaseController
{
    public function index()
    {
        $sb   = new SupabaseService();
        $rows = $sb->select('announcements', [], '*', 50);
        usort($rows, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        foreach ($rows as &$r) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $r['sender_id']], 'full_name');
            $r['sender_name'] = $p['full_name'] ?? '';
        }
        return view('admin/announcements/index', ['title' => 'Pengumuman', 'announcements' => $rows]);
    }

    public function store()
    {
        $sb = new SupabaseService();
        $sb->insert('announcements', [
            'title'     => trim((string) $this->request->getPost('title')),
            'body'      => trim((string) $this->request->getPost('body')),
            'target'    => $this->request->getPost('target') ?: 'all',
            'sender_id' => session()->get('user_id'),
        ]);
        return redirect()->to('/admin/announcements')->with('success', 'Pengumuman dikirim.');
    }

    public function update(int $id)
    {
        $sb = new SupabaseService();
        $sb->update('announcements', ['id' => 'eq.' . $id], [
            'title'  => trim((string) $this->request->getPost('title')),
            'body'   => trim((string) $this->request->getPost('body')),
            'target' => $this->request->getPost('target') ?: 'all',
        ]);
        return redirect()->to('/admin/announcements')->with('success', 'Pengumuman diperbarui.');
    }

    public function delete(int $id)
    {
        (new SupabaseService())->delete('announcements', ['id' => 'eq.' . $id]);
        return redirect()->to('/admin/announcements')->with('success', 'Pengumuman dihapus.');
    }
}