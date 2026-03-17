<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 space-y-6">

  <!-- Hero Banner -->
  <div class="relative overflow-hidden bg-gradient-to-r from-primary-700 via-primary-800 to-purple-900 rounded-2xl p-6 shadow-lg">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full"></div>
      <div class="absolute right-20 -bottom-8 w-24 h-24 bg-white/5 rounded-full"></div>
    </div>
    <div class="relative">
      <p class="text-primary-300 text-sm mb-1">Selamat datang kembali,</p>
      <h2 class="text-white text-xl font-bold"><?= esc(session()->get('full_name')) ?> 👋</h2>
      <p class="text-primary-300 text-sm mt-1"><?= date('l, d F Y') ?></p>
      <div class="flex flex-wrap gap-2 mt-3">
        <?php if (!empty($today_subjects)): ?>
          <div class="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-400/30 text-blue-200 px-3 py-1.5 rounded-lg text-xs font-medium">
            <i class="fa-solid fa-calendar-day"></i> <?= count($today_subjects) ?> pelajaran hari ini
          </div>
        <?php endif; ?>
        <?php if (!empty($pending_tasks)): ?>
          <div class="inline-flex items-center gap-2 bg-orange-500/20 border border-orange-400/30 text-orange-300 px-3 py-1.5 rounded-lg text-xs font-medium">
            <i class="fa-solid fa-clock-rotate-left"></i> <?= count($pending_tasks) ?> tugas menunggu
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kiri: Mapel Hari Ini + Tugas Menunggu -->
    <div class="lg:col-span-2 space-y-4">

      <!-- Mata Pelajaran Hari Ini -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-calendar-day text-blue-500 text-sm"></i>
            Pelajaran Hari Ini — <?= esc($today_name) ?>
          </h3>
          <a href="<?= base_url('student/subjects') ?>" class="text-xs text-primary-600 font-medium">Semua →</a>
        </div>
        <?php if (empty($today_subjects)): ?>
          <div class="text-center py-10">
            <i class="fa-solid fa-mug-hot text-3xl text-slate-300 mb-2 block"></i>
            <p class="text-sm text-slate-400">Tidak ada pelajaran hari ini.</p>
            <p class="text-xs text-slate-400 mt-1">Waktunya belajar mandiri 📚</p>
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
              <a href="<?= base_url('student/subjects/' . $s['id']) ?>"
                class="relative overflow-hidden rounded-xl p-4 text-white group hover:scale-[1.02] transition-all shadow-sm">
                <div class="absolute inset-0 bg-gradient-to-br <?= $g ?>"></div>
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                <div class="relative flex items-start gap-3">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm leading-tight"><?= esc($s['name']) ?></p>
                    <p class="text-white/70 text-xs mt-1"><?= esc($s['teacher_name']) ?></p>
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

      <!-- Tugas Menunggu -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-list-check text-orange-500 text-sm"></i> Tugas Menunggu
        </h3>
        <?php if (empty($pending_tasks)): ?>
          <div class="text-center py-6">
            <i class="fa-solid fa-circle-check text-3xl text-green-400 mb-2 block"></i>
            <p class="text-xs text-slate-400">Semua tugas sudah selesai! 🎉</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($pending_tasks as $t):
              $isUrgent = $t['deadline'] && strtotime($t['deadline']) <= strtotime('+2 days');
            ?>
              <a href="<?= base_url('student/subjects/' . $t['subject_id']) ?>"
                class="flex items-center gap-3 p-3 rounded-xl border transition-colors hover:bg-slate-50
                  <?= $isUrgent ? 'border-red-200 bg-red-50/50' : 'border-slate-100 bg-slate-50/50' ?>">
                <div class="w-8 h-8 rounded-lg <?= $isUrgent ? 'bg-red-100' : 'bg-orange-100' ?> flex items-center justify-center flex-shrink-0">
                  <i class="fa-solid fa-clipboard-list <?= $isUrgent ? 'text-red-500' : 'text-orange-500' ?> text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-slate-700 truncate"><?= esc($t['title']) ?></p>
                  <p class="text-xs text-slate-400 truncate"><?= esc($t['subject_name']) ?></p>
                </div>
                <?php if ($t['deadline']): ?>
                  <span class="text-xs <?= $isUrgent ? 'text-red-500 font-semibold' : 'text-slate-400' ?> flex-shrink-0">
                    <?= date('d M', strtotime($t['deadline'])) ?>
                  </span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Kanan: Pengumuman + Tugas Dinilai -->
    <div class="space-y-4">

      <!-- Pengumuman -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-bullhorn text-primary-500 text-sm"></i> Pengumuman
        </h3>
        <?php if (empty($announcements)): ?>
          <p class="text-xs text-slate-400 text-center py-4">Tidak ada pengumuman.</p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($announcements as $a): ?>
              <div class="border-l-2 border-primary-400 pl-3">
                <p class="text-xs font-semibold text-slate-700"><?= esc($a['title']) ?></p>
                <p class="text-xs text-slate-500 mt-0.5 line-clamp-3"><?= esc($a['body']) ?></p>
                <p class="text-xs text-slate-400 mt-1"><?= date('d M Y', strtotime($a['created_at'])) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tugas Dinilai -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-star text-yellow-500 text-sm"></i> Tugas Dinilai
        </h3>
        <?php if (empty($graded_tasks)): ?>
          <div class="text-center py-6">
            <i class="fa-solid fa-hourglass-half text-3xl text-slate-300 mb-2 block"></i>
            <p class="text-xs text-slate-400">Belum ada tugas yang dinilai.</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($graded_tasks as $t):
              $grade      = (int)$t['grade'];
              $gradeColor = $grade >= 80 ? 'bg-green-500' : ($grade >= 60 ? 'bg-yellow-500' : 'bg-red-500');
            ?>
              <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                  <i class="fa-solid fa-star text-yellow-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-slate-700 truncate"><?= esc($t['title']) ?></p>
                  <p class="text-xs text-slate-400 truncate"><?= esc($t['subject_name']) ?></p>
                  <?php if (!empty($t['feedback'])): ?>
                    <p class="text-xs text-slate-500 italic truncate mt-0.5">"<?= esc($t['feedback']) ?>"</p>
                  <?php endif; ?>
                </div>
                <span class="text-xs font-bold text-white <?= $gradeColor ?> px-2.5 py-1 rounded-lg flex-shrink-0 min-w-[2.5rem] text-center">
                  <?= $grade ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?= $this->endSection() ?>