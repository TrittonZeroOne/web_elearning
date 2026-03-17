<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="pt-6">
  <?php if (empty($subjects)): ?>
    <div class="bg-white rounded-2xl p-16 text-center shadow-sm border border-slate-100">
      <i class="fa-solid fa-chalkboard-user text-5xl text-slate-300 mb-4 block"></i>
      <p class="text-slate-500 font-medium">Belum ada mata pelajaran</p>
      <p class="text-sm text-slate-400 mt-1">Hubungi admin untuk menambahkan mata pelajaran.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      $dayColors = ['Senin'=>'bg-blue-500','Selasa'=>'bg-green-500','Rabu'=>'bg-purple-500','Kamis'=>'bg-orange-500','Jumat'=>'bg-red-500','Sabtu'=>'bg-pink-500'];
      foreach ($subjects as $s):
        $dc = $dayColors[$s['schedule_day']] ?? 'bg-slate-400';
      ?>
        <a href="<?=base_url('/teacher/subjects/'. $s['id']) ?>"
          class="group bg-white rounded-2xl shadow-sm border border-slate-100 p-5 hover:shadow-md hover:border-primary-200 transition-all">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform">
              <i class="fa-solid fa-book text-white text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-slate-800 text-sm truncate group-hover:text-primary-600 transition-colors"><?= esc($s['name']) ?></h3>
              <p class="text-xs text-slate-500 mt-0.5"><?= esc($s['class_name']) ?></p>
            </div>
          </div>
          <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
            <span class="inline-block w-2.5 h-2.5 rounded-full <?= $dc ?>"></span>
            <span class="text-xs text-slate-500"><?= esc($s['schedule_day']) ?></span>
            <span class="text-xs text-slate-400">·</span>
            <span class="text-xs text-slate-500"><?= esc($s['schedule_time']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>