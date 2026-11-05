<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.75rem; font-weight: 800;"><?= $__('reports') ?> & Analytics</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Real database analytics across orders, prep speed, staff, and sync</p>
  </div>

  <div style="display: flex; gap: 0.75rem;">
    <!-- Date Range Picker Buttons -->
    <a href="/reports?days=7" class="btn btn-sm <?= $days === 7 ? 'btn-primary' : 'btn-outline' ?>">Last 7 Days</a>
    <a href="/reports?days=30" class="btn btn-sm <?= $days === 30 ? 'btn-primary' : 'btn-outline' ?>">Last 30 Days</a>
    <a href="/reports?days=90" class="btn btn-sm <?= $days === 90 ? 'btn-primary' : 'btn-outline' ?>">Last 90 Days</a>
    <a href="/reports/export" class="btn btn-success btn-sm">📥 Export CSV</a>
  </div>
</div>

<!-- Primary Speed & Reliability KPI Cards -->
<div class="grid grid-4" style="margin-bottom: 1.5rem;">
  <div class="card stat-card">
    <div>
      <div class="stat-label">Avg Prep Time</div>
      <div class="stat-value"><?= $times['avg_prep_time'] ? round((float)$times['avg_prep_time'], 1) . 'm' : '--' ?></div>
    </div>
    <div style="font-size: 2rem;">⏱️</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Avg Pickup Time</div>
      <div class="stat-value"><?= $times['avg_pickup_time'] ? round((float)$times['avg_pickup_time'], 1) . 'm' : '--' ?></div>
    </div>
    <div style="font-size: 2rem;">🚶</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Cancelled Orders</div>
      <div class="stat-value" style="color: var(--danger);"><?= (int)($cancellationStats['cancelled_count'] ?? 0) ?></div>
    </div>
    <div style="font-size: 2rem;">❌</div>
  </div>

  <div class="card stat-card">
    <div>
      <div class="stat-label">Push Alerts Sent</div>
      <div class="stat-value" style="color: var(--success);"><?= (int)($systemStats['sent_notifications'] ?? 0) ?></div>
    </div>
    <div style="font-size: 2rem;">🔔</div>
  </div>
</div>

<!-- Busy Hours & Product Popularity -->
<div class="grid grid-2" style="margin-bottom: 1.5rem;">
  <!-- Busy Hours Table / Histogram -->
  <div class="card">
    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Peak Operating Hours (00 - 23h)</h3>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Hour</th>
            <th>Orders</th>
            <th>Volume Bar</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($busyHours)): ?>
            <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No order data in this timeframe.</td></tr>
          <?php else: ?>
            <?php 
              $maxOrders = max(array_column($busyHours, 'orders_count') ?: [1]); 
              foreach ($busyHours as $bh): 
                $pct = round(($bh['orders_count'] / $maxOrders) * 100);
            ?>
              <tr>
                <td style="font-weight: 700;"><?= sprintf('%02d:00', $bh['hour_of_day']) ?></td>
                <td><strong><?= $bh['orders_count'] ?></strong> orders</td>
                <td style="width: 50%;">
                  <div style="background: #e2e8f0; height: 10px; border-radius: 9999px; overflow: hidden;">
                    <div style="background: var(--primary); width: <?= $pct ?>%; height: 100%;"></div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Product Popularity Ranking -->
  <div class="card">
    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Top Selling Items</h3>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty Sold</th>
            <th>Sales (IQD)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($popularProducts)): ?>
            <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No sales data available.</td></tr>
          <?php else: ?>
            <?php foreach ($popularProducts as $p): ?>
              <tr>
                <td style="font-weight: 700;"><?= $e($p['product_name']) ?></td>
                <td><span class="badge badge-preparing"><?= $p['total_qty'] ?> units</span></td>
                <td><strong><?= number_format((float)$p['total_sales'], 0) ?> IQD</strong></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Staff Performance & Edge Synchronization -->
<div class="grid grid-2">
  <!-- Staff Performance -->
  <div class="card">
    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Staff Performance</h3>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Staff Name</th>
            <th>Role</th>
            <th>Orders Handled</th>
            <th>Sales Handled</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staffStats as $st): ?>
            <tr>
              <td style="font-weight: 700;"><?= $e($st['staff_name']) ?></td>
              <td><span class="badge badge-collected"><?= $e($st['role_name']) ?></span></td>
              <td><?= (int)$st['orders_handled'] ?></td>
              <td><?= number_format((float)$st['sales_handled'], 0) ?> IQD</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Local Hub Synchronization Logs -->
  <div class="card">
    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Local Edge Sync Batches</h3>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Batch ID</th>
            <th>Orders</th>
            <th>Conflicts</th>
            <th>Status</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($syncBatches)): ?>
            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No offline batches synced yet. Cloud is primary.</td></tr>
          <?php else: ?>
            <?php foreach ($syncBatches as $sb): ?>
              <tr>
                <td><code><?= substr($sb['batch_id'], 0, 12) ?>...</code></td>
                <td><?= $sb['orders_count'] ?></td>
                <td>
                  <?= $sb['conflicts_count'] > 0 ? "<span style='color:var(--danger); font-weight:700;'>{$sb['conflicts_count']}</span>" : "0" ?>
                </td>
                <td><span class="badge badge-online"><?= $sb['status'] ?></span></td>
                <td><?= date('M d, H:i', strtotime($sb['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
