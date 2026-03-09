<?php
require_once __DIR__ . "/config/db.php";

header('Content-Type: application/json; charset=utf-8');

$type  = $_GET['type'] ?? '';
$value = trim($_GET['value'] ?? '');

$out = ['ok' => true, 'available' => false, 'message' => 'Request tidak valid.'];

try {
    if ($type === 'email') {
        $email = strtolower($value);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $out = ['ok'=>true,'available'=>false,'message'=>'Format email tidak valid.'];
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM users WHERE LOWER(email) = ? LIMIT 1");
            $stmt->execute([$email]);
            $exists = (bool)$stmt->fetchColumn();
            $out = ['ok'=>true,'available'=>!$exists,'message'=>$exists?'Email sudah terdaftar.':'Email tersedia.'];
        }
    }

    elseif ($type === 'username') {
        $u = $value;

        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $u)) {
            $out = ['ok'=>true,'available'=>false,'message'=>'Username tidak valid.'];
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$u]);
            $exists = (bool)$stmt->fetchColumn();
            $out = ['ok'=>true,'available'=>!$exists,'message'=>$exists?'Username sudah terdaftar.':'Username tersedia.'];
        }
    }

    echo json_encode($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'available'=>false,'message'=>'Server error saat cek data.']);
}
