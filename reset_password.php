<?php
/**
 * reset_password.php
 * Utilitas untuk mengatur ulang password admin atau membuat akun user baru.
 * HAPUS FILE INI setelah selesai digunakan di production!
 */
require_once 'includes/config.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || strlen($password) < 6) {
        $msg = '<div class="alert err">Username wajib diisi dan password minimal 6 karakter.</div>';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($action === 'reset_admin') {
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
            $stmt->bind_param('ss', $hashed, $username);
            $stmt->execute();
            $msg = $stmt->affected_rows > 0
                ? '<div class="alert ok">Password admin <strong>' . htmlspecialchars($username) . '</strong> berhasil diperbarui.</div>'
                : '<div class="alert err">Username admin tidak ditemukan.</div>';

        } elseif ($action === 'new_admin') {
            $stmt = $conn->prepare("INSERT INTO admin (nama, username, password, email) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $nama, $username, $hashed, $email);
            if ($stmt->execute()) {
                $msg = '<div class="alert ok">Akun admin <strong>' . htmlspecialchars($username) . '</strong> berhasil dibuat.</div>';
            } else {
                $msg = '<div class="alert err">Gagal: username mungkin sudah ada.</div>';
            }

        } elseif ($action === 'new_user') {
            $stmt = $conn->prepare("INSERT INTO calon_mahasiswa (nama, username, password, status) VALUES (?,?,?,'aktif')");
            $stmt->bind_param('sss', $nama, $username, $hashed);
            if ($stmt->execute()) {
                $msg = '<div class="alert ok">Akun user <strong>' . htmlspecialchars($username) . '</strong> berhasil dibuat.</div>';
            } else {
                $msg = '<div class="alert err">Gagal: username mungkin sudah ada.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password — SPK Prodi</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  body{font-family:'Plus Jakarta Sans',sans-serif;background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
  .card{background:#fff;border-radius:14px;padding:36px 32px;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.1)}
  h2{font-size:20px;margin-bottom:4px;color:#0f172a}
  .sub{font-size:13px;color:#64748b;margin-bottom:24px}
  .warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px;font-size:13px;color:#92400e;margin-bottom:20px}
  .form-group{margin-bottom:16px}
  label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:5px}
  input,select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:inherit;font-size:14px;outline:none}
  input:focus,select:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,.1)}
  button{width:100%;padding:12px;background:#1a56db;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px}
  button:hover{background:#1042a8}
  .alert{border-radius:8px;padding:12px 16px;font-size:14px;margin-bottom:20px}
  .alert.ok{background:#d1fae5;color:#065f46}
  .alert.err{background:#fee2e2;color:#b91c1c}
</style>
</head>
<body>
<div class="card">
  <h2>⚙️ Manajemen Akun</h2>
  <p class="sub">Buat akun baru atau reset password. <strong>Hapus file ini setelah selesai!</strong></p>
  <div class="warn">⚠️ File ini tidak dilindungi login. Gunakan hanya saat setup awal, lalu hapus dari server.</div>

  <?= $msg ?>

  <form method="POST">
    <div class="form-group">
      <label>Tindakan</label>
      <select name="action" onchange="toggleNama(this.value)">
        <option value="reset_admin">Reset Password Admin</option>
        <option value="new_admin">Buat Akun Admin Baru</option>
        <option value="new_user">Buat Akun User / Mahasiswa Baru</option>
      </select>
    </div>
    <div class="form-group" id="wrap-nama" style="display:none">
      <label>Nama Lengkap</label>
      <input type="text" name="nama" placeholder="Nama lengkap">
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="Username" required>
    </div>
    <div class="form-group" id="wrap-email" style="display:none">
      <label>Email (opsional)</label>
      <input type="email" name="email" placeholder="email@contoh.com">
    </div>
    <div class="form-group">
      <label>Password Baru</label>
      <input type="password" name="password" placeholder="Minimal 6 karakter" required>
    </div>
    <button type="submit">Proses</button>
  </form>
</div>
<script>
function toggleNama(val) {
  const showExtra = val === 'new_admin' || val === 'new_user';
  document.getElementById('wrap-nama').style.display  = showExtra ? '' : 'none';
  document.getElementById('wrap-email').style.display = showExtra ? '' : 'none';
}
</script>
</body>
</html>
