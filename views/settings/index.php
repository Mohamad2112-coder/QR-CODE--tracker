<div style="margin-bottom: 1.5rem;">
  <h1 style="font-size: 1.75rem; font-weight: 800;"><?= $__('settings') ?> & Workspace Configuration</h1>
  <p style="color: var(--text-muted); font-size: 0.95rem;">Configure business identity, branding, staff accounts, and Local Hub</p>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

<div class="grid grid-2" style="align-items: start;">
  <!-- General Business Settings -->
  <div class="card">
    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">🏢 Business Information & Operations</h3>
    
    <form action="/settings/general" method="POST">
      <?= $csrfField() ?>

      <div class="form-group">
        <label class="form-label">Business Name</label>
        <input type="text" name="name" value="<?= $e($business['name']) ?>" class="form-control" required>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" value="<?= $e($business['phone'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">Default Workspace Language</label>
          <select name="default_language" class="form-control">
            <option value="en" <?= $business['default_language'] === 'en' ? 'selected' : '' ?>>English (LTR)</option>
            <option value="ar" <?= $business['default_language'] === 'ar' ? 'selected' : '' ?>>العربية - Arabic (RTL)</option>
            <option value="ku" <?= $business['default_language'] === 'ku' ? 'selected' : '' ?>>کوردی - Kurdish (RTL)</option>
          </select>
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Currency</label>
          <input type="text" value="IQD" class="form-control" readonly>
        </div>

        <div class="form-group">
          <label class="form-label">Order Number Prefix</label>
          <input type="text" name="order_number_prefix" value="<?= $e($business['order_number_prefix'] ?? 'ORD-') ?>" class="form-control" required>
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Est. Prep Time (Minutes)</label>
          <input type="number" name="estimated_prep_time_minutes" value="<?= (int)($business['estimated_prep_time_minutes'] ?? 15) ?>" class="form-control" min="1" required>
        </div>

        <div class="form-group">
          <label class="form-label">QR Token Expiration Grace Period (Minutes)</label>
          <input type="number" name="qr_expiration_grace_minutes" value="<?= (int)($business['qr_expiration_grace_minutes'] ?? 60) ?>" class="form-control" min="5" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Ready Push Message (English)</label>
        <input type="text" name="notification_ready_message_en" value="<?= $e($business['notification_ready_message_en'] ?? '') ?>" class="form-control">
      </div>

      <div class="form-group">
        <label class="form-label">Ready Push Message (Arabic)</label>
        <input type="text" name="notification_ready_message_ar" value="<?= $e($business['notification_ready_message_ar'] ?? '') ?>" class="form-control" dir="rtl">
      </div>

      <div class="form-group">
        <label class="form-label">Ready Push Message (Kurdish)</label>
        <input type="text" name="notification_ready_message_ku" value="<?= $e($business['notification_ready_message_ku'] ?? '') ?>" class="form-control" dir="rtl">
      </div>

      <button type="submit" class="btn btn-primary">Save Business Settings</button>
    </form>
  </div>

  <!-- Branding & Colors -->
  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="card">
      <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">🎨 Branding & Customer Colors</h3>

      <form action="/settings/branding" method="POST">
        <?= $csrfField() ?>

        <div class="grid grid-2">
          <div class="form-group">
            <label class="form-label">Primary Brand Color</label>
            <input type="color" name="primary_color" value="<?= $e($business['primary_color'] ?? '#0284c7') ?>" class="form-control" style="height: 44px; padding: 0.2rem;">
          </div>

          <div class="form-group">
            <label class="form-label">Accent Highlight Color</label>
            <input type="color" name="accent_color" value="<?= $e($business['accent_color'] ?? '#f59e0b') ?>" class="form-control" style="height: 44px; padding: 0.2rem;">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Tagline (Shown on Customer Pager)</label>
          <input type="text" name="tagline" value="<?= $e($business['tagline'] ?? '') ?>" class="form-control" placeholder="e.g. Fresh Artisan Coffee & Bites">
        </div>

        <div class="form-group">
          <label class="form-label">Logo URL</label>
          <input type="text" name="logo_url" value="<?= $e($business['logo_url'] ?? '') ?>" class="form-control" placeholder="https://example.com/logo.png">
        </div>

        <button type="submit" class="btn btn-primary">Save Branding</button>
      </form>
    </div>

    <!-- Local Business Hub Configuration -->
    <div class="card">
      <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">🖥️ Local Business Hub (Edge LAN)</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
        Keep cashier and kitchen working during internet outages. Devices sync automatically when internet returns.
      </p>

      <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">HUB SECRET KEY:</div>
        <code style="font-size: 0.95rem; font-weight: 700; color: var(--primary);"><?= $e($business['hub_secret_key']) ?></code>
      </div>

      <form action="/settings/devices" method="POST" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <?= $csrfField() ?>
        <input type="text" name="device_name" class="form-control" placeholder="Device Name (e.g. Cashier PC 1)" style="flex: 1;" required>
        <select name="device_type" class="form-control" style="width: 140px;">
          <option value="cashier_pos">Cashier POS</option>
          <option value="kitchen_kds">Kitchen KDS</option>
          <option value="hub_server">Hub Edge Server</option>
        </select>
        <button type="submit" class="btn btn-outline">+ Register Device</button>
      </form>
    </div>
  </div>
</div>

<!-- Staff Management Table -->
<div class="card" style="margin-top: 1.5rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
      <h3 style="font-size: 1.2rem; font-weight: 700;">👥 Staff Members & Roles</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted);">Manage employee access across Cashier, Kitchen, and Management</p>
    </div>

    <button onclick="document.getElementById('staffModal').classList.add('active')" class="btn btn-primary btn-sm">
      + Add Staff Member
    </button>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $u): ?>
          <tr>
            <td><strong><?= $e($u['name']) ?></strong></td>
            <td><?= $e($u['email']) ?></td>
            <td><span class="badge badge-preparing"><?= $e($u['role_name']) ?></span></td>
            <td>
              <?= $u['is_active'] ? '<span class="badge badge-online">Active</span>' : '<span class="badge badge-offline">Inactive</span>' ?>
            </td>
            <td>
              <form action="/settings/staff/<?= $u['id'] ?>/toggle" method="POST" style="display: inline;">
                <?= $csrfField() ?>
                <button type="submit" class="btn btn-outline btn-sm">
                  <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Staff Modal -->
<div id="staffModal" class="modal-overlay">
  <div class="modal-box" style="text-align: left;">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Create Staff Account</h3>
    
    <form action="/settings/staff" method="POST">
      <?= $csrfField() ?>

      <div class="form-group">
        <label class="form-label">Employee Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. David Cashier" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" placeholder="david@business.com" required>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
        </div>

        <div class="form-group">
          <label class="form-label">Assigned Role *</label>
          <select name="role_id" class="form-control" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>"><?= ucfirst($r['name']) ?> - <?= $e($r['description']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" onclick="document.getElementById('staffModal').classList.remove('active')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Staff Member</button>
      </div>
    </form>
  </div>
</div>
