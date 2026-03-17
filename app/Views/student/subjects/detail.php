<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $sid = $subject['id']; $classId = $subject['class_id']; ?>

<!-- CSRF meta untuk JS -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-name"  content="<?= csrf_token() ?>">

<div class="pt-4 space-y-4">

  <!-- Subject Header -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
    <a href="<?= base_url('student/subjects') ?>" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 flex-shrink-0 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="flex-1 min-w-0">
      <h2 class="font-bold text-slate-800 text-base"><?= esc($subject['name']) ?></h2>
      <p class="text-xs text-slate-500"><?= esc($subject['teacher_name'] ?? '') ?> · <?= esc($subject['schedule_day']) ?>, <?= esc($subject['schedule_time']) ?></p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex border-b border-slate-200 overflow-x-auto">
      <?php
      $tabs = [
        'materi'  => ['Materi',  'fa-file-lines',     base_url('student/subjects/').$sid.'/materi'],
        'tugas'   => ['Tugas',   'fa-clipboard-list', base_url('student/subjects/').$sid.'/tugas'],
        'absensi' => ['Absensi', 'fa-calendar-check', base_url('student/subjects/').$sid.'/absensi'],
        'diskusi' => ['Diskusi', 'fa-comments',       base_url('student/subjects/').$sid.'/diskusi'],      ];
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
              <?php if ($m['content_url']): ?>
                <a href="<?= esc($m['content_url']) ?>" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg flex-shrink-0">
                  <i class="fa-solid fa-external-link-alt text-sm"></i>
                </a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($active_tab === 'tugas'): ?>
    <!-- ══ TUGAS ══ -->
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

      <?php if (empty($assignments)): ?>
        <div class="text-center py-14">
          <i class="fa-solid fa-clipboard-list text-5xl text-slate-300 mb-3 block"></i>
          <p class="text-slate-500">Belum ada tugas</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($assignments as $a):
            $sub       = $a['my_submission'] ?? null;
            $isOverdue = $a['deadline'] && strtotime($a['deadline']) < time();
            $submitted = !empty($sub);
            // Warna status
            if ($submitted)       { $iconBg = 'bg-green-100 text-green-600'; $icon = 'fa-circle-check'; }
            elseif ($isOverdue)   { $iconBg = 'bg-red-100 text-red-500';     $icon = 'fa-clipboard-list'; }
            else                  { $iconBg = 'bg-orange-100 text-orange-600'; $icon = 'fa-clipboard-list'; }
          ?>
            <details class="group border border-slate-200 rounded-2xl overflow-hidden">
              <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-slate-50/70 list-none select-none">
                <div class="w-10 h-10 rounded-xl <?= $iconBg ?> flex items-center justify-center flex-shrink-0">
                  <i class="fa-solid <?= $icon ?> text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-slate-700 text-sm"><?= esc($a['title']) ?></p>
                  <div class="flex flex-wrap items-center gap-2 mt-1">
                    <?php if ($a['deadline']): ?>
                      <span class="text-xs <?= $isOverdue ? 'text-red-500 font-medium' : 'text-slate-400' ?>">
                        <i class="fa-regular fa-clock mr-1"></i><?= date('d M Y', strtotime($a['deadline'])) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($submitted): ?>
                      <span class="text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-check mr-0.5"></i>Terkumpul
                      </span>
                      <?php if ($sub['grade'] !== null): ?>
                        <span class="text-xs bg-primary-100 text-primary-700 font-bold px-2 py-0.5 rounded-full">
                          Nilai: <?= $sub['grade'] ?>
                        </span>
                      <?php endif; ?>
                    <?php elseif ($isOverdue): ?>
                      <span class="text-xs bg-red-100 text-red-600 font-semibold px-2 py-0.5 rounded-full">Terlambat</span>
                    <?php else: ?>
                      <span class="text-xs bg-orange-100 text-orange-600 font-semibold px-2 py-0.5 rounded-full">Belum dikumpulkan</span>
                    <?php endif; ?>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-open:rotate-180 transition-transform flex-shrink-0"></i>
              </summary>

              <!-- Konten expand -->
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

                <?php if ($submitted && !empty($sub['feedback'])): ?>
                  <div class="bg-white rounded-xl p-3 border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 mb-1"><i class="fa-solid fa-comment-dots mr-1"></i>Feedback Guru:</p>
                    <p class="text-xs text-slate-600"><?= esc($sub['feedback']) ?></p>
                  </div>
                <?php endif; ?>

                <?php if ($submitted && !empty($sub['file_url'])): ?>
                  <a href="<?= esc($sub['file_url']) ?>" target="_blank"
                    class="inline-flex items-center gap-2 text-xs text-blue-600 hover:underline">
                    <i class="fa-solid fa-file-arrow-down"></i> Lihat file yang dikumpulkan
                  </a>
                <?php endif; ?>

                <?php if (!$submitted && !$isOverdue): ?>
                  <form action="<?= base_url('student/assignments/submit') ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 pt-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="subject_id"    value="<?= $sid ?>">
                    <input type="file" name="file" required
                      class="flex-1 text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary-100 file:text-primary-700">
                    <button type="submit"
                      class="bg-primary-600 hover:bg-primary-700 text-white text-xs px-4 py-2 rounded-xl font-semibold transition-colors flex-shrink-0">
                      <i class="fa-solid fa-upload mr-1"></i>Kumpulkan
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($active_tab === 'absensi'): ?>
    <!-- ══ ABSENSI ══ -->
      <?php
      $total = $total ?? 0;
      $hadir = $hadir ?? 0;
      $persen= $persen ?? 0;
      $pctColor = $persen >= 75 ? 'bg-green-500' : ($persen >= 50 ? 'bg-orange-500' : 'bg-red-500');
      ?>
      <div class="mb-5 bg-white border border-slate-100 rounded-2xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-semibold text-slate-700">Kehadiran Keseluruhan</p>
          <span class="text-lg font-bold <?= $persen >= 75 ? 'text-green-600' : ($persen >= 50 ? 'text-orange-500' : 'text-red-500') ?>"><?= $persen ?>%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2.5">
          <div class="<?= $pctColor ?> h-2.5 rounded-full transition-all" style="width:<?= $persen ?>%"></div>
        </div>
        <div class="flex gap-4 mt-3">
          <?php
          $statDisplay = ['Hadir'=>[$hadir??0,'text-green-600'],'Sakit'=>[$sakit??0,'text-blue-600'],'Izin'=>[$izin??0,'text-orange-500'],'Alfa'=>[$alfa??0,'text-red-500']];
          foreach ($statDisplay as $st => [$cnt, $cl]):
          ?>
            <div class="text-center">
              <p class="text-base font-bold <?= $cl ?>"><?= $cnt ?></p>
              <p class="text-xs text-slate-400"><?= $st ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (empty($history)): ?>
        <p class="text-center py-10 text-slate-400">Belum ada data absensi.</p>
      <?php else: ?>
        <div class="space-y-2">
          <?php
          $statusBadge = ['Hadir'=>'bg-green-100 text-green-700','Sakit'=>'bg-blue-100 text-blue-700','Izin'=>'bg-orange-100 text-orange-700','Alfa'=>'bg-red-100 text-red-600'];
          foreach ($history as $r):
            $bc = $statusBadge[$r['status']] ?? 'bg-slate-100 text-slate-600';
          ?>
            <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl">
              <i class="fa-regular fa-calendar text-slate-400 text-sm flex-shrink-0"></i>
              <span class="flex-1 text-sm text-slate-700"><?= date('d M Y', strtotime($r['date'])) ?></span>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $bc ?>"><?= $r['status'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

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
          <button onclick="sendMessage()" class="bg-primary-600 hover:bg-primary-700 text-white w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0">
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
<?php if ($active_tab === 'diskusi'): ?>
<script>
const chatBox  = document.getElementById('chatBox');
const chatInput= document.getElementById('chatInput');
const CLASS_ID = '<?= esc($classId) ?>';
const MY_ID    = '<?= session()->get('user_id') ?>';
let lastId     = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
const CSRF_NAME = document.querySelector('meta[name="csrf-name"]').content;
const CSRF_HASH = document.querySelector('meta[name="csrf-token"]').content;

