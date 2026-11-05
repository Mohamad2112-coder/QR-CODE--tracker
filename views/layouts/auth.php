<!DOCTYPE html>
<html lang="<?= $currentLocale ?>" dir="<?= $direction ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $e($title ?? 'Sign In - QR Order Pager SaaS') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem;">
  <div style="width: 100%; max-width: 440px;">
    <!-- Language Switcher Bar on Auth Screen -->
    <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: #fff;">
      <a href="/set-locale?lang=en" style="color:#fff; text-decoration:none; <?= $currentLocale === 'en' ? 'font-weight:bold; text-decoration:underline;' : '' ?>">English</a>
      <span>•</span>
      <a href="/set-locale?lang=ar" style="color:#fff; text-decoration:none; <?= $currentLocale === 'ar' ? 'font-weight:bold; text-decoration:underline;' : '' ?>">العربية</a>
      <span>•</span>
      <a href="/set-locale?lang=ku" style="color:#fff; text-decoration:none; <?= $currentLocale === 'ku' ? 'font-weight:bold; text-decoration:underline;' : '' ?>">کوردی</a>
    </div>

    <?= $content ?>
  </div>
  <script src="/assets/js/app.js"></script>
</body>
</html>
