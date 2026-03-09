<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['pic','admin']);
require_once __DIR__ . "/../help_log/activity_log.php";

$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($actorId <= 0) {
  die("Session login tidak ditemukan. Silakan login ulang.");
}

function htrim($s) { return trim((string)$s); }

function splitApps(string $apps): array {
  $apps = trim($apps);
  if ($apps === '') return [];
  $parts = preg_split('/[;,]/', $apps);
  $parts = array_map(fn($x) => trim($x), $parts);
  $parts = array_values(array_filter($parts, fn($x) => $x !== ''));
  // unique (case-insensitive)
  $seen = [];
  $out = [];
  foreach ($parts as $a) {
    $k = mb_strtolower($a);
    if (!isset($seen[$k])) {
      $seen[$k] = true;
      $out[] = $a;
    }
  }
  return $out;
}

// =======================
// INPUT
// =======================
$unique_code     = htrim($_POST['unique_code'] ?? '');
$unique_name     = htrim($_POST['unique_name'] ?? '');
$location_id     = (int)($_POST['location_id'] ?? 0);
$condition_id    = (int)($_POST['condition_id'] ?? 0);
$check_status_id = (int)($_POST['check_status_id'] ?? 0);

$is_ready = ((int)($_POST['is_ready'] ?? 0) === 1) ? 1 : 0;
$internet = ((int)($_POST['internet'] ?? 0) === 1) ? 1 : 0;

// catatan (TIDAK masuk pcs karena kolomnya tidak ada)
$pic_name      = htrim($_POST['pic_name'] ?? '');       // dari hidden create.php
$error_note    = htrim($_POST['error_note'] ?? '');
$internet_note = htrim($_POST['internet_note'] ?? '');
$appsText      = htrim($_POST['apps'] ?? '');

if ($unique_code === '' || $unique_name === '' || !$location_id || !$condition_id || !$check_status_id) {
  die("Input wajib belum lengkap. Kembali dan isi semua field wajib.");
}

// kalau pic_name kosong, fallback ambil dari DB user login
if ($pic_name === '') {
  $stmtU = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ? LIMIT 1");
  $stmtU->execute([$actorId]);
  $u = $stmtU->fetch(PDO::FETCH_ASSOC);

  $pic_name = trim((string)($u['full_name'] ?? ''));
  if ($pic_name === '') $pic_name = trim((string)($u['email'] ?? ''));
  if ($pic_name === '') $pic_name = 'Unknown User';
}

// buat change_note format
$change_note = "PICName: {$pic_name} | Error: {$error_note} | InternetNote: {$internet_note}";

try {
  $pdo->beginTransaction();

  // =======================
  // 1) INSERT pcs  (TANPA pic_name!)
  // =======================
  $stmt = $pdo->prepare("
    INSERT INTO pcs
      (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at)
    VALUES
      (:unique_code, :unique_name, :location_id, :condition_id, :check_status_id, :internet, :is_ready, :updated_by, NOW(), NOW())
  ");
  $stmt->execute([
    ':unique_code'     => $unique_code,
    ':unique_name'     => $unique_name,
    ':location_id'     => $location_id,
    ':condition_id'    => $condition_id,
    ':check_status_id' => $check_status_id,
    ':internet'        => $internet,
    ':is_ready'        => $is_ready,
    ':updated_by'      => $actorId, // AUTO dari session
  ]);

  $pc_id = (int)$pdo->lastInsertId();

  $stmtUp = $pdo->prepare("
    INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at)
    VALUES (:pc_id, :updated_by, :change_note, NOW())
  ");
  $stmtUp->execute([
    ':pc_id'      => $pc_id,
    ':updated_by' => $actorId,
    ':change_note'=> $change_note
  ]);

  // =======================
  // 3) APPS => applications + pc_applications
  // =======================
  $apps = splitApps($appsText);

  if (!empty($apps)) {

    $stmtInsApp = $pdo->prepare("INSERT INTO applications (app_name) VALUES (?)");
    $stmtGetApp = $pdo->prepare("SELECT app_id FROM applications WHERE app_name = ? LIMIT 1");
    $stmtLink   = $pdo->prepare("INSERT INTO pc_applications (pc_id, app_id) VALUES (?, ?)");

    foreach ($apps as $appName) {
      // insert kalau belum ada
      try {
        $stmtInsApp->execute([$appName]);
        $appId = (int)$pdo->lastInsertId();
      } catch (PDOException $e) {
        $stmtGetApp->execute([$appName]);
        $appId = (int)($stmtGetApp->fetchColumn() ?? 0);
        if ($appId <= 0) throw $e;
      }

      // link ke pc
      try {
        $stmtLink->execute([$pc_id, $appId]);
      } catch (PDOException $e) {
      }
    }
  }

  // =======================
  // 4) ACTIVITY LOG
  // =======================
  log_activity(
    $pdo,
    $actorId,
    "CREATE_PC",
    "pcs",
    $pc_id,
    "Menambahkan PC: {$unique_name} ({$unique_code})",
    [
      "unique_code"     => $unique_code,
      "unique_name"     => $unique_name,
      "location_id"     => $location_id,
      "condition_id"    => $condition_id,
      "check_status_id" => $check_status_id,
      "internet"        => $internet,
      "is_ready"        => $is_ready,
      "pic_name"        => $pic_name,
      "error_note"      => $error_note,
      "internet_note"   => $internet_note,
      "apps"            => $apps,
    ]
  );

  $pdo->commit();
  header("Location: index.php");
  exit;

} catch (PDOException $e) {
  $pdo->rollBack();
  die("Gagal simpan data: " . $e->getMessage());
}