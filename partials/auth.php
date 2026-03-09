<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

/**
 * BASE URL untuk project (URL, bukan path Windows)
 * Sesuaikan kalau root kamu beda.
 */
$BASE_URL = "/HTML/";

// Kalau belum login, lempar ke halaman login
if (!isset($_SESSION["user"]["user_id"])) {
  header("Location: {$BASE_URL}auth-login.php");
  exit;
}

// Ambil dari session
$sessionUser = $_SESSION["user"];
$userId   = (int)$sessionUser["user_id"];
$email    = $sessionUser["email"] ?? "";
$fullName = $sessionUser["full_name"] ?? $email;
$role     = $sessionUser["role"] ?? "User";

// Refresh dari DB (biar paling update + validasi aktif)
$stmt = $pdo->prepare("SELECT email, full_name, role, is_active, photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser || (int)$dbUser["is_active"] !== 1) {
  session_unset();
  session_destroy();
  header("Location: {$BASE_URL}auth-login.php");
  exit;
}

// Update variabel dari DB
$email    = $dbUser["email"] ?? $email;
$fullName = !empty($dbUser["full_name"]) ? $dbUser["full_name"] : ($fullName ?: $email);
$role     = !empty($dbUser["role"]) ? $dbUser["role"] : $role;

// =====================
// AVATAR HANDLING (FIXED)
// =====================

// Default avatar URL (ABSOLUTE URL dari root)
$DEFAULT_AVATAR = $BASE_URL . "assets/images/default-user.png";

// photo dari DB: simpan RELATIVE, contoh: "assets/images/users/abc.png"
$photoDb = $dbUser["photo"] ?? "";

// Cek file fisik di server
// Jika DB menyimpan "assets/images/users/abc.png" -> path fisik:
// DOCUMENT_ROOT + "/HTML/" + "assets/images/users/abc.png"
$photoPath = $_SERVER['DOCUMENT_ROOT'] . $BASE_URL . ltrim($photoDb, "/");

if (!empty($photoDb) && file_exists($photoPath)) {
  // URL untuk ditampilkan
  $photo = $BASE_URL . ltrim($photoDb, "/");
} else {
  $photo = $DEFAULT_AVATAR;
}
