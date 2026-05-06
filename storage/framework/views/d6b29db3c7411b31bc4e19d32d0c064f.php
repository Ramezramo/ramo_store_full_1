<?php $__env->startSection('title', $vendor->shop_name.' — Vendor'); ?>
<?php $__env->startSection('page-title', 'Vendor Detail'); ?>

<?php $__env->startSection('content'); ?>

<?php
  $sc = match($vendor->status) {
    'approved'=>'badge-green','pending'=>'badge-yellow',
    'blocked'=>'badge-red','rejected'=>'badge-gray',default=>'badge-gray'
  };
?>

<?php if(session('success')): ?>
  <div class="alert alert-success" style="margin-bottom:16px"><?php echo e(session('success')); ?></div>
<?php endif; ?>


<div style="margin-bottom:20px">
  <a href="<?php echo e(route('admin.vendors')); ?>" style="color:var(--muted);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Vendors
  </a>
</div>


<div class="card" style="margin-bottom:24px;padding:24px">
  <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center">
      <?php if($vendor->shop_logo): ?>
        <img src="<?php echo e($vendor->shop_logo); ?>" style="width:100%;height:100%;object-fit:cover" alt="logo">
      <?php else: ?>
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <?php endif; ?>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:20px;font-weight:700;margin-bottom:4px"><?php echo e($vendor->shop_name); ?></div>
      <div style="color:var(--muted);font-size:13px"><?php echo e($vendor->first_name); ?> <?php echo e($vendor->last_name); ?> &nbsp;·&nbsp; <?php echo e($vendor->email); ?></div>
      <?php if($vendor->shop_address): ?>
        <div style="color:var(--muted);font-size:12px;margin-top:2px"><?php echo e($vendor->shop_address); ?></div>
      <?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <span class="badge <?php echo e($sc); ?>" style="font-size:13px;padding:6px 14px"><?php echo e(ucfirst($vendor->status)); ?></span>
      <?php if($vendor->status !== 'approved'): ?>
        <form method="POST" action="<?php echo e(route('admin.vendors.approve', $vendor->id)); ?>">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <button class="btn btn-success btn-sm">Approve</button>
        </form>
      <?php endif; ?>
      <?php if($vendor->status !== 'blocked'): ?>
        <form method="POST" action="<?php echo e(route('admin.vendors.block', $vendor->id)); ?>" onsubmit="return confirm('Block this vendor?')">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <button class="btn btn-warning btn-sm">Block</button>
        </form>
      <?php endif; ?>
      <?php if($vendor->status !== 'rejected'): ?>
        <form method="POST" action="<?php echo e(route('admin.vendors.reject', $vendor->id)); ?>" onsubmit="return confirm('Reject this vendor?')">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <button class="btn btn-danger btn-sm">Reject</button>
        </form>
      <?php endif; ?>
      <form method="POST" action="<?php echo e(route('admin.vendors.delete', $vendor->id)); ?>" onsubmit="return confirm('Permanently delete this vendor? This cannot be undone.')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button class="btn btn-danger btn-sm">Delete</button>
      </form>
    </div>
  </div>
