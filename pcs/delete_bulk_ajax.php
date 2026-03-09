<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['pic','admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
  exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  echo json_encode(['ok' => false, 'message' => 'Token tidak valid. Refresh halaman.']);
  exit;
}

$rawIds = $_POST['ids'] ?? '';
$idsArray = json_decode($rawIds, true);

if (!is_array($idsArray) || empty($idsArray)) {
  echo json_encode(['ok' => false, 'message' => 'Tidak ada data yang dipilih.']);
  exit;
}

// Filter to ensure all are integers
$validIds = array_filter(array_map('intval', $idsArray), function($id) {
    return $id > 0;
});

if (empty($validIds)) {
  echo json_encode(['ok' => false, 'message' => 'Format ID tidak valid.']);
  exit;
}

$placeholders = implode(',', array_fill(0, count($validIds), '?'));
$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);

try {
  $pdo->beginTransaction();

  // Ambil data untuk log
  $stmtSel = $pdo->prepare("SELECT pc_id, unique_code, unique_name FROM pcs WHERE pc_id IN ($placeholders)");
  $stmtSel->execute(array_values($validIds));
  $pcsToDelete = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

  if (empty($pcsToDelete)) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'message' => 'Data tidak ditemukan di database.']);
    exit;
  }

  // Delete dari db
  $stmtDel = $pdo->prepare("DELETE FROM pcs WHERE pc_id IN ($placeholders)");
  $stmtDel->execute(array_values($validIds));
  $deletedCount = $stmtDel->rowCount();

  require_once __DIR__ . "/../help_log/activity_log.php";

  foreach ($pcsToDelete as $pc) {
    log_activity(
      $pdo,
      $actorId,
      "DELETE_PC",
      "pcs",
      $pc['pc_id'],
      "Menghapus PC: {$pc['unique_name']} ({$pc['unique_code']})",
      null
    );
  }

  $pdo->commit();

  echo json_encode([
    'ok' => true,
    'message' => "Berhasil menghapus $deletedCount data PC terpilih."
  ]);

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['ok' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
}
