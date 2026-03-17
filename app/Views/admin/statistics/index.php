<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 space-y-6">

  <!-- Header Banner -->
  <div class="relative overflow-hidden bg-gradient-to-r from-purple-700 via-purple-800 to-slate-900 rounded-2xl p-6 shadow-lg">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
      <div class="absolute right-24 -bottom-6 w-24 h-24 bg-white/5 rounded-full"></div>
    </div>
    <div class="relative flex items-center gap-4">
      <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-chart-bar text-white text-xl"></i>
      </div>
      <div>
        <h2 class="text-white text-xl font-bold">Statistik Aplikasi</h2>
        <p class="text-purple-300 text-sm mt-0.5">Data terkini penggunaan SMA E-Learn</p>
      </div>
      <div class="ml-auto hidden sm:block">
        <span class="text-purple-300 text-xs bg-white/10 px-3 py-1.5 rounded-lg">
          <i class="fa-solid fa-clock mr-1"></i><?= date('d M Y, H:i') ?>
        </span>
      </div>
    </div>
  </div>

  <!-- ══ PENGGUNA ══ -->
  <div>
    <div class="flex items-center gap-2 mb-3">
      <i class="fa-solid fa-users text-purple-600 text-sm"></i>
      <h3 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Pengguna</h3>
      <div class="flex-1 h-px bg-slate-200"></div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <?php
      $statCards = [
        ['icon'=>'fa-user-graduate', 'label'=>'Siswa',          'value'=>$total_siswa,   'color'=>'blue'],
        ['icon'=>'fa-chalkboard-user','label'=>'Guru',           'value'=>$total_guru,    'color'=>'green'],
        ['icon'=>'fa-school',         'label'=>'Kelas',          'value'=>$total_kelas,   'color'=>'orange'],
        ['icon'=>'fa-book',           'label'=>'Mata Pelajaran', 'value'=>$total_mapel,   'color'=>'purple'],
      ];
      $colorMap = [
        'blue'   => ['bg'=>'bg-blue-50',   'border'=>'border-blue-200',   'icon'=>'text-blue-500',   'val'=>'text-blue-600'],
        'green'  => ['bg'=>'bg-green-50',  'border'=>'border-green-200',  'icon'=>'text-green-500',  'val'=>'text-green-600'],
        'orange' => ['bg'=>'bg-orange-50', 'border'=>'border-orange-200', 'icon'=>'text-orange-500', 'val'=>'text-orange-600'],
        'purple' => ['bg'=>'bg-purple-50', 'border'=>'border-purple-200', 'icon'=>'text-purple-500', 'val'=>'text-purple-600'],
        'red'    => ['bg'=>'bg-red-50',    'border'=>'border-red-200',    'icon'=>'text-red-500',    'val'=>'text-red-600'],
        'teal'   => ['bg'=>'bg-teal-50',   'border'=>'border-teal-200',   'icon'=>'text-teal-500',   'val'=>'text-teal-600'],
        'indigo' => ['bg'=>'bg-indigo-50', 'border'=>'border-indigo-200', 'icon'=>'text-indigo-500', 'val'=>'text-indigo-600'],
        'amber'  => ['bg'=>'bg-amber-50',  'border'=>'border-amber-200',  'icon'=>'text-amber-500',  'val'=>'text-amber-600'],
      ];
      foreach ($statCards as $card):
        $c = $colorMap[$card['color']];
      ?>
        <div class="<?= $c['bg'] ?> border <?= $c['border'] ?> rounded-2xl p-5 flex flex-col items-center text-center shadow-sm">
          <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3">
            <i class="fa-solid <?= $card['icon'] ?> <?= $c['icon'] ?> text-lg"></i>
          </div>
          <p class="text-3xl font-bold <?= $c['val'] ?> leading-none"><?= number_format($card['value']) ?></p>
          <p class="text-xs font-medium text-slate-500 mt-2"><?= $card['label'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ KONTEN AKADEMIK ══ -->
  <div>
    <div class="flex items-center gap-2 mb-3">
      <i class="fa-solid fa-book-open text-purple-600 text-sm"></i>
      <h3 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Konten Akademik</h3>
      <div class="flex-1 h-px bg-slate-200"></div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <?php
      $contentCards = [
        ['icon'=>'fa-file-lines',    'label'=>'Materi',       'value'=>$total_materi,  'color'=>'red'],
        ['icon'=>'fa-clipboard-list','label'=>'Tugas',         'value'=>$total_tugas,   'color'=>'teal'],
        ['icon'=>'fa-upload',        'label'=>'Pengumpulan',  'value'=>$total_submisi, 'color'=>'indigo'],
        ['icon'=>'fa-user-check',    'label'=>'Absensi',      'value'=>$total_absensi, 'color'=>'amber'],
      ];
      foreach ($contentCards as $card):
        $c = $colorMap[$card['color']];
      ?>
        <div class="<?= $c['bg'] ?> border <?= $c['border'] ?> rounded-2xl p-5 flex flex-col items-center text-center shadow-sm">
          <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3">
            <i class="fa-solid <?= $card['icon'] ?> <?= $c['icon'] ?> text-lg"></i>
          </div>
          <p class="text-3xl font-bold <?= $c['val'] ?> leading-none"><?= number_format($card['value']) ?></p>
          <p class="text-xs font-medium text-slate-500 mt-2"><?= $card['label'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ RINGKASAN TOTAL + DETAIL ══ -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <!-- Ringkasan Total -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
      <h4 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-sigma text-purple-500 text-sm"></i> Total Keseluruhan
      </h4>
      <div class="space-y-3">
        <?php
        $totals = [
          ['label'=>'Total Pengguna',  'value'=>$total_siswa + $total_guru,          'icon'=>'fa-users',       'color'=>'text-blue-600',   'bg'=>'bg-blue-100'],
          ['label'=>'Total Konten',    'value'=>$total_materi + $total_tugas,         'icon'=>'fa-layer-group', 'color'=>'text-teal-600',   'bg'=>'bg-teal-100'],
          ['label'=>'Total Aktivitas', 'value'=>$total_submisi + $total_absensi,      'icon'=>'fa-bolt',        'color'=>'text-indigo-600', 'bg'=>'bg-indigo-100'],
        ];
        foreach ($totals as $t):
        ?>
          <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
            <div class="w-9 h-9 <?= $t['bg'] ?> rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fa-solid <?= $t['icon'] ?> <?= $t['color'] ?> text-sm"></i>
            </div>
            <div class="flex-1">
              <p class="text-xs text-slate-500"><?= $t['label'] ?></p>
            </div>
            <p class="text-xl font-bold <?= $t['color'] ?>"><?= number_format($t['value']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Grand total -->
      <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-600">Grand Total</p>
        <p class="text-2xl font-bold text-purple-600">
          <?= number_format($total_siswa + $total_guru + $total_kelas + $total_mapel + $total_materi + $total_tugas + $total_submisi + $total_absensi) ?>
        </p>
      </div>
    </div>

    <!-- Distribusi Siswa per Kelas -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
      <h4 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-chart-pie text-orange-500 text-sm"></i> Siswa per Kelas
      </h4>
      <?php if (empty($class_stats)): ?>
        <p class="text-xs text-slate-400 text-center py-8">Belum ada data kelas.</p>
      <?php else:
        $maxCount = max(array_column($class_stats, 'count')) ?: 1;
        $barColors = ['bg-blue-500','bg-purple-500','bg-green-500','bg-orange-500','bg-teal-500','bg-red-500'];
        $i = 0;
        foreach ($class_stats as $cs):
          $pct = round(($cs['count'] / $maxCount) * 100);
          $bar = $barColors[$i++ % count($barColors)];
      ?>
        <div class="mb-3">
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-slate-600"><?= esc($cs['name']) ?></span>
            <span class="text-xs font-bold text-slate-700"><?= $cs['count'] ?> siswa</span>
          </div>
          <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full <?= $bar ?> rounded-full transition-all" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Top 5 Mapel Pengumpulan -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
      <h4 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-trophy text-yellow-500 text-sm"></i> Top Pengumpulan
      </h4>
      <?php if (empty($subject_submit_stats)): ?>
        <p class="text-xs text-slate-400 text-center py-8">Belum ada data pengumpulan.</p>
      <?php else:
        $maxSub  = max(array_column($subject_submit_stats, 'count')) ?: 1;
        $medals  = ['🥇','🥈','🥉','4️⃣','5️⃣'];
        foreach ($subject_submit_stats as $idx => $ss):
          $pct = round(($ss['count'] / $maxSub) * 100);
      ?>
        <div class="mb-3">
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">
              <span><?= $medals[$idx] ?? ($idx+1) ?></span>
              <span class="truncate max-w-[120px]"><?= esc($ss['name']) ?></span>
            </span>
            <span class="text-xs font-bold text-slate-700"><?= $ss['count'] ?></span>
          </div>
          <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-yellow-400 rounded-full" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

  </div>
</div>

<?= $this->endSection() ?>