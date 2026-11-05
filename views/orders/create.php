<div class="pos-container">
  <!-- Left Side: Product Catalog & Search -->
  <div style="display: flex; flex-direction: column; gap: 1rem; overflow-y: auto;">
    <div style="display: flex; gap: 0.75rem; align-items: center;">
      <input type="text" id="posSearch" class="form-control" placeholder="🔍 <?= $__('pos_search_products') ?>" style="flex: 1;" onkeyup="filterProducts()">
      <div style="display: flex; gap: 0.5rem; overflow-x: auto; white-space: nowrap;">
        <button class="btn btn-outline btn-sm active-cat-btn" onclick="filterCategory('all', this)">
          <?= $__('pos_all_categories') ?>
        </button>
        <?php foreach ($categories as $cat): ?>
          <button class="btn btn-outline btn-sm" onclick="filterCategory('<?= $cat['id'] ?>', this)">
            <?= $e($cat['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-3" id="productGrid">
      <?php foreach ($products as $p): ?>
        <div class="product-card" data-cat="<?= $p['category_id'] ?>" data-name="<?= strtolower($p['name']) ?>" onclick="addToCart('<?= $p['id'] ?>', '<?= addslashes($p['name']) ?>', <?= (float)$p['price'] ?>)">
          <div>
            <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 0.25rem;"><?= $e($p['name']) ?></div>
            <?php if (!empty($p['description'])): ?>
              <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;"><?= $e($p['description']) ?></div>
            <?php endif; ?>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
            <span class="product-price"><?= number_format((float)$p['price'], 0) ?> IQD</span>
            <span class="btn btn-primary btn-sm">+ Add</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Right Side: Order Summary Cart Drawer -->
  <div class="cart-panel">
    <div style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.15rem; font-weight: 700;">🛒 <?= $__('pos_order_summary') ?></h3>
      <button onclick="clearCart()" class="btn btn-outline btn-sm" style="color: var(--danger);">
        <?= $__('pos_clear') ?>
      </button>
    </div>

    <div class="cart-items" id="cartItems">
      <div style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem;" id="emptyCartNotice">
        <?= $__('pos_empty_cart') ?>
      </div>
    </div>

    <div class="cart-footer">
      <div class="form-group" style="margin-bottom: 0.75rem;">
        <input type="text" id="orderNotes" class="form-control" placeholder="<?= $__('pos_order_notes') ?>">
      </div>

      <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem;">
        <span><?= $__('pos_total') ?>:</span>
        <span id="cartTotalDisplay">IQD 0</span>
      </div>

      <button id="btnPlaceOrder" onclick="submitPosOrder()" class="btn btn-primary btn-lg" style="width: 100%;" disabled>
        ⚡ <?= $__('pos_place_order') ?>
      </button>
    </div>
  </div>
</div>

<!-- Modal: Digital QR Pager & Print Slip -->
<div id="qrModal" class="modal-overlay">
  <div class="modal-box" id="printSlipArea">
    <h2 style="font-size: 1.1rem; color: var(--text-muted); text-transform: uppercase;"><?= $e($tenant['name']) ?></h2>
    <div style="font-size: 2.5rem; font-weight: 900; color: var(--primary); margin: 0.5rem 0;" id="modalOrderNumber">ORD-000</div>
    
    <p style="font-size: 0.95rem; color: #475569; margin-bottom: 1rem;">
      <?= $__('pos_scan_instruction') ?>
    </p>

    <!-- QR Code SVG Container -->
    <div id="modalQrSvgContainer" style="display: flex; justify-content: center; margin: 1rem 0;">
      <img id="modalQrImg" src="" alt="Order QR" style="max-width: 220px; height: auto;">
    </div>

    <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem; word-break: break-all;">
      <a id="modalTrackingLink" href="#" target="_blank" style="color: var(--primary); font-weight: 600;"></a>
    </div>

    <div style="display: flex; gap: 0.75rem; justify-content: center;" class="no-print">
      <button onclick="window.print()" class="btn btn-outline">
        🖨️ <?= $__('pos_print_receipt') ?>
      </button>
      <button onclick="closeQrModalAndReset()" class="btn btn-primary">
        ➕ <?= $__('pos_new_sale') ?>
      </button>
    </div>
  </div>
</div>

<script>
let cart = {};
const formatIQD = amount => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Math.round(amount));

function addToCart(id, name, price) {
  if (cart[id]) {
    cart[id].quantity += 1;
  } else {
    cart[id] = { product_id: id, name: name, price: price, quantity: 1, notes: '' };
  }
  renderCart();
}

function updateQty(id, delta) {
  if (!cart[id]) return;
  cart[id].quantity += delta;
  if (cart[id].quantity <= 0) {
    delete cart[id];
  }
  renderCart();
}

function updateNotes(id, text) {
  if (cart[id]) {
    cart[id].notes = text;
  }
}

function clearCart() {
  cart = {};
  renderCart();
}

function renderCart() {
  const container = document.getElementById('cartItems');
  const emptyNotice = document.getElementById('emptyCartNotice');
  const btn = document.getElementById('btnPlaceOrder');
  const totalDisplay = document.getElementById('cartTotalDisplay');

  const items = Object.values(cart);
  if (items.length === 0) {
    container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem;">No items added yet. Click products to build the order.</div>';
    totalDisplay.innerText = 'IQD 0';
    btn.disabled = true;
    return;
  }

  let total = 0;
  let html = '';
  items.forEach(it => {
    const subtotal = it.price * it.quantity;
    total += subtotal;
    html += `
      <div class="cart-item">
        <div style="flex: 1;">
          <div style="font-weight: 700; font-size: 0.95rem;">${it.name}</div>
          <div style="font-size: 0.85rem; color: var(--text-muted);">${formatIQD(it.price)} IQD ea</div>
          <input type="text" class="form-control" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; margin-top: 0.35rem;" 
                 placeholder="Special instructions..." value="${it.notes}" oninput="updateNotes('${it.product_id}', this.value)">
        </div>
        <div style="display: flex; align-items: center; gap: 0.4rem; margin: 0 0.75rem;">
          <button onclick="updateQty('${it.product_id}', -1)" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem;">-</button>
          <span style="font-weight: 700; min-width: 20px; text-align: center;">${it.quantity}</span>
          <button onclick="updateQty('${it.product_id}', 1)" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem;">+</button>
        </div>
        <div style="font-weight: 700; min-width: 60px; text-align: right;">
          ${formatIQD(subtotal)} IQD
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
  totalDisplay.innerText = `${formatIQD(total)} IQD`;
  btn.disabled = false;
}

function filterCategory(catId, btnEl) {
  document.querySelectorAll('.active-cat-btn').forEach(b => b.classList.remove('active-cat-btn'));
  btnEl.classList.add('active-cat-btn');

  const cards = document.querySelectorAll('.product-card');
  cards.forEach(card => {
    if (catId === 'all' || card.getAttribute('data-cat') === catId) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

function filterProducts() {
  const query = document.getElementById('posSearch').value.toLowerCase().trim();
  const cards = document.querySelectorAll('.product-card');
  cards.forEach(card => {
    const name = card.getAttribute('data-name');
    if (name.includes(query)) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

async function submitPosOrder() {
  const items = Object.values(cart);
  if (items.length === 0) return;

  const btn = document.getElementById('btnPlaceOrder');
  btn.disabled = true;
  btn.innerText = 'Creating Order...';

  const orderNotes = document.getElementById('orderNotes').value;
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  try {
    const res = await fetch('/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        items: items,
        customer_notes: orderNotes
      })
    });

    const data = await res.json();
    if (res.ok && data.status === 'success') {
      // Show Digital QR Pager Modal
      document.getElementById('modalOrderNumber').innerText = data.order_number;
      document.getElementById('modalQrImg').src = data.qr_svg;
      document.getElementById('modalTrackingLink').href = data.tracking_url;
      document.getElementById('modalTrackingLink').innerText = data.tracking_url;
      document.getElementById('qrModal').classList.add('active');

      // Clear Cart
      cart = {};
      document.getElementById('orderNotes').value = '';
      renderCart();
    } else {
      alert('Order creation failed: ' + (data.error || 'Unknown error'));
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerText = '⚡ Create Order & Generate QR';
  }
}

function closeQrModalAndReset() {
  document.getElementById('qrModal').classList.remove('active');
}
</script>
