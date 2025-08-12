<?php
// index.php - single-file interface to send Telegram official OTP using MadelineProto
// WARNING: This script downloads madeline.php from official source if not present.
// REQUIRED: PHP 7.4+, ext-gmp, ext-openssl, writable 'sessions/' directory.
// Replace api_id/api_hash with your values or keep as set below.

error_reporting(E_ALL);
ini_set('display_errors', 1);

$api_id = 18148868;
$api_hash = 'f3952fb78d7ea8932d3c02386b347051';

// Ensure sessions directory exists and writable
@mkdir(__DIR__ . '/sessions', 0777, true);

// Try to download madeline.php if missing
if (!file_exists(__DIR__ . '/madeline.php')) {
    // attempt to download official phar (may be disabled on some hosts)
    $pharUrl = 'https://phar.madelineproto.xyz/madeline.php';
    if (ini_get('allow_url_fopen')) {
        @copy($pharUrl, __DIR__ . '/madeline.php');
    }
}

$madelineAvailable = file_exists(__DIR__ . '/madeline.php');

$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone']) && $madelineAvailable) {
    $phone = trim($_POST['phone']);
    if ($phone === '') {
        $status = 'Nomor tidak boleh kosong.';
    } else {
        try {
            require_once __DIR__ . '/madeline.php';
            // settings
            $settings = ['app_info' => ['api_id' => (int)$api_id, 'api_hash' => $api_hash]];
            $sessionFile = __DIR__ . '/sessions/session.madeline';
            $MadelineProto = new \danog\MadelineProto\API($sessionFile, $settings);

            // send OTP (phoneLogin will trigger Telegram to send code)
            $MadelineProto->phoneLogin($phone);

            $status = 'OTP resmi Telegram telah dikirim ke: ' . htmlspecialchars($phone);
        } catch (\Throwable $e) {
            $status = 'Gagal mengirim OTP: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kirim OTP Telegram (MadelineProto)</title>
  <style>
    body{font-family:system-ui,Arial;background:#f3f4f6;margin:0;padding:40px}
    .card{max-width:520px;margin:0 auto;background:#fff;padding:24px;border-radius:12px;box-shadow:0 6px 24px rgba(16,24,40,0.08)}
    input{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:8px;margin-top:8px}
    button{padding:12px 16px;background:#0066cc;color:#fff;border:0;border-radius:8px;cursor:pointer;margin-top:12px}
    .muted{color:#6b7280;font-size:13px}
    pre{background:#0b1220;color:#e6eef6;padding:12px;border-radius:8px;overflow:auto}
  </style>
</head>
<body>
  <div class="card">
    <h2>Kirim OTP Resmi Telegram</h2>
    <p class="muted">Masukkan nomor Telegram (sertakan kode negara), misal <code>+6281234567890</code></p>
    <?php if (!$madelineAvailable): ?>
      <p style="color:#b91c1c">madeline.php belum tersedia dan otomatis download gagal. Silakan unduh manual dari <a href="https://phar.madelineproto.xyz/madeline.php" target="_blank">https://phar.madelineproto.xyz/madeline.php</a> lalu upload ke folder ini.</p>
    <?php endif; ?>
    <?php if ($status): ?>
      <div style="margin:12px 0;padding:10px;background:#eef2ff;border-radius:8px"><?php echo htmlspecialchars($status); ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Nomor HP Telegram</label>
      <input type="text" name="phone" placeholder="+6281234567890" required>
      <button type="submit" <?php echo $madelineAvailable ? '' : 'disabled'; ?>>Kirim OTP</button>
    </form>

    <hr style="margin:18px 0">
    <p class="muted">Catatan teknis:</p>
    <ul class="muted">
      <li>Butuh ekstensi PHP: <code>gmp</code>, <code>openssl</code>, dan <code>curl</code> (direkomendasikan).</li>
      <li>Folder <code>sessions/</code> harus bisa ditulisi oleh web server user (chmod 0777 sessions).</li>
      <li>Jika hosting memblokir koneksi keluar ke IP Telegram, script tidak akan berhasil.</li>
    </ul>
  </div>
</body>
</html>

