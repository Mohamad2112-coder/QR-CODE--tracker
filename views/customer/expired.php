<div class="card" style="text-align: center; padding: 2.5rem 1.5rem; border-radius: 16px;">
  <div style="font-size: 3rem; margin-bottom: 0.75rem;">📦</div>
  <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem; color: #0f172a;">
    Order #<?= $e($order['order_number'] ?? '') ?> Has Expired
  </h2>
  <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5;">
    This order was completed and collected. For privacy and security reasons, digital order tracking links expire after pickup.
  </p>
  <div style="font-size: 0.9rem; font-weight: 600; color: var(--primary);">
    Thank you for visiting <?= $e($order['business_name'] ?? 'our business') ?>!
  </div>
</div>
