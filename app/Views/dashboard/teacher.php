<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 space-y-6">

  <!-- Stats Row -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
          <i class="fa-solid fa-book text-primary-600 text-sm"></i>
        </div>
        <p class="text-sm text-slate-500">Mata Pelajaran</p>
      </div>
      <p class="text-3xl font-bold text-primary-600"><?= $total_subjects ?></p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
          <i class="fa-solid fa-clock text-orange-500 text-sm"></i>
        </div>
        <p class="text-sm text-slate-500">Belum Dinilai</p>
      </div>
      <p class="text-3xl font-bold text-orange-500"><?= count($ungraded_submit) ?></p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 col-span-2 sm:col-span-1">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
          <i class="fa-solid fa-calendar-day text-blue-600 text-sm"></i>
        </div>
        <p class="text-sm text-slate-500">Hari Ini</p>
      </div>
      <p class="text-3xl font-bold text-blue-600"><?= count($today_subjects) ?> <span class="text-base font-normal text-slate-400">kelas</span></p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kiri: Jadwal Hari Ini (full width seperti versi asal) -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 h-full">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-calendar-day text-blue-500 text-sm"></i>
            Jadwal Mengajar — <?= esc($today_name) ?>
          </h3>
          <a href="<?= base_url('teacher/subjects') ?>" class="text-xs text-primary-600 font-medium hover:text-primary-700">Semua →</a>
        </div>
        <?php if (empty($today_subjects)): ?>
          <div class="text-center py-16">
            <i class="fa-solid fa-mug-hot text-4xl text-slate-200 mb-3 block"></i>
            <p class="text-sm text-slate-400">Tidak ada jadwal mengajar hari ini.</p>
            <p class="text-xs text-slate-400 mt-1">Nikmati hari libur 🎉</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php
            $gradients = [
              'from-purple-500 to-purple-700', 'from-blue-500 to-blue-700',
              'from-green-500 to-emerald-700', 'from-orange-500 to-amber-600',
              'from-pink-500 to-rose-600',     'from-teal-500 to-cyan-600',
            ];
            $i = 0;
            foreach ($today_subjects as $s):
              $g = $gradients[$i++ % count($gradients)];
            ?>
              <a href="<?= base_url('teacher/subjects/' . $s['id']) ?>"
                class="relative overflow-hidden rounded-xl p-4 text-white group hover:scale-[1.02] transition-all shadow-sm">
                <div class="absolute inset-0 bg-gradient-to-br <?= $g ?>"></div>
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                <div class="relative flex items-start gap-3">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm leading-tight"><?= esc($s['name']) ?></p>
                    <p class="text-white/70 text-xs mt-1"><?= esc($s['class_name']) ?></p>
                  </div>
                  <div class="flex-shrink-0 text-right">
                    <p class="text-white/90 text-xs font-bold"><?= esc($s['schedule_time']) ?></p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Kanan: Pengumuman + Tugas Belum Dinilai -->
    <div class="space-y-4">

      <!-- Pengumuman -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-bullhorn text-primary-500 text-sm"></i> Pengumuman
        </h3>
        <?php if (empty($announcements)): ?>
          <p class="text-xs text-slate-400 text-center py-4">Tidak ada pengumuman.</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($announcements as $a): ?>
              <div class="border-l-2 border-primary-400 pl-3">
                <p class="text-xs font-semibold text-slate-700"><?= esc($a['title']) ?></p>
                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2"><?= esc($a['body']) ?></p>
                <p class="text-xs text-slate-400 mt-1"><?= date('d M Y', strtotime($a['created_at'])) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tugas Belum Dinilai -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-pen-to-square text-orange-500 text-sm"></i> Belum Dinilai
        </h3>
        <?php if (empty($ungraded_submit)): ?>
          <div class="text-center py-6">
            <i class="fa-solid fa-circle-check text-3xl text-green-400 mb-2 block"></i>
            <p class="text-xs text-slate-400">Semua sudah dinilai! 🎉</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($ungraded_submit as $s): ?>
              <div class="flex items-center gap-3 p-3 rounded-xl bg-orange-50/60 border border-orange-100">
                <!-- Avatar -->
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-amber-500
                            flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                  <?= strtoupper(substr($s['student_name'], 0, 1)) ?>
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-slate-700 truncate"><?= esc($s['student_name']) ?></p>
                  <p class="text-xs text-slate-500 truncate"><?= esc($s['subject_name']) ?>
                    <?php if ($s['class_name']): ?>
                      · <span class="font-medium"><?= esc($s['class_name']) ?></span>
                    <?php endif; ?>
                  </p>
                  <p class="text-xs text-slate-400 truncate italic"><?= esc($s['assignment_title']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?= $this->endSection() ?>