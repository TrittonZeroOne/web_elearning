<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dashboard') ?> — SMA E-Learn</title>
  <!-- CSRF meta untuk AJAX -->
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name"  content="<?= csrf_token() ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50:'#faf5ff', 100:'#f3e8ff', 200:'#e9d5ff',
              300:'#d8b4fe', 400:'#c084fc', 500:'#a855f7',
              600:'#9333ea', 700:'#7e22ce', 800:'#6b21a8', 900:'#581c87'
            }
          },
          fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .nav-link {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.625rem 0.75rem; border-radius: 0.75rem;
      font-size: 0.875rem; font-weight: 500;
      color: rgb(148 163 184); transition: all 0.15s ease;
      cursor: pointer; text-decoration: none;
    }
    .nav-link:hover { background: rgba(255,255,255,0.10); color: #fff; }
    .nav-link.active { background: rgba(255,255,255,0.15); color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .nav-section {
      display: block; padding: 1rem 0.75rem 0.25rem;
      font-size: 0.7rem; font-weight: 600;
      color: rgba(196,181,253,0.70); text-transform: uppercase; letter-spacing: 0.1em;
    }
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
  </style>
</head>
<body class="h-full bg-slate-50 font-sans antialiased">

<!-- Mobile Overlay -->
<div id="mobileOverlay" onclick="closeSidebar()"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden hidden transition-opacity"></div>

<div class="flex h-full min-h-screen">

  <!-- ══ SIDEBAR ══ -->
  <aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 via-purple-950 to-slate-900
           flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
      <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
        <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
      </div>
      <div>
        <p class="text-white font-bold text-sm leading-tight">SMA E-Learn</p>
        <p class="text-purple-400 text-xs mt-0.5">via Supabase</p>
      </div>
      <button onclick="closeSidebar()" class="ml-auto md:hidden text-white/40 hover:text-white p-1">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>

    <!-- User Profile -->
    <div class="px-4 py-3 mx-3 mt-3 bg-white/5 rounded-xl border border-white/10">
      <div class="flex items-center gap-3">
        <?php if (session()->get('avatar_url')): ?>
          <img src="<?= esc(session()->get('avatar_url')) ?>" alt=""
            class="w-9 h-9 rounded-full object-cover ring-2 ring-primary-400 flex-shrink-0">
        <?php else: ?>
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-purple-700
                      flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
            <?= strtoupper(substr(session()->get('full_name') ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <p class="text-white text-xs font-semibold truncate"><?= esc(session()->get('full_name')) ?></p>
          <p class="text-purple-400 text-xs capitalize"><?= esc(session()->get('role')) ?></p>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
      <?php $role = session()->get('role'); $uri = uri_string(); ?>

      <a href="<?= base_url(relativePath: 'dashboard') ?>" class="nav-link <?= ($uri === 'dashboard') ? 'active' : '' ?>">
        <i class="fa-solid fa-house-chimney w-4 text-center"></i>
        <span><?= $role === 'student' ? 'Beranda' : 'Dashboard' ?></span>
      </a>

      <?php if ($role === 'admin'): ?>
        <!-- ── ADMIN ── -->
        <p class="nav-section">Manajemen</p>
        <a href="<?= base_url('admin/users') ?>" class="nav-link <?= str_starts_with($uri, 'admin/users') ? 'active' : '' ?>">
          <i class="fa-solid fa-users w-4 text-center"></i> Pengguna
        </a>
        <a href="<?= base_url('admin/classes') ?>" class="nav-link <?= str_starts_with($uri, 'admin/classes') ? 'active' : '' ?>">
          <i class="fa-solid fa-school w-4 text-center"></i> Kelas
        </a>
        <a href="<?= base_url('admin/subjects') ?>" class="nav-link <?= str_starts_with($uri, 'admin/subjects') ? 'active' : '' ?>">
          <i class="fa-solid fa-book w-4 text-center"></i> Mata Pelajaran
        </a>
        <a href="<?= base_url('admin/announcements') ?>" class="nav-link <?= str_starts_with($uri, 'admin/announcements') ? 'active' : '' ?>">
          <i class="fa-solid fa-bullhorn w-4 text-center"></i> Pengumuman
        </a>
        <a href="<?= base_url('admin/statistics') ?>" class="nav-link <?= str_starts_with($uri, 'admin/statistics') ? 'active' : '' ?>">
          <i class="fa-solid fa-chart-bar w-4 text-center"></i> Statistik
        </a>
        <p class="nav-section">Komunikasi</p>
        <a href="<?= base_url('admin/chat') ?>" class="nav-link <?= str_starts_with($uri, 'admin/chat') ? 'active' : '' ?>">
          <i class="fa-solid fa-comments w-4 text-center"></i> Chat Guru
        </a>

      <?php elseif ($role === 'teacher'): ?>
        <!-- ── TEACHER ── -->
        <p class="nav-section">Pengajaran</p>
        <a href="<?= base_url('teacher/subjects') ?>" class="nav-link <?= str_starts_with($uri, 'teacher/subjects') ? 'active' : '' ?>">
          <i class="fa-solid fa-chalkboard-user w-4 text-center"></i> Mata Pelajaran
        </a>
        <p class="nav-section">Komunikasi</p>
        <!-- Guru bisa chat dengan Admin & Siswa -->
        <a href="<?= base_url('teacher/chat') ?>" class="nav-link <?= str_starts_with($uri, 'teacher/chat') ? 'active' : '' ?>">
          <i class="fa-solid fa-comments w-4 text-center"></i> Pesan
        </a>

      <?php elseif ($role === 'student'): ?>
        <!-- ── STUDENT ── -->
        <p class="nav-section">Belajar</p>
        <a href="<?= base_url('student/subjects') ?>" class="nav-link <?= str_starts_with($uri, 'student/subjects') ? 'active' : '' ?>">
          <i class="fa-solid fa-book-open w-4 text-center"></i> Mata Pelajaran
        </a>
        <p class="nav-section">Komunikasi</p>
        <!-- FIX: /student/chat bukan /teacher/chat -->
        <a href="<?= base_url('student/chat') ?>" class="nav-link <?= str_starts_with($uri, 'student/chat') ? 'active' : '' ?>">
          <i class="fa-solid fa-comments w-4 text-center"></i> Chat Guru
        </a>

      <?php endif; ?>
    </nav>

    <!-- Logout -->
    <div class="px-3 pb-4 border-t border-white/10 pt-3">
      <a href="<?= base_url('logout') ?>" class="nav-link hover:!bg-red-500/20 hover:!text-red-300 group">
        <i class="fa-solid fa-right-from-bracket w-4 text-center group-hover:text-red-300"></i>
        <span>Keluar</span>
      </a>
    </div>
  </aside>

  <!-- ══ MAIN CONTENT ══ -->
  <div class="flex-1 md:ml-64 flex flex-col min-h-screen">

    <!-- TOP BAR -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
      <div class="flex items-center gap-3 px-4 sm:px-6 h-14">
        <button onclick="openSidebar()"
          class="md:hidden flex items-center justify-center w-9 h-9 rounded-lg hover:bg-slate-100 transition-colors">
          <i class="fa-solid fa-bars text-slate-600"></i>
        </button>
        <div class="flex items-center gap-2 min-w-0">
          <h1 class="text-slate-800 font-semibold text-sm sm:text-base truncate"><?= esc($title ?? 'Dashboard') ?></h1>
        </div>
        <div class="ml-auto flex items-center gap-2">
          <span class="hidden sm:block text-xs text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
            <?= date('d M Y') ?>
          </span>
          <div class="md:hidden w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-purple-700
                      flex items-center justify-center text-white text-xs font-bold">
            <?= strtoupper(substr(session()->get('full_name') ?? 'U', 0, 1)) ?>
          </div>
        </div>
      </div>
    </header>

    <!-- FLASH MESSAGES -->
    <?php if ($flash = session()->getFlashdata('success')): ?>
      <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-2.5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check text-green-500 flex-shrink-0"></i>
        <span><?= esc($flash) ?></span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fa-solid fa-times text-xs"></i></button>
      </div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
      <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-2.5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm shadow-sm" role="alert">
        <i class="fa-solid fa-circle-exclamation text-red-500 flex-shrink-0"></i>
        <span><?= esc($flash) ?></span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fa-solid fa-times text-xs"></i></button>
      </div>
    <?php endif; ?>
    <?php if ($errors = session()->getFlashdata('errors')): ?>
      <div class="mx-4 sm:mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm shadow-sm">
        <p class="font-semibold mb-1 flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation text-red-500"></i>Terdapat kesalahan:
        </p>
        <ul class="list-disc list-inside space-y-0.5">
          <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- PAGE CONTENT -->
    <main class="flex-1 px-4 sm:px-6 pb-10">
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>

<script>
function openSidebar() {
  document.getElementById('sidebar').classList.remove('-translate-x-full');
  document.getElementById('mobileOverlay').classList.remove('hidden');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.add('-translate-x-full');
  document.getElementById('mobileOverlay').classList.add('hidden');
}
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>