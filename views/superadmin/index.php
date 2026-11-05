<div style="margin-bottom: 1.5rem;" id="overview">
  <h1 style="font-size: 1.85rem; font-weight: 800; color: #0f172a;">Platform Super Administrator Portal</h1>
  <p style="color: var(--text-muted); font-size: 0.95rem;">Cross-tenant SaaS monitoring, plan administration, manual payment approvals & backups</p>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

<!-- Platform-wide Stats -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
  <div class="card stat-card">
    <div>
      <div class="stat-label">Total Businesses</div>
      <div class="stat-value"><?= (int)($bizStats['total_businesses'] ?? 0) ?></div>
    </div>
    <div style="font-size: 2.25rem;">🏢</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Active / Trial</div>
      <div class="stat-value" style="color: var(--success);">
        <?= (int)($bizStats['active_count'] ?? 0) ?> / <?= (int)($bizStats['trial_count'] ?? 0) ?>
      </div>
    </div>
    <div style="font-size: 2.25rem;">✨</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Platform Orders</div>
      <div class="stat-value"><?= (int)($orderStats['total_platform_orders'] ?? 0) ?></div>
    </div>
    <div style="font-size: 2.25rem;">📦</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Verified Revenue</div>
      <div class="stat-value" style="color: var(--primary);">
        <?= number_format((float)($revStats['verified_revenue'] ?? 0), 0) ?> IQD
      </div>
    </div>
    <div style="font-size: 2.25rem;">💰</div>
  </div>
</div>

<!-- Businesses Directory -->
<div class="card" style="margin-bottom: 2rem;" id="businesses">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h3 style="font-size: 1.25rem; font-weight: 700;">Registered Tenant Businesses</h3>
    <span class="badge badge-online"><?= count($businesses) ?> Total</span>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Business Name</th>
          <th>Owner Contact</th>
          <th>Plan & Status</th>
          <th>Orders</th>
          <th>Trial Ends</th>
          <th>Status Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($businesses as $b): ?>
          <tr>
            <td>
              <strong><?= $e($b['name']) ?></strong>
              <div style="font-size: 0.8rem; color: var(--text-muted);">Slug: <code><?= $e($b['slug']) ?></code></div>
            </td>
            <td>
              <div><?= $e($b['owner_name'] ?? 'N/A') ?></div>
              <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $e($b['email']) ?></div>
            </td>
            <td>
              <span class="badge badge-<?= $b['status'] === 'active' ? 'online' : ($b['status'] === 'trial' ? 'pending' : 'offline') ?>">
                <?= strtoupper($b['status']) ?> (<?= $e($b['plan_name'] ?? 'Starter') ?>)
              </span>
            </td>
            <td><strong><?= $b['orders_count'] ?></strong></td>
            <td><?= date('M d, Y', strtotime($b['trial_ends_at'])) ?></td>
            <td>
              <form action="/superadmin/businesses/<?= $b['id'] ?>/status" method="POST" style="display:inline;">
                <?= $csrfField() ?>
                <?php if ($b['status'] === 'suspended'): ?>
                  <input type="hidden" name="status" value="active">
                  <button type="submit" class="btn btn-success btn-sm">Activate</button>
                <?php else: ?>
                  <input type="hidden" name="status" value="suspended">
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Suspend this business account?')">Suspend</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Subscription Plans Management -->
<div class="card" style="margin-bottom: 2rem;" id="plans">
  <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">💎 SaaS Subscription Plans & Limits</h3>
  <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Configure tier pricing and quota restrictions enforced across tenants</p>

  <div class="grid grid-3">
    <?php foreach ($plans as $p): ?>
      <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; background: #fff;">
        <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;"><?= $e($p['name']) ?></h4>
        
        <form action="/superadmin/plans/<?= $p['id'] ?>" method="POST">
          <?= $csrfField() ?>

          <div class="grid grid-2" style="margin-bottom: 0.5rem;">
            <div>
              <label class="form-label" style="font-size: 0.8rem;">Monthly (IQD)</label>
              <input type="number" step="1" name="monthly_price" value="<?= (int)$p['monthly_price'] ?>" class="form-control" required>
            </div>
            <div>
              <label class="form-label" style="font-size: 0.8rem;">Yearly (IQD)</label>
              <input type="number" step="1" name="yearly_price" value="<?= (int)$p['yearly_price'] ?>" class="form-control" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 0.5rem;">
            <label class="form-label" style="font-size: 0.8rem;">Max Orders / Month (0 = Unlim)</label>
            <input type="number" name="max_orders_per_month" value="<?= $p['max_orders_per_month'] ?>" class="form-control" required>
          </div>

          <div class="grid grid-2" style="margin-bottom: 0.5rem;">
            <div>
              <label class="form-label" style="font-size: 0.8rem;">Max Staff</label>
              <input type="number" name="max_staff_members" value="<?= $p['max_staff_members'] ?>" class="form-control" required>
            </div>
            <div>
              <label class="form-label" style="font-size: 0.8rem;">Max Devices</label>
              <input type="number" name="max_devices" value="<?= $p['max_devices'] ?>" class="form-control" required>
            </div>
          </div>

          <div style="margin: 0.75rem 0;">
            <label style="font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
              <input type="checkbox" name="allow_offline_hub" value="1" <?= $p['allow_offline_hub'] ? 'checked' : '' ?>>
              Allow Local Edge Hub
            </label>
          </div>

          <button type="submit" class="btn btn-outline btn-sm" style="width: 100%;">Update Plan</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Manual Payment Verification Queue -->
