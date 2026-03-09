<?php
function e($str) {
  return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
}

function url($path) {
  return "/HTML/" . ltrim($path, "/");
}

function get_user_photo($photoDb) {
    if (!$photoDb) {
        return "/HTML/assets/images/default-avatar.png";
    }
    // If it's a full path starting with uploads/, prefix with /HTML/
    return "/HTML/" . ltrim($photoDb, "/");
}
