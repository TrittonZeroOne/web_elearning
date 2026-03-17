<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">

  <!-- FORM TAMBAH -->
  <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <h3 class="font-semibold text-slate-800 mb-4">
      <i class="fa-solid fa-plus-circle text-primary-600 mr-1"></i> Tambah Mata Pelajaran
    </h3>
    <form action="<?= base_url('admin/subjects/store') ?>" method="POST" class="space-y-3">
      <?= csrf_field() ?>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Mata Pelajaran</label>
        <input type="text" name="name" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Guru Pengampu</label>
        <select name="teacher_id" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <option value="">Pilih guru...</option>
          <?php foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>"><?= esc($t['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Kelas</label>
        <select name="class_id" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <option value="">Pilih kelas...</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Hari</label>
        <select name="schedule_day"
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d): ?>
            <option><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Waktu Mulai &ndash; Selesai</label>
        <div class="flex items-center gap-2">
          <input type="time" id="add_time_start"
            class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
            onchange="syncAddTime()">
          <span class="text-slate-400 text-sm">&ndash;</span>
          <input type="time" id="add_time_end"
            class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
            onchange="syncAddTime()">
        </div>
        <input type="hidden" name="schedule_time" id="add_schedule_time">
        <p class="text-xs text-slate-400 mt-1">Contoh: 07:00 &ndash; 08:30</p>
      </div>

      <button type="submit"
        class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
        <i class="fa-solid fa-plus mr-1"></i> Tambah
      </button>
    </form>
  </div>

  <!-- TABEL -->
  <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h3 class="font-semibold text-slate-800">
        Daftar Mata Pelajaran <span class="text-slate-400 font-normal">(<?= count($subjects) ?>)</span>
      </h3>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="mx-5 mt-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
      </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50/80 border-b border-slate-100">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs">Mata Pelajaran</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs hidden sm:table-cell">Guru</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs">Kelas</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs hidden md:table-cell">Jadwal</th>
            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <?php if (empty($subjects)): ?>
            <tr><td colspan="5" class="text-center py-10 text-slate-400">Belum ada mata pelajaran.</td></tr>
          <?php endif; ?>
          <?php foreach ($subjects as $s): ?>
            <tr class="hover:bg-slate-50/50">
              <td class="px-5 py-3.5 font-medium text-slate-700"><?= esc($s['name']) ?></td>
              <td class="px-5 py-3.5 text-slate-500 hidden sm:table-cell"><?= esc($s['teacher_name']) ?></td>
              <td class="px-5 py-3.5">
                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full"><?= esc($s['class_name']) ?></span>
              </td>
              <td class="px-5 py-3.5 text-slate-400 text-xs hidden md:table-cell">
                <?= esc($s['schedule_day']) ?>, <?= esc($s['schedule_time']) ?>
              </td>
              <td class="px-5 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button type="button"
                    onclick="openEditModal(
                      <?= $s['id'] ?>,
                      '<?= esc(addslashes($s['name'])) ?>',
                      '<?= esc($s['teacher_id']) ?>',
                      '<?= esc($s['class_id']) ?>',
                      '<?= esc($s['schedule_day']) ?>',
                      '<?= esc($s['schedule_time']) ?>'
                    )"
                    class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                    title="Edit">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <form action="<?= base_url('admin/subjects/delete/' . $s['id']) ?>" method="POST"
                    onsubmit="return confirm('Hapus mata pelajaran ini?')" class="inline">
                    <?= csrf_field() ?>
                    <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                      <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL EDIT MATA PELAJARAN -->
<div id="editModal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative animate-fadeIn">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-semibold text-slate-800 text-lg">
        <i class="fa-solid fa-pen text-primary-600 mr-2"></i>Edit Mata Pelajaran
      </h3>
      <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>
    <form id="editForm" action="" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Mata Pelajaran</label>
        <input type="text" name="name" id="edit_name" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Guru Pengampu</label>
        <select name="teacher_id" id="edit_teacher_id" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <?php foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>"><?= esc($t['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Kelas</label>
        <select name="class_id" id="edit_class_id" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Hari</label>
        <select name="schedule_day" id="edit_schedule_day"
          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d): ?>
            <option><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Waktu Mulai &ndash; Selesai</label>
        <div class="flex items-center gap-2">
          <input type="time" id="edit_time_start"
            class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
            onchange="syncEditTime()">
          <span class="text-slate-400 text-sm">&ndash;</span>
          <input type="time" id="edit_time_end"
            class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
            onchange="syncEditTime()">
        </div>
        <input type="hidden" name="schedule_time" id="edit_schedule_time">
        <p class="text-xs text-slate-400 mt-1">Contoh: 07:00 &ndash; 08:30</p>
      </div>
      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeEditModal()"
          class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
          Batal
        </button>
        <button type="submit"
          class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
          <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  @keyframes fadeIn { from { opacity:0; transform:scale(.96) } to { opacity:1; transform:scale(1) } }
  .animate-fadeIn { animation: fadeIn .18s ease-out both }
</style>
<script>
function syncAddTime() {
  const s = document.getElementById('add_time_start').value;
  const e = document.getElementById('add_time_end').value;
  document.getElementById('add_schedule_time').value = (s && e) ? s + ' - ' + e : (s || e);
}
function openEditModal(id, name, teacherId, classId, day, scheduleTime) {
  document.getElementById('editForm').action = '<?= base_url('admin/subjects/update/') ?>' + id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_schedule_day').value = day;
  setSelect('edit_teacher_id', teacherId);
  setSelect('edit_class_id', classId);
  document.getElementById('edit_schedule_time').value = scheduleTime;
  const parts = scheduleTime ? scheduleTime.split(' - ') : [];
  document.getElementById('edit_time_start').value = parts[0] ? parts[0].trim() : '';
  document.getElementById('edit_time_end').value   = parts[1] ? parts[1].trim() : '';
  const modal = document.getElementById('editModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeEditModal() {
  const modal = document.getElementById('editModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
function syncEditTime() {
  const s = document.getElementById('edit_time_start').value;
  const e = document.getElementById('edit_time_end').value;
  document.getElementById('edit_schedule_time').value = (s && e) ? s + ' - ' + e : (s || e);
}
function setSelect(id, val) {
  const sel = document.getElementById(id);
  for (let o of sel.options) { if (o.value == val) { o.selected = true; break; } }
}
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditModal();
});
</script>
<?= $this->endSection() ?>