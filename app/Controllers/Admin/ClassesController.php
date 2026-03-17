<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

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
        $sb   = new SupabaseService();
        $id   = strtoupper(str_replace(' ', '-', trim((string) $this->request->getPost('id'))));
        $name = trim((string) $this->request->getPost('name'));
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