<?php

function upload_profile_image(array $file, string $targetDirAbs, string $targetDirRel): string
{
  if (!isset($file['error']) || is_array($file['error'])) {
    throw new RuntimeException('Upload file tidak valid.');
  }

  // 1) cek error upload
  if ($file['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload gagal (error code: ' . $file['error'] . ').');
  }

  // 2) batasi ukuran (misal max 2MB)
  $maxBytes = 2 * 1024 * 1024;
  if (($file['size'] ?? 0) > $maxBytes) {
    throw new RuntimeException('Ukuran file terlalu besar. Maksimal 2MB.');
  }

  // 3) pastikan benar-benar gambar (cek mime dari konten)
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($file['tmp_name']);

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($allowed[$mime])) {
    throw new RuntimeException('Tipe file tidak diizinkan. Hanya JPG/PNG/WEBP.');
  }

  // 4) buat nama file random
  $ext = $allowed[$mime];
  $newName = 'user_' . bin2hex(random_bytes(12)) . '.' . $ext;

  // 5) pastikan folder ada
  if (!is_dir($targetDirAbs)) {
    if (!mkdir($targetDirAbs, 0755, true) && !is_dir($targetDirAbs)) {
      throw new RuntimeException('Gagal membuat folder upload.');
    }
  }

  // 6) simpan
  $destAbs = rtrim($targetDirAbs, '/\\') . DIRECTORY_SEPARATOR . $newName;
  if (!move_uploaded_file($file['tmp_name'], $destAbs)) {
    throw new RuntimeException('Gagal menyimpan file upload.');
  }

  // 7) return path RELATIVE untuk disimpan ke DB
  // contoh: uploads/users/user_xxx.jpg
  return rtrim($targetDirRel, '/\\') . '/' . $newName;
}
