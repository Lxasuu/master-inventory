<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']); // Admin Only

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["ok" => false, "message" => "Method tidak valid"]);
  exit;
}

$id = (int)($_POST["id"] ?? 0);
if ($id <= 0) {
  echo json_encode(["ok" => false, "message" => "ID tidak valid"]);
  exit;
}

$myId = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($myId === $id) {
  echo json_encode(["ok" => false, "message" => "Tidak bisa menghapus akun yang sedang login."]);
  exit;
}

$stmt = $pdo->prepare("SELECT user_id, photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo json_encode(["ok" => false, "message" => "User tidak ditemukan"]);
  exit;
}

function deletePhotoFile(?string $dbPath): void {
  if (!$dbPath) return;
  $abs = realpath(__DIR__ . "/..") . "/" . ltrim($dbPath, "/");
  if ($abs && file_exists($abs)) @unlink($abs);
}

try {
  if (!empty($user["photo"])) {
    deletePhotoFile($user["photo"]);
  }

  $del = $pdo->prepare("DELETE FROM users WHERE user_id = ? LIMIT 1");
  $del->execute([$id]);

  echo json_encode(["ok" => true]);
  exit;

} catch (PDOException $e) {
  error_log("DELETE AJAX FAILED user_id=$id: " . $e->getMessage());
  echo json_encode(["ok" => false, "message" => "Gagal menghapus data."]);
  exit;
}
