<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['user','pic','admin']);

if (!isset($_SESSION["user"]["user_id"])) {
  header("Location: ../auth-login.php");
  exit;
}

$userId = (int)$_SESSION["user"]["user_id"];

// tandai SEMUA notifikasi user ini sebagai read
$stmt = $pdo->prepare("UPDATE activity_logs SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$userId]);

// setelah itu pindah ke halaman view all (ganti sesuai file kamu)
header("Location: ../notification/index.php");
exit;
