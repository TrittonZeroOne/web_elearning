<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-6 max-w-2xl">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
      <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-purple-700 rounded-xl flex items-center justify-center">
        <i class="fa-solid fa-comments text-white text-sm"></i>
      </div>
      <div>
        <h2 class="font-semibold text-slate-800">Pesan Langsung</h2>
        <p class="text-xs text-slate-400">Chat privat dengan guru</p>
      </div>
    </div>

    <!-- Teacher list -->
    <?php if (empty($teachers)): ?>
      <div class="text-center py-16">
        <i class="fa-solid fa-chalkboard-user text-3xl text-slate-300 mb-3 block"></i>
        <p class="text-sm text-slate-400">Belum ada guru terdaftar.</p>
      </div>
    <?php else: ?>
      <ul class="divide-y divide-slate-50">
        <?php foreach ($teachers as $t):
          $last = $t['last_message'];
          $unread = (int)($t['unread'] ?? 0);
        ?>
          <li>
            <a href="<?= base_url('admin/chat/' . esc($t['id'])) ?>"
               class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors group">

              <!-- Avatar -->
              <div class="relative flex-shrink-0">
                <?php if (!empty($t['avatar_url'])): ?>
                  <img src="<?= esc($t['avatar_url']) ?>" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-sm" alt="">
                <?php else: ?>
                  <div class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    <?= strtoupper(substr($t['full_name'], 0, 1)) ?>
                  </div>
                <?php endif; ?>
                <?php if ($unread > 0): ?>
                  <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    <?= $unread > 9 ? '9+' : $unread ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-baseline justify-between gap-2">
                  <p class="text-sm font-semibold text-slate-700 truncate <?= $unread ? 'text-slate-900' : '' ?>">
                    <?= esc($t['full_name']) ?>
                  </p>
                  <?php if ($last): ?>
                    <span class="text-xs text-slate-400 flex-shrink-0">
                      <?= date('H:i', strtotime($last['sent_at'])) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <p class="text-xs mt-0.5 truncate <?= $unread ? 'text-slate-700 font-medium' : 'text-slate-400' ?>">
                  <?php if ($last): ?>
                    <?= $last['sender_id'] === session()->get('user_id') ? '<span class="text-slate-400">Anda: </span>' : '' ?>
                    <?= esc(mb_strimwidth($last['message'], 0, 55, '…')) ?>
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
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>