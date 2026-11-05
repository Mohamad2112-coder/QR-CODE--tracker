<div class="card" style="padding: 2rem; border-radius: 16px;">
  <div style="text-align: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary);">Start Your 14-Day Free Trial</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Transform your customer orders into digital smartphone pagers</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= $e($error) ?></div>
  <?php endif; ?>

  <form action="/register" method="POST">
    <?= $csrfField() ?>

    <div class="form-group">
      <label class="form-label">Business / Restaurant Name *</label>
      <input type="text" name="business_name" class="form-control" placeholder="e.g. Blue Lagoon Cafe" required autofocus>
    </div>

    <div class="form-group">
      <label class="form-label">Your Name (Owner) *</label>
      <input type="text" name="owner_name" class="form-control" placeholder="e.g. Alex Johnson" required>
    </div>

    <div class="form-group">
      <label class="form-label">Work Email *</label>
      <input type="email" name="email" class="form-control" placeholder="owner@bluelagoon.com" required>
    </div>

    <div class="form-group">
      <label class="form-label">Password (min 8 chars) *</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="8" required>
    </div>

    <div class="grid grid-2">
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
      </div>

      <div class="form-group">
        <label class="form-label">Default Language</label>
        <select name="default_language" class="form-control">
          <option value="en">English (LTR)</option>
          <option value="ar">العربية - Arabic (RTL)</option>
          <option value="ku">کوردی - Kurdish (RTL)</option>
        </select>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
      Create Business Workspace & Start Trial
    </button>
  </form>

  <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 0.9rem;">
    <span>Already have an account?</span>
    <a href="/login" style="color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 0.25rem;">
      Sign In
    </a>
  </div>
</div>
