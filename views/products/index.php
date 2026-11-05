<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.75rem; font-weight: 800;"><?= $__('products') ?> Catalog</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Manage products, pricing, and category availability</p>
  </div>

  <div style="display: flex; gap: 0.75rem;">
    <button onclick="document.getElementById('categoryModal').classList.add('active')" class="btn btn-outline">
      + New Category
    </button>
    <button onclick="document.getElementById('productModal').classList.add('active')" class="btn btn-primary">
      + Add Product
    </button>
  </div>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Status</th>
          <th>Availability</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">No products found. Click "+ Add Product" to create your first item.</td></tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <div style="font-weight: 700;"><?= $e($p['name']) ?></div>
                <?php if (!empty($p['description'])): ?>
                  <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $e($p['description']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= $e($p['category_name'] ?? 'General') ?></td>
              <td><strong><?= number_format((float)$p['price'], 0) ?> IQD</strong></td>
              <td>
                <?php if ($p['is_active']): ?>
                  <span class="badge badge-online">Active</span>
                <?php else: ?>
                  <span class="badge badge-offline">Archived</span>
                <?php endif; ?>
              </td>
              <td>
                <form action="/products/<?= $p['id'] ?>/toggle" method="POST" style="display: inline;">
                  <?= $csrfField() ?>
                  <button type="submit" class="btn btn-sm <?= $p['is_available'] ? 'btn-success' : 'btn-danger' ?>">
                    <?= $p['is_available'] ? '✓ In Stock' : '✕ Sold Out' ?>
                  </button>
                </form>
              </td>
              <td>
                <form action="/products/<?= $p['id'] ?>/delete" method="POST" onsubmit="return confirm('Archive this product?')" style="display: inline;">
                  <?= $csrfField() ?>
                  <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);">Archive</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Product Modal -->
<div id="productModal" class="modal-overlay">
  <div class="modal-box" style="text-align: left;">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Add New Product</h3>
    <form action="/products" method="POST">
      <?= $csrfField() ?>

      <div class="form-group">
        <label class="form-label">Product Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Vanilla Iced Latte" required>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">-- None / General --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= $e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Price (IQD) *</label>
          <input type="number" step="1" min="0" name="price" class="form-control" placeholder="3750" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description (Optional)</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Ingredients, dietary notes..."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" onclick="document.getElementById('productModal').classList.remove('active')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Category Modal -->
<div id="categoryModal" class="modal-overlay">
  <div class="modal-box" style="text-align: left;">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">New Category</h3>
    <form action="/categories" method="POST">
      <?= $csrfField() ?>

      <div class="form-group">
        <label class="form-label">Category Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Desserts" required>
      </div>

      <div class="form-group">
        <label class="form-label">Display Order</label>
        <input type="number" name="display_order" class="form-control" value="0">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" onclick="document.getElementById('categoryModal').classList.remove('active')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>
