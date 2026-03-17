<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 space-y-6">

  <!-- Stats Grid -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <?php
    $stats = [
      ['Siswa',         $total_students, 'fa-user-graduate',   'from-blue-500 to-blue-600',    'bg-blue-50 text-blue-600'],
      ['Guru',          $total_teachers, 'fa-chalkboard-user', 'from-purple-500 to-purple-700', 'bg-purple-50 text-purple-600'],
      ['Mata Pelajaran',$total_subjects, 'fa-book',            'from-green-500 to-emerald-600', 'bg-green-50 text-green-600'],
      ['Kelas',         $total_classes,  'fa-school',          'from-orange-500 to-amber-600',  'bg-orange-50 text-orange-600'],
    ];
    foreach ($stats as [$label, $value, $icon, $grad, $ic]):
    ?>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <p class="text-sm text-slate-500 font-medium"><?= $label ?></p>
          <div class="w-10 h-10 bg-gradient-to-br <?= $grad ?> rounded-xl flex items-center justify-center shadow-sm">
            <i class="fa-solid <?= $icon ?> text-white text-sm"></i>
          </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= number_format($value) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <h3 class="font-semibold text-slate-800 mb-4">Aksi Cepat</h3>
      <div class="grid grid-cols-2 gap-3">
        <?php
        $actions = [
          ['admin/users/create', 'fa-user-plus', 'Tambah Pengguna', 'from-purple-500 to-purple-700'],
          ['admin/classes',      'fa-school',     'Kelola Kelas',    'from-blue-500 to-blue-600'],
          ['admin/subjects',     'fa-book',       'Kelola Mapel',    'from-green-500 to-emerald-600'],
          ['admin/announcements','fa-bullhorn',   'Pengumuman',      'from-orange-500 to-amber-600'],
        ];
        foreach ($actions as [$url, $icon, $label, $grad]):
        ?>
          <a href="<?= base_url($url) ?>"
            class="flex flex-col items-center gap-2.5 p-4 rounded-xl border-2 border-transparent
                   hover:border-slate-200 hover:bg-slate-50 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-br <?= $grad ?> rounded-xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
              <i class="fa-solid <?= $icon ?> text-white text-lg"></i>
            </div>
            <span class="text-xs font-semibold text-slate-600 text-center"><?= $label ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Announcements -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-800">Pengumuman Terbaru</h3>
        <a href="<?= base_url('admin/announcements') ?>" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Lihat semua →</a>
      </div>
      <?php if (empty($announcements)): ?>
        <div class="text-center py-8">
          <i class="fa-solid fa-bullhorn text-2xl text-slate-300 mb-2 block"></i>
          <p class="text-sm text-slate-400">Belum ada pengumuman.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($announcements as $a):
            $tc = $a['target']==='all' ? 'bg-slate-100 text-slate-600' : ($a['target']==='student' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700');
          ?>
            <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors">
              <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-bullhorn text-primary-600 text-xs"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="text-sm font-semibold text-slate-700 truncate"><?= esc($a['title']) ?></p>
                  <span class="<?= $tc ?> text-xs px-1.5 py-0.5 rounded-md font-medium"><?= $a['target'] ?></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5"><?= esc($a['sender_name']) ?> · <?= date('d M', strtotime($a['created_at'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>