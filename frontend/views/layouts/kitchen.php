<!DOCTYPE html>
<html lang="<?= $currentLocale ?>" dir="<?= $direction ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $e($title ?? 'Kitchen Display System (KDS)') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body style="background: #0f172a; color: #f8fafc;">
  <header style="background: #1e293b; padding: 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155;">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #38bdf8;">🍳 <?= $e($tenant['name'] ?? 'Kitchen Display System') ?></h2>
      <span class="badge badge-online network-status-badge">● Online</span>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem;">
      <button onclick="SoundNotifier.playChime('new_order')" class="btn btn-outline btn-sm" style="color: #cbd5e1; border-color: #475569;">
        🔔 Test Audio Chime
      </button>
      <button onclick="document.documentElement.requestFullscreen ? document.documentElement.requestFullscreen() : null;" class="btn btn-primary btn-sm">
        ⛶ Fullscreen
      </button>
      <a href="/dashboard" class="btn btn-outline btn-sm" style="color: #cbd5e1; border-color: #475569;">
        Back to Dashboard
      </a>
    </div>
  </header>

  <main style="padding: 1rem;">
    <?= $content ?>
  </main>

  <script src="/assets/js/app.js"></script>
</body>
</html>
