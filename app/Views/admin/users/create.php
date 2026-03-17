<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 max-w-lg">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center gap-3 mb-6">
      <a href="<?= base_url('admin/users') ?>" class="p-2 rounded-xl hover:bg-slate-100 text-slate-500"><i class="fa-solid fa-arrow-left text-sm"></i></a>
      <h2 class="font-semibold text-slate-800">Tambah Pengguna Baru</h2>
    </div>

    <div class="mb-5 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700 flex items-start gap-2">
      <i class="fa-solid fa-circle-info mt-0.5 flex-shrink-0"></i>
      <span>Pengguna akan dibuat di Supabase Auth dan tabel <code class="font-mono">profiles</code>. Email harus belum terdaftar di Supabase.</span>
    </div>

    <form action="<?= base_url('admin/users/store') ?>" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" name="full_name" value="<?= old('full_name') ?>" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" value="<?= old('email') ?>" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
        <input type="password" name="password" required minlength="6"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all">
        <p class="text-xs text-slate-400 mt-1">Minimal 6 karakter</p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
          <select name="role" id="roleSelect" onchange="toggleClassField(this.value)" required
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all">
            <option value="">Pilih role...</option>
            <option value="teacher">Guru</option>
            <option value="student">Siswa</option>
          </select>
        </div>
        <div id="classField" class="hidden">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas</label>
          <select name="class_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all">
            <option value="">Pilih kelas...</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
          <i class="fa-solid fa-user-plus mr-1"></i> Buat Pengguna
        </button>
        <a href="<?= base_url('admin/users') ?>" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition-colors">Batal</a>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function toggleClassField(role) {
  document.getElementById('classField').classList.toggle('hidden', role !== 'student');
}
</script>
<?= $this->endSection() ?>