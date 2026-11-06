<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.75rem; font-weight: 800;"><?= $e($tenant['name']) ?></h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
      Operational Overview & Real-Time Metrics • <?= date('l, F j, Y') ?>
    </p>
  </div>

  <div style="display: flex; gap: 0.75rem;">
    <a href="/orders/create" class="btn btn-primary">
      🛒 <?= $__('pos') ?> (New Order)
    </a>
    <a href="/kitchen" class="btn btn-accent">
      🍳 <?= $__('kitchen') ?>
    </a>
  </div>
</div>

<!-- Subscription Banner if on Trial -->
<?php if (!empty($subscription) && $subscription['status'] === 'trial'): ?>
  <div class="alert alert-success" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
    <div>
      <strong>✨ 14-Day Free Trial Active (<?= $e($subscription['plan_name']) ?>):</strong>
      Your trial is active until <?= date('M d, Y', strtotime($subscription['trial_ends_at'])) ?>.
    </div>
    <a href="/settings#billing" class="btn btn-sm btn-outline" style="background:#fff;">Manage Plan</a>
  </div>
<?php endif; ?>

<!-- Real Database Metric Cards -->
<div class="grid grid-4" style="margin-bottom: 1.5rem;">
  <div class="card stat-card">
    <div>
      <div class="stat-label">Orders Today</div>
      <div class="stat-value"><?= (int)($stats['total_today'] ?? 0) ?></div>
    </div>
    <div style="font-size: 2.25rem;">📦</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-value"><?= number_format((float)($stats['today_revenue'] ?? 0), 0) ?> IQD</div>
    </div>
    <div style="font-size: 2.25rem;">💵</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Avg Prep Time</div>
      <div class="stat-value"><?= $avgPrepTime > 0 ? "{$avgPrepTime}m" : '--' ?></div>
    </div>
    <div style="font-size: 2.25rem;">⏱️</div>
  </div>

  <div class="card stat-card" style="<?= $waitingTooLongCount > 0 ? 'border-color:var(--danger);' : '' ?>">
    <div>
      <div class="stat-label">Waiting > 15m</div>
      <div class="stat-value" style="<?= $waitingTooLongCount > 0 ? 'color:var(--danger);' : '' ?>">
        <?= $waitingTooLongCount ?>
      </div>
    </div>
    <div style="font-size: 2.25rem;">⚠️</div>
  </div>
</div>

<!-- Status Pipeline Badges -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
  <div class="card" style="border-left: 4px solid #b45309; text-align: center; padding: 1rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #b45309;">PENDING</div>
    <div style="font-size: 1.75rem; font-weight: 800; color: #b45309;"><?= (int)($stats['pending_count'] ?? 0) ?></div>
  </div>

  <div class="card" style="border-left: 4px solid #0284c7; text-align: center; padding: 1rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #0284c7;">PREPARING</div>
    <div style="font-size: 1.75rem; font-weight: 800; color: #0284c7;"><?= (int)($stats['preparing_count'] ?? 0) ?></div>
  </div>

  <div class="card" style="border-left: 4px solid #16a34a; text-align: center; padding: 1rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #16a34a;">READY FOR PICKUP</div>
    <div style="font-size: 1.75rem; font-weight: 800; color: #16a34a;"><?= (int)($stats['ready_count'] ?? 0) ?></div>
  </div>

  <div class="card" style="border-left: 4px solid #64748b; text-align: center; padding: 1rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b;">COLLECTED</div>
    <div style="font-size: 1.75rem; font-weight: 800; color: #64748b;"><?= (int)($stats['collected_count'] ?? 0) ?></div>
  </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h3 style="font-size: 1.15rem; font-weight: 700;">Recent Orders</h3>
    <a href="/orders" class="btn btn-outline btn-sm">View All Orders →</a>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Status</th>
          <th>Total</th>
          <th>Source</th>
          <th>Placed At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentOrders)): ?>
          <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No orders placed today yet.</td></tr>
        <?php else: ?>
          <?php foreach ($recentOrders as $ro): ?>
            <tr>
              <td>
                <strong><a href="/orders/<?= $ro['id'] ?>" style="color:var(--primary); text-decoration:none;"><?= $e($ro['order_number']) ?></a></strong>
              </td>
              <td>
                <span class="badge badge-<?= $e($ro['status']) ?>"><?= $__('order_status_' . $ro['status']) ?></span>
              </td>
              <td><?= number_format((float)$ro['total_amount'], 0) ?> IQD</td>
              <td>
                <?php if ($ro['is_offline_created']): ?>
                  <span class="badge badge-offline">Offline Edge</span>
                <?php else: ?>
                  <span class="badge badge-online">Cloud POS</span>
                <?php endif; ?>
              </td>
              <td><?= date('H:i:s', strtotime($ro['created_at'])) ?></td>
              <td>
                <a href="/track/<?= $e($ro['tracking_token']) ?>" target="_blank" class="btn btn-outline btn-sm">
                  📱 Customer View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
