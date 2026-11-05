<div class="card" style="padding: 2rem; border-radius: 16px;">
  <div style="text-align: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--primary);">QR Order Pager</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;">Multi-Tenant Digital Order Notification SaaS</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= $e($error) ?></div>
  <?php endif; ?>

  <form action="/login" method="POST">
    <?= $csrfField() ?>

    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" placeholder="name@business.com" required autofocus>
    </div>

    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
      <?= $__('login') ?>
    </button>
  </form>

  <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 0.9rem;">
    <span>New restaurant or café?</span>
    <a href="/register" style="color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 0.25rem;">
      Start 14-Day Free Trial
    </a>
  </div>

  <div style="margin-top: 1rem; background: #f8fafc; padding: 0.75rem; border-radius: 8px; font-size: 0.8rem; color: #475569;">
    <strong>Demo Credentials:</strong><br>
    • Tenant Owner: <code>owner@sunrisecafe.com</code> / <code>Password123!</code><br>
    • Cashier: <code>cashier@sunrisecafe.com</code> / <code>Password123!</code><br>
    • Kitchen KDS: <code>kitchen@sunrisecafe.com</code> / <code>Password123!</code><br>
    • Super Admin: <code>admin@qrpager.io</code> / <code>Password123!</code>
  </div>
</div>
