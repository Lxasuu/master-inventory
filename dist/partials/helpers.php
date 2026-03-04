<?php
function e($str) {
  return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
}

function url($path) {
  return "/HTML/dist/" . ltrim($path, "/");
}

function get_user_photo($photoDb) {
    global $BASE_URL;
    $base = $BASE_URL ?? "/HTML/dist/";
    if (!$photoDb) {
        return $base . "assets/images/default-avatar.png";
    }
    return $base . ltrim($photoDb, "/");
}
