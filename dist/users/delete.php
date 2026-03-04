<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']);

if (!isset($_SESSION["user"]["user_id"])) { header("Location: ../auth-login.php"); exit; }

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

if ((int)($_SESSION["user"]["user_id"] ?? 0) === $id) {
  header("Location: index.php");
  exit;
}

$stmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

  if (!empty($user["photo"])) {
    $abs = realpath(__DIR__ . "/..") . "/" . $user["photo"];
    if (file_exists($abs)) @unlink($abs);
  }

  $del = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
  $del->execute([$id]);
}

header("Location: index.php");
exit;
