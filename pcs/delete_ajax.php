<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['admin','pic']);

require_once __DIR__ . "/../help_log/activity_log.php";

header('Content-Type: application/json; charset=utf-8');

function jexit(array $data, int $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($actorId <= 0) {
  jexit(["ok" => false, "message" => "Session login tidak ditemukan. Silakan login ulang."], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jexit(["ok" => false, "message" => "Method tidak valid."], 405);
}

$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf($token)) {
  jexit(["ok" => false, "message" => "CSRF token tidak valid. Silakan refresh halaman."], 403);
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  jexit(["ok" => false, "message" => "ID tidak valid."], 400);
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT pc_id, unique_code, unique_name FROM pcs WHERE pc_id = ? LIMIT 1");
  $stmt->execute([$id]);
  $pc = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$pc) {
    $pdo->rollBack();
    jexit(["ok" => false, "message" => "Data tidak ditemukan."], 404);
  }

  $stmt = $pdo->prepare("DELETE FROM pc_updates WHERE pc_id = ?");
  $stmt->execute([$id]);

  $stmt = $pdo->prepare("DELETE FROM pc_applications WHERE pc_id = ?");
  $stmt->execute([$id]);

  $stmt = $pdo->prepare("DELETE FROM pcs WHERE pc_id = ?");
  $stmt->execute([$id]);

  // log notifikasi delete
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

  jexit(["ok" => true, "message" => "Data PC berhasil dihapus."]);

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  jexit(["ok" => false, "message" => "Gagal menghapus data. Silakan coba lagi."], 500);
}
