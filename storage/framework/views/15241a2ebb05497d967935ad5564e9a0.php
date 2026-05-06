<?php $__env->startSection('title', 'Categories, Brands & Requests'); ?>
<?php $__env->startSection('page-title', 'Categories, Brands & Requests'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Page tabs ──────────────────────────────────────────── */
.page-tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:0}
.page-tab{padding:9px 18px;font-size:13px;font-weight:600;color:var(--muted);border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;border-radius:6px 6px 0 0;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.page-tab:hover{color:var(--text)}
.page-tab.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-badge{background:var(--accent);color:#fff;font-size:9px;font-weight:800;padding:1px 5px;border-radius:10px;min-width:16px;text-align:center}
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── Summary chips ──────────────────────────────────────── */
.summary-row{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.summary-chip{display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--border);border-radius:8px;padding:10px 14px;flex:1;min-width:120px;text-decoration:none;color:inherit;transition:.13s}
.summary-chip:hover{border-color:var(--accent)}
.summary-chip-value{font-size:22px;font-weight:800;line-height:1}
.summary-chip-label{font-size:11px;color:var(--muted)}

/* ── Cat/brand item rows ────────────────────────────────── */
.cat-tree{display:flex;flex-direction:column;gap:2px}
.cat-row{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;border:1px solid transparent;transition:.13s;background:var(--card);cursor:pointer}
.cat-row:hover{border-color:var(--border);background:rgba(255,255,255,.03)}
.cat-row.is-child{margin-left:28px;border-left:2px solid var(--border);border-radius:0 8px 8px 0}

/* ── Thumbnail in row ───────────────────────────────────── */
.cat-thumb{width:36px;height:36px;border-radius:6px;object-fit:cover;background:rgba(255,255,255,.06);border:1px solid var(--border);flex-shrink:0}
.cat-thumb-placeholder{width:36px;height:36px;border-radius:6px;background:rgba(255,255,255,.06);border:1px dashed var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--muted)}

.cat-row-name{flex:1;font-size:13px;font-weight:600;min-width:0}
.cat-row-meta{font-size:11px;color:var(--muted);white-space:nowrap}
.cat-row-actions{display:flex;gap:5px;flex-shrink:0;align-items:center}

/* ── Inline forms ───────────────────────────────────────── */
.inline-edit-form{display:none;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:16px;margin-top:4px;margin-bottom:4px}
.inline-edit-form.is-child{margin-left:28px}
.inline-edit-form.open{display:block}
.inline-form-row{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end}
.inline-form-row .form-group{flex:1;min-width:140px}
.inline-form-row input,.inline-form-row select{width:100%;padding:7px 10px;font-size:12px}

/* ── Image upload widget ────────────────────────────────── */
.img-upload-zone{position:relative}
.img-preview-wrap{position:relative;display:inline-block}
.img-preview{width:72px;height:72px;border-radius:8px;object-fit:cover;border:2px solid var(--border);display:block}
.img-remove-btn{position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:var(--red);color:#fff;border:none;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;line-height:1}
.img-drop-area{width:72px;height:72px;border-radius:8px;border:2px dashed var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:.15s;background:rgba(255,255,255,.03);font-size:10px;color:var(--muted);gap:4px;text-align:center}
.img-drop-area:hover{border-color:var(--accent);color:var(--accent);background:rgba(232,93,38,.05)}
.img-drop-area svg{opacity:.5}
.img-file-input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}

/* ── Add card ───────────────────────────────────────────── */
.add-card{background:rgba(232,93,38,.05);border:1px dashed rgba(232,93,38,.3);border-radius:10px;padding:16px;margin-bottom:20px;display:none}
.add-card.open{display:block}
.add-card-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:12px}

/* ── Delete confirm strip ───────────────────────────────── */
.del-confirm{display:none;gap:5px;align-items:center;margin-top:4px;padding:6px 8px;background:rgba(239,68,68,.06);border-radius:6px}
.del-confirm.open{display:flex}

