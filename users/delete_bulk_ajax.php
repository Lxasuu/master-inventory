<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
  exit;
}

$rawIds = $_POST['ids'] ?? '';
$idsArray = json_decode($rawIds, true);

if (!is_array($idsArray) || empty($idsArray)) {
  echo json_encode(['ok' => false, 'message' => 'Tidak ada data pengguna yang dipilih.']);
  exit;
}

// Filter to ensure all are integers
$validIds = array_filter(array_map('intval', $idsArray), function($id) {
    return $id > 0;
});

if (empty($validIds)) {
  echo json_encode(['ok' => false, 'message' => 'Format ID pengguna tidak valid.']);
  exit;
}

$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);

// Mencegah admin menghapus dirinya sendiri
if (in_array($actorId, $validIds)) {
    echo json_encode(['ok' => false, 'message' => 'Terdapat akun Anda dalam pilihan. Anda tidak dapat menghapus akun Anda sendiri.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($validIds), '?'));

try {
  $pdo->beginTransaction();

  // Cek apakah ada master account/admin lain yang tak boleh dihapus
  // Contoh: mencegah delete akun super (misal admin utama ID=1). Sesuaikan dengan bussines logic.
  // if (in_array(1, $validIds)) { ... }

  // Ambil data untuk log
  $stmtSel = $pdo->prepare("SELECT user_id, username, email FROM users WHERE user_id IN ($placeholders)");
  $stmtSel->execute(array_values($validIds));
  $usersToDelete = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

  if (empty($usersToDelete)) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'message' => 'Data pengguna tidak ditemukan di database.']);
    exit;
  }

  // Delete DB
  $stmtDel = $pdo->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
  $stmtDel->execute(array_values($validIds));
  $deletedCount = $stmtDel->rowCount();

  require_once __DIR__ . "/../help_log/activity_log.php";

  foreach ($usersToDelete as $u) {
    log_activity(
      $pdo,
      $actorId,
      "DELETE_USER",
      "users",
      $u['user_id'],
      "Menghapus pengguna: {$u['username']} ({$u['email']})",
      null
    );
  }

  $pdo->commit();

  echo json_encode([
    'ok' => true,
    'message' => "Berhasil menghapus $deletedCount pengguna terpilih."
  ]);

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['ok' => false, 'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()]);
}
