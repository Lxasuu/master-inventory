<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['user','pic','admin']);

if (!isset($_SESSION["user"]["user_id"])) {
  header("Location: ../auth-login.php");
  exit;
}

$userId = (int)$_SESSION["user"]["user_id"];
$logId  = (int)($_GET["id"] ?? 0);

if ($logId <= 0) die("ID notifikasi tidak valid.");

// ambil notifikasi milik user ini
$stmt = $pdo->prepare("
  SELECT log_id, action, entity, entity_id, title, detail, is_read, created_at
  FROM activity_logs
  WHERE log_id = ? AND user_id = ?
  LIMIT 1
");
$stmt->execute([$logId, $userId]);
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) die("Notifikasi tidak ditemukan.");

// mark as read
$pdo->prepare("UPDATE activity_logs SET is_read = 1 WHERE log_id = ? AND user_id = ?")
    ->execute([$logId, $userId]);

$detailArr = [];
if (!empty($log["detail"])) {
  $tmp = json_decode($log["detail"], true);
  if (is_array($tmp)) $detailArr = $tmp;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// link data terkait (khusus pcs)
$openLink = null;
if ($log["entity"] === "pcs" && !empty($log["entity_id"])) {
  $openLink = "../pcs/edit.php?id=" . (int)$log["entity_id"];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Notifikasi</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/icons.min.css">
  <link rel="stylesheet" href="../assets/css/app.min.css">
</head>
<body>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Detail Notifikasi</h4>
      <div class="text-muted"><?= h($log["action"]) ?> • <?= h($log["entity"]) ?></div>
    </div>
    <a href="../index.php" class="btn btn-light">
      <i class="mdi mdi-arrow-left"></i> Kembali
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="mb-1"><?= h($log["title"]) ?></h5>
      <div class="text-muted mb-3">
        Waktu: <?= h($log["created_at"] ?? '-') ?>
      </div>

      <?php if ($openLink): ?>
        <a class="btn btn-primary btn-sm mb-3" href="<?= h($openLink) ?>">
          Buka data PC terkait
        </a>
      <?php endif; ?>

      <h6 class="mb-2">Detail (JSON)</h6>
      <?php if (!$detailArr): ?>
        <div class="text-muted">Tidak ada detail.</div>
      <?php else: ?>
        <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;"><?= h(json_encode($detailArr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/app.js"></script>
</body>
</html>
