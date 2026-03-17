<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 space-y-4">

  <!-- Filter Bar -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-medium text-slate-500 mb-1">Cari pengguna</label>
        <div class="relative">
          <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
          <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Nama atau email..."
            class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Role</label>
        <select name="role" class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <option value="">Semua Role</option>
          <option value="admin"   <?= $role==='admin'   ? 'selected':'' ?>>Admin</option>
          <option value="teacher" <?= $role==='teacher' ? 'selected':'' ?>>Guru</option>
          <option value="student" <?= $role==='student' ? 'selected':'' ?>>Siswa</option>
        </select>
      </div>
      <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
        <i class="fa-solid fa-filter mr-1"></i> Filter
      </button>
      <a href="<?= base_url('admin/users/create') ?>" class="ml-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
        <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">Tambah Pengguna</span>
      </a>
    </form>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50/80">
            <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wide">Pengguna</th>
            <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wide hidden md:table-cell">Email</th>
            <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wide">Role</th>
            <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wide hidden sm:table-cell">Kelas</th>
            <th class="px-5 py-3.5"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <?php if (empty($users)): ?>
            <tr><td colspan="5" class="text-center py-12 text-slate-400">
              <i class="fa-solid fa-users text-2xl mb-2 block opacity-40"></i>
              Tidak ada pengguna ditemukan.
            </td></tr>
          <?php else: ?>
            <?php
            $roleConfig = [
              'admin'   => 'bg-red-100 text-red-700',
              'teacher' => 'bg-blue-100 text-blue-700',
              'student' => 'bg-green-100 text-green-700',
            ];
            foreach ($users as $u):
              $rc = $roleConfig[$u['role']] ?? 'bg-gray-100 text-gray-600';
            ?>
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                      <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                      <p class="font-semibold text-slate-700 text-sm"><?= esc($u['full_name']) ?></p>
                      <p class="text-xs text-slate-400 md:hidden"><?= esc($u['email']) ?></p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-sm hidden md:table-cell"><?= esc($u['email']) ?></td>
                <td class="px-5 py-3.5">
                  <span class="<?= $rc ?> text-xs font-semibold px-2.5 py-1 rounded-full capitalize"><?= $u['role'] ?></span>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-sm hidden sm:table-cell"><?= esc($u['class_name'] ?? '—') ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-1.5 justify-end">
                    <a href="<?= base_url('admin/users/edit/' . $u['id']) ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                      <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </a>
                    <?php if ($u['id'] !== session()->get('user_id')): ?>
                      <form action="<?= base_url('admin/users/delete/' . $u['id']) ?>" method="POST"
                        onsubmit="return confirmDelete('<?= addslashes((string)($u['full_name'] ?? '')) ?>', '<?= (string)($u['email'] ?? '') ?>')">
                        <?= csrf_field() ?>
                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                          <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
      <span>Menampilkan <strong class="text-slate-600"><?= count($users) ?></strong> pengguna</span>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function confirmDelete(name, email) {
  return confirm(
    "⚠️ Hapus Pengguna\n\n" +
    "Nama  : " + name + "\n" +
    "Email : " + email + "\n\n" +
    "User akan dihapus dari Supabase Auth DAN database.\n" +
    "Aksi ini TIDAK bisa dibatalkan!"
  );
}
</script>
<?= $this->endSection() ?>