/* ── Requests ───────────────────────────────────────────── */
.req-note-form{display:none;margin-top:8px}
.req-note-form.open{display:flex;flex-direction:column;gap:6px}
.req-note-form input,.req-note-form select{padding:5px 8px;border-radius:5px;border:1px solid var(--border);background:var(--card);color:var(--text);font-size:12px;width:220px}

/* ── Misc ───────────────────────────────────────────────── */
.filter-bar{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;align-items:center}
.filter-bar input[type=search]{flex:1;min-width:180px;padding:7px 12px;font-size:13px}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $activeTab = session('tab', request('tab', 'categories')); ?>

<?php if(session('success')): ?><div class="alert alert-success">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
<?php if(session('error')): ?><div class="alert alert-error">✗ <?php echo e(session('error')); ?></div><?php endif; ?>


<div class="page-tabs">
  <a href="?tab=categories" class="page-tab <?php echo e($activeTab==='categories' ? 'active':''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Categories
    <span class="tab-badge" style="background:rgba(255,255,255,.1);color:var(--muted)"><?php echo e($parentCats->count()+$childCats->flatten()->count()); ?></span>
  </a>
  <a href="?tab=brands" class="page-tab <?php echo e($activeTab==='brands' ? 'active':''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>
    Brands
    <span class="tab-badge" style="background:rgba(255,255,255,.1);color:var(--muted)"><?php echo e($brands->count()); ?></span>
  </a>
  <a href="?tab=requests" class="page-tab <?php echo e($activeTab==='requests' ? 'active':''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Vendor Requests
    <?php if($counts['pending']>0): ?><span class="tab-badge"><?php echo e($counts['pending']); ?></span><?php endif; ?>
  </a>
</div>



