<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $sid = $subject['id']; $classId = $subject['class_id']; ?>

<!-- CSRF meta untuk JS -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-name"  content="<?= csrf_token() ?>">

<div class="pt-4 space-y-4">

  <!-- Subject Header -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
    <a href="<?= base_url('teacher/subjects') ?>" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 flex-shrink-0 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="flex-1 min-w-0">
      <h2 class="font-bold text-slate-800 text-base"><?= esc($subject['name']) ?></h2>
      <p class="text-xs text-slate-500"><?= esc($subject['class_name']) ?> · <?= esc($subject['schedule_day']) ?>, <?= esc($subject['schedule_time']) ?></p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex border-b border-slate-200 overflow-x-auto">
      <?php
      $tabs = [
        'materi'  => ['Materi',  'fa-file-lines',     base_url('teacher/subjects/').$sid.'/materi'],
        'tugas'   => ['Tugas',   'fa-clipboard-list', base_url('teacher/subjects/').$sid.'/tugas'],
        'absensi' => ['Absensi', 'fa-calendar-check', base_url('teacher/subjects/').$sid.'/absensi'],
        'diskusi' => ['Diskusi', 'fa-comments',       base_url('teacher/subjects/').$sid.'/diskusi'],
      ];
      foreach ($tabs as $key => [$label, $icon, $url]):
        $isActive = $active_tab === $key;
      ?>
        <a href="<?= $url ?>"
          class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors flex-shrink-0
                 <?= $isActive ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' ?>">
          <i class="fa-solid <?= $icon ?> text-xs"></i> <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="p-5">

    <?php if ($active_tab === 'materi'): ?>
    <!-- ══ MATERI ══ -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-check mr-1"></i><?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-exclamation mr-1"></i><?= session()->getFlashdata('error') ?>
        </div>
      <?php endif; ?>

      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500"><?= count($materials ?? []) ?> materi</p>
        <button onclick="openModal('modalMateri')"
          class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors">
          <i class="fa-solid fa-plus"></i> Tambah Materi
        </button>
      </div>

      <?php if (empty($materials)): ?>
        <div class="text-center py-14">
          <i class="fa-solid fa-folder-open text-5xl text-slate-300 mb-3 block"></i>
          <p class="text-slate-500">Belum ada materi</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php
          $typeIcons = [
            'PDF'     => ['fa-file-pdf',  'bg-red-100 text-red-600',       'PDF'],
            'Video'   => ['fa-video',     'bg-blue-100 text-blue-600',     'Video'],
            'Link'    => ['fa-link',      'bg-teal-100 text-teal-600',     'Link'],
            'Dokumen' => ['fa-file-word', 'bg-indigo-100 text-indigo-600', 'Dokumen'],
            'Lainnya' => ['fa-paperclip', 'bg-gray-100 text-gray-600',     'File'],
          ];
          foreach ($materials as $m):
            [$icon, $badge, $typeLabel] = $typeIcons[$m['type']] ?? ['fa-paperclip','bg-gray-100 text-gray-600','File'];
          ?>
            <div class="flex items-center gap-3 p-4 border border-slate-100 rounded-xl hover:bg-slate-50/70 transition-colors">
              <div class="w-10 h-10 rounded-xl <?= $badge ?> flex items-center justify-center flex-shrink-0">
                <i class="fa-solid <?= $icon ?> text-sm"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-700 text-sm"><?= esc($m['title']) ?></p>
                <?php if ($m['description']): ?>
                  <p class="text-xs text-slate-400 truncate"><?= esc($m['description']) ?></p>
                <?php endif; ?>
                <p class="text-xs text-slate-400"><?= $typeLabel ?> · <?= date('d M Y', strtotime($m['created_at'])) ?></p>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <?php if (!empty($m['content_url'])): ?>
                  <a href="<?= esc($m['content_url']) ?>" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg" title="Buka">
                    <i class="fa-solid fa-external-link-alt text-sm"></i>
                  </a>
                <?php endif; ?>
                <form action="<?= base_url('teacher/materials/delete/' . $m['id']) ?>" method="POST" onsubmit="return confirm('Hapus?')">
                  <?= csrf_field() ?>
                  <button class="p-2 text-red-400 hover:bg-red-50 rounded-lg"><i class="fa-solid fa-trash text-sm"></i></button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Modal Tambah Materi -->
      <div id="modalMateri" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Tambah Materi</h3>
            <button onclick="closeModal('modalMateri')" class="p-2 text-slate-400 hover:bg-slate-100 rounded-xl"><i class="fa-solid fa-times"></i></button>
          </div>
          <form action="<?= base_url('teacher/materials/store') ?>" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="subject_id" value="<?= $sid ?>">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Judul *</label>
              <input type="text" name="title" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
              <textarea name="description" rows="2" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 resize-none"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Tipe</label>
              <select name="type" id="matType" onchange="toggleMatField(this.value)"
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                <option value="PDF">PDF</option>
                <option value="Video">Video</option>
                <option value="Link">Link / URL</option>
                <option value="Dokumen">Dokumen</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div id="matFileField">
              <label class="block text-sm font-medium text-slate-700 mb-1">Upload File <span id="matFileHint" class="text-xs text-slate-400">(maks 10MB)</span></label>
              <input type="file" name="file" id="matFileInput" accept=".pdf,.doc,.docx"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-primary-100 file:text-primary-700">
            </div>
            <div id="matUrlField" class="hidden">
              <label class="block text-sm font-medium text-slate-700 mb-1">URL</label>
              <input type="url" name="content_url" placeholder="https://..."
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            </div>
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">Simpan</button>
          </form>
        </div>
      </div>

    <?php elseif ($active_tab === 'tugas'): ?>
    <!-- ══ TUGAS ══ -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-check mr-1"></i><?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500"><?= count($assignments ?? []) ?> tugas</p>
        <button onclick="openModal('modalTugas')"
          class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors">
          <i class="fa-solid fa-plus"></i> Buat Tugas
        </button>
      </div>

      <?php if (empty($assignments)): ?>
        <div class="text-center py-14">
          <i class="fa-solid fa-clipboard-list text-5xl text-slate-300 mb-3 block"></i>
          <p class="text-slate-500">Belum ada tugas</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($assignments as $a):
            $isOverdue = $a['deadline'] && strtotime($a['deadline']) < time();
          ?>
            <details class="group border border-slate-200 rounded-2xl overflow-hidden">
              <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-slate-50/70 list-none">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                  <i class="fa-solid fa-clipboard-list text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-slate-700 text-sm"><?= esc($a['title']) ?></p>
                  <div class="flex flex-wrap gap-2 mt-1">
                    <?php if ($a['deadline']): ?>
                      <span class="text-xs <?= $isOverdue ? 'text-red-500 font-medium' : 'text-slate-400' ?>">
                        <i class="fa-regular fa-clock mr-1"></i><?= date('d M Y', strtotime($a['deadline'])) ?>
                      </span>
                    <?php endif; ?>
                    <span class="text-xs bg-blue-50 text-blue-700 font-medium px-2 py-0.5 rounded-full">
                      <?= $a['submission_count'] ?> dikumpulkan
                    </span>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-open:rotate-180 transition-transform flex-shrink-0"></i>
              </summary>
              <div class="border-t border-slate-100 bg-slate-50/30 p-4 space-y-3">
                <?php if ($a['description']): ?>
                  <p class="text-sm text-slate-600 whitespace-pre-line"><?= esc($a['description']) ?></p>
                <?php endif; ?>
                <?php if ($a['file_url']): ?>
                  <a href="<?= esc($a['file_url']) ?>" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fa-solid fa-file-arrow-down"></i> Unduh Lampiran Tugas
                  </a>
                <?php endif; ?>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Pengumpulan (<?= $a['submission_count'] ?>)</p>
                <?php if (empty($a['submissions'])): ?>
                  <p class="text-xs text-slate-400 italic">Belum ada yang mengumpulkan.</p>
                <?php else: ?>
                  <div class="space-y-2">
                    <?php foreach ($a['submissions'] as $sub): ?>
                      <div class="flex items-center gap-3 bg-white border border-slate-100 rounded-xl p-3">
                        <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                          <?= strtoupper(substr($sub['student_name'], 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                          <p class="text-xs font-semibold text-slate-700"><?= esc($sub['student_name']) ?></p>
                          <p class="text-xs text-slate-400"><?= date('d M Y H:i', strtotime($sub['submitted_at'])) ?></p>
                        </div>
                        <?php if ($sub['file_url']): ?>
                          <a href="<?= esc($sub['file_url']) ?>" target="_blank" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg flex-shrink-0">
                            <i class="fa-solid fa-file-arrow-down text-sm"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($sub['grade'] !== null): ?>
                          <span class="text-xs font-bold text-white bg-green-500 px-2 py-1 rounded-lg flex-shrink-0"><?= $sub['grade'] ?></span>
                        <?php else: ?>
                          <button onclick="openGradeModal(<?= $sub['id'] ?>, '<?= esc(addslashes($sub['student_name'])) ?>')"
                            class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg flex-shrink-0">Nilai</button>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <div class="pt-2 border-t border-slate-100">
                  <form action="<?= base_url('teacher/assignments/delete/' . $a['id']) ?>" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                    <?= csrf_field() ?>
                    <button class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 font-medium">
                      <i class="fa-solid fa-trash text-xs"></i> Hapus Tugas
                    </button>
                  </form>
                </div>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Modal Grade -->
      <div id="gradeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Beri Nilai</h3>
            <button onclick="closeModal('gradeModal')" class="p-2 text-slate-400 hover:bg-slate-100 rounded-xl"><i class="fa-solid fa-times"></i></button>
          </div>
          <form id="gradeForm" action="" method="POST" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <p class="text-sm text-slate-600">Siswa: <strong id="gradeStudentName"></strong></p>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Nilai (0–100)</label>
              <input type="number" name="grade" min="0" max="100" required
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Feedback</label>
              <textarea name="feedback" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">Simpan Nilai</button>
          </form>
        </div>
      </div>

      <!-- Modal Buat Tugas -->
      <div id="modalTugas" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Buat Tugas Baru</h3>
            <button onclick="closeModal('modalTugas')" class="p-2 text-slate-400 hover:bg-slate-100 rounded-xl"><i class="fa-solid fa-times"></i></button>
          </div>
          <form action="<?= base_url('teacher/assignments/store') ?>" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="subject_id" value="<?= $sid ?>">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Judul *</label>
              <input type="text" name="title" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi / Instruksi</label>
              <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 resize-none"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Deadline</label>
              <input type="date" name="deadline" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Lampiran <span class="text-xs text-slate-400">(opsional)</span></label>
              <input type="file" name="file" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-orange-100 file:text-orange-700">
            </div>
            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">Buat Tugas</button>
          </form>
        </div>
      </div>

    <?php elseif ($active_tab === 'absensi'): ?>
    <!-- ══ ABSENSI ══ -->
      <?php
      // Validasi: apakah tanggal yang dipilih sesuai hari jadwal?
      $schedDay = $subject['schedule_day'] ?? '';
      $dayMap = ['Senin'=>1,'Selasa'=>2,'Rabu'=>3,'Kamis'=>4,'Jumat'=>5,'Sabtu'=>6,'Minggu'=>0];
      $scheduledDow = $dayMap[$schedDay] ?? -1;
      $selectedDow  = (int)date('w', strtotime($date)); // 0=Sun
      $isScheduleDay = ($scheduledDow === $selectedDow);
      ?>
      <!-- Form filter tanggal (GET, terpisah dari form save) -->
      <form action="<?= base_url('teacher/subjects/' . $sid . '/absensi') ?>" method="GET" class="mb-0">
        <div class="flex flex-wrap items-end gap-3 mb-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
            <input type="date" name="date" id="absenDate" value="<?= esc($date) ?>" max="<?= date('Y-m-d') ?>"
              class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
          </div>
          <button type="submit"
            class="flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            <i class="fa-solid fa-search"></i> Lihat
          </button>
          <a href="<?= base_url('teacher/attendance/' . $sid . '/export') ?>"
            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            <i class="fa-solid fa-file-csv"></i> Export CSV
          </a>
          <?php
          $summary = ['Hadir'=>0,'Sakit'=>0,'Izin'=>0,'Alfa'=>0];
          foreach ($statusMap as $st) { if (isset($summary[$st])) $summary[$st]++; }
          $chipColors = ['Hadir'=>'bg-green-100 text-green-700','Sakit'=>'bg-blue-100 text-blue-700','Izin'=>'bg-orange-100 text-orange-700','Alfa'=>'bg-red-100 text-red-700'];
          ?>
          <div class="flex flex-wrap gap-2 ml-auto">
            <?php foreach ($summary as $st => $cnt): ?>
              <span class="text-xs font-semibold px-3 py-1.5 rounded-full <?= $chipColors[$st] ?>"><?= $st ?>: <?= $cnt ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </form><!-- end form filter -->

      <!-- Form save absensi (POST) -->
      <form action="<?= base_url('teacher/attendance/save') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="subject_id" value="<?= $sid ?>">
        <input type="hidden" name="date" value="<?= esc($date) ?>">
        <div class="hidden"><!-- spacer --></div>
        <?php if (!$isScheduleDay): ?>
          <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 text-sm flex items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
            <span>Tanggal yang dipilih bukan hari <strong><?= esc($schedDay) ?></strong> (hari jadwal mata pelajaran ini). Absensi hanya bisa disimpan pada hari yang sesuai jadwal.</span>
          </div>
        <?php endif; ?>
        <?php if (empty($students)): ?>
          <p class="text-center py-10 text-slate-400">Belum ada siswa di kelas ini.</p>
        <?php else: ?>
          <div class="space-y-2 mb-5">
            <?php foreach ($students as $stu):
              $cur = $statusMap[$stu['id']] ?? 'Hadir';
              $opts = ['Hadir'=>'peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600',
                       'Sakit'=>'peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600',
                       'Izin' =>'peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500',
                       'Alfa' =>'peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600'];
            ?>
              <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                  <?= strtoupper(substr($stu['full_name'], 0, 1)) ?>
                </div>
                <span class="flex-1 text-sm font-medium text-slate-700"><?= esc($stu['full_name']) ?></span>
                <div class="flex gap-1">
                  <?php foreach ($opts as $opt => $cls): ?>
                    <label class="cursor-pointer">
                      <input type="radio" name="status[<?= $stu['id'] ?>]" value="<?= $opt ?>"
                        <?= $cur === $opt ? 'checked' : '' ?> class="sr-only peer">
                      <span class="inline-block border border-slate-200 text-slate-500 text-xs font-semibold px-2.5 py-1 rounded-lg transition-all <?= $cls ?>">
                        <?= $opt ?>
                      </span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="submit" <?= !$isScheduleDay ? 'disabled' : '' ?>
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors <?= !$isScheduleDay ? 'opacity-40 cursor-not-allowed' : '' ?>">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Absensi
          </button>
        <?php endif; ?>
      </form>

    <?php elseif ($active_tab === 'diskusi'): ?>
    <!-- ══ DISKUSI ══ -->
      <div class="flex flex-col" style="height:62vh">
        <div id="chatBox" class="flex-1 overflow-y-auto space-y-3 pr-1 mb-3">
          <?php if (empty($messages)): ?>
            <div class="flex items-center justify-center h-full text-center text-slate-400">
              <div><i class="fa-solid fa-comments text-4xl mb-2 block opacity-30"></i><p class="text-sm">Belum ada pesan.</p></div>
            </div>
          <?php endif; ?>
          <?php
          $prevDateLabel = null;
          foreach ($messages as $msg):
            $isMe = $msg['sender_id'] === session()->get('user_id');
            $msgTs = strtotime($msg['sent_at']);
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $msgDate = date('Y-m-d', $msgTs);
            if ($msgDate === $today) $dateLabel = 'Hari ini';
            elseif ($msgDate === $yesterday) $dateLabel = 'Kemarin';
            else $dateLabel = date('d M Y', $msgTs);
            $showSeparator = ($dateLabel !== $prevDateLabel);
            $prevDateLabel = $dateLabel;
          ?>
            <?php if ($showSeparator): ?>
              <div class="flex items-center gap-3 my-2" data-separator="<?= esc($dateLabel) ?>">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs text-slate-400 font-medium px-2 py-1 bg-slate-100 rounded-full whitespace-nowrap"><?= esc($dateLabel) ?></span>
                <div class="flex-1 h-px bg-slate-200"></div>
              </div>
            <?php endif; ?>
            <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>" data-msgid="<?= $msg['id'] ?>">
              <div class="max-w-[78%]">
                <?php if (!$isMe): ?>
                  <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-5 h-5 rounded-full bg-slate-400 flex items-center justify-center text-white text-xs font-bold"><?= strtoupper(substr($msg['sender_name'],0,1)) ?></div>
                    <span class="text-xs font-semibold text-slate-600"><?= esc($msg['sender_name']) ?></span>
                    <?php if ($msg['sender_role']==='teacher'): ?><span class="text-xs bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded-md font-semibold">Guru</span><?php endif; ?>
                  </div>
                <?php endif; ?>
                <div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm <?= $isMe ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-slate-100 text-slate-800 rounded-bl-sm' ?>">
                  <?= nl2br(esc((string)$msg['message'])) ?>
                </div>
                <p class="text-xs text-slate-400 mt-1 <?= $isMe ? 'text-right' : '' ?>"><?= date('H:i', strtotime($msg['sent_at'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="border-t border-slate-200 pt-3 flex-shrink-0 flex gap-2">
          <input id="chatInput" type="text" placeholder="Tulis pesan..."
            class="flex-1 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}">
          <button onclick="sendMessage()" class="bg-primary-600 hover:bg-primary-700 text-white w-11 h-11 rounded-2xl flex items-center justify-center transition-colors flex-shrink-0">
            <i class="fa-solid fa-paper-plane text-sm"></i>
          </button>
        </div>
      </div>
    <?php endif; ?>

    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function openModal(id)  { const el=document.getElementById(id); el.classList.remove('hidden'); el.classList.add('flex'); }
function closeModal(id) { const el=document.getElementById(id); el.classList.add('hidden'); el.classList.remove('flex'); }
function toggleMatField(type) {
  const useFile = ['PDF','Video','Dokumen','Lainnya'].includes(type);
  document.getElementById('matFileField').classList.toggle('hidden', !useFile);
  document.getElementById('matUrlField').classList.toggle('hidden', useFile);
  const inp  = document.getElementById('matFileInput');
  const hint = document.getElementById('matFileHint');
  if (type === 'Video') {
    inp.accept = '.mp4,.webm,.mkv,.mov';
    hint.textContent = '(mp4/webm/mkv/mov, maks 100MB)';
  } else {
    inp.accept = '.pdf,.doc,.docx';
    hint.textContent = '(maks 10MB)';
  }
}
function openGradeModal(subId, name) {
  document.getElementById('gradeStudentName').textContent = name;
  document.getElementById('gradeForm').action = '<?= base_url('teacher/submissions/grade/') ?>' + subId;
  openModal('gradeModal');
}

<?php if ($active_tab === 'diskusi'): ?>
const chatBox  = document.getElementById('chatBox');
const chatInput= document.getElementById('chatInput');
const CLASS_ID = '<?= esc($classId) ?>';
const MY_ID    = '<?= session()->get('user_id') ?>';
let lastId     = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
const CSRF_NAME  = document.querySelector('meta[name="csrf-name"]').content;
const CSRF_HASH  = document.querySelector('meta[name="csrf-token"]').content;

chatBox.scrollTop = chatBox.scrollHeight;

function sendMessage() {
  const text = chatInput.value.trim();
  if (!text) return;
  chatInput.value = '';
  fetch('<?= base_url('teacher/discussion/send') ?>', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'class_id=' + encodeURIComponent(CLASS_ID)
        + '&message='  + encodeURIComponent(text)
        + '&' + CSRF_NAME + '=' + encodeURIComponent(CSRF_HASH)
  }).then(r=>r.json()).then(data=>{ if(data.message) appendMsg(data.message, true); });
}

function getDateLabel(isoStr) {
  const d = new Date(isoStr);
  const today = new Date(); today.setHours(0,0,0,0);
  const yesterday = new Date(today); yesterday.setDate(today.getDate()-1);
  const msgDay = new Date(d); msgDay.setHours(0,0,0,0);
  if (msgDay.getTime()===today.getTime()) return 'Hari ini';
  if (msgDay.getTime()===yesterday.getTime()) return 'Kemarin';
  return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
}

function appendMsg(msg, isMe) {
  // date separator
  const label = getDateLabel(msg.sent_at);
  const existing = chatBox.querySelector(`[data-separator="${CSS.escape(label)}"]`);
  if (!existing) {
    const sep = document.createElement('div');
    sep.className = 'flex items-center gap-3 my-2';
    sep.dataset.separator = label;
    sep.innerHTML = `<div class="flex-1 h-px bg-slate-200"></div><span class="text-xs text-slate-400 font-medium px-2 py-1 bg-slate-100 rounded-full whitespace-nowrap">${label}</span><div class="flex-1 h-px bg-slate-200"></div>`;
    chatBox.appendChild(sep);
  }
  const div = document.createElement('div');
  div.className = 'flex ' + (isMe ? 'justify-end' : 'justify-start');
  div.dataset.msgid = msg.id;
  const time = new Date(msg.sent_at).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const badge  = msg.sender_role==='teacher' ? '<span class="text-xs bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded-md font-semibold">Guru</span>' : '';
  const sender = isMe ? '' : `<div class="flex items-center gap-2 mb-1.5"><div class="w-5 h-5 rounded-full bg-slate-400 flex items-center justify-center text-white text-xs font-bold">${msg.sender_name.charAt(0).toUpperCase()}</div><span class="text-xs font-semibold text-slate-600">${msg.sender_name}</span>${badge}</div>`;
  div.innerHTML = `<div class="max-w-[78%]">${sender}<div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm ${isMe?'bg-primary-600 text-white rounded-br-sm':'bg-slate-100 text-slate-800 rounded-bl-sm'}">${String(msg.message ?? '').replace(/\n/g,'<br>')}</div><p class="text-xs text-slate-400 mt-1 ${isMe?'text-right':''}">${time}</p></div>`;
  chatBox.appendChild(div);
  chatBox.scrollTop = chatBox.scrollHeight;
  if (parseInt(msg.id) > lastId) lastId = parseInt(msg.id);
}

setInterval(()=>{
  fetch(`<?= base_url('teacher/discussion/') ?>${CLASS_ID}/poll/${lastId}`)
    .then(r=>r.json()).then(data=>{
      (data.messages||[]).forEach(msg=>{
        if (!document.querySelector(`[data-msgid="${msg.id}"]`)) appendMsg(msg, msg.sender_id===MY_ID);
      });
    }).catch(()=>{});
}, 3000);
<?php endif; ?>
</script>
<?= $this->endSection() ?>