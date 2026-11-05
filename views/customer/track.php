<div class="tracking-header">
  <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?= $e($order['business_name']) ?></h1>
  <?php if (!empty($order['tagline'])): ?>
    <p style="font-size: 0.85rem; color: var(--text-muted);"><?= $e($order['tagline']) ?></p>
  <?php endif; ?>
  <div style="font-size: 2.25rem; font-weight: 900; margin-top: 0.5rem; color: #0f172a;">
    <?= $__('customer_order_number', ['order_number' => $order['order_number']]) ?>
  </div>
</div>

<!-- Visual Stepper Progress Bar -->
<?php
  $statusRanks = ['pending' => 1, 'preparing' => 2, 'ready' => 3, 'collected' => 4];
  $currentRank = $statusRanks[$order['status']] ?? 1;
?>
<div class="stepper">
  <div class="step-node <?= $currentRank >= 1 ? ($currentRank === 1 ? 'active' : 'completed') : '' ?>">
    <div class="step-circle"><?= $currentRank > 1 ? '✓' : '1' ?></div>
    <div class="step-label"><?= $__('order_status_pending') ?></div>
  </div>

  <div class="step-node <?= $currentRank >= 2 ? ($currentRank === 2 ? 'active' : 'completed') : '' ?>">
    <div class="step-circle"><?= $currentRank > 2 ? '✓' : '2' ?></div>
    <div class="step-label"><?= $__('order_status_preparing') ?></div>
  </div>

  <div class="step-node <?= $currentRank >= 3 ? ($currentRank === 3 ? 'active' : 'completed') : '' ?>">
    <div class="step-circle"><?= $currentRank > 3 ? '✓' : '3' ?></div>
    <div class="step-label"><?= $__('order_status_ready') ?></div>
  </div>

  <div class="step-node <?= $currentRank >= 4 ? 'completed' : '' ?>">
    <div class="step-circle"><?= $currentRank >= 4 ? '✓' : '4' ?></div>
    <div class="step-label"><?= $__('order_status_collected') ?></div>
  </div>
</div>

<!-- Status Hero Card -->
<div class="status-hero status-<?= $e($order['status']) ?>" id="statusHero">
  <div class="status-animation-icon" aria-hidden="true">
    <?php
      echo match($order['status']) {
        'pending' => '⏳',
        'preparing' => '🍳',
        'ready' => '🎉',
        'collected' => '✅',
        'cancelled' => '❌',
        default => '📱'
      };
    ?>
  </div>

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;" id="statusTitle">
    <?= $__('order_status_' . $order['status']) ?>
  </h2>

  <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.4;" id="statusDesc">
    <?= $__('customer_status_desc_' . $order['status']) ?>
  </p>

  <?php if ($order['status'] === 'preparing' && !empty($order['estimated_prep_time_minutes'])): ?>
    <div style="margin-top: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--primary);">
      ⏱ <?= $__('estimated_time') ?>: ~<?= $order['estimated_prep_time_minutes'] ?> <?= $__('minutes') ?>
    </div>
  <?php endif; ?>
</div>

<!-- Order Items List -->
<div class="card">
  <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
    <?= $__('order_items_title') ?>
  </h3>

  <?php foreach ($order['items'] as $it): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed #f1f5f9;">
      <div>
        <span style="font-weight: 700;"><?= $it['quantity'] ?>x</span>
        <span><?= $e($it['name']) ?></span>
        <?php if (!empty($it['item_notes'])): ?>
          <div style="font-size: 0.8rem; color: #b45309;"><?= $e($it['item_notes']) ?></div>
        <?php endif; ?>
      </div>
      <div style="font-weight: 600;">
        <?= number_format((float)$it['subtotal'], 0) ?> IQD
      </div>
    </div>
  <?php endforeach; ?>

  <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.15rem; margin-top: 0.75rem; padding-top: 0.5rem; border-top: 2px solid var(--border);">
    <span><?= $__('pos_total') ?></span>
    <span style="color: var(--primary);"><?= number_format((float)$order['total_amount'], 0) ?> IQD</span>
  </div>
</div>

<div style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted);">
  <span><?= $__('last_updated') ?>: <span id="lastUpdated"><?= date('H:i:s') ?></span></span> •
  <a href="javascript:location.reload()" style="color: var(--primary); text-decoration: none;"><?= $__('refresh_status') ?></a>
</div>

<script>
const orderToken = <?= json_encode($order['tracking_token'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
let currentStatus = '<?= $order['status'] ?>';

// Live Status Polling
async function pollOrderStatus() {
  if (document.visibilityState !== 'visible') return;
  try {
    const res = await fetch(`/api/customer/status/${orderToken}`, { cache: 'no-store' });
    if (!res.ok) return;
    const data = await res.json();

    if (data.status && data.status !== currentStatus) {
      // Reload to update the stepper and the animated status state together.
      location.reload();
      return;
    }

    document.getElementById('lastUpdated').innerText = new Date().toLocaleTimeString();
  } catch (err) {
    console.error('Status poll error:', err);
  }
}

// Poll immediately, then keep checking while this page is visible.
pollOrderStatus();
setInterval(pollOrderStatus, 5000);
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    pollOrderStatus();
  }
});
</script>
