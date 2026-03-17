<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Services\SupabaseService;

class ChatController extends BaseController
{
    private SupabaseService $sb;
    private string $myId;

    public function __construct()
    {
        $this->sb   = new SupabaseService();
        $this->myId = (string) session()->get('user_id');
    }

    private function roomId(string $a, string $b): string
    {
        $ids = [$a, $b];
        sort($ids);
        return implode('_', $ids);
    }

    /**
     * Daftar kontak guru:
     * - Semua Admin
     * - Semua Siswa dari kelas yang diajar guru ini
     */
    public function index()
    {
        // Ambil semua admin
        $admins = $this->sb->select('profiles', ['role' => 'eq.admin'], 'id,full_name,avatar_url,role', 50);

        // Ambil subject yang diajar guru ini → class_id unik
        $subjects  = $this->sb->select('subjects', ['teacher_id' => 'eq.' . $this->myId], 'class_id,name', 50);
        $classIds  = array_unique(array_column($subjects, 'class_id'));

        // Ambil semua siswa dari kelas tersebut
        $students = [];
        foreach ($classIds as $cid) {
            $cls  = $this->sb->selectOne('classes', ['id' => 'eq.' . $cid], 'name');
            $rows = $this->sb->select('profiles', [
                'class_id' => 'eq.' . $cid,
                'role'     => 'eq.student',
            ], 'id,full_name,avatar_url,role', 200);
            foreach ($rows as &$r) {
                $r['class_name'] = $cls['name'] ?? '';
            }
            $students = array_merge($students, $rows);
        }

        // Gabung & enrichment last_message + unread
        $contacts = [];
        foreach (array_merge($admins, $students) as $c) {
            $c['last_message'] = $this->lastMessage($c['id']);
            $c['unread']       = $this->unreadCount($c['id']);
            $contacts[] = $c;
        }

        // Sort: ada pesan terbaru dulu
        usort($contacts, fn($a, $b) =>
            strcmp($b['last_message']['sent_at'] ?? '', $a['last_message']['sent_at'] ?? '')
        );

        return view('teacher/chat/index', [
            'title'    => 'Pesan',
            'contacts' => $contacts,
        ]);
    }

    public function conversation(string $otherId)
    {
        $other = $this->sb->selectOne('profiles', ['id' => 'eq.' . $otherId], 'id,full_name,avatar_url,role');
        if (!$other) return redirect()->to('/teacher/chat')->with('error', 'Pengguna tidak ditemukan.');

        $messages = $this->getMessages($otherId, 60);
        $this->markRead($otherId);

        return view('teacher/chat/conversation', [
            'title'    => 'Chat — ' . $other['full_name'],
            'other'    => $other,
            'messages' => $messages,
            'my_id'    => $this->myId,
        ]);
    }

    public function send()
    {
        $receiverId = trim((string) $this->request->getPost('receiver_id'));
        $message    = trim((string) $this->request->getPost('message'));

        if (!$message || !$receiverId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Data tidak lengkap']);
        }

        $inserted = $this->sb->insert('direct_messages', [
            'room_id'     => $this->roomId($this->myId, $receiverId),
            'sender_id'   => $this->myId,
            'receiver_id' => $receiverId,
            'message'     => $message,
            'is_read'     => false,
        ]);

        if (!$inserted) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Gagal menyimpan pesan']);
        }

        return $this->response->setJSON(['message' => $inserted, 'my_id' => $this->myId]);
    }

    public function poll(string $otherId, int $lastId)
    {
        $roomId = $this->roomId($this->myId, $otherId);
        $msgs   = $this->sb->select('direct_messages', [
            'room_id' => 'eq.' . $roomId,
            'id'      => 'gt.' . $lastId,
        ], '*', 50);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        if (!empty($msgs)) $this->markRead($otherId);
        return $this->response->setJSON(['messages' => $msgs, 'my_id' => $this->myId]);
    }

    private function getMessages(string $otherId, int $limit): array
    {
        $roomId = $this->roomId($this->myId, $otherId);
        $msgs   = $this->sb->select('direct_messages', ['room_id' => 'eq.' . $roomId], '*', $limit);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        return is_array($msgs) ? $msgs : [];
    }

    private function lastMessage(string $otherId): ?array
    {
        $roomId = $this->roomId($this->myId, $otherId);
        $msgs   = $this->sb->select('direct_messages', ['room_id' => 'eq.' . $roomId], '*', 200);
        if (empty($msgs)) return null;
        usort($msgs, fn($a, $b) => strcmp($b['sent_at'] ?? '', $a['sent_at'] ?? ''));
        return $msgs[0];
    }

    private function unreadCount(string $fromId): int
    {
        $roomId = $this->roomId($this->myId, $fromId);
        $msgs   = $this->sb->select('direct_messages', [
            'room_id'   => 'eq.' . $roomId,
            'sender_id' => 'eq.' . $fromId,
            'is_read'   => 'eq.false',
        ], 'id', 999);
        return count($msgs);
    }

    private function markRead(string $fromId): void
    {
        $roomId = $this->roomId($this->myId, $fromId);
        $this->sb->update('direct_messages', [
            'room_id'   => 'eq.' . $roomId,
            'sender_id' => 'eq.' . $fromId,
            'is_read'   => 'eq.false',
        ], ['is_read' => true]);
    }
}