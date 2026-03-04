<?php
require_once __DIR__ . "/partials/bootstrap.php";

$id = (int)($_GET["id"] ?? 0);
$uid = (int)($_SESSION["user"]["user_id"] ?? 0);

if ($id > 0 && $uid > 0) {
  $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $uid]);
}

header("Location: " . ($_SERVER["HTTP_REFERER"] ?? "index.php"));
exit;
