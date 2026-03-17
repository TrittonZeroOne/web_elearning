<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">

  <!-- FORM BUAT PENGUMUMAN -->
  <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <h3 class="font-semibold text-slate-800 mb-4">
      <i class="fa-solid fa-bullhorn text-primary-600 mr-1"></i> Buat Pengumuman
    </h3>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="mb-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= session()->getFlashdata('error') ?>
      </div>
    <?php endif; ?>

    <form action="<?= base_url('admin/announcements/store') ?>" method="POST" class="space-y-3">
      <?= csrf_field() ?>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Judul</label>
        <input type="text" name="title" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
          placeholder="Judul pengumuman...">
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Target Penerima</label>
        <select name="target"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <option value="all">Semua Pengguna</option>
          <option value="student">Siswa</option>
          <option value="teacher">Guru</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Isi Pengumuman</label>
        <textarea name="body" required rows="5"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 resize-none"
          placeholder="Tulis isi pengumuman di sini..."></textarea>
      </div>

      <button type="submit"
        class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
        <i class="fa-solid fa-paper-plane mr-1"></i> Kirim
      </button>
    </form>
  </div>

  <!-- DAFTAR PENGUMUMAN -->
  <div class="lg:col-span-3 space-y-3">

    <?php if (session()->getFlashdata('success')): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
      </div>
    <?php endif; ?>

    <?php if (empty($announcements)): ?>
      <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
        <i class="fa-solid fa-bullhorn text-4xl text-slate-300 mb-3 block"></i>
        <p class="text-slate-400">Belum ada pengumuman.</p>
      </div>
    <?php else: ?>
      <?php foreach ($announcements as $a):
        $tc = $a['target'] === 'all'
          ? 'bg-slate-100 text-slate-600'
          : ($a['target'] === 'student' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700');
        $targetLabel = $a['target'] === 'all' ? 'Semua' : ucfirst($a['target']);
        $bodyJs      = esc(addslashes(str_replace(["\r", "\n"], ' ', $a['body'])));
        $titleJs     = esc(addslashes($a['title']));
      ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="fa-solid fa-bullhorn text-primary-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2 flex-wrap">
                <h4 class="font-semibold text-slate-800 text-sm"><?= esc($a['title']) ?></h4>
                <div class="flex items-center gap-2">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $tc ?>">
                    <?= $targetLabel ?>
                  </span>
                  <!-- Tombol Edit -->
                  <button type="button"
                    onclick="openEditAnnouncement(<?= (int)$a['id'] ?>, '<?= $titleJs ?>', '<?= esc($a['target']) ?>', '<?= $bodyJs ?>')"
                    class="p-1 text-slate-400 hover:text-primary-600 transition-colors" title="Edit">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <!-- Tombol Hapus -->
                  <form action="<?= base_url('admin/announcements/delete/' . $a['id']) ?>" method="POST"
                    onsubmit="return confirm('Hapus pengumuman ini?')" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                      <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                  </form>
                </div>
              </div>
              <p class="text-sm text-slate-600 mt-1"><?= esc($a['body']) ?></p>
              <p class="text-xs text-slate-400 mt-2">
                <i class="fa-regular fa-user mr-1"></i><?= esc($a['sender_name']) ?> &middot;
                <?= date('d M Y H:i', strtotime($a['created_at'])) ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL EDIT PENGUMUMAN -->
<div id="editAnnouncementModal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-fadeIn">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-semibold text-slate-800 text-lg">
        <i class="fa-solid fa-pen text-primary-600 mr-2"></i>Edit Pengumuman
      </h3>
      <button type="button" onclick="closeEditAnnouncement()"
        class="text-slate-400 hover:text-slate-600 transition-colors">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <form id="editAnnouncementForm" action="" method="POST" class="space-y-4">
      <?= csrf_field() ?>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Judul</label>
        <input type="text" name="title" id="ea_title" required
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Target Penerima</label>
        <select name="target" id="ea_target"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          <option value="all">Semua Pengguna</option>
          <option value="student">Siswa</option>
          <option value="teacher">Guru</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Isi Pengumuman</label>
        <textarea name="body" id="ea_body" required rows="4"
          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 resize-none"></textarea>
      </div>

      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeEditAnnouncement()"
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
function openEditAnnouncement(id, title, target, body) {
  document.getElementById('editAnnouncementForm').action = '<?= base_url('admin/announcements/update/') ?>' + id;
  document.getElementById('ea_title').value = title;
  document.getElementById('ea_body').value  = body;
  var sel = document.getElementById('ea_target');
  for (var i = 0; i < sel.options.length; i++) {
    sel.options[i].selected = (sel.options[i].value === target);
  }
  var modal = document.getElementById('editAnnouncementModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeEditAnnouncement() {
  var modal = document.getElementById('editAnnouncementModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

document.getElementById('editAnnouncementModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditAnnouncement();
});
</script>

<?= $this->endSection() ?>