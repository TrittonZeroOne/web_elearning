<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="pt-4" style="height: calc(100vh - 7rem);">
<div class="h-full flex flex-col bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

  <!-- Header -->
  <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 bg-white flex-shrink-0">
    <a href="<?= base_url('student/chat') ?>" class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <?php if (!empty($teacher['avatar_url'])): ?>
      <img src="<?= esc($teacher['avatar_url']) ?>" class="w-9 h-9 rounded-full object-cover" alt="">
    <?php else: ?>
      <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700
                  flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
        <?= strtoupper(substr($teacher['full_name'], 0, 1)) ?>
      </div>
    <?php endif; ?>
    <div class="flex-1">
      <p class="text-sm font-semibold text-slate-800"><?= esc($teacher['full_name']) ?></p>
      <p class="text-xs font-medium text-primary-600">Guru</p>
    </div>
  </div>

  <!-- Pesan -->
  <div id="chatBox" class="flex-1 overflow-y-auto px-4 py-4 space-y-1"
       style="background: linear-gradient(135deg,#f0f9ff 0%,#f8fafc 100%)">
    <?php if (empty($messages)): ?>
      <div class="flex flex-col items-center justify-center h-full text-center">
        <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mb-3">
          <i class="fa-solid fa-comments text-primary-500 text-xl"></i>
        </div>
        <p class="text-sm font-medium text-slate-500">Mulai percakapan</p>
        <p class="text-xs text-slate-400 mt-1">Kirim pesan pertama ke <?= esc($teacher['full_name']) ?></p>
      </div>
    <?php else: ?>
      <?php $prevDate = ''; foreach ($messages as $msg):
        $isMe    = ($msg['sender_id'] === $my_id);
        $msgDate = date('Y-m-d', strtotime($msg['sent_at']));
        $today   = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
      ?>
        <?php if ($msgDate !== $prevDate): $prevDate = $msgDate; ?>
          <div class="flex items-center gap-3 py-2">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-xs text-slate-400 font-medium px-3 py-1 bg-white border border-slate-200 rounded-full">
              <?= $msgDate === $today ? 'Hari ini' : ($msgDate === $yesterday ? 'Kemarin' : date('d M Y', strtotime($msg['sent_at']))) ?>
            </span>
            <div class="flex-1 h-px bg-slate-200"></div>
          </div>
        <?php endif; ?>
        <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?> mb-0.5" data-msg-id="<?= $msg['id'] ?>">
          <div class="max-w-[78%]">
            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm
              <?= $isMe ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-white text-slate-800 border border-slate-100 rounded-bl-sm' ?>">
              <?= nl2br(esc((string)($msg['message'] ?? ''))) ?>
            </div>
            <p class="text-xs text-slate-400 mt-1 flex items-center gap-1 <?= $isMe ? 'justify-end' : '' ?>">
              <?= date('H:i', strtotime($msg['sent_at'])) ?>
              <?php if ($isMe): ?>
                <i class="fa-solid <?= $msg['is_read'] ? 'fa-check-double text-primary-400' : 'fa-check text-slate-300' ?> text-xs"></i>
              <?php endif; ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <div id="chatEnd"></div>
  </div>

  <!-- Input -->
  <div class="flex-shrink-0 px-4 py-3 bg-white border-t border-slate-100">
    <div class="flex items-end gap-2">
      <textarea id="msgInput" rows="1" placeholder="Tulis pesan..." maxlength="1000"
        class="flex-1 resize-none bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-sm
               focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400
               transition-all placeholder-slate-400 overflow-y-auto"
        style="min-height:42px;max-height:128px"
        onkeydown="handleKey(event)"></textarea>
      <button onclick="sendMessage()" id="sendBtn"
        class="w-10 h-10 bg-primary-600 hover:bg-primary-700 active:scale-95 rounded-xl flex items-center
               justify-center text-white transition-all shadow-sm flex-shrink-0">
        <i class="fa-solid fa-paper-plane text-sm"></i>
      </button>
    </div>
    <p class="text-xs text-slate-400 mt-1.5 pl-1">Enter kirim · Shift+Enter baris baru</p>
  </div>

</div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const RECEIVER_ID = '<?= esc($teacher['id']) ?>';
const MY_ID       = '<?= esc($my_id) ?>';
const CSRF_NAME   = document.querySelector('meta[name="csrf-name"]')?.content  || '<?= csrf_token() ?>';
const CSRF_HASH   = document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_hash() ?>';

