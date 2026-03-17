<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class SubjectsController extends BaseController
{
    public function index()
    {
        $sb       = new SupabaseService();
        $subjects = $sb->select('subjects', [], '*', 200);
        foreach ($subjects as &$s) {
            $cls = $sb->selectOne('classes',  ['id' => 'eq.' . $s['class_id']],   'name');
            $tch = $sb->selectOne('profiles', ['id' => 'eq.' . $s['teacher_id']], 'full_name');
            $s['class_name']   = $cls['name']      ?? '';
            $s['teacher_name'] = $tch['full_name'] ?? '';
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

    public function edit(int $id)
    {
        $sb      = new SupabaseService();
        $subject = $sb->selectOne('subjects', ['id' => 'eq.' . $id]);
        if (!$subject) {
            return redirect()->to('/admin/subjects')->with('error', 'Mata pelajaran tidak ditemukan.');
        }
        return view('admin/subjects/edit', [
            'title'    => 'Edit Mata Pelajaran',
            'subject'  => $subject,
            'classes'  => $sb->select('classes',  [], '*', 200),
            'teachers' => $sb->select('profiles', ['role' => 'eq.teacher'], 'id,full_name', 200),
        ]);
    }

    public function update(int $id)
    {
        $sb = new SupabaseService();
        $sb->update('subjects', ['id' => 'eq.' . $id], [
            'name'          => trim((string) $this->request->getPost('name')),
            'teacher_id'    => $this->request->getPost('teacher_id'),
            'class_id'      => $this->request->getPost('class_id'),
            'schedule_day'  => $this->request->getPost('schedule_day'),
            'schedule_time' => $this->request->getPost('schedule_time'),
        ]);
        return redirect()->to('/admin/subjects')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        (new SupabaseService())->delete('subjects', ['id' => 'eq.' . $id]);
        return redirect()->to('/admin/subjects')->with('success', 'Mata pelajaran dihapus.');
    }
}