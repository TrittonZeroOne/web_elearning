<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SMA E-Learn</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        colors: { primary: { 500:'#a855f7',600:'#9333ea',700:'#7e22ce',800:'#6b21a8',900:'#581c87' } },
        fontFamily: { sans: ['Inter','system-ui','sans-serif'] }
      }}
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: radial-gradient(ellipse at top left, #3b0764, #1e1b4b 40%, #0f172a 80%); }
    .glass { background: rgba(255,255,255,0.06); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1); }
  </style>
</head>
<body class="min-h-screen font-sans antialiased flex items-center justify-center p-4">

  <!-- Background decorative blobs -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -left-40 w-80 h-80 bg-purple-700/30 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 -right-20 w-96 h-96 bg-violet-800/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 left-1/3 w-72 h-72 bg-indigo-900/30 rounded-full blur-3xl"></div>
  </div>

  <div class="w-full max-w-md relative z-10">

    <!-- Logo section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600 rounded-2xl mb-4 shadow-lg shadow-purple-900/50">
        <i class="fa-solid fa-graduation-cap text-white text-2xl"></i>
      </div>
      <h1 class="text-white text-2xl font-bold tracking-tight">SMA E-Learn</h1>
      <p class="text-purple-300/70 text-sm mt-1">Platform Pembelajaran Digital</p>
    </div>

    <!-- Login Card -->
    <div class=" glass rounded-2xl p-8 shadow-2xl">
      <h2 class="text-center text-white text-lg font-semibold mb-1">Masuk ke Akun</h2>
      <p class="text-center text-purple-300/60 text-sm mb-6">Gunakan Akun yang telah terdaftar</p>

      <!-- Flash alerts -->
      <?php if ($err = session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-2 bg-red-500/20 border border-red-400/30 text-red-300 px-4 py-3 rounded-xl text-sm mb-5">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i> <?= esc($err) ?>
        </div>
      <?php endif; ?>
      <?php if ($suc = session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-2 bg-green-500/20 border border-green-400/30 text-green-300 px-4 py-3 rounded-xl text-sm mb-5">
          <i class="fa-solid fa-circle-check flex-shrink-0"></i> <?= esc($suc) ?>
        </div>
      <?php endif; ?>

      <form action="<?= base_url('login') ?>" method="POST" class="space-y-4">
        <?= csrf_field() ?>

        <!-- Email -->
        <div>
          <label class="block text-xs font-medium text-purple-300 mb-1.5 uppercase tracking-wide">Email</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-purple-400/60 text-sm"></i>
            <input type="email" name="email" required autofocus
              value="<?= old('email') ?>"
              placeholder="nama@gmail.com"
              class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white text-sm placeholder-white/25
                     focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500/50 transition-all">
          </div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-xs font-medium text-purple-300 mb-1.5 uppercase tracking-wide">Password</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-purple-400/60 text-sm"></i>
            <input type="password" name="password" id="passwordInput" required
              placeholder="••••••••"
              class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-11 py-3 text-white text-sm placeholder-white/25
                     focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500/50 transition-all">
            <button type="button" onclick="togglePassword()" tabindex="-1"
              class="absolute right-3.5 top-3.5 text-purple-400/60 hover:text-purple-300 transition-colors">
              <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
            </button>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit"
          class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-xl text-sm
                 transition-all duration-150 flex items-center justify-center gap-2 shadow-lg shadow-purple-900/40 mt-2">
          <i class="fa-solid fa-right-to-bracket"></i> Masuk
        </button>
      </form>

    <p class="text-center text-white/20 text-xs mt-6">
      © <?= date('Y') ?> SMA E-Learn · Powered by Supabase
    </p>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('passwordInput');
      const icon  = document.getElementById('eyeIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash text-sm';
      } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye text-sm';
      }
    }
  </script>
</body>
</html>