chatBox.scrollTop = chatBox.scrollHeight;

function sendMessage() {
  const text = chatInput.value.trim();
  if (!text) return;
  chatInput.value = '';
  fetch('<?= base_url('student/discussion/send') ?>', {
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
  const time   = new Date(msg.sent_at).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const badge  = msg.sender_role==='teacher' ? '<span class="text-xs bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded-md font-semibold">Guru</span>' : '';
  const sender = isMe ? '' : `<div class="flex items-center gap-2 mb-1.5"><div class="w-5 h-5 rounded-full bg-slate-400 flex items-center justify-center text-white text-xs font-bold">${msg.sender_name.charAt(0).toUpperCase()}</div><span class="text-xs font-semibold text-slate-600">${msg.sender_name}</span>${badge}</div>`;
  div.innerHTML = `<div class="max-w-[78%]">${sender}<div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm ${isMe?'bg-primary-600 text-white rounded-br-sm':'bg-slate-100 text-slate-800 rounded-bl-sm'}">${String(msg.message ?? '').replace(/\n/g,'<br>')}</div><p class="text-xs text-slate-400 mt-1 ${isMe?'text-right':''}">${time}</p></div>`;
  chatBox.appendChild(div);
  chatBox.scrollTop = chatBox.scrollHeight;
  if (parseInt(msg.id) > lastId) lastId = parseInt(msg.id);
}

setInterval(()=>{
  fetch(`<?= base_url('student/discussion/') ?>${CLASS_ID}/poll/${lastId}`)
    .then(r=>r.json()).then(data=>{
      (data.messages||[]).forEach(msg=>{
        if(!document.querySelector(`[data-msgid="${msg.id}"]`)) appendMsg(msg, msg.sender_id===MY_ID);
      });
    }).catch(()=>{});
}, 3000);
</script>
<?php endif; ?>
<?= $this->endSection() ?>