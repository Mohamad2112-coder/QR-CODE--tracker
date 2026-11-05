<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <h1 style="font-size: 1.75rem; font-weight: 800;">Order <?= $e($order['order_number']) ?></h1>
      <span class="badge badge-<?= $e($order['status']) ?>" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
        <?= $__('order_status_' . $order['status']) ?>
      </span>
    </div>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;">
      Placed on <?= date('F j, Y \a\t H:i:s', strtotime($order['created_at'])) ?>
      <?= $order['is_offline_created'] ? '• <span class="badge badge-offline">Created Offline</span>' : '' ?>
    </p>
  </div>

  <div style="display: flex; gap: 0.75rem;">
    <a href="/orders" class="btn btn-outline">← Back to Orders</a>
    <a href="/track/<?= $e($order['tracking_token']) ?>" target="_blank" class="btn btn-primary">
      📱 Open Customer Pager
    </a>
  </div>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

<div class="grid grid-2" style="align-items: start;">
  <!-- Left Side: Order Items Snapshot & Totals -->
  <div style="display: flex; flex-direction: column; gap: 1.25rem;">
    <div class="card">
      <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Order Items (Historical Snapshot)</h3>
      
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div style="font-weight: 700;"><?= $e($it['product_name_snapshot']) ?></div>
                  <?php if (!empty($it['item_notes'])): ?>
                    <div style="font-size: 0.8rem; color: #b45309;">Note: <?= $e($it['item_notes']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= number_format((float)$it['unit_price_snapshot'], 0) ?> IQD</td>
                <td><strong><?= $it['quantity'] ?></strong></td>
                <td><?= number_format((float)$it['subtotal'], 0) ?> IQD</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align: right; font-weight: 700; font-size: 1.1rem;">Total:</td>
              <td style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">
                <?= number_format((float)$order['total_amount'], 0) ?> IQD
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <?php if (!empty($order['customer_notes'])): ?>
        <div style="margin-top: 1rem; background: #fffbeb; border: 1px solid #fef3c7; padding: 0.75rem; border-radius: 8px;">
          <strong style="color: #92400e; font-size: 0.85rem;">Customer Notes:</strong>
          <p style="margin-top: 0.25rem; font-size: 0.95rem; color: #78350f;"><?= $e($order['customer_notes']) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Status Change Actions -->
    <div class="card">
      <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Update Order Status</h3>
      
      <?php if ($order['status'] === 'cancelled'): ?>
        <p style="color: var(--danger); font-weight: 600;">This order is cancelled and closed.</p>
      <?php elseif ($order['status'] === 'collected'): ?>
        <p style="color: var(--success); font-weight: 600;">This order has been collected and completed.</p>
      <?php else: ?>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
          <?php if ($order['status'] === 'pending'): ?>
            <form action="/orders/<?= $order['id'] ?>/status" method="POST">
              <?= $csrfField() ?>
              <input type="hidden" name="status" value="preparing">
              <button type="submit" class="btn btn-primary">Start Preparing</button>
            </form>
          <?php endif; ?>

          <?php if (in_array($order['status'], ['pending', 'preparing'])): ?>
            <form action="/orders/<?= $order['id'] ?>/status" method="POST">
              <?= $csrfField() ?>
              <input type="hidden" name="status" value="ready">
              <button type="submit" class="btn btn-success">Mark Ready for Pickup (Trigger Push)</button>
            </form>
          <?php endif; ?>

          <?php if ($order['status'] === 'ready'): ?>
            <form action="/orders/<?= $order['id'] ?>/status" method="POST">
              <?= $csrfField() ?>
              <input type="hidden" name="status" value="collected">
              <button type="submit" class="btn btn-outline" style="color: #475569;">Mark Collected</button>
            </form>
          <?php endif; ?>

          <?php if (in_array($order['status'], ['pending', 'preparing'])): ?>
            <form action="/orders/<?= $order['id'] ?>/status" method="POST" onsubmit="return confirm('Cancel this order?')">
              <?= $csrfField() ?>
              <input type="hidden" name="status" value="cancelled">
              <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Side: QR Code & Status History Timeline -->
  <div style="display: flex; flex-direction: column; gap: 1.25rem;">
    <!-- Digital Pager QR Code Card -->
    <div class="card" style="text-align: center;">
      <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Customer Digital Pager QR</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
        Scan with camera or share public tracking link
      </p>

      <div style="display: flex; justify-content: center; margin: 1rem 0;">
        <img src="<?= $qrSvg ?>" alt="Order QR" style="max-width: 200px; height: auto;">
      </div>

      <div style="background: #f8fafc; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; word-break: break-all;">
        <a href="<?= $trackingUrl ?>" target="_blank" style="color: var(--primary);"><?= $trackingUrl ?></a>
      </div>
    </div>

    <!-- Status Audit Timeline -->
    <div class="card">
      <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Status Audit History</h3>

      <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid var(--border);">
        <?php foreach ($history as $h): ?>
          <div style="margin-bottom: 1.25rem; position: relative;">
            <div style="position: absolute; left: -1.95rem; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--primary);"></div>
            <div style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase;">
              <?= $e($h['new_status']) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
              <?= date('M d, H:i:s', strtotime($h['transition_time'])) ?> • 
              Source: <code><?= $e($h['source']) ?></code>
              <?= !empty($h['user_name']) ? "by {$e($h['user_name'])}" : '' ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
