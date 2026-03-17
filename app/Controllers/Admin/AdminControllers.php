<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

// ── ClassesController ─────────────────────────────────────────────
class ClassesController extends BaseController
{
    public function index()
    {
        $sb      = new SupabaseService();
        $classes = $sb->select('classes', [], '*', 200);
        foreach ($classes as &$c) {
            $students = $sb->select('profiles', ['class_id' => 'eq.' . $c['id'], 'role' => 'eq.student'], 'id');
            $c['student_count'] = count($students);
        }
        usort($classes, fn($a, $b) => strcmp($a['name'], $b['name']));
        return view('admin/classes/index', ['title' => 'Manajemen Kelas', 'classes' => $classes]);
    }

    public function store()
    {
        $sb  = new SupabaseService();
        $id  = strtoupper(str_replace(' ', '-', trim((string) $this->request->getPost('id'))));
        $name= trim((string) $this->request->getPost('name'));
        if (!$id || !$name) return redirect()->back()->with('error', 'ID dan nama kelas wajib diisi.');
        $sb->insert('classes', ['id' => $id, 'name' => $name]);
        return redirect()->to('/admin/classes')->with('success', 'Kelas ditambahkan.');
    }

    public function delete(string $id)
    {
        (new SupabaseService())->delete('classes', ['id' => 'eq.' . $id]);
        return redirect()->to('/admin/classes')->with('success', 'Kelas dihapus.');
    }
}

// ── SubjectsController ────────────────────────────────────────────
class SubjectsController extends BaseController
{
    public function index()
    {
        $sb       = new SupabaseService();
        $subjects = $sb->select('subjects', [], '*', 200);
        foreach ($subjects as &$s) {
            $cls = $sb->selectOne('classes',  ['id' => 'eq.' . $s['class_id']],   'name');
            $tch = $sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
            $s['class_name']   = $cls['name']       ?? '';
            $s['teacher_name'] = $tch['full_name']  ?? '';
        }
        return view('admin/subjects/index', [
            'title'    => 'Mata Pelajaran',
            'subjects' => $subjects,
            'classes'  => $sb->select('classes',  [], '*', 200),
            'teachers' => $sb->select('profiles', ['role' => 'eq.teacher'], 'id,full_name', 200),
        ]);
    }

    public function store()
    {
        $sb = new SupabaseService();
        $sb->insert('subjects', [
            'name'          => trim((string) $this->request->getPost('name')),
            'teacher_id'    => $this->request->getPost('teacher_id'),
            'class_id'      => $this->request->getPost('class_id'),
            'schedule_day'  => $this->request->getPost('schedule_day'),
            'schedule_time' => $this->request->getPost('schedule_time'),
        ]);
        return redirect()->to('/admin/subjects')->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function delete(int $id)
    {
        (new SupabaseService())->delete('subjects', ['id' => 'eq.' . $id]);
        return redirect()->to('/admin/subjects')->with('success', 'Mata pelajaran dihapus.');
    }
}

// ── AnnouncementsController ───────────────────────────────────────
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

    public function delete(int $id)
    {
        (new SupabaseService())->delete('announcements', ['id' => 'eq.' . $id]);
        return redirect()->to('/admin/announcements')->with('success', 'Pengumuman dihapus.');
    }
}
