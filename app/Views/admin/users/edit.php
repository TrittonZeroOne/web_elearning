<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 max-w-lg">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center gap-3 mb-6">
      <a href="<?= base_url('admin/users') ?>" class="p-2 rounded-xl hover:bg-slate-100 text-slate-500"><i class="fa-solid fa-arrow-left text-sm"></i></a>
      <h2 class="font-semibold text-slate-800">Edit Pengguna</h2>
    </div>
    <form action="<?= base_url('admin/users/update/' . esc($user['id'])) ?>" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
        <input type="text" name="full_name" value="<?= esc($user['full_name']) ?>" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
        <input type="email" value="<?= esc($user['email']) ?>" disabled
          class="w-full border border-slate-100 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed">
        <p class="text-xs text-slate-400 mt-1">Email tidak bisa diubah di sini.</p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
          <select name="role" id="roleSelect" onchange="toggleClassField(this.value)" required
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            <option value="teacher" <?= $user['role']==='teacher' ? 'selected':'' ?>>Guru</option>
            <option value="student" <?= $user['role']==='student' ? 'selected':'' ?>>Siswa</option>
          </select>
        </div>
        <div id="classField" class="<?= $user['role']!=='student' ? 'hidden':'' ?>">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas</label>
          <select name="class_id"
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            <option value="">Tidak ada</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $user['class_id']===$c['id']?'selected':'' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">Simpan Perubahan</button>
        <a href="<?= base_url('admin/users') ?>" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition-colors">Batal</a>
      </div>
    </form>

    <!-- Ubah Password -->
    <div class="mt-6 pt-6 border-t border-slate-100">
      <button onclick="togglePasswordForm()" id="pwdToggleBtn"
        class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors mb-4">
        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
        Ubah Password
        <i id="pwdToggleIcon" class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform"></i>
      </button>

      <div id="passwordForm" class="hidden">
        <form action="<?= base_url('admin/users/change-password/' . esc($user['id'])) ?>" method="POST" class="space-y-4">
          <?= csrf_field() ?>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
            <div class="relative">
              <input type="password" name="new_password" id="newPassword" required minlength="6"
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm pr-10
                       focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
                placeholder="Min. 6 karakter">
              <button type="button" onclick="togglePwd('newPassword', 'eyeNew')"
                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                <i id="eyeNew" class="fa-solid fa-eye text-sm"></i>
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input type="password" name="confirm_password" id="confirmPassword" required minlength="6"
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm pr-10
                       focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
                placeholder="Ulangi password baru">
              <button type="button" onclick="togglePwd('confirmPassword', 'eyeConfirm')"
                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                <i id="eyeConfirm" class="fa-solid fa-eye text-sm"></i>
              </button>
            </div>
            <p id="pwdMatchHint" class="text-xs mt-1 hidden"></p>
          </div>
          <button type="submit" id="pwdSubmitBtn"
            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
            <i class="fa-solid fa-key mr-1"></i> Simpan Password Baru
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function toggleClassField(role) {
  document.getElementById('classField').classList.toggle('hidden', role !== 'student');
}
function togglePasswordForm() {
  const form = document.getElementById('passwordForm');
  const icon = document.getElementById('pwdToggleIcon');
  const hidden = form.classList.toggle('hidden');
  icon.style.transform = hidden ? '' : 'rotate(180deg)';
}
function togglePwd(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
// Real-time match check
document.addEventListener('DOMContentLoaded', () => {
  const np  = document.getElementById('newPassword');
  const cp  = document.getElementById('confirmPassword');
  const btn = document.getElementById('pwdSubmitBtn');
  const hint = document.getElementById('pwdMatchHint');

  function checkMatch() {
    if (!cp.value) { hint.classList.add('hidden'); return; }
    const match = np.value === cp.value;
    hint.classList.remove('hidden');
    hint.textContent = match ? '✓ Password cocok' : '✗ Password tidak cocok';
    hint.className   = 'text-xs mt-1 ' + (match ? 'text-green-600' : 'text-red-500');
    btn.disabled     = !match;
    btn.classList.toggle('opacity-50', !match);
    btn.classList.toggle('cursor-not-allowed', !match);
  }
  np.addEventListener('input', checkMatch);
  cp.addEventListener('input', checkMatch);
});
</script>
<?= $this->endSection() ?>