<?php
function require_role($roles) {
  if (!is_array($roles)) $roles = [$roles];
  $current = $_SESSION['user']['role'] ?? null;

  if (!$current || !in_array($current, $roles, true)) {
    http_response_code(403);
    echo "403 Forbidden - Anda tidak punya akses.";
    exit;
  }
}

function can($roles) {
  if (!is_array($roles)) $roles = [$roles];
  $current = $_SESSION['user']['role'] ?? null;
  return $current && in_array($current, $roles, true);
}
