<?php // pcs/_partials/actionbar.php ?>
<div class="card pc-card pc-actionbar">
  <div class="card-body">
    <div class="pc-actionbar-inner">
      <div class="pc-actionbar-left">
        <div class="pc-actionbar-title">Data PC</div>
        <div class="pc-actionbar-sub">Kelola data PC dan monitoring status.</div>
      </div>

      <?php if (function_exists('can') && can(['pic','admin'])): ?>
        <a href="create.php" class="btn btn-primary btn-addpc">
          <span class="btn-addpc-ic"><i class="mdi mdi-plus"></i></span>
          <span>Add PC</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
