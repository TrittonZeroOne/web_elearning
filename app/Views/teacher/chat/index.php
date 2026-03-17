<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 max-w-2xl">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
      <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-primary-600 rounded-xl flex items-center justify-center">
        <i class="fa-solid fa-comments text-white text-sm"></i>
      </div>
      <div>
        <h2 class="font-semibold text-slate-800">Pesan</h2>
        <p class="text-xs text-slate-400">Chat dengan admin &amp; siswa yang kamu ajar</p>
      </div>
    </div>

    <?php if (empty($contacts)): ?>
      <div class="text-center py-16">
        <i class="fa-solid fa-comments text-3xl text-slate-300 mb-3 block"></i>
        <p class="text-sm text-slate-400">Belum ada kontak.</p>
      </div>
    <?php else: ?>
      <?php
      $admins   = array_values(array_filter($contacts, fn($c) => ($c['role'] ?? '') === 'admin'));
      $students = array_values(array_filter($contacts, fn($c) => ($c['role'] ?? '') === 'student'));
      $myId     = session()->get('user_id');
      ?>

      <?php foreach ([['Administrator', $admins, 'bg-red-100 text-red-600'], ['Siswa', $students, 'bg-primary-100 text-primary-600']] as [$label, $group, $badgeClass]):
        if (empty($group)) continue; ?>
        <div class="px-5 py-2 bg-slate-50/80 border-b border-slate-100">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide"><?= $label ?></p>
        </div>
        <ul class="divide-y divide-slate-50">
          <?php foreach ($group as $c):
            $last   = $c['last_message'];
            $unread = (int)($c['unread'] ?? 0);
          ?>
            <li>
              <a href="<?= base_url('teacher/chat/' . esc($c['id'])) ?>"
                 class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors group">
                <div class="relative flex-shrink-0">
                  <?php if (!empty($c['avatar_url'])): ?>
                    <img src="<?= esc($c['avatar_url']) ?>" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-sm" alt="">
                  <?php else: ?>
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                      <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($unread > 0): ?>
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                      <?= $unread > 9 ? '9+' : $unread ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-baseline justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-700 truncate">
                      <?= esc($c['full_name']) ?>
                      <span class="ml-1 text-xs font-normal <?= $badgeClass ?> px-1.5 py-0.5 rounded-md"><?= $label === 'Siswa' ? ($c['class_name'] ?? 'Siswa') : 'Admin' ?></span>
                    </p>
                    <?php if ($last): ?>
                      <span class="text-xs text-slate-400 flex-shrink-0"><?= date('H:i', strtotime($last['sent_at'])) ?></span>
                    <?php endif; ?>
                  </div>
                  <p class="text-xs truncate mt-0.5 <?= $unread ? 'text-slate-700 font-medium' : 'text-slate-400' ?>">
                    <?php if ($last): ?>
                      <?= $last['sender_id'] === $myId ? '<span class="text-slate-400">Anda: </span>' : '' ?>
                      <?= esc(mb_strimwidth((string)($last['message'] ?? ''), 0, 55, '…')) ?>
                    <?php else: ?>
                      <span class="italic">Belum ada pesan</span>
                    <?php endif; ?>
                  </p>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-primary-400 transition-colors flex-shrink-0"></i>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>