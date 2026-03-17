<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
  <!-- Add Form -->
  <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
      <i class="fa-solid fa-plus-circle text-primary-600"></i> Tambah Kelas
    </h3>
    <form action="<?= base_url('admin/classes/store') ?>" method="POST" class="space-y-3">
      <?= csrf_field() ?>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">ID Kelas</label>
        <input type="text" name="id" required placeholder="X-IPA-1"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
        <p class="text-xs text-slate-400 mt-1">Contoh: X-IPA-1, XI-IPS-2</p>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Kelas</label>
        <input type="text" name="name" required placeholder="X IPA 1"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
      </div>
      <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
        <i class="fa-solid fa-plus mr-1"></i> Tambah
      </button>
    </form>
  </div>

  <!-- List -->
  <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-semibold text-slate-800">Daftar Kelas</h3>
      <span class="badge-pill bg-slate-100 text-slate-600"><?= count($classes) ?> kelas</span>
    </div>
    <div class="divide-y divide-slate-50">
      <?php foreach ($classes as $c): ?>
        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 transition-colors">
          <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-school text-primary-600 text-sm"></i>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-slate-700 text-sm"><?= esc($c['name']) ?></p>
            <p class="text-xs text-slate-400"><code><?= esc($c['id']) ?></code> · <?= $c['student_count'] ?> siswa</p>
          </div>
          <form action="<?= base_url('admin/classes/delete/' . $c['id']) ?>" method="POST"
            onsubmit="return confirm('Hapus kelas <?= esc($c['name']) ?>?')">
            <?= csrf_field() ?>
            <button class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
              <i class="fa-solid fa-trash text-sm"></i>
            </button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (empty($classes)): ?>
        <div class="text-center py-12 text-slate-400">
          <i class="fa-solid fa-school text-2xl mb-2 block opacity-40"></i>
          Belum ada kelas.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
