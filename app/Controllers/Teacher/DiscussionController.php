<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class DiscussionController extends BaseController
{
    public function send()
    {
        $sb      = new SupabaseService();
        $classId = $this->request->getPost('class_id');
        $message = trim((string) $this->request->getPost('message'));

        if (!$message) return $this->response->setStatusCode(400)->setJSON(['error' => 'Pesan kosong']);

        $inserted = $sb->insert('class_discussions', [
            'class_id'  => $classId,
            'sender_id' => session()->get('user_id'),
            'message'   => $message,
        ]);

        if ($inserted) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $inserted['sender_id']], 'full_name,role');
            $inserted['sender_name'] = $p['full_name'] ?? session()->get('full_name');
            $inserted['sender_role'] = $p['role'] ?? session()->get('role');
        }

        return $this->response->setJSON(['message' => $inserted]);
    }

    public function poll(string $classId, int $lastId)
    {
        $sb   = new SupabaseService();
        $msgs = $sb->select('class_discussions', [
            'class_id' => 'eq.' . $classId,
            'id'       => 'gt.' . $lastId,
        ], '*', 50);

        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));

        foreach ($msgs as &$m) {
            $p = $sb->selectOne('profiles', ['id' => 'eq.' . $m['sender_id']], 'full_name,role');
            $m['sender_name'] = $p['full_name'] ?? '';
            $m['sender_role'] = $p['role'] ?? '';
        }

        return $this->response->setJSON(['messages' => $msgs]);
    }
}