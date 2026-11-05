<div class="kds-board">
  <!-- 1. PENDING (NEW ORDERS) -->
  <div class="kds-col" id="colPending">
    <div class="kds-col-header" style="color: #b45309;">
      <span>⏳ PENDING (<span id="countPending">0</span>)</span>
    </div>
    <div class="kds-orders-container" id="ordersPending" style="display:flex; flex-direction:column; gap:1rem;"></div>
  </div>

  <!-- 2. PREPARING (IN COOKING/MAKING) -->
  <div class="kds-col" id="colPreparing">
    <div class="kds-col-header" style="color: #0284c7;">
      <span>🍳 PREPARING (<span id="countPreparing">0</span>)</span>
    </div>
    <div class="kds-orders-container" id="ordersPreparing" style="display:flex; flex-direction:column; gap:1rem;"></div>
  </div>

  <!-- 3. READY FOR PICKUP -->
  <div class="kds-col" id="colReady">
    <div class="kds-col-header" style="color: #16a34a;">
      <span>🔔 READY FOR PICKUP (<span id="countReady">0</span>)</span>
    </div>
    <div class="kds-orders-container" id="ordersReady" style="display:flex; flex-direction:column; gap:1rem;"></div>
  </div>
</div>

<script>
let knownOrderIds = new Set();
let isInitialLoad = true;

async function fetchKitchenOrders() {
  try {
    const res = await fetch('/api/kitchen/orders');
    if (!res.ok) return;
    const data = await res.json();
    if (data.status === 'success') {
      renderKds(data.orders);
    }
  } catch (err) {
    console.error('KDS poll error:', err);
  }
}

function renderKds(orders) {
  const containerPending = document.getElementById('ordersPending');
  const containerPrep = document.getElementById('ordersPreparing');
  const containerReady = document.getElementById('ordersReady');

  let pendingHtml = '';
  let prepHtml = '';
  let readyHtml = '';

  let cPending = 0;
  let cPrep = 0;
  let cReady = 0;

  let hasNewOrders = false;

  orders.forEach(ord => {
    if (!knownOrderIds.has(ord.id)) {
      knownOrderIds.add(ord.id);
      if (!isInitialLoad && ord.status === 'pending') {
        hasNewOrders = true;
      }
    }

    const elapsedMins = Math.floor(ord.elapsed_seconds / 60);
    const isLongWait = elapsedMins >= 15;
    const waitClass = isLongWait ? 'badge badge-cancelled' : (elapsedMins >= 8 ? 'badge badge-pending' : 'badge badge-online');

    let itemsHtml = '';
    ord.items.forEach(it => {
      itemsHtml += `
        <div style="display:flex; justify-content:space-between; margin-bottom: 0.35rem; font-size: 1.05rem;">
          <div>
            <strong>${it.quantity}x</strong> <span style="font-weight:600;">${it.product_name_snapshot}</span>
            ${it.item_notes ? `<div style="font-size:0.85rem; color:#b45309; margin-left:1.5rem;">Note: ${it.item_notes}</div>` : ''}
          </div>
        </div>
      `;
    });

    let actionButton = '';
    if (ord.status === 'pending') {
      cPending++;
      actionButton = `
        <button onclick="updateKdsStatus('${ord.id}', 'preparing')" class="btn btn-primary btn-lg" style="width:100%;">
          🍳 Start Preparing
        </button>
      `;
    } else if (ord.status === 'preparing') {
      cPrep++;
      actionButton = `
        <button onclick="updateKdsStatus('${ord.id}', 'ready')" class="btn btn-success btn-lg" style="width:100%;">
          🔔 Mark Ready (Notify)
        </button>
      `;
    } else if (ord.status === 'ready') {
      cReady++;
      actionButton = `
        <button onclick="updateKdsStatus('${ord.id}', 'collected')" class="btn btn-outline btn-lg" style="width:100%; color:#0f172a; border-color:#cbd5e1;">
          ✓ Mark Collected
        </button>
      `;
    }

    const cardHtml = `
      <div class="kds-order-card ${ord.status === 'ready' ? 'ready-card' : ''} ${isLongWait ? 'long-wait' : ''}" style="color: #0f172a;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
          <span style="font-size:1.5rem; font-weight:900; color:var(--primary);">${ord.order_number}</span>
          <span class="${waitClass}" style="font-size:0.9rem;">⏱ ${elapsedMins}m</span>
        </div>

        <div style="margin-bottom:1rem; min-height: 50px;">
          ${itemsHtml}
        </div>

        ${ord.customer_notes ? `
          <div style="background:#fffbeb; padding:0.5rem; border-radius:6px; font-size:0.85rem; color:#92400e; margin-bottom:0.75rem;">
            📌 <strong>Note:</strong> ${ord.customer_notes}
          </div>
        ` : ''}

        ${actionButton}
      </div>
    `;

    if (ord.status === 'pending') pendingHtml += cardHtml;
    else if (ord.status === 'preparing') prepHtml += cardHtml;
    else if (ord.status === 'ready') readyHtml += cardHtml;
  });

  containerPending.innerHTML = pendingHtml || '<div style="color:#64748b; text-align:center; padding:1.5rem;">No pending orders</div>';
  containerPrep.innerHTML = prepHtml || '<div style="color:#64748b; text-align:center; padding:1.5rem;">No preparing orders</div>';
  containerReady.innerHTML = readyHtml || '<div style="color:#64748b; text-align:center; padding:1.5rem;">No orders waiting pickup</div>';

  document.getElementById('countPending').innerText = cPending;
  document.getElementById('countPreparing').innerText = cPrep;
  document.getElementById('countReady').innerText = cReady;

  if (hasNewOrders) {
    SoundNotifier.playChime('new_order');
  }

  isInitialLoad = false;
}

async function updateKdsStatus(orderId, newStatus) {
  try {
    const res = await fetch(`/api/kitchen/orders/${orderId}/status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: newStatus })
    });
    const data = await res.json();
    if (res.ok && data.status === 'success') {
      fetchKitchenOrders();
    } else {
      alert('Error updating status: ' + (data.error || 'Unknown error'));
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  }
}

// Initial fetch & recurring 4-second auto-poll
fetchKitchenOrders();
setInterval(fetchKitchenOrders, 4000);
</script>