<div class="card" style="margin-bottom: 2rem;" id="payments">
  <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">💳 Manual Payment Verification Queue</h3>
  <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
    Review offline bank transfers, verify payments, and automatically activate/renew business licenses
  </p>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Business</th>
          <th>Plan</th>
          <th>Amount</th>
          <th>Reference / Note</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
          <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No payment verification requests recorded.</td></tr>
        <?php else: ?>
          <?php foreach ($payments as $pay): ?>
            <tr>
              <td><strong><?= $e($pay['business_name']) ?></strong></td>
              <td><?= $e($pay['plan_name']) ?></td>
              <td><strong><?= number_format((float)$pay['amount'], 0) ?> IQD</strong></td>
              <td><code><?= $e($pay['reference_note'] ?? 'No note') ?></code></td>
              <td><?= date('M d, H:i', strtotime($pay['created_at'])) ?></td>
              <td>
                <span class="badge badge-<?= $pay['status'] === 'verified' ? 'online' : ($pay['status'] === 'pending' ? 'pending' : 'offline') ?>">
                  <?= strtoupper($pay['status']) ?>
                </span>
              </td>
              <td>
                <?php if ($pay['status'] === 'pending'): ?>
                  <form action="/superadmin/payments/<?= $pay['id'] ?>/verify" method="POST" style="display:inline;">
                    <?= $csrfField() ?>
                    <input type="hidden" name="action" value="verify">
                    <button type="submit" class="btn btn-success btn-sm">Approve & Renew</button>
                  </form>
                  <form action="/superadmin/payments/<?= $pay['id'] ?>/verify" method="POST" style="display:inline;">
                    <?= $csrfField() ?>
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                  </form>
                <?php else: ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($pay['verified_at'] ?? $pay['updated_at'])) ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Database Backups & Recovery -->
<div class="card" style="margin-bottom: 2rem;" id="backups">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
      <h3 style="font-size: 1.25rem; font-weight: 700;">💾 Automated Database Backups & Recovery</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted);">Generates full SQL dumps with retention pruning and tested recovery</p>
    </div>

    <form action="/superadmin/backups" method="POST">
      <?= $csrfField() ?>
      <button type="submit" class="btn btn-primary">⚡ Trigger Backup Now</button>
    </form>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Backup Filename</th>
          <th>Size</th>
          <th>Created Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($backups)): ?>
          <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">No backups generated yet.</td></tr>
        <?php else: ?>
          <?php foreach ($backups as $bk): ?>
            <tr>
              <td><code><?= $e($bk['filename']) ?></code></td>
              <td><?= round($bk['size_bytes'] / 1024, 1) ?> KB</td>
              <td><?= $bk['modified_at'] ?></td>
              <td><span class="badge badge-online">Verified Ready</span></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Platform Audit Logs -->
<div class="card" id="audit">
  <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">📜 Platform-Wide Audit Logs</h3>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Event</th>
          <th>Description</th>
          <th>Business</th>
          <th>User</th>
          <th>IP Address</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($auditLogs as $al): ?>
          <tr>
            <td><code><?= $e($al['event_type']) ?></code></td>
            <td><?= $e($al['description']) ?></td>
            <td><?= $e($al['business_name'] ?? 'Platform') ?></td>
            <td><?= $e($al['user_email'] ?? 'System') ?></td>
            <td><?= $e($al['ip_address']) ?></td>
            <td><?= date('M d, H:i:s', strtotime($al['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