</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Contact & Account</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Full Name</td><td style="font-weight:500"><?php echo e($vendor->first_name); ?> <?php echo e($vendor->last_name); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Email</td><td><?php echo e($vendor->email); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Phone</td><td><?php echo e($vendor->phone ?: '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Joined</td><td><?php echo e($vendor->created_at ? \Carbon\Carbon::parse($vendor->created_at)->format('d M Y, H:i') : '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Rating</td><td><?php echo e($vendor->rating ?? '0'); ?> / 5 (<?php echo e($vendor->rating_count ?? 0); ?> reviews)</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Commission</td>
        <td>
          <?php if($vendor->sales_commission_percentage !== null): ?>
            <span style="font-weight:600;color:var(--primary)"><?php echo e($vendor->sales_commission_percentage); ?>%</span>
          <?php else: ?>
            <span style="color:var(--muted)">Not set</span>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

  
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Shop Info</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Shop Name</td><td style="font-weight:500"><?php echo e($vendor->shop_name); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Address</td><td><?php echo e($vendor->shop_address ?: '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Products</td><td><strong><?php echo e($productCount); ?></strong></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Min. Order</td><td><?php echo e($vendor->minimum_order_amount ? number_format($vendor->minimum_order_amount).' EGP' : '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Free Delivery Over</td><td><?php echo e($vendor->free_delivery_over_amount ? number_format($vendor->free_delivery_over_amount).' EGP' : '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Vacation</td>
        <td>
          <?php if($vendor->vacation_status): ?>
            <span class="badge badge-yellow">On Vacation</span>
            <?php if($vendor->vacation_start_date !== 'empty'): ?>
              <span style="font-size:11px;color:var(--muted);margin-left:6px"><?php echo e($vendor->vacation_start_date); ?> – <?php echo e($vendor->vacation_end_date); ?></span>
            <?php endif; ?>
          <?php else: ?>
            <span style="color:var(--muted)">No</span>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

</div>


<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Banking / Payout Info</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;font-size:13px">
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Account Holder</div>
      <div style="font-weight:500"><?php echo e($vendor->holder_name ?: '—'); ?></div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Bank Name</div>
      <div style="font-weight:500"><?php echo e($vendor->bank_name ?: '—'); ?></div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Branch</div>
      <div><?php echo e($vendor->branch ?: '—'); ?></div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Account No.</div>
      <div><?php echo e($vendor->account_no ?: '—'); ?></div>
    </div>
  </div>
</div>


<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Recent Sub-Orders</div>
  <?php if(isset($subOrders) && $subOrders->isNotEmpty()): ?>
    <div class="table-wrap" style="margin:0">
      <table>
        <thead>
          <tr>
            <th>Sub-Order</th>
            <th>Parent Order</th>
            <th>Status</th>
            <th>Total</th>
            <th>Tracking</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $subOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $sc = match($sub->status) {
                'completed'=>'badge-green','pending'=>'badge-yellow',
                'processing'=>'badge-blue','shipped'=>'badge-orange',
                'cancelled'=>'badge-red', default=>'badge-gray'
              };
            ?>
            <tr>
              <td style="font-weight:600">#<?php echo e($sub->id); ?></td>
              <td>#<?php echo e($sub->parent_order_id); ?></td>
              <td><span class="badge <?php echo e($sc); ?>"><?php echo e(ucfirst($sub->status)); ?></span></td>
              <td><?php echo e(number_format($sub->total, 2)); ?></td>
              <td style="font-size:12px;color:var(--muted)">
                <?php echo e($sub->tracking_number ?: '—'); ?>

                <?php if($sub->tracking_carrier): ?> / <?php echo e($sub->tracking_carrier); ?> <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="color:var(--muted);font-size:13px">No sub-orders yet.</div>
  <?php endif; ?>
</div>


<div class="card" style="padding:20px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">
    <div style="font-weight:600;font-size:14px">Products <span style="color:var(--muted);font-weight:400">(<?php echo e($productCount); ?> total)</span></div>
    <?php if($productCount > 10): ?>
      <a href="<?php echo e(route('admin.products')); ?>?vendor=<?php echo e($vendor->id); ?>" style="font-size:12px;color:var(--primary)">View all</a>
    <?php endif; ?>
  </div>
  <?php if($products->isEmpty()): ?>
    <div style="color:var(--muted);font-size:13px;text-align:center;padding:24px 0">No products yet.</div>
  <?php else: ?>
    <div class="table-wrap" style="margin:0">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Publish Status</th>
            <th>Acceptance</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $ac = match($p->acceptance_status ?? 'pending') {
                'approved'=>'badge-green','pending'=>'badge-yellow',
                'rejected'=>'badge-red',default=>'badge-gray'
              };
            ?>
            <tr>
              <td style="color:var(--muted);font-size:12px">#<?php echo e($p->id); ?></td>
              <td style="font-weight:500;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($p->name); ?></td>
              <td><span class="badge <?php echo e($p->status === 'publish' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e($p->status ?: '—'); ?></span></td>
              <td><span class="badge <?php echo e($ac); ?>"><?php echo e(ucfirst($p->acceptance_status ?? 'pending')); ?></span></td>
              <td style="color:var(--muted);font-size:12px"><?php echo e($p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y') : '—'); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/vendors/show.blade.php ENDPATH**/ ?>