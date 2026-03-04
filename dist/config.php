<?php
session_start();

// === DB CONFIG ===
$dbHost = "127.0.0.1";
$dbName = "inventory_meta_sql";   
$dbUser = "root";
$dbPass = "";                    

try {
  $pdo = new PDO(
    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
    $dbUser,
    $dbPass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (Throwable $e) {
  die("DB connect error: " . $e->getMessage());
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_login(): void {
  if (!isset($_SESSION["user"]["user_id"])) {
    header("Location: auth-login.php");
    exit;
  }
}