<div class="tab-panel <?php echo e($activeTab==='categories' ? 'active':''); ?>">

  <div class="summary-row">
    <div class="summary-chip"><div><div class="summary-chip-value"><?php echo e($parentCats->count()); ?></div><div class="summary-chip-label">Parent Categories</div></div></div>
    <div class="summary-chip"><div><div class="summary-chip-value"><?php echo e($childCats->flatten()->count()); ?></div><div class="summary-chip-label">Sub-categories</div></div></div>
    <div class="summary-chip" style="flex:2;justify-content:flex-end">
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-cat-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Category
      </button>
    </div>
  </div>

  
  <div class="add-card" id="add-cat-form">
    <div class="add-card-title">New Category</div>
    <form method="POST" action="<?php echo e(route('admin.categories.store')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="inline-form-row" style="align-items:flex-start">

        
        <div class="form-group" style="flex:0 0 auto">
          <label>Image</label>
          <div class="img-upload-zone">
            <div class="img-preview-wrap" id="add-cat-preview-wrap" style="display:none">
              <img src="" id="add-cat-preview-img" class="img-preview">
              <button type="button" class="img-remove-btn" onclick="clearImage('add-cat')">✕</button>
            </div>
            <label class="img-drop-area" id="add-cat-drop" for="add-cat-file">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Upload
            </label>
            <input type="file" name="image" id="add-cat-file" accept="image/*" class="img-file-input" style="position:static;opacity:1;width:0;height:0;overflow:hidden" onchange="previewImage(this,'add-cat')">
          </div>
        </div>

        <div class="form-group" style="flex:2;min-width:160px">
          <label>Name *</label>
          <input type="text" name="name" required placeholder="e.g. Summer Collection">
        </div>
        <div class="form-group">
          <label>Parent Category</label>
          <select name="parent_id">
            <option value="">— Top-level —</option>
            <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($pc->id); ?>"><?php echo e($pc->name); ?></option>
              <?php if(isset($childCats[$pc->id])): ?>
                <?php $__currentLoopData = $childCats[$pc->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($cc->id); ?>">&nbsp;&nbsp;↳ <?php echo e($cc->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group" style="max-width:80px">
          <label>Order</label>
          <input type="number" name="menu_order" value="0" min="0">
        </div>
        <div class="form-group" style="flex:2">
          <label>Description</label>
          <input type="text" name="description" placeholder="Optional">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
        </div>
      </div>
    </form>
  </div>

  <div class="filter-bar">
    <input type="search" placeholder="Filter categories…" oninput="filterItems('cat-tree','.cat-item',this.value)">
    <span style="font-size:12px;color:var(--muted)">Click a row to edit inline</span>
  </div>

  <div class="cat-tree" id="cat-tree">
    <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $pCount    = $catCounts[$parent->id] ?? 0;
        $childList = $childCats[$parent->id] ?? collect();
        $hasKids   = $childList->count() > 0;
        $imgUrl    = $parent->image ? Storage::disk('public')->url($parent->image) : null;
      ?>

      
      <div class="cat-row cat-item" data-name="<?php echo e(strtolower($parent->name)); ?>"
           onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')">
        <?php if($imgUrl): ?>
          <img src="<?php echo e($imgUrl); ?>" class="cat-thumb" alt="">
        <?php else: ?>
          <div class="cat-thumb-placeholder">🖼</div>
        <?php endif; ?>
        <div class="cat-row-name">
          <?php echo e($parent->name); ?>

          <?php if($hasKids): ?><span class="badge badge-blue" style="margin-left:6px;font-size:9px"><?php echo e($childList->count()); ?> sub</span><?php endif; ?>
        </div>
        <div class="cat-row-meta"><?php echo e($pCount); ?> products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('del-<?php echo e($parent->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
          </button>
        </div>
      </div>

      
      <div class="inline-edit-form" id="edit-<?php echo e($parent->id); ?>" onclick="event.stopPropagation()">
        <form method="POST" action="<?php echo e(route('admin.categories.update', $parent->id)); ?>" enctype="multipart/form-data">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <div class="inline-form-row" style="align-items:flex-start">

            <div class="form-group" style="flex:0 0 auto">
              <label>Image</label>
              <div class="img-upload-zone">
                <div class="img-preview-wrap" id="ec<?php echo e($parent->id); ?>-preview-wrap" style="<?php echo e($imgUrl ? '' : 'display:none'); ?>">
                  <img src="<?php echo e($imgUrl ?? ''); ?>" id="ec<?php echo e($parent->id); ?>-preview-img" class="img-preview">
                  <button type="button" class="img-remove-btn" onclick="clearImage('ec<?php echo e($parent->id); ?>')">✕</button>
                </div>
                <label class="img-drop-area" id="ec<?php echo e($parent->id); ?>-drop" for="ec<?php echo e($parent->id); ?>-file"
                       style="<?php echo e($imgUrl ? 'display:none' : ''); ?>">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  Upload
                </label>
                <input type="file" name="image" id="ec<?php echo e($parent->id); ?>-file" accept="image/*"
                       style="width:0;height:0;overflow:hidden;position:absolute"
                       onchange="previewImage(this,'ec<?php echo e($parent->id); ?>')">
                <input type="hidden" name="remove_image" id="ec<?php echo e($parent->id); ?>-remove" value="">
              </div>
            </div>

            <div class="form-group" style="flex:2;min-width:140px">
              <label>Name *</label>
              <input type="text" name="name" value="<?php echo e($parent->name); ?>" required>
            </div>
            <div class="form-group">
              <label>Parent</label>
              <select name="parent_id">
                <option value="">— Top-level —</option>
                <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($pc->id===$parent->id): ?><?php continue; ?>@endif
                  <option value="<?php echo e($pc->id); ?>" <?php echo e($parent->parent==$pc->id ? 'selected':''); ?>><?php echo e($pc->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group" style="max-width:80px">
              <label>Order</label>
              <input type="number" name="menu_order" value="<?php echo e($parent->menu_order ?? 0); ?>" min="0">
            </div>
            <div class="form-group" style="flex:2">
              <label>Description</label>
              <input type="text" name="description" value="<?php echo e($parent->description ?? ''); ?>">
            </div>
            <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      
      <div class="del-confirm" id="del-<?php echo e($parent->id); ?>">
        <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($parent->name); ?>"?<?php if($hasKids): ?> (<?php echo e($childList->count()); ?> sub-cats will move to top-level)<?php endif; ?></span>
        <form method="POST" action="<?php echo e(route('admin.categories.destroy', $parent->id)); ?>" style="display:contents">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <?php if($hasKids): ?><input type="hidden" name="force" value="1"><?php endif; ?>
          <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-<?php echo e($parent->id); ?>')">Cancel</button>
      </div>

      
      <?php $__currentLoopData = $childList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $cCount  = $catCounts[$child->id] ?? 0;
          $cImgUrl = $child->image ? Storage::disk('public')->url($child->image) : null;
        ?>
        <div class="cat-row cat-item is-child" data-name="<?php echo e(strtolower($child->name)); ?>"
             onclick="toggleEdit('edit-<?php echo e($child->id); ?>')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11" style="color:var(--muted);flex-shrink:0"><polyline points="9 18 3 12 9 6"/></svg>
          <?php if($cImgUrl): ?>
            <img src="<?php echo e($cImgUrl); ?>" class="cat-thumb" alt="" style="width:28px;height:28px">
          <?php else: ?>
            <div class="cat-thumb-placeholder" style="width:28px;height:28px;font-size:11px">🖼</div>
          <?php endif; ?>
          <div class="cat-row-name" style="font-weight:500;color:var(--muted)"><?php echo e($child->name); ?></div>
          <div class="cat-row-meta"><?php echo e($cCount); ?> products</div>
          <div class="cat-row-actions" onclick="event.stopPropagation()">
            <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($child->id); ?>')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('del-<?php echo e($child->id); ?>')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
            </button>
          </div>
        </div>

        <div class="inline-edit-form is-child" id="edit-<?php echo e($child->id); ?>" onclick="event.stopPropagation()">
          <form method="POST" action="<?php echo e(route('admin.categories.update', $child->id)); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <div class="inline-form-row" style="align-items:flex-start">

              <div class="form-group" style="flex:0 0 auto">
                <label>Image</label>
                <div class="img-upload-zone">
                  <div class="img-preview-wrap" id="ec<?php echo e($child->id); ?>-preview-wrap" style="<?php echo e($cImgUrl ? '' : 'display:none'); ?>">
                    <img src="<?php echo e($cImgUrl ?? ''); ?>" id="ec<?php echo e($child->id); ?>-preview-img" class="img-preview">
                    <button type="button" class="img-remove-btn" onclick="clearImage('ec<?php echo e($child->id); ?>')">✕</button>
                  </div>
                  <label class="img-drop-area" id="ec<?php echo e($child->id); ?>-drop" for="ec<?php echo e($child->id); ?>-file"
                         style="<?php echo e($cImgUrl ? 'display:none' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Upload
                  </label>
                  <input type="file" name="image" id="ec<?php echo e($child->id); ?>-file" accept="image/*"
                         style="width:0;height:0;overflow:hidden;position:absolute"
                         onchange="previewImage(this,'ec<?php echo e($child->id); ?>')">
                  <input type="hidden" name="remove_image" id="ec<?php echo e($child->id); ?>-remove" value="">
                </div>
              </div>

              <div class="form-group" style="flex:2;min-width:130px">
                <label>Name *</label>
                <input type="text" name="name" value="<?php echo e($child->name); ?>" required>
              </div>
              <div class="form-group">
                <label>Parent</label>
                <select name="parent_id">
                  <option value="">— Top-level —</option>
                  <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($pc->id); ?>" <?php echo e($child->parent==$pc->id ? 'selected':''); ?>><?php echo e($pc->name); ?></option>
                    <?php if(isset($childCats[$pc->id])): ?>
                      <?php $__currentLoopData = $childCats[$pc->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($cc->id===$child->id): ?><?php continue; ?>@endif
                        <option value="<?php echo e($cc->id); ?>">&nbsp;&nbsp;↳ <?php echo e($cc->name); ?></option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div class="form-group" style="max-width:80px">
                <label>Order</label>
                <input type="number" name="menu_order" value="<?php echo e($child->menu_order ?? 0); ?>" min="0">
              </div>
              <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
                <button type="submit" class="btn btn-success btn-sm">Save</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($child->id); ?>')">Cancel</button>
              </div>
            </div>
          </form>
        </div>

        <div class="del-confirm is-child" id="del-<?php echo e($child->id); ?>">
          <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($child->name); ?>"?</span>
          <form method="POST" action="<?php echo e(route('admin.categories.destroy', $child->id)); ?>" style="display:contents">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
          </form>
          <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-<?php echo e($child->id); ?>')">Cancel</button>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>



<div class="tab-panel <?php echo e($activeTab==='brands' ? 'active':''); ?>">

  <div class="summary-row">
    <div class="summary-chip"><div><div class="summary-chip-value"><?php echo e($brands->count()); ?></div><div class="summary-chip-label">Total Brands</div></div></div>
    <div class="summary-chip" style="flex:2;justify-content:flex-end">
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-brand-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Brand
      </button>
    </div>
  </div>

  <div class="add-card" id="add-brand-form">
    <div class="add-card-title">New Brand</div>
    <form method="POST" action="<?php echo e(route('admin.brands.store')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="inline-form-row" style="align-items:flex-start">
        <div class="form-group" style="flex:0 0 auto">
          <label>Logo / Image</label>
          <div class="img-upload-zone">
            <div class="img-preview-wrap" id="add-brand-preview-wrap" style="display:none">
              <img src="" id="add-brand-preview-img" class="img-preview">
              <button type="button" class="img-remove-btn" onclick="clearImage('add-brand')">✕</button>
            </div>
            <label class="img-drop-area" id="add-brand-drop" for="add-brand-file">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Upload
            </label>
            <input type="file" name="image" id="add-brand-file" accept="image/*"
                   style="width:0;height:0;overflow:hidden;position:absolute"
                   onchange="previewImage(this,'add-brand')">
          </div>
        </div>
        <div class="form-group" style="flex:1;min-width:160px">
          <label>Brand Name *</label>
          <input type="text" name="name" required placeholder="e.g. Nike">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
        </div>
      </div>
    </form>
  </div>

  <div class="filter-bar">
    <input type="search" placeholder="Filter brands…" oninput="filterItems('brand-tree','.brand-item',this.value)">
  </div>

  <div class="cat-tree" id="brand-tree">
    <?php $__empty_1 = true; $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $bCount  = $brandCounts[$brand->id] ?? 0;
        $bImgUrl = ($brand->image ?? null) ? Storage::disk('public')->url($brand->image) : null;
      ?>
      <div class="cat-row brand-item" data-name="<?php echo e(strtolower($brand->name)); ?>"
           onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">
        <?php if($bImgUrl): ?>
          <img src="<?php echo e($bImgUrl); ?>" class="cat-thumb" alt="" style="background:#fff">
        <?php else: ?>
          <div class="cat-thumb-placeholder">🏷</div>
        <?php endif; ?>
        <div class="cat-row-name"><?php echo e($brand->name); ?></div>
        <div class="cat-row-meta"><?php echo e($bCount); ?> products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('bdel-<?php echo e($brand->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
          </button>
        </div>
      </div>

      <div class="inline-edit-form" id="bedit-<?php echo e($brand->id); ?>" onclick="event.stopPropagation()">
        <form method="POST" action="<?php echo e(route('admin.brands.update', $brand->id)); ?>" enctype="multipart/form-data">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <div class="inline-form-row" style="align-items:flex-start">
            <div class="form-group" style="flex:0 0 auto">
              <label>Logo / Image</label>
              <div class="img-upload-zone">
                <div class="img-preview-wrap" id="eb<?php echo e($brand->id); ?>-preview-wrap" style="<?php echo e($bImgUrl ? '' : 'display:none'); ?>">
                  <img src="<?php echo e($bImgUrl ?? ''); ?>" id="eb<?php echo e($brand->id); ?>-preview-img" class="img-preview" style="background:#fff">
                  <button type="button" class="img-remove-btn" onclick="clearImage('eb<?php echo e($brand->id); ?>')">✕</button>
                </div>
                <label class="img-drop-area" id="eb<?php echo e($brand->id); ?>-drop" for="eb<?php echo e($brand->id); ?>-file"
                       style="<?php echo e($bImgUrl ? 'display:none' : ''); ?>">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  Upload
                </label>
                <input type="file" name="image" id="eb<?php echo e($brand->id); ?>-file" accept="image/*"
                       style="width:0;height:0;overflow:hidden;position:absolute"
                       onchange="previewImage(this,'eb<?php echo e($brand->id); ?>')">
                <input type="hidden" name="remove_image" id="eb<?php echo e($brand->id); ?>-remove" value="">
              </div>
            </div>
            <div class="form-group" style="flex:1;min-width:140px">
              <label>Name *</label>
              <input type="text" name="name" value="<?php echo e($brand->name); ?>" required>
            </div>
            <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      <div class="del-confirm" id="bdel-<?php echo e($brand->id); ?>">
        <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($brand->name); ?>"?</span>
        <form method="POST" action="<?php echo e(route('admin.brands.destroy', $brand->id)); ?>" style="display:contents">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('bdel-<?php echo e($brand->id); ?>')">Cancel</button>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="card" style="text-align:center;padding:32px;color:var(--muted)">No brands yet.</div>
    <?php endif; ?>
  </div>
</div>



<div class="tab-panel <?php echo e($activeTab==='requests' ? 'active':''); ?>">

  <div class="summary-row">
    <?php $__currentLoopData = ['pending'=>['Pending','badge-yellow'],'approved'=>['Approved','badge-green'],'rejected'=>['Rejected','badge-red']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s=>[$label,$cls]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="?tab=requests&status=<?php echo e($s); ?>" class="summary-chip" style="<?php echo e($status===$s ? 'border-color:var(--accent)':''); ?>">
      <div><div class="summary-chip-value"><?php echo e($counts[$s]); ?></div><div class="summary-chip-label"><?php echo e($label); ?></div></div>
      <?php if($status===$s): ?><span class="badge <?php echo e($cls); ?>" style="margin-left:auto">Active</span><?php endif; ?>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <form method="GET" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="tab" value="requests">
    <div class="form-group">
      <label>Status</label>
      <select name="status" onchange="this.form.submit()" style="min-width:130px">
        <option value="pending"  <?php echo e($status==='pending'  ? 'selected':''); ?>>Pending</option>
        <option value="approved" <?php echo e($status==='approved' ? 'selected':''); ?>>Approved</option>
        <option value="rejected" <?php echo e($status==='rejected' ? 'selected':''); ?>>Rejected</option>
        <option value=""         <?php echo e($status===''         ? 'selected':''); ?>>All</option>
      </select>
    </div>
    <div class="form-group">
      <label>Type</label>
      <select name="type" onchange="this.form.submit()" style="min-width:130px">
        <option value=""         <?php echo e($type===''         ? 'selected':''); ?>>All Types</option>
        <option value="category" <?php echo e($type==='category' ? 'selected':''); ?>>Category</option>
        <option value="brand"    <?php echo e($type==='brand'    ? 'selected':''); ?>>Brand</option>
      </select>
    </div>
  </form>

  <?php if($requests->isEmpty()): ?>
    <div class="card" style="text-align:center;padding:48px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="color:var(--muted);margin:0 auto 12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <p style="color:var(--muted)">No <?php echo e($status ?: ''); ?> requests found.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Type</th><th>Name</th><th>Parent</th>
            <th>Vendor</th><th>Status</th><th>Date</th><th>Actions / Note</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="color:var(--muted)"><?php echo e($req->id); ?></td>
            <td><?php if($req->type==='category'): ?><span class="badge badge-blue">Category</span><?php else: ?><span class="badge badge-purple">Brand</span><?php endif; ?></td>
            <td style="font-weight:600"><?php echo e($req->name); ?></td>
            <td style="font-size:12px">
              <?php if($req->type==='category'): ?>
                <?php if($req->parent_category_name): ?><span class="badge badge-green">↳ <?php echo e($req->parent_category_name); ?></span>
                <?php else: ?><span style="color:var(--muted)">Top-level</span><?php endif; ?>
              <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
            </td>
            <td style="font-size:12px"><?php echo e($req->vendor_name ?? '—'); ?></td>
            <td><?php if($req->status==='pending'): ?><span class="badge badge-yellow">Pending</span><?php elseif($req->status==='approved'): ?><span class="badge badge-green">Approved</span><?php else: ?><span class="badge badge-red">Rejected</span><?php endif; ?></td>
            <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?php echo e(\Carbon\Carbon::parse($req->created_at)->format('M d, Y')); ?></td>
            <td>
              <?php if($req->status==='pending'): ?>
              <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start">
                <div style="display:flex;gap:5px">
                  <button onclick="toggleNote('req-a-<?php echo e($req->id); ?>')" class="btn btn-sm btn-success">✓ Approve</button>
                  <button onclick="toggleNote('req-r-<?php echo e($req->id); ?>')" class="btn btn-sm btn-danger">✗ Reject</button>
                </div>
                <div id="req-a-<?php echo e($req->id); ?>" class="req-note-form">
                  <form method="POST" action="<?php echo e(route('admin.cbr.approve', $req->id)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <?php if($req->type==='category'): ?>
                    <div>
                      <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:2px">Override parent</label>
                      <select name="parent_category_id">
                        <option value="">— Keep as requested —</option>
                        <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($cat->id); ?>" <?php echo e($req->parent_category_id==$cat->id ? 'selected':''); ?>><?php echo e($cat->name); ?></option>
                          <?php if(isset($childCats[$cat->id])): ?>
                            <?php $__currentLoopData = $childCats[$cat->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($cc->id); ?>" <?php echo e($req->parent_category_id==$cc->id ? 'selected':''); ?>>&nbsp;&nbsp;↳ <?php echo e($cc->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                    </div>
                    <?php endif; ?>
                    <input type="text" name="admin_note" placeholder="Note (optional)">
                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                  </form>
                </div>
                <div id="req-r-<?php echo e($req->id); ?>" class="req-note-form">
                  <form method="POST" action="<?php echo e(route('admin.cbr.reject', $req->id)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <input type="text" name="admin_note" placeholder="Reason (optional)">
                    <button type="submit" class="btn btn-sm btn-danger">Confirm</button>
                  </form>
                </div>
              </div>
              <?php else: ?>
                <span style="color:var(--muted);font-size:12px"><?php echo e($req->admin_note ?: '—'); ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <?php if($requests->hasPages()): ?><div class="pagination" style="margin-top:16px"><?php echo e($requests->links()); ?></div><?php endif; ?>
  <?php endif; ?>
</div>

<script>
function toggleEdit(id){const el=document.getElementById(id);el.classList.toggle('open');if(el.classList.contains('open')){const i=el.querySelector('input[name="name"]');if(i)i.focus()}}
function toggleDel(id){document.getElementById(id).classList.toggle('open')}
function toggleAddForm(id){const el=document.getElementById(id);el.classList.toggle('open');if(el.classList.contains('open')){const i=el.querySelector('input[name="name"]');if(i)i.focus()}}
function toggleNote(id){document.getElementById(id).classList.toggle('open')}
function filterItems(treeId,selector,val){val=val.toLowerCase();document.querySelectorAll('#'+treeId+' '+selector).forEach(function(el){el.style.display=el.dataset.name.includes(val)?'':'none'})}

/* ── Image preview / clear ──────────────────────────────── */
function previewImage(input, prefix) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById(prefix+'-preview-img').src = e.target.result;
    document.getElementById(prefix+'-preview-wrap').style.display = '';
    const drop = document.getElementById(prefix+'-drop');
    if (drop) drop.style.display = 'none';
    const rm = document.getElementById(prefix+'-remove');
    if (rm) rm.value = '';
  };
  reader.readAsDataURL(input.files[0]);
}

function clearImage(prefix) {
  document.getElementById(prefix+'-preview-img').src = '';
  document.getElementById(prefix+'-preview-wrap').style.display = 'none';
  const drop = document.getElementById(prefix+'-drop');
  if (drop) drop.style.display = '';
  const fileInput = document.getElementById(prefix+'-file');
  if (fileInput) fileInput.value = '';
  const rm = document.getElementById(prefix+'-remove');
  if (rm) rm.value = '1';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/category-brand-requests/index.blade.php ENDPATH**/ ?>