<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Sesuaikan jika path folder kamu berubah.
 * Kalau aksesnya: http://inventory_meta.test/HTML/
 * maka BASE_URL = "/HTML/"
 */
$BASE_URL = "/HTML/";

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/notifications.php";
require_once __DIR__ . "/authorize.php";
require_once __DIR__ . "/auth.php"; // ini akan redirect kalau belum login

$notifUnread = 0;
$notifications = [];

if (!empty($_SESSION["user"]["user_id"])) {
  $userId = (int)$_SESSION["user"]["user_id"];

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND is_read = 0");
  $stmt->execute([$userId]);
  $notifUnread = (int)$stmt->fetchColumn();

  $stmt = $pdo->prepare("
    SELECT log_id, action, entity, entity_id, title, detail, is_read, created_at
    FROM activity_logs
    WHERE user_id = ?
    ORDER BY log_id DESC
    LIMIT 5
  ");
  $stmt->execute([$userId]);
  $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


