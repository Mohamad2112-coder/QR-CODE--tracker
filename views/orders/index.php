<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.75rem; font-weight: 800;"><?= $__('orders') ?> Management</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Track, search, and manage all customer orders</p>
  </div>

  <a href="/orders/create" class="btn btn-primary">
    + New Order (POS)
  </a>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

<!-- Filters & Search Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
  <form action="/orders" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
    <input type="text" name="q" value="<?= $e($search) ?>" class="form-control" placeholder="Search by Order # or Notes..." style="max-width: 300px;">
    
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <a href="/orders" class="btn btn-sm <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?>">All</a>
      <a href="/orders?status=pending" class="btn btn-sm <?= $currentStatus === 'pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
      <a href="/orders?status=preparing" class="btn btn-sm <?= $currentStatus === 'preparing' ? 'btn-primary' : 'btn-outline' ?>">Preparing</a>
      <a href="/orders?status=ready" class="btn btn-sm <?= $currentStatus === 'ready' ? 'btn-primary' : 'btn-outline' ?>">Ready</a>
      <a href="/orders?status=collected" class="btn btn-sm <?= $currentStatus === 'collected' ? 'btn-primary' : 'btn-outline' ?>">Collected</a>
      <a href="/orders?status=cancelled" class="btn btn-sm <?= $currentStatus === 'cancelled' ? 'btn-primary' : 'btn-outline' ?>">Cancelled</a>
    </div>

    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>
</div>

<!-- Orders Table -->
<div class="card">
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Status</th>
          <th>Items</th>
          <th>Total</th>
          <th>Cashier / Staff</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">No orders found matching criteria.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $ord): ?>
            <tr>
              <td>
                <strong><a href="/orders/<?= $ord['id'] ?>" style="color:var(--primary); text-decoration:none;"><?= $e($ord['order_number']) ?></a></strong>
                <?php if ($ord['is_offline_created']): ?>
                  <span class="badge badge-offline" style="font-size: 0.65rem;">Offline Edge</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?= $e($ord['status']) ?>">
                  <?= $__('order_status_' . $ord['status']) ?>
                </span>
              </td>
              <td><?= $ord['items_count'] ?> item(s)</td>
              <td><strong><?= number_format((float)$ord['total_amount'], 0) ?> IQD</strong></td>
              <td><?= $e($ord['cashier_name'] ?? 'System') ?></td>
              <td><?= date('M d, H:i', strtotime($ord['created_at'])) ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem;">
                  <a href="/orders/<?= $ord['id'] ?>" class="btn btn-outline btn-sm">Details</a>
                  <a href="/track/<?= $ord['tracking_token'] ?>" target="_blank" class="btn btn-outline btn-sm">📱 QR Link</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
