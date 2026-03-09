<?php
// partials/layout_end.php
$extraScriptPartial = $extraScriptPartial ?? null;
?>

</div><!-- end #layout-wrapper -->

<div class="rightbar-overlay"></div>

<?php require __DIR__ . "/scripts.php"; ?>

<?php if (!empty($extraScriptPartial) && file_exists($extraScriptPartial)): ?>
  <?php require $extraScriptPartial; ?>
<?php endif; ?>

</body>
</html>
