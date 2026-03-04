<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['user','pic','admin']);

if (!isset($_SESSION["user"]["user_id"])) {
    header("Location: ../auth-login.php");
    exit;
}

$userId = (int)$_SESSION["user"]["user_id"];
$logId  = (int)($_GET["id"] ?? 0);
$to     = (string)($_GET["to"] ?? "index.php");

if ($logId <= 0) {
    header("Location: ../index.php");
    exit;
}

// mark as read (pastikan milik user ini)
$stmt = $pdo->prepare("UPDATE activity_logs SET is_read = 1 WHERE log_id = ? AND user_id = ?");
$stmt->execute([$logId, $userId]);

// rapihin target
$to = ltrim($to, "/");

// kalau to sudah mengandung "HTML/dist/", buang biar tidak dobel
$to = preg_replace('#^HTML/dist/#', '', $to);

// redirect
header("Location: ../" . $to);
exit;
