<?php

namespace App\Controllers\Admin;

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

    /**
     * Room ID deterministik: gabungkan dua UUID diurutkan A-Z, pisah dengan '_'
     * Hasilnya selalu sama tidak peduli siapa yang mulai chat.
     */
    private function roomId(string $a, string $b): string
    {
        $ids = [$a, $b];
        sort($ids);
        return implode('_', $ids);
    }

    // Daftar guru ─────────────────────────────────────────────
    public function index()
    {
        $teachers = $this->sb->select('profiles', ['role' => 'eq.teacher'], 'id,full_name,avatar_url', 200);
        usort($teachers, fn($a, $b) => strcmp($a['full_name'], $b['full_name']));

        foreach ($teachers as &$t) {
            $t['last_message'] = $this->lastMessage($t['id']);
            $t['unread']       = $this->unreadCount($t['id']);
        }
        unset($t);

        usort($teachers, fn($a, $b) =>
            strcmp($b['last_message']['sent_at'] ?? '', $a['last_message']['sent_at'] ?? '')
        );

        return view('admin/chat/index', ['title' => 'Chat dengan Guru', 'teachers' => $teachers]);
    }

    // Percakapan ──────────────────────────────────────────────
    public function conversation(string $teacherId)
    {
        $teacher = $this->sb->selectOne('profiles',
            ['id' => 'eq.' . $teacherId, 'role' => 'eq.teacher'],
            'id,full_name,avatar_url'
        );
        if (!$teacher) return redirect()->to('/admin/chat')->with('error', 'Guru tidak ditemukan.');

        $messages = $this->getMessages($teacherId, 60);
        $this->markRead($teacherId);

        return view('admin/chat/conversation', [
            'title'    => 'Chat — ' . $teacher['full_name'],
            'teacher'  => $teacher,
            'messages' => $messages,
            'my_id'    => $this->myId,
        ]);
    }

    // Kirim pesan (AJAX) ──────────────────────────────────────
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

    // Polling pesan baru (AJAX) ───────────────────────────────
    public function poll(string $teacherId, int $lastId)
    {
        $roomId = $this->roomId($this->myId, $teacherId);

        $msgs = $this->sb->select('direct_messages', [
            'room_id' => 'eq.' . $roomId,
            'id'      => 'gt.' . $lastId,
            'order'   => 'sent_at.asc',
        ], '*', 50);

        if (!empty($msgs)) $this->markRead($teacherId);

        return $this->response->setJSON(['messages' => $msgs, 'my_id' => $this->myId]);
    }

    // Tandai terbaca (AJAX) ───────────────────────────────────
    public function read(string $teacherId)
    {
        $this->markRead($teacherId);
        return $this->response->setJSON(['ok' => true]);
    }

    // Debug ───────────────────────────────────────────────────
    public function debug()
    {
        $result = [];
        $test   = $this->sb->select('direct_messages', [], '*', 3);
        $result['table_direct_messages'] = is_array($test)
            ? '✓ Ada (' . count($test) . ' sample rows)'
            : '❌ Tabel tidak ditemukan — jalankan migration SQL';
        $result['sample_data']     = $test;
        $result['my_id']           = $this->myId;
        $result['room_id_example'] = $this->roomId($this->myId, 'OTHER_UUID_HERE');
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Helpers ─────────────────────────────────────────────────

    private function getMessages(string $otherId, int $limit): array
    {
        $roomId = $this->roomId($this->myId, $otherId);
        // order=sent_at.asc → PostgREST sort ascending, ambil $limit terakhir
        $msgs = $this->sb->select('direct_messages', [
            'room_id' => 'eq.' . $roomId,
            'order'   => 'sent_at.asc',
        ], '*', $limit);
        return is_array($msgs) ? $msgs : [];
    }

    private function lastMessage(string $otherId): ?array
    {
        $roomId = $this->roomId($this->myId, $otherId);
        // order desc + limit 1 = pesan TERBARU
        $msgs = $this->sb->select('direct_messages', [
            'room_id' => 'eq.' . $roomId,
            'order'   => 'sent_at.desc',
        ], '*', 1);
        return (!empty($msgs) && is_array($msgs)) ? $msgs[0] : null;
    }

    private function unreadCount(string $fromId): int
    {
        $roomId = $this->roomId($this->myId, $fromId);
        $msgs   = $this->sb->select('direct_messages', [
            'room_id'     => 'eq.' . $roomId,
            'sender_id'   => 'eq.' . $fromId,
            'is_read'     => 'eq.false',
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