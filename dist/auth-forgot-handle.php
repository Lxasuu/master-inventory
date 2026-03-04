<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false, // ubah true kalau sudah HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . "/config/db.php";

// composer autoload (vendor ada di HTML/, sedangkan file ini di HTML/dist/)
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require __DIR__ . "/config/mail.php";

$login = trim($_POST["login"] ?? "");
if ($login === "") {
    header("Location: auth-forgot.php");
    exit;
}

function redirectSent() {
    header("Location: auth-forgot.php?sent=1");
    exit;
}

// cari user
$stmt = $pdo->prepare("
    SELECT user_id, email, is_active
    FROM users
    WHERE email = ? OR username = ?
    LIMIT 1
");
$stmt->execute([$login, $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// selalu return sukses (anti enumeration)
if (!$user || (int)$user["is_active"] !== 1) {
    redirectSent();
}

$userId = (int)$user["user_id"];
$email  = $user["email"];

// hapus token lama (1 token aktif)
$pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$userId]);

// generate token
$token = bin2hex(random_bytes(32));
$tokenHash = password_hash($token, PASSWORD_DEFAULT);
$expiresAt = date("Y-m-d H:i:s", strtotime("+30 minutes"));

$ins = $pdo->prepare("
    INSERT INTO password_resets (user_id, token_hash, expires_at)
    VALUES (?, ?, ?)
");
$ins->execute([$userId, $tokenHash, $expiresAt]);

// build reset link
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER["HTTP_HOST"];
$basePath = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\"); // hasil: /HTML/dist
$resetLink = $scheme . "://" . $host . $basePath . "/auth-reset.php?uid=" . $userId . "&token=" . urlencode($token);

// ==== KIRIM EMAIL via PHPMailer SMTP ====
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    // SMTP
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->Port       = $mailConfig['port'];
    $mail->SMTPSecure = $mailConfig['encryption']; // 'tls' atau 'ssl'

    // sender & recipient
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($email);

    // content
    $mail->isHTML(true);
    $mail->Subject = 'Reset Password - Meta Inventory';

    $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

    $mail->Body = '
        <div style="font-family: Arial, sans-serif; line-height:1.6;">
            <h2>Reset Password</h2>
            <p>Halo,</p>
            <p>Klik tombol/link berikut untuk reset password. Link berlaku <b>30 menit</b>.</p>
            <p>
                <a href="'.$safeLink.'"
                   style="display:inline-block;padding:10px 14px;background:#4b5cff;color:#fff;text-decoration:none;border-radius:10px;">
                   Reset Password
                </a>
            </p>
            <p>Atau copy link ini:</p>
            <p><a href="'.$safeLink.'">'.$safeLink.'</a></p>
            <p>Jika kamu tidak meminta reset, abaikan email ini.</p>
            <hr>
            <small>Meta Inventory System</small>
        </div>
    ';

    $mail->AltBody = "Reset Password (30 menit):\n$resetLink\n\nJika kamu tidak meminta reset, abaikan email ini.";

    $mail->send();
} catch (Exception $e) {
   file_put_contents(__DIR__.'/mail_error.log', date('c').' '.$mail->ErrorInfo.PHP_EOL, FILE_APPEND);
}

redirectSent();
