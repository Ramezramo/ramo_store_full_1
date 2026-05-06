<?php $__env->startSection('title', 'Categories, Brands & Requests'); ?>
<?php $__env->startSection('page-title', 'Categories, Brands & Requests'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Page tabs ────────────────────────────────────────────── */
.page-tabs {
  display: flex; gap: 4px; margin-bottom: 24px;
  border-bottom: 1px solid var(--border); padding-bottom: 0;
}
.page-tab {
  padding: 9px 18px; font-size: 13px; font-weight: 600;
  color: var(--muted); border: none; background: none; cursor: pointer;
  border-bottom: 2px solid transparent; margin-bottom: -1px;
  transition: color .15s, border-color .15s; border-radius: 6px 6px 0 0;
  display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
}
.page-tab:hover { color: var(--text); }
.page-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
.page-tab .tab-badge {
  background: var(--accent); color: #fff; font-size: 9px; font-weight: 800;
  padding: 1px 5px; border-radius: 10px; min-width: 16px; text-align: center;
}

/* ── Tab panels ───────────────────────────────────────────── */
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Category tree ────────────────────────────────────────── */
.cat-tree { display: flex; flex-direction: column; gap: 2px; }
.cat-row {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 8px;
  border: 1px solid transparent; transition: .13s;
  background: var(--card);
}
.cat-row:hover { border-color: var(--border); background: rgba(255,255,255,.03); }
.cat-row.is-child { margin-left: 28px; border-left: 2px solid var(--border); border-radius: 0 8px 8px 0; }
.cat-row-name { flex: 1; font-size: 13px; font-weight: 600; min-width: 0; }
.cat-row-meta { font-size: 11px; color: var(--muted); white-space: nowrap; }
.cat-row-actions { display: flex; gap: 5px; flex-shrink: 0; align-items: center; }

/* ── Inline edit form ─────────────────────────────────────── */
.inline-edit-form {
  display: none; background: rgba(255,255,255,.04);
  border: 1px solid var(--border); border-radius: 8px;
  padding: 14px; margin-top: 4px; margin-bottom: 4px;
}
.inline-edit-form.is-child { margin-left: 28px; }
.inline-edit-form.open { display: block; }
.inline-form-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; }
.inline-form-row .form-group { flex: 1; min-width: 140px; }
.inline-form-row input, .inline-form-row select {
  width: 100%; padding: 7px 10px; font-size: 12px;
}

/* ── Add form card ────────────────────────────────────────── */
.add-card {
  background: rgba(232,93,38,.06); border: 1px dashed rgba(232,93,38,.3);
  border-radius: 10px; padding: 16px; margin-bottom: 20px;
  display: none;
}
.add-card.open { display: block; }

/* ── Request rows ─────────────────────────────────────────── */
.req-note-form { display:none; margin-top:8px; }
.req-note-form.open { display:flex; flex-direction:column; gap:6px; }
.req-note-form input,.req-note-form select {
  padding:5px 8px; border-radius:5px; border:1px solid var(--border);
  background:var(--card); color:var(--text); font-size:12px; width:220px;
}

/* ── Stat summary row ─────────────────────────────────────── */
.summary-row {
  display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
}
.summary-chip {
  display: flex; align-items: center; gap: 8px;
  background: var(--card); border: 1px solid var(--border);
  border-radius: 8px; padding: 10px 14px; flex: 1; min-width: 120px;
}
.summary-chip-value { font-size: 22px; font-weight: 800; line-height: 1; }
.summary-chip-label { font-size: 11px; color: var(--muted); }

/* ── Delete confirm ───────────────────────────────────────── */
.del-confirm { display:none; gap:5px; align-items:center; margin-top:5px; }
.del-confirm.open { display:flex; }

