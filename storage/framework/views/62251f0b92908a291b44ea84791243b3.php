<?php $__env->startSection('title', 'Submit Request'); ?>
<?php $__env->startSection('page-title', 'Request New Category or Brand'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:600px">
  <div style="background:var(--white);border:1px solid var(--light);border-radius:12px;padding:28px">
    <p style="color:var(--mid);font-size:13px;margin-bottom:24px;line-height:1.6">
      Can't find the category or brand you need? Submit a request and our admin team will review it.
      Once approved, it will be available for all vendors to use.
    </p>

    <?php if($errors->any()): ?>
      <div class="vs-alert vs-alert-error">
        <div>
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><?php echo e($error); ?></div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('vendor.requests.store')); ?>" id="req-form">
      <?php echo csrf_field(); ?>

      <div class="vs-form-group">
        <label class="vs-label">Request Type <span style="color:var(--red)">*</span></label>
        <select name="type" id="req-type" class="vs-input <?php echo e($errors->has('type') ? 'err' : ''); ?>" required onchange="toggleParent()">
          <option value="">— Select type —</option>
          <option value="category" <?php echo e(old('type') === 'category' ? 'selected' : ''); ?>>Category</option>
          <option value="brand" <?php echo e(old('type') === 'brand' ? 'selected' : ''); ?>>Brand</option>
        </select>
        <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="vs-err"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div class="vs-form-group">
        <label class="vs-label">Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" class="vs-input <?php echo e($errors->has('name') ? 'err' : ''); ?>"
               value="<?php echo e(old('name')); ?>" placeholder="e.g. Electronics, Nike, Furniture…" required maxlength="255">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="vs-err"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      
      <div class="vs-form-group" id="parent-group" style="display:none">
        <label class="vs-label">
          Parent Category
          <span style="color:var(--mid);font-weight:400;text-transform:none">(leave empty for top-level)</span>
        </label>
        <select name="parent_category_id" id="parent-select" class="vs-input">
          <option value="">— No parent (top-level category) —</option>
          <?php
            $topLevel = $categories->filter(fn($c) => $c->parent == 0 || $c->parent === null);
            $children = $categories->filter(fn($c) => $c->parent > 0)->keyBy('id');
          ?>
          <?php $__currentLoopData = $topLevel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat->id); ?>" <?php echo e(old('parent_category_id') == $cat->id ? 'selected' : ''); ?>>
              <?php echo e($cat->name); ?>

            </option>
            <?php $__currentLoopData = $categories->where('parent', $cat->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($child->id); ?>" <?php echo e(old('parent_category_id') == $child->id ? 'selected' : ''); ?>>
                &nbsp;&nbsp;&nbsp;↳ <?php echo e($child->name); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <div style="font-size:12px;color:var(--mid);margin-top:4px">
          Selecting a parent makes this a sub-category of the chosen one.
        </div>
        <?php $__errorArgs = ['parent_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="vs-err"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div class="vs-form-group">
        <label class="vs-label">Description <span style="color:var(--mid);font-weight:400">(optional)</span></label>
        <textarea name="description" class="vs-input" rows="3" maxlength="1000"
                  placeholder="Briefly describe what this category or brand covers…"><?php echo e(old('description')); ?></textarea>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="vs-err"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="vs-btn vs-btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Submit Request
        </button>
        <a href="<?php echo e(route('vendor.requests')); ?>" class="vs-btn vs-btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
function toggleParent() {
  const type = document.getElementById('req-type').value;
  const group = document.getElementById('parent-group');
  const sel = document.getElementById('parent-select');
  if (type === 'category') {
    group.style.display = 'block';
  } else {
    group.style.display = 'none';
    sel.value = '';
  }
}
// Run on load in case of old() repopulation
document.addEventListener('DOMContentLoaded', toggleParent);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.vendor.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/vendor/requests/create.blade.php ENDPATH**/ ?>