let lastMsgId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;

const textarea = document.getElementById('msgInput');
textarea.addEventListener('input', () => {
  textarea.style.height = 'auto';
  textarea.style.height = Math.min(textarea.scrollHeight, 128) + 'px';
});

function scrollToBottom(smooth = false) {
  document.getElementById('chatEnd')?.scrollIntoView({ behavior: smooth ? 'smooth' : 'instant' });
}
function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}
function escHtml(t) {
  return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function getDateLabel(iso) {
  const d = new Date(iso), today = new Date(), yd = new Date();
  today.setHours(0,0,0,0); yd.setHours(0,0,0,0); yd.setDate(yd.getDate()-1);
  const md = new Date(d); md.setHours(0,0,0,0);
  if (md.getTime() === today.getTime()) return 'Hari ini';
  if (md.getTime() === yd.getTime())   return 'Kemarin';
  return d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
}
function appendBubble(msg, pending = false) {
  const isMe  = msg.sender_id === MY_ID;
  const label = getDateLabel(msg.sent_at);
  if (!document.querySelector(`[data-date-sep="${CSS.escape(label)}"]`)) {
    const sep = document.createElement('div');
    sep.className = 'flex items-center gap-3 py-2';
    sep.dataset.dateSep = label;
    sep.innerHTML = `<div class="flex-1 h-px bg-slate-200"></div>
      <span class="text-xs text-slate-400 font-medium px-3 py-1 bg-white border border-slate-200 rounded-full">${escHtml(label)}</span>
      <div class="flex-1 h-px bg-slate-200"></div>`;
    document.getElementById('chatEnd').before(sep);
  }
  const time     = new Date(msg.sent_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
  const readIcon = isMe
    ? (pending
        ? '<i class="fa-solid fa-circle-notch fa-spin text-white/40 text-xs"></i>'
        : `<i class="fa-solid ${msg.is_read ? 'fa-check-double text-primary-300' : 'fa-check text-white/60'} text-xs"></i>`)
    : '';
  const wrap = document.createElement('div');
  wrap.className = `flex ${isMe ? 'justify-end' : 'justify-start'} mb-0.5`;
  if (pending) wrap.dataset.pending = '1';
  if (msg.id)  wrap.dataset.msgId   = msg.id;
  wrap.innerHTML = `<div class="max-w-[78%]">
    <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm
      ${isMe ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-white text-slate-800 border border-slate-100 rounded-bl-sm'}">
      ${escHtml(msg.message).replace(/\n/g, '<br>')}
    </div>
    <p class="text-xs text-slate-400 mt-1 flex items-center gap-1 ${isMe ? 'justify-end' : ''}">
      ${time} ${readIcon}
    </p>
  </div>`;
  document.getElementById('chatEnd').before(wrap);
  scrollToBottom(true);
}
async function sendMessage() {
  const input = textarea.value.trim();
  if (!input) return;
  document.getElementById('sendBtn').disabled = true;
  textarea.value = ''; textarea.style.height = 'auto';
  appendBubble({ id: 0, sender_id: MY_ID, message: input, sent_at: new Date().toISOString(), is_read: false }, true);
  try {
    const fd = new FormData();
    fd.append('receiver_id', RECEIVER_ID);
    fd.append('message', input);
    fd.append(CSRF_NAME, CSRF_HASH);
    const res  = await fetch('<?= base_url('student/chat/send') ?>', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.message?.id) {
      document.querySelectorAll('[data-pending]').forEach(el => el.remove());
      appendBubble(data.message);
      lastMsgId = data.message.id;
    }
  } catch(e) { console.error(e); }
  finally { document.getElementById('sendBtn').disabled = false; textarea.focus(); }
}
async function poll() {
  try {
    const res  = await fetch(`<?= base_url('student/chat/poll/') ?>${RECEIVER_ID}/${lastMsgId}`);
    const data = await res.json();
    (data.messages || []).forEach(m => {
      if (m.id > lastMsgId && !document.querySelector(`[data-msg-id="${m.id}"]`)) {
        appendBubble(m); lastMsgId = m.id;
      }
    });
  } catch(e) {}
}
scrollToBottom();
setInterval(poll, 3000);
</script>
<?= $this->endSection() ?>