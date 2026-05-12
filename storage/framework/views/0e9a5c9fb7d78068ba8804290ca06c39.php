<?php if($paginator->hasPages()): ?>
  <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(is_string($element)): ?>
      <span class="dots"><?php echo e($element); ?></span>
    <?php endif; ?>
    <?php if(is_array($element)): ?>
      <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($page == $paginator->currentPage()): ?>
          <span class="current"><?php echo e($page); ?></span>
        <?php else: ?>
          <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/admin/pagination.blade.php ENDPATH**/ ?>