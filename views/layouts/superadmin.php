<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $e($title ?? 'Platform Super Admin') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <meta name="csrf-token" content="<?= $csrfToken() ?>">
</head>
<body style="background: #f1f5f9;">
  <nav class="navbar" style="border-bottom: 2px solid #0284c7;">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <a href="/superadmin" class="nav-brand" style="color: #0f172a;">
        🛡️ <span style="color: #0284c7;">QRPager</span> SaaS Platform Super Admin
      </a>
    </div>

    <ul class="nav-links">
      <li><a href="/superadmin#overview">📊 Overview</a></li>
      <li><a href="/superadmin#businesses">🏢 Businesses</a></li>
      <li><a href="/superadmin#plans">💎 Subscription Plans</a></li>
      <li><a href="/superadmin#payments">💰 Manual Payments</a></li>
      <li><a href="/superadmin#backups">💾 Backups & Recovery</a></li>
      <li><a href="/superadmin#audit">📜 Platform Audit Logs</a></li>
      <li><a href="/logout" style="color: var(--danger);">🚪 Sign Out</a></li>
    </ul>
  </nav>

  <main class="container">
    <?= $content ?>
  </main>

  <script src="/assets/js/app.js"></script>
</body>
</html>
