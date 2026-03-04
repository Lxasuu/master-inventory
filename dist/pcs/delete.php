<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin','pic']);

require_once __DIR__ . "/../help_log/activity_log.php";
$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($actorId <= 0) die("Session login tidak ditemukan. Silakan login ulang.");

$id = (int)($_GET['id'] ?? 0);
if (!$id) die("ID tidak valid.");

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT pc_id, unique_code, unique_name FROM pcs WHERE pc_id = ? LIMIT 1");
  $stmt->execute([$id]);
  $pc = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$pc) {
    $pdo->rollBack();
    die("Data tidak ditemukan.");
  }

  $stmt = $pdo->prepare("DELETE FROM pc_updates WHERE pc_id = ?");
  $stmt->execute([$id]);

  $stmt = $pdo->prepare("DELETE FROM pc_applications WHERE pc_id = ?");
  $stmt->execute([$id]);

  $stmt = $pdo->prepare("DELETE FROM pcs WHERE pc_id = ?");
  $stmt->execute([$id]);

  log_activity(
    $pdo,
    $actorId,
    "DELETE_PC",
    "pcs",
    $id,
    "Menghapus PC: {$pc['unique_name']} ({$pc['unique_code']})",
    $pc
  );

  $pdo->commit();
  header("Location: index.php");
  exit;

} catch (PDOException $e) {
  $pdo->rollBack();
  die("Gagal delete: " . $e->getMessage());
}
