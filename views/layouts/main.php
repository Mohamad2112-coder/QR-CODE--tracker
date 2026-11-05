<!DOCTYPE html>
<html lang="<?= $currentLocale ?>" dir="<?= $direction ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $e($title ?? 'QR Order Pager SaaS') ?></title>
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/assets/css/app.css">
  <meta name="csrf-token" content="<?= $csrfToken() ?>">
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <a href="/dashboard" class="nav-brand">
        <span>📱</span> <?= $e($auth['business_name'] ?? 'QR Order Pager') ?>
      </a>
      <span class="badge badge-online network-status-badge">● Online</span>
    </div>

    <ul class="nav-links">
      <li><a href="/dashboard">📊 <?= $__('dashboard') ?></a></li>
      <li><a href="/orders/create">🛒 <?= $__('pos') ?></a></li>
      <li><a href="/kitchen">🍳 <?= $__('kitchen') ?></a></li>
      <li><a href="/orders">📋 <?= $__('orders') ?></a></li>
      <li><a href="/products">🍔 <?= $__('products') ?></a></li>
      <li><a href="/reports">📈 <?= $__('reports') ?></a></li>
      <li><a href="/settings">⚙️ <?= $__('settings') ?></a></li>

      <!-- Language Switcher -->
      <li style="display: flex; align-items: center; gap: 0.25rem;">
        <a href="/set-locale?lang=en" style="<?= $currentLocale === 'en' ? 'font-weight:bold; color:var(--primary);' : '' ?>">EN</a>
        <span>|</span>
        <a href="/set-locale?lang=ar" style="<?= $currentLocale === 'ar' ? 'font-weight:bold; color:var(--primary);' : '' ?>">عربي</a>
        <span>|</span>
        <a href="/set-locale?lang=ku" style="<?= $currentLocale === 'ku' ? 'font-weight:bold; color:var(--primary);' : '' ?>">کوردی</a>
      </li>

      <!-- User Profile & Logout -->
      <li>
        <a href="/logout" style="color: var(--danger);">
          🚪 <?= $__('logout') ?>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main Container -->
  <main class="container">
    <?= $content ?>
  </main>

  <script src="/assets/js/app.js"></script>
</body>
</html>
