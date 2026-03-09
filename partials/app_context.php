<?php
// Wajib: $pdo sudah ada dari bootstrap.php dan session sudah jalan

$BASE_URL = "/HTML/dist/";

$sessionUser = $_SESSION["user"] ?? [];
$userId   = (int)($sessionUser["user_id"] ?? 0);
$email    = $sessionUser["email"] ?? "";
$fullName = $sessionUser["full_name"] ?? $email;
$role     = $sessionUser["role"] ?? "User";

// refresh user dari DB + validasi active (kalau kolom is_active ada)
$stmt = $pdo->prepare("SELECT email, full_name, role, is_active, photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser || (isset($dbUser["is_active"]) && (int)$dbUser["is_active"] !== 1)) {
  session_unset();
  session_destroy();
  header("Location: {$BASE_URL}auth-login.php");
  exit;
}

$email    = $dbUser["email"] ?? $email;
$fullName = $dbUser["full_name"] ?: ($fullName ?: $email);
$role     = $dbUser["role"] ?: $role;

// photo URL
$photoDb = $dbUser["photo"] ?? "";
$photo = get_user_photo($photoDb);

// notifications
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
