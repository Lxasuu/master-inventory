<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin','pic']);
require_once __DIR__ . "/../help_log/activity_log.php";
$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);


$actorId = (int)($_SESSION["user"]["user_id"] ?? 0);
if ($actorId <= 0) die("Session login tidak ditemukan. Silakan login ulang.");

function splitApps(string $apps): array {
  $apps = trim($apps);
  if ($apps === '') return [];
  $parts = preg_split('/[;,]/', $apps);
  $parts = array_map(fn($x) => trim($x), $parts);
  return array_values(array_filter($parts, fn($x) => $x !== ''));
}

$pc_id = (int)($_POST['pc_id'] ?? 0);
if (!$pc_id) die("ID tidak valid.");

$unique_code = trim($_POST['unique_code'] ?? '');
$unique_name = trim($_POST['unique_name'] ?? '');
$location_id = (int)($_POST['location_id'] ?? 0);
$condition_id = (int)($_POST['condition_id'] ?? 0);
$check_status_id = (int)($_POST['check_status_id'] ?? 0);

$is_ready = ((int)($_POST['is_ready'] ?? 0)) ? 1 : 0;
$internet = ((int)($_POST['internet'] ?? 0)) ? 1 : 0;

$pic_name = trim($_POST['pic_name'] ?? '');
$error_note = trim($_POST['error_note'] ?? '');
$internet_note = trim($_POST['internet_note'] ?? '');
$apps = trim($_POST['apps'] ?? '');

$updated_by = $_POST['updated_by'] ?? null;
$updated_by = ($updated_by === '' || $updated_by === null) ? null : (int)$updated_by;

if ($unique_code === '' || $unique_name === '' || !$location_id || !$condition_id || !$check_status_id) {
  die("Input wajib belum lengkap.");
}

$stmt = $pdo->prepare("SELECT * FROM pcs WHERE pc_id = ? LIMIT 1");
$stmt->execute([$pc_id]);
$before = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$before) die("Data PC tidak ditemukan.");

try {
  $pdo->beginTransaction();

  // 1) update pcs
  $stmt = $pdo->prepare("
    UPDATE pcs
    SET unique_code = :unique_code,
        unique_name = :unique_name,
        location_id = :location_id,
        condition_id = :condition_id,
        check_status_id = :check_status_id,
        internet = :internet,
        is_ready = :is_ready,
        updated_by = :updated_by,
        updated_at = NOW()
    WHERE pc_id = :pc_id
  ");
  $stmt->execute([
    ':unique_code' => $unique_code,
    ':unique_name' => $unique_name,
    ':location_id' => $location_id,
    ':condition_id' => $condition_id,
    ':check_status_id' => $check_status_id,
    ':internet' => $internet,
    ':is_ready' => $is_ready,
    ':updated_by' => $updated_by,
    ':pc_id' => $pc_id
  ]);

  // 2) update apps: hapus pivot lalu insert lagi
  $stmt = $pdo->prepare("DELETE FROM pc_applications WHERE pc_id = ?");
  $stmt->execute([$pc_id]);

  foreach (splitApps($apps) as $appName) {
    $stmt = $pdo->prepare("SELECT app_id FROM applications WHERE app_name = ? LIMIT 1");
    $stmt->execute([$appName]);
    $app_id = $stmt->fetchColumn();

    if (!$app_id) {
      $stmt = $pdo->prepare("INSERT INTO applications (app_name) VALUES (?)");
      $stmt->execute([$appName]);
      $app_id = (int)$pdo->lastInsertId();
    } else {
      $app_id = (int)$app_id;
    }

    $stmt = $pdo->prepare("
      INSERT INTO pc_applications (pc_id, app_id, installed, installed_at)
      VALUES (:pc_id, :app_id, 1, NOW())
    ");
    $stmt->execute([':pc_id' => $pc_id, ':app_id' => $app_id]);
  }

  // 3) simpan notes sebagai history (pc_updates)
  $noteParts = [];
  if ($pic_name !== '') $noteParts[] = "PICName: $pic_name";
  if ($error_note !== '') $noteParts[] = "Error: $error_note";
  if ($internet_note !== '') $noteParts[] = "InternetNote: $internet_note";
  $change_note = implode(" | ", $noteParts);

  if ($change_note !== '' && $updated_by) {
    $stmt = $pdo->prepare("
      INSERT INTO pc_updates
        (pc_id, updated_by, condition_id, check_status_id, internet, is_ready, change_note, updated_at)
      VALUES
        (:pc_id, :updated_by, :condition_id, :check_status_id, :internet, :is_ready, :change_note, NOW())
    ");
    $stmt->execute([
      ':pc_id' => $pc_id,
      ':updated_by' => $updated_by,
      ':condition_id' => $condition_id,
      ':check_status_id' => $check_status_id,
      ':internet' => $internet,
      ':is_ready' => $is_ready,
      ':change_note' => $change_note,
    ]);
  }

  // ambil data sesudah update
  $stmt = $pdo->prepare("SELECT * FROM pcs WHERE pc_id = ? LIMIT 1");
  $stmt->execute([$pc_id]);
  $after = $stmt->fetch(PDO::FETCH_ASSOC);

  // bikin diff perubahan field penting
  $changes = [];
  $watch = [
    'unique_code' => 'Kode Unik',
    'unique_name' => 'Nama Unik',
    'location_id' => 'Lokasi',
    'condition_id' => 'Kondisi',
    'check_status_id' => 'Status Check',
    'internet' => 'Internet',
    'is_ready' => 'Ready',
  ];

  foreach ($watch as $k => $label) {
    $b = $before[$k] ?? null;
    $a = $after[$k] ?? null;
    if ((string)$b !== (string)$a) {
      $changes[] = [
        'field' => $k,
        'label' => $label,
        'before' => $b,
        'after'  => $a,
      ];
    }
  }

  // TEMPAT LOG AKTIVITAS (notifikasi)
    log_activity(
      $pdo,
      $actorId,
      "UPDATE_PC",
      "pcs",
      $pc_id,
      "Update PC: {$unique_name} ({$unique_code})",
      [
        "pc_id" => $pc_id,
        "unique_code" => $unique_code,
        "unique_name" => $unique_name,
        "location_id" => $location_id,
        "condition_id" => $condition_id,
        "check_status_id" => $check_status_id,
        "internet" => $internet,
        "is_ready" => $is_ready,
      ]
    );


  $pdo->commit();
  header("Location: index.php");
  exit;

} catch (PDOException $e) {
  $pdo->rollBack();
  die("Gagal update: " . $e->getMessage());
}