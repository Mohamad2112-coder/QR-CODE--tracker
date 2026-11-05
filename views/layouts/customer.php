<!DOCTYPE html>
<html lang="<?= $currentLocale ?>" dir="<?= $direction ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $e($title ?? 'Order Tracking') ?></title>
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/assets/css/app.css?v=20261105-status-animation">
  <style>
    :root {
      --primary: <?= $order['primary_color'] ?? '#0284c7' ?>;
      --accent: <?= $order['accent_color'] ?? '#f59e0b' ?>;
    }
  </style>
</head>
<body style="background-color: #f1f5f9;">
  <div class="customer-page">
    <!-- Language Switcher Bar -->
    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
      <a href="?lang=en" class="btn btn-outline btn-sm" style="<?= $currentLocale === 'en' ? 'background:var(--primary); color:#fff; border-color:var(--primary);' : '' ?>">English</a>
      <a href="?lang=ar" class="btn btn-outline btn-sm" style="<?= $currentLocale === 'ar' ? 'background:var(--primary); color:#fff; border-color:var(--primary);' : '' ?>">العربية</a>
      <a href="?lang=ku" class="btn btn-outline btn-sm" style="<?= $currentLocale === 'ku' ? 'background:var(--primary); color:#fff; border-color:var(--primary);' : '' ?>">کوردی</a>
    </div>

    <?= $content ?>
  </div>

  <script src="/assets/js/app.js?v=20261105-audio-playback-fix"></script>
</body>
</html>