/* ── Search/filter bar ────────────────────────────────────── */
.filter-bar { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.filter-bar input[type=search] { flex:1; min-width:180px; padding:7px 12px; font-size:13px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
  $activeTab = session('tab', request('tab', 'categories'));
?>


<?php if(session('success')): ?>
  <div class="alert alert-success">✓ <?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
  <div class="alert alert-error">✗ <?php echo e(session('error')); ?></div>
<?php endif; ?>


<div class="page-tabs">
  <a href="?tab=categories" class="page-tab <?php echo e($activeTab === 'categories' ? 'active' : ''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Categories
    <span class="tab-badge" style="background:rgba(255,255,255,.12);color:var(--muted)"><?php echo e($parentCats->count() + $childCats->flatten()->count()); ?></span>
  </a>
  <a href="?tab=brands" class="page-tab <?php echo e($activeTab === 'brands' ? 'active' : ''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>
    Brands
    <span class="tab-badge" style="background:rgba(255,255,255,.12);color:var(--muted)"><?php echo e($brands->count()); ?></span>
  </a>
  <a href="?tab=requests" class="page-tab <?php echo e($activeTab === 'requests' ? 'active' : ''); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Vendor Requests
    <?php if($counts['pending'] > 0): ?>
      <span class="tab-badge"><?php echo e($counts['pending']); ?></span>
    <?php endif; ?>
  </a>
</div>



<div class="tab-panel <?php echo e($activeTab === 'categories' ? 'active' : ''); ?>">

  
  <div class="summary-row">
    <div class="summary-chip">
      <div><div class="summary-chip-value"><?php echo e($parentCats->count()); ?></div><div class="summary-chip-label">Parent Categories</div></div>
    </div>
    <div class="summary-chip">
      <div><div class="summary-chip-value"><?php echo e($childCats->flatten()->count()); ?></div><div class="summary-chip-label">Sub-categories</div></div>
    </div>
    <div class="summary-chip" style="flex:2">
      <div style="flex:1"></div>
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-cat-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Category
      </button>
    </div>
  </div>

  
  <div class="add-card" id="add-cat-form">
    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:12px">New Category</div>
    <form method="POST" action="<?php echo e(route('admin.categories.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="inline-form-row">
        <div class="form-group">
          <label>Name *</label>
          <input type="text" name="name" required placeholder="e.g. Summer Collection" autofocus>
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
        <div class="form-group" style="max-width:90px">
          <label>Order</label>
          <input type="number" name="menu_order" value="0" min="0" style="width:80px">
        </div>
        <div class="form-group">
          <label>Description</label>
          <input type="text" name="description" placeholder="Optional">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create Category</button>
        </div>
      </div>
    </form>
  </div>

  
  <div class="filter-bar">
    <input type="search" id="cat-search" placeholder="Filter categories…" oninput="filterCats(this.value)">
    <span style="font-size:12px;color:var(--muted)">Click a row to edit it inline</span>
  </div>

  
  <div class="cat-tree" id="cat-tree">
    <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $pCount = $catCounts[$parent->id] ?? 0;
        $childList = $childCats[$parent->id] ?? collect();
        $hasChildren = $childList->count() > 0;
      ?>

      
      <div class="cat-row cat-item" data-name="<?php echo e(strtolower($parent->name)); ?>"
           onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')">
        <div class="cat-row-name">
          <span><?php echo e($parent->name); ?></span>
          <?php if($hasChildren): ?>
            <span class="badge badge-blue" style="margin-left:6px;font-size:9px"><?php echo e($childList->count()); ?> sub</span>
          <?php endif; ?>
        </div>
        <div class="cat-row-meta"><?php echo e($pCount); ?> products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')" title="Edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="toggleDel('del-<?php echo e($parent->id); ?>')" title="Delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          </button>
        </div>
      </div>

      
      <div class="inline-edit-form" id="edit-<?php echo e($parent->id); ?>" onclick="event.stopPropagation()">
        <form method="POST" action="<?php echo e(route('admin.categories.update', $parent->id)); ?>">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <div class="inline-form-row">
            <div class="form-group">
              <label>Name *</label>
              <input type="text" name="name" value="<?php echo e($parent->name); ?>" required>
            </div>
            <div class="form-group">
              <label>Parent Category</label>
              <select name="parent_id">
                <option value="">— Top-level —</option>
                <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($pc->id === $parent->id): ?> <?php continue; ?> <?php endif; ?>
                  <option value="<?php echo e($pc->id); ?>" <?php echo e($parent->parent == $pc->id ? 'selected' : ''); ?>><?php echo e($pc->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group" style="max-width:90px">
              <label>Order</label>
              <input type="number" name="menu_order" value="<?php echo e($parent->menu_order ?? 0); ?>" min="0" style="width:80px">
            </div>
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="description" value="<?php echo e($parent->description ?? ''); ?>">
            </div>
            <div class="form-group" style="justify-content:flex-end;gap:5px;flex-direction:row">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($parent->id); ?>')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      
      <div class="del-confirm" id="del-<?php echo e($parent->id); ?>" onclick="event.stopPropagation()">
        <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($parent->name); ?>"?<?php if($hasChildren): ?> Has <?php echo e($childList->count()); ?> sub-categories.<?php endif; ?></span>
        <form method="POST" action="<?php echo e(route('admin.categories.destroy', $parent->id)); ?>" style="display:contents">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <?php if($hasChildren): ?><input type="hidden" name="force" value="1"><?php endif; ?>
          <button type="submit" class="btn btn-danger btn-sm">Confirm Delete</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-<?php echo e($parent->id); ?>')">Cancel</button>
      </div>

      
      <?php $__currentLoopData = $childList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $cCount = $catCounts[$child->id] ?? 0; ?>
        <div class="cat-row cat-item is-child" data-name="<?php echo e(strtolower($child->name)); ?>"
             onclick="toggleEdit('edit-<?php echo e($child->id); ?>')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="color:var(--muted);flex-shrink:0"><polyline points="9 18 3 12 9 6"/></svg>
          <div class="cat-row-name" style="font-weight:500;color:var(--muted)"><?php echo e($child->name); ?></div>
          <div class="cat-row-meta"><?php echo e($cCount); ?> products</div>
          <div class="cat-row-actions" onclick="event.stopPropagation()">
            <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($child->id); ?>')" title="Edit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="toggleDel('del-<?php echo e($child->id); ?>')" title="Delete">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
            </button>
          </div>
        </div>

        
        <div class="inline-edit-form is-child" id="edit-<?php echo e($child->id); ?>" onclick="event.stopPropagation()">
          <form method="POST" action="<?php echo e(route('admin.categories.update', $child->id)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <div class="inline-form-row">
              <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="<?php echo e($child->name); ?>" required>
              </div>
              <div class="form-group">
                <label>Parent Category</label>
                <select name="parent_id">
                  <option value="">— Top-level —</option>
                  <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($pc->id); ?>" <?php echo e($child->parent == $pc->id ? 'selected' : ''); ?>><?php echo e($pc->name); ?></option>
                    <?php if(isset($childCats[$pc->id])): ?>
                      <?php $__currentLoopData = $childCats[$pc->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($cc->id === $child->id): ?> <?php continue; ?> <?php endif; ?>
                        <option value="<?php echo e($cc->id); ?>" <?php echo e($child->parent == $cc->id ? 'selected' : ''); ?>>&nbsp;&nbsp;↳ <?php echo e($cc->name); ?></option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div class="form-group" style="max-width:90px">
                <label>Order</label>
                <input type="number" name="menu_order" value="<?php echo e($child->menu_order ?? 0); ?>" min="0" style="width:80px">
              </div>
              <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" value="<?php echo e($child->description ?? ''); ?>">
              </div>
              <div class="form-group" style="justify-content:flex-end;gap:5px;flex-direction:row">
                <button type="submit" class="btn btn-success btn-sm">Save</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-<?php echo e($child->id); ?>')">Cancel</button>
              </div>
            </div>
          </form>
        </div>

        
        <div class="del-confirm is-child" id="del-<?php echo e($child->id); ?>" onclick="event.stopPropagation()">
          <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($child->name); ?>"?</span>
          <form method="POST" action="<?php echo e(route('admin.categories.destroy', $child->id)); ?>" style="display:contents">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-danger btn-sm">Confirm Delete</button>
          </form>
          <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-<?php echo e($child->id); ?>')">Cancel</button>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>



<div class="tab-panel <?php echo e($activeTab === 'brands' ? 'active' : ''); ?>">

  <div class="summary-row">
    <div class="summary-chip">
      <div><div class="summary-chip-value"><?php echo e($brands->count()); ?></div><div class="summary-chip-label">Total Brands</div></div>
    </div>
    <div class="summary-chip" style="flex:2">
      <div style="flex:1"></div>
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-brand-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Brand
      </button>
    </div>
  </div>

  <div class="add-card" id="add-brand-form">
    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:12px">New Brand</div>
    <form method="POST" action="<?php echo e(route('admin.brands.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="inline-form-row">
        <div class="form-group">
          <label>Brand Name *</label>
          <input type="text" name="name" required placeholder="e.g. Nike">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create Brand</button>
        </div>
      </div>
    </form>
  </div>

  <div class="filter-bar">
    <input type="search" id="brand-search" placeholder="Filter brands…" oninput="filterBrands(this.value)">
  </div>

  <div class="cat-tree" id="brand-tree">
    <?php $__empty_1 = true; $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php $bCount = $brandCounts[$brand->id] ?? 0; ?>
      <div class="cat-row brand-item" data-name="<?php echo e(strtolower($brand->name)); ?>"
           onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">
        <div class="cat-row-name"><?php echo e($brand->name); ?></div>
        <div class="cat-row-meta"><?php echo e($bCount); ?> products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="toggleDel('bdel-<?php echo e($brand->id); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
          </button>
        </div>
      </div>

      <div class="inline-edit-form" id="bedit-<?php echo e($brand->id); ?>" onclick="event.stopPropagation()">
        <form method="POST" action="<?php echo e(route('admin.brands.update', $brand->id)); ?>">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <div class="inline-form-row">
            <div class="form-group">
              <label>Name *</label>
              <input type="text" name="name" value="<?php echo e($brand->name); ?>" required>
            </div>
            <div class="form-group" style="justify-content:flex-end;gap:5px;flex-direction:row">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-<?php echo e($brand->id); ?>')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      <div class="del-confirm" id="bdel-<?php echo e($brand->id); ?>" onclick="event.stopPropagation()">
        <span style="font-size:12px;color:var(--red)">Delete "<?php echo e($brand->name); ?>"?</span>
        <form method="POST" action="<?php echo e(route('admin.brands.destroy', $brand->id)); ?>" style="display:contents">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button type="submit" class="btn btn-danger btn-sm">Confirm Delete</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('bdel-<?php echo e($brand->id); ?>')">Cancel</button>
      </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="card" style="text-align:center;padding:32px;color:var(--muted)">No brands yet.</div>
    <?php endif; ?>
  </div>
</div>



<div class="tab-panel <?php echo e($activeTab === 'requests' ? 'active' : ''); ?>">

  
  <div class="summary-row">
    <?php $__currentLoopData = ['pending'=>['Pending','badge-yellow'],'approved'=>['Approved','badge-green'],'rejected'=>['Rejected','badge-red']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s=>[$label,$cls]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="?tab=requests&status=<?php echo e($s); ?>" class="summary-chip" style="text-decoration:none;<?php echo e($status===$s ? 'border-color:var(--accent)' : ''); ?>">
      <div>
        <div class="summary-chip-value"><?php echo e($counts[$s]); ?></div>
        <div class="summary-chip-label"><?php echo e($label); ?></div>
      </div>
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
            <th>Vendor</th><th>Status</th><th>Date</th>
            <th>Actions / Note</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="color:var(--muted)"><?php echo e($req->id); ?></td>
            <td>
              <?php if($req->type === 'category'): ?><span class="badge badge-blue">Category</span>
              <?php else: ?><span class="badge badge-purple">Brand</span><?php endif; ?>
            </td>
            <td style="font-weight:600"><?php echo e($req->name); ?></td>
            <td style="font-size:12px">
              <?php if($req->type === 'category'): ?>
                <?php if($req->parent_category_name): ?>
                  <span class="badge badge-green">↳ <?php echo e($req->parent_category_name); ?></span>
                <?php else: ?><span style="color:var(--muted)">Top-level</span><?php endif; ?>
              <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
            </td>
            <td style="font-size:12px"><?php echo e($req->vendor_name ?? '—'); ?></td>
            <td>
              <?php if($req->status==='pending'): ?><span class="badge badge-yellow">Pending</span>
              <?php elseif($req->status==='approved'): ?><span class="badge badge-green">Approved</span>
              <?php else: ?><span class="badge badge-red">Rejected</span><?php endif; ?>
            </td>
            <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?php echo e(\Carbon\Carbon::parse($req->created_at)->format('M d, Y')); ?></td>
            <td>
              <?php if($req->status === 'pending'): ?>
              <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start">
                <div style="display:flex;gap:5px">
                  <button onclick="toggleNote('req-a-<?php echo e($req->id); ?>')" class="btn btn-sm btn-success">✓ Approve</button>
                  <button onclick="toggleNote('req-r-<?php echo e($req->id); ?>')" class="btn btn-sm btn-danger">✗ Reject</button>
                </div>

                <div id="req-a-<?php echo e($req->id); ?>" class="req-note-form">
                  <form method="POST" action="<?php echo e(route('admin.cbr.approve', $req->id)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <?php if($req->type === 'category'): ?>
                    <div>
                      <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:2px">Override parent</label>
                      <select name="parent_category_id">
                        <option value="">— Keep as requested —</option>
                        <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($cat->id); ?>" <?php echo e($req->parent_category_id == $cat->id ? 'selected':''); ?>><?php echo e($cat->name); ?></option>
                          <?php if(isset($childCats[$cat->id])): ?>
                            <?php $__currentLoopData = $childCats[$cat->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($cc->id); ?>" <?php echo e($req->parent_category_id == $cc->id ? 'selected':''); ?>>&nbsp;&nbsp;↳ <?php echo e($cc->name); ?></option>
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

    <?php if($requests->hasPages()): ?>
      <div class="pagination" style="margin-top:16px"><?php echo e($requests->links()); ?></div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
function toggleEdit(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
  if (el.classList.contains('open')) {
    const inp = el.querySelector('input[name="name"]');
    if (inp) inp.focus();
  }
}
function toggleDel(id) {
  document.getElementById(id).classList.toggle('open');
}
function toggleAddForm(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
  if (el.classList.contains('open')) {
    const inp = el.querySelector('input[name="name"]');
    if (inp) inp.focus();
  }
}
function toggleNote(id) {
  document.getElementById(id).classList.toggle('open');
}
function filterCats(val) {
  val = val.toLowerCase();
  document.querySelectorAll('.cat-item').forEach(function(el) {
    const match = el.dataset.name.includes(val);
    el.style.display = match ? '' : 'none';
  });
}
function filterBrands(val) {
  val = val.toLowerCase();
  document.querySelectorAll('.brand-item').forEach(function(el) {
    const match = el.dataset.name.includes(val);
    el.style.display = match ? '' : 'none';
  });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/category-brand-requests/index.blade.php ENDPATH**/ ?>