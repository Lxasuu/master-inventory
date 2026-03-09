<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['user','pic','admin']);

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["user"]["user_id"])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Unauthorized"]);
  exit;
}

$userId = (int)$_SESSION["user"]["user_id"];

try {
  // tandai semua notifikasi user ini jadi read
  $stmt = $pdo->prepare("UPDATE activity_logs SET is_read = 1 WHERE user_id = ? AND is_read = 0");
  $stmt->execute([$userId]);

  // ambil unread terbaru (should be 0)
  $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND is_read = 0");
  $stmt2->execute([$userId]);
  $unread = (int)$stmt2->fetchColumn();

  echo json_encode(["ok" => true, "unread" => $unread]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Server error"]);
  exit;
}
