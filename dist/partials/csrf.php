<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
  return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
