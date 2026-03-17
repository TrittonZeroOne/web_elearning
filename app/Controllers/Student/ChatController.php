<?php

namespace App\Controllers\Student;

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

    /** Daftar guru yang mengajar siswa ini (berdasarkan class_id) */
    public function index()
    {
        $classId  = session()->get('class_id');
        // Ambil subject di kelas siswa → ambil teacher_id unik
        $subjects = $this->sb->select('subjects', ['class_id' => 'eq.' . $classId], 'teacher_id,name', 100);

        $teacherIds = array_unique(array_column($subjects, 'teacher_id'));

        $teachers = [];
        foreach ($teacherIds as $tid) {
            $t = $this->sb->selectOne('profiles', ['id' => 'eq.' . $tid], 'id,full_name,avatar_url');
            if (!$t) continue;
            // Mata pelajaran yang diajar
            $mapels = array_filter($subjects, fn($s) => $s['teacher_id'] === $tid);
            $t['subjects']     = implode(', ', array_column(array_values($mapels), 'name'));
            $t['last_message'] = $this->lastMessage($tid);
            $t['unread']       = $this->unreadCount($tid);
            $teachers[] = $t;
        }

        // Sort: ada pesan terbaru dulu, lalu nama
        usort($teachers, fn($a, $b) =>
            strcmp($b['last_message']['sent_at'] ?? '', $a['last_message']['sent_at'] ?? '')
        );

        return view('student/chat/index', [
            'title'    => 'Chat dengan Guru',
            'teachers' => $teachers,
        ]);
    }

    public function conversation(string $teacherId)
    {
        $teacher = $this->sb->selectOne('profiles',
            ['id' => 'eq.' . $teacherId, 'role' => 'eq.teacher'],
            'id,full_name,avatar_url'
        );
        if (!$teacher) return redirect()->to('/student/chat')->with('error', 'Guru tidak ditemukan.');

        $messages = $this->getMessages($teacherId, 60);
        $this->markRead($teacherId);

        return view('student/chat/conversation', [
            'title'    => 'Chat — ' . $teacher['full_name'],
            'teacher'  => $teacher,
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

    public function poll(string $teacherId, int $lastId)
    {
        $roomId = $this->roomId($this->myId, $teacherId);
        $msgs   = $this->sb->select('direct_messages', [
            'room_id' => 'eq.' . $roomId,
            'id'      => 'gt.' . $lastId,
        ], '*', 50);
        usort($msgs, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        if (!empty($msgs)) $this->markRead($teacherId);
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