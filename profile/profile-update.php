<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['user','pic','admin']);

if (!isset($_SESSION["user"]["user_id"])) {
  header("Location: ../auth-login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: profile.php");
  exit;
}

/* =======================
   CSRF CHECK
======================= */
$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || !verify_csrf($token)) {
  header("Location: profile.php?error=" . urlencode("CSRF token tidak valid."));
  exit;
}

$user_id = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($user_id <= 0) {
  header("Location: ../auth-login.php");
  exit;
}

/* =======================
   INPUT
======================= */
$email       = trim($_POST["email"] ?? "");
$username    = strtolower(trim($_POST["username"] ?? ""));
$full_name   = trim($_POST["full_name"] ?? "");
$removePhoto = isset($_POST["remove_photo"]) ? 1 : 0;

$old_password = (string)($_POST["old_password"] ?? "");
$new_password = (string)($_POST["new_password"] ?? "");

/* =======================
   VALIDATION
======================= */
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: profile.php?error=" . urlencode("Email tidak valid."));
  exit;
}

if ($username === "") {
  header("Location: profile.php?error=" . urlencode("Username wajib diisi."));
  exit;
}

if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
  header("Location: profile.php?error=" . urlencode(
    "Username harus 3–20 karakter (huruf kecil, angka, underscore)."
  ));
  exit;
}

/* =======================
   GET EXISTING USER
======================= */
$stmt = $pdo->prepare("
  SELECT user_id, username, email, full_name, photo, password_hash
  FROM users
  WHERE user_id = ?
  LIMIT 1
");
$stmt->execute([$user_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
  session_destroy();
  header("Location: ../auth-login.php");
  exit;
}

/* =======================
   CHECK UNIQUE USERNAME
======================= */
$stmt = $pdo->prepare("
  SELECT user_id FROM users
  WHERE username = ? AND user_id <> ?
  LIMIT 1
");
$stmt->execute([$username, $user_id]);

if ($stmt->fetch()) {
  header("Location: profile.php?error=" . urlencode("Username sudah digunakan."));
  exit;
}

/* =======================
   PHOTO HELPERS
======================= */
function deletePhotoFile(?string $dbPath): void {
  if (!$dbPath) return;
  $root = realpath(__DIR__ . "/..");
  $abs  = $root . "/" . ltrim($dbPath, "/");
  if (file_exists($abs) && is_file($abs)) @unlink($abs);
}

function uploadUserPhoto(int $userId, array $file): array {
  if (empty($file["name"])) return [null, ""];
  if ($file["error"] !== UPLOAD_ERR_OK) return [null, "Upload foto gagal."];

  if ($file["size"] > 2 * 1024 * 1024) {
    return [null, "Ukuran foto maksimal 2MB."];
  }

  $allowed = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp",
  ];

  $mime = mime_content_type($file["tmp_name"]);
  if (!isset($allowed[$mime])) {
    return [null, "Format foto harus JPG/PNG/WEBP."];
  }

  $root = realpath(__DIR__ . "/..");
  $dir  = $root . "/uploads/users";
  if (!is_dir($dir)) mkdir($dir, 0755, true);

  $name = "user_{$userId}_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
  $path = $dir . "/" . $name;

  if (!move_uploaded_file($file["tmp_name"], $path)) {
    return [null, "Gagal menyimpan foto."];
  }

  return ["uploads/users/" . $name, ""];
}

/* =======================
   PHOTO LOGIC
======================= */
$newPhoto = $existing["photo"];
$hasUpload = ($_FILES["photo"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($removePhoto) {
  deletePhotoFile($existing["photo"]);
  $newPhoto = null;
}

if ($hasUpload) {
  [$path, $err] = uploadUserPhoto($user_id, $_FILES["photo"]);
  if ($err) {
    header("Location: profile.php?error=" . urlencode($err));
    exit;
  }
  deletePhotoFile($existing["photo"]);
  $newPhoto = $path;
}

/* =======================
   PASSWORD LOGIC
======================= */
$new_hash = null;
if ($new_password !== "") {
  if ($old_password === "" || !password_verify($old_password, $existing["password_hash"])) {
    header("Location: profile.php?error=" . urlencode("Password lama salah."));
    exit;
  }
  if (strlen($new_password) < 6) {
    header("Location: profile.php?error=" . urlencode("Password baru minimal 6 karakter."));
    exit;
  }
  $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
}

/* =======================
   UPDATE USER
======================= */
$fields = [
  "username = ?",
  "email = ?",
  "full_name = ?",
  "updated_at = NOW()"
];

$params = [
  $username,
  $email,
  ($full_name !== "" ? $full_name : null)
];

if ($newPhoto !== $existing["photo"]) {
  $fields[] = "photo = ?";
  $params[] = $newPhoto;
}

if ($new_hash) {
  $fields[] = "password_hash = ?";
  $params[] = $new_hash;
}

$params[] = $user_id;

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

/* =======================
   REFRESH SESSION
======================= */
$_SESSION["user"]["username"]  = $username;
$_SESSION["user"]["email"]     = $email;
$_SESSION["user"]["full_name"] = ($full_name !== "" ? $full_name : $email);

header("Location: profile.php?success=1");
exit;
