<?php $__env->startSection('title', 'Products'); ?>
<?php $__env->startSection('page-title', 'Products Management'); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Product name…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Approval</label>
    <select name="acceptance">
      <option value="">All</option>
      <option value="pending" <?php echo e($acceptance=='pending'?'selected':''); ?>>Pending</option>
      <option value="approved" <?php echo e($acceptance=='approved'?'selected':''); ?>>Approved</option>
      <option value="rejected" <?php echo e($acceptance=='rejected'?'selected':''); ?>>Rejected</option>
    </select>
  </div>
  <div class="form-group">
    <label>Visibility</label>
    <select name="status">
      <option value="">All</option>
      <option value="publish" <?php echo e($status=='publish'?'selected':''); ?>>Published</option>
      <option value="draft" <?php echo e($status=='draft'?'selected':''); ?>>Draft</option>
      <option value="private" <?php echo e($status=='private'?'selected':''); ?>>Private</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $acceptance || $status): ?>
    <div class="form-group" style="justify-content:flex-end">
      <a href="<?php echo e(route('admin.products')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<form method="POST" action="<?php echo e(route('admin.products.bulk')); ?>" id="bulk-products-form">
  <?php echo csrf_field(); ?>
  <div style="margin:10px 0 12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select name="bulk_action" class="filter-select" style="max-width:180px">
      <option value="">Bulk actions</option>
      <option value="approve">Approve</option>
      <option value="reject">Reject</option>
      <option value="delete">Delete</option>
    </select>
    <button type="submit" class="btn btn-primary" onclick="return confirmBulkProducts()">Apply</button>
    <span style="color:var(--muted);font-size:13px"><?php echo e($products->total()); ?> product(s) found</span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:34px"><input type="checkbox" id="select-all-products" onchange="document.querySelectorAll('.product-check').forEach(cb => cb.checked = this.checked)"></th>
          <th>ID</th>
          <th>Name</th>
          <th>Shop</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Approval</th>
          <th>Visibility</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $ac = match($product->acceptance_status) {
            'approved'=>'badge-green','pending'=>'badge-yellow','rejected'=>'badge-red',default=>'badge-gray'
          };
          $sc = match($product->status) {
            'publish'=>'badge-blue','draft'=>'badge-gray','private'=>'badge-orange',default=>'badge-gray'
          };
        ?>
        <tr>
          <td><input type="checkbox" class="product-check" name="ids[]" value="<?php echo e($product->id); ?>"></td>
          <td style="color:var(--muted)">#<?php echo e($product->id); ?></td>
          <td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($product->name); ?></td>
          <td style="color:var(--muted);font-size:12px"><?php echo e($product->shop_name ?? '—'); ?></td>
          <td style="font-weight:600">
            <?php if($product->min_price == $product->max_price): ?>
              <?php echo e(number_format($product->min_price ?? 0, 2)); ?>

            <?php else: ?>
              <?php echo e(number_format($product->min_price ?? 0, 2)); ?>–<?php echo e(number_format($product->max_price ?? 0, 2)); ?>

            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?php echo e($product->stock_status === 'instock' ? 'badge-green' : 'badge-red'); ?>">
              <?php echo e($product->stock_status === 'instock' ? 'In Stock' : 'Out'); ?>

            </span>
          </td>
          <td><span class="badge <?php echo e($ac); ?>"><?php echo e(ucfirst($product->acceptance_status)); ?></span></td>
          <td>
            <form method="POST" action="<?php echo e(route('admin.products.toggle', $product->id)); ?>" style="display:flex;gap:4px">
              <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
              <select name="status" style="padding:4px 6px;font-size:12px;height:28px">
                <option value="publish" <?php echo e($product->status=='publish'?'selected':''); ?>>Publish</option>
                <option value="draft" <?php echo e($product->status=='draft'?'selected':''); ?>>Draft</option>
                <option value="private" <?php echo e($product->status=='private'?'selected':''); ?>>Private</option>
              </select>
              <button class="btn btn-ghost btn-sm">Set</button>
            </form>
          </td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <a href="<?php echo e(route('admin.products.show', $product->id)); ?>" class="btn btn-secondary btn-sm">View</a>
              <?php if($product->acceptance_status !== 'approved'): ?>
                <form method="POST" action="<?php echo e(route('admin.products.approve', $product->id)); ?>">
                  <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                  <button class="btn btn-success btn-sm">Approve</button>
                </form>
              <?php endif; ?>
              <?php if($product->acceptance_status !== 'rejected'): ?>
                <form method="POST" action="<?php echo e(route('admin.products.reject', $product->id)); ?>" onsubmit="return confirm('Reject this product?')">
                  <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                  <button class="btn btn-warning btn-sm">Reject</button>
                </form>
              <?php endif; ?>
              <form method="POST" action="<?php echo e(route('admin.products.delete', $product->id)); ?>" onsubmit="return confirm('Delete this product?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No products found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</form>

<div class="pagination"><?php echo e($products->links('admin.pagination')); ?></div>

<script>
function confirmBulkProducts() {
  const action = document.querySelector('select[name="bulk_action"]').value;
  const checked = document.querySelectorAll('.product-check:checked').length;
  if (!action) { alert('Choose a bulk action.'); return false; }
  if (!checked) { alert('Select at least one product.'); return false; }
  if (action === 'delete') return confirm('Delete selected products? This cannot be undone.');
  return confirm('Apply this bulk action to selected products?');
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/products.blade.php ENDPATH**/ ?>