  <?php
  require_once __DIR__ . "/../partials/bootstrap.php";
  require_role(['admin']);

  $BASE_URL = "/HTML/";

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $BASE_URL . "users/index.php");
    exit;
  }

  /**
   * ===============
   * 1) Ambil public_id dari POST (bukan user_id)
   * ===============
   */
  $public_id = (string)($_POST['public_id'] ?? '');
  if (!preg_match('/^[a-f0-9]{32}$/i', $public_id)) {
    header("Location: " . $BASE_URL . "users/index.php?error=invalid_user");
    exit;
  }

  // =========================
  // INPUT
  // =========================
  $username  = trim($_POST['username'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $full_name = trim($_POST['full_name'] ?? '');
  $role      = strtolower(trim($_POST['role'] ?? 'user'));
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $removePhoto = (isset($_POST['remove_photo']) && ($_POST['remove_photo'] === '1')) ? 1 : 0;

  // =========================
  // VALIDATION
  // =========================
  $allowed_roles = ['user', 'pic', 'admin'];
  if (!in_array($role, $allowed_roles, true)) {
    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=invalid_role");
    exit;
  }

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=invalid_email");
    exit;
  }

  // username wajib, tanpa spasi
  if ($username === '' || preg_match('/\s/', $username) || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=username_invalid");
    exit;
  }

  // =========================
  // GET EXISTING (berdasarkan public_id)
  // =========================
  $stmt = $pdo->prepare("SELECT user_id, public_id, email, username, photo FROM users WHERE public_id = ? LIMIT 1");
  $stmt->execute([$public_id]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$existing) {
    header("Location: " . $BASE_URL . "users/index.php?error=user_not_found");
    exit;
  }

  $user_id = (int)$existing['user_id']; 
  $oldPhotoDbPath = $existing['photo'] ?? null;

  // cek username unik
  $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id <> ? LIMIT 1");
  $stmt->execute([$username, $user_id]);
  if ($stmt->fetch()) {
    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=username_exists");
    exit;
  }

  // cek email unik
  $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1");
  $stmt->execute([$email, $user_id]);
  if ($stmt->fetch()) {
    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=email_exists");
    exit;
  }

  // =========================
  // HELPERS
  // =========================
  function dist_root(): string {
    $root = realpath(__DIR__ . "/..");
    return $root ?: (__DIR__ . "/..");
  }

  function deletePhotoFile(?string $dbPath): void {
    if (!$dbPath) return;
    $abs = dist_root() . "/" . ltrim($dbPath, "/"); // dist/<dbPath>
    if (file_exists($abs)) @unlink($abs);
  }

  // =========================
  // PHOTO LOGIC
  // =========================
  $newPhotoDbPath = $oldPhotoDbPath; // default KEEP OLD

  $hasNewUpload = (
    isset($_FILES['photo']) &&
    ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
  );

  // Jika remove dicentang dan TIDAK ada upload baru → set null
  if ($removePhoto && !$hasNewUpload) {
    $newPhotoDbPath = null;
  }

  // Jika ada upload baru, override semua
  if ($hasNewUpload) {

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=upload_failed");
      exit;
    }

    $tmp  = $_FILES['photo']['tmp_name'];
    $size = (int)$_FILES['photo']['size'];

    if ($size > 2 * 1024 * 1024) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=photo_too_large");
      exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    $allowed_mimes = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
    ];

    if (!isset($allowed_mimes[$mime])) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=invalid_photo_type");
      exit;
    }

    $ext = $allowed_mimes[$mime];

    $upload_dir = dist_root() . "/uploads/users";
    if (!is_dir($upload_dir)) {
      if (!mkdir($upload_dir, 0777, true)) {
        header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=upload_dir_missing");
        exit;
      }
    }
    if (!is_writable($upload_dir)) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=upload_dir_missing");
      exit;
    }

    $new_filename  = "user_" . $user_id . "_" . time() . "." . $ext;
    $dest_path_abs = $upload_dir . "/" . $new_filename;

    if (!move_uploaded_file($tmp, $dest_path_abs)) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=upload_move_failed");
      exit;
    }

    // simpan path ke DB (relatif dari dist)
    $newPhotoDbPath = "uploads/users/" . $new_filename;

    // hapus foto lama kalau ada
    if (!empty($oldPhotoDbPath)) {
      deletePhotoFile($oldPhotoDbPath);
    }
  }

  // Jika remove dicentang dan tidak upload baru → hapus file lama
  if ($removePhoto && !$hasNewUpload && !empty($oldPhotoDbPath)) {
    deletePhotoFile($oldPhotoDbPath);
  }

  // =========================
  // PASSWORD (OPTIONAL)
  // =========================
  $new_password = (string)($_POST['new_password'] ?? '');
  $new_hash = null;

  if ($new_password !== '') {
    if (strlen($new_password) < 6) {
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=password_too_short");
      exit;
    }
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
  }

  // =========================
  // BUILD UPDATE
  // =========================
  $fields = [
    "username = ?",
    "email = ?",
    "full_name = ?",
    "role = ?",
    "is_active = ?",
    "updated_at = NOW()",
  ];

  $params = [
    $username,
    $email,
    ($full_name !== '' ? $full_name : null),
    ($role !== '' ? $role : null),
    $is_active,
  ];

  // photo hanya update kalau upload baru atau remove benar-benar aktif
  $photoChanged = ($removePhoto === 1) || $hasNewUpload;
  if ($photoChanged) {
    $fields[] = "photo = ?";
    $params[] = $newPhotoDbPath; 
  }

  if ($new_hash !== null) {
    $fields[] = "password_hash = ?";
    $params[] = $new_hash;
  }

  $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = ?";

  try {
    $stmt = $pdo->prepare($sql);
    $params[] = $user_id;
    $stmt->execute($params);

    header("Location: " . $BASE_URL . "users/index.php?success=updated");
    exit;

  } catch (PDOException $e) {
    error_log("UPDATE USER FAILED (public_id=$public_id, user_id=$user_id): " . $e->getMessage());

    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      $msg = $e->getMessage();
      if (stripos($msg, 'username') !== false) {
        header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=username_exists");
        exit;
      }
      header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=email_exists");
      exit;
    }

    header("Location: " . $BASE_URL . "users/edit.php?u=" . urlencode($public_id) . "&error=update_failed");
    exit;
  }
