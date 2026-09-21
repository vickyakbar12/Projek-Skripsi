<?php
/**
 * setup.php — Jalankan SEKALI untuk membuat akun admin pertama.
 * HAPUS FILE INI setelah selesai!
 */
require_once 'includes/config.php';

// Cek apakah sudah ada admin
$existing = $conn->query("SELECT COUNT(*) as c FROM admin")->fetch_assoc()['c'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $email    = trim($_POST['email'] ?? '');

    if (!$nama || !$username || !$password) {
        $msg = ['err', 'Semua field wajib diisi.'];
    } elseif (strlen($password) < 6) {
        $msg = ['err', 'Password minimal 6 karakter.'];
    } elseif ($password !== $confirm) {
        $msg = ['err', 'Konfirmasi password tidak cocok.'];
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin (nama, username, password, email) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $nama, $username, $hashed, $email);
        if ($stmt->execute()) {
            // Langsung login
            $_SESSION['role']     = 'admin';
            $_SESSION['id']       = $conn->insert_id;
            $_SESSION['nama']     = $nama;
            $_SESSION['username'] = $username;
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $msg = ['err', 'Gagal menyimpan. Username mungkin sudah digunakan.'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup Admin — SPK Prodi</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(135deg,#0f172a,#1e3a5f,#1a56db);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:16px;padding:40px 36px;max-width:460px;width:100%;box-shadow:0 12px 40px rgba(0,0,0,.2)}
.brand{text-align:center;margin-bottom:28px}
.brand-icon{width:56px;height:56px;background:linear-gradient(135deg,#1a56db,#6366f1);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:24px;color:#fff;margin-bottom:12px}
h2{font-size:20px;font-weight:800;color:#0f172a;margin-bottom:4px}
.sub{font-size:13px;color:#64748b;margin-bottom:24px}
.warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:20px;display:flex;gap:8px;align-items:flex-start}
.alert-ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;font-size:13px;color:#065f46;margin-bottom:20px}
.alert-err{background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:13px;color:#b91c1c;margin-bottom:20px}
.fg{margin-bottom:16px}
label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:5px}
.iw{position:relative}
.iw i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none}
input{width:100%;padding:10px 14px 10px 38px;border:1.5px solid #e2e8f0;border-radius:10px;font-family:inherit;font-size:14px;color:#334155;outline:none}
input:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,.1)}
button{width:100%;padding:13px;background:linear-gradient(135deg,#1a56db,#6366f1);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;margin-top:4px}
button:hover{opacity:.9}
.login-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.login-link a{color:#1a56db;font-weight:600;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-icon"><i class="fas fa-shield-halved"></i></div>
    <h2>Setup Admin Pertama</h2>
    <p class="sub">Buat akun administrator untuk mengelola SPK Prodi</p>
  </div>

  <?php if ($existing > 0): ?>
    <div class="warn"><i class="fas fa-triangle-exclamation"></i>
      Akun admin sudah ada. Halaman ini tidak bisa digunakan lagi.<br>
      Silakan <a href="login.php">login</a> atau hapus file setup.php.
    </div>
  <?php else: ?>

  <?php if ($msg): ?>
    <div class="alert-<?= $msg[0] ?>"><?= $msg[1] ?></div>
  <?php endif; ?>

  <div class="warn"><i class="fas fa-triangle-exclamation" style="margin-top:2px"></i>
    <span>Hapus file <strong>setup.php</strong> dari server setelah akun admin dibuat!</span>
  </div>

  <form method="POST">
    <div class="fg">
      <label>Nama Lengkap</label>
      <div class="iw"><i class="fas fa-id-card"></i>
        <input type="text" name="nama" placeholder="Nama administrator" value="<?= htmlspecialchars($_POST['nama']??'') ?>" required>
      </div>
    </div>
    <div class="fg">
      <label>Username</label>
      <div class="iw"><i class="fas fa-user"></i>
        <input type="text" name="username" placeholder="Username login" value="<?= htmlspecialchars($_POST['username']??'') ?>" required>
      </div>
    </div>
    <div class="fg">
      <label>Email (opsional)</label>
      <div class="iw"><i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="email@domain.com" value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
    </div>
    <div class="fg">
      <label>Password</label>
      <div class="iw"><i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Minimal 6 karakter" required>
      </div>
    </div>
    <div class="fg">
      <label>Konfirmasi Password</label>
      <div class="iw"><i class="fas fa-lock"></i>
        <input type="password" name="confirm" placeholder="Ulangi password" required>
      </div>
    </div>
    <button type="submit"><i class="fas fa-shield-halved"></i> Buat Akun Admin</button>
  </form>
  <?php endif; ?>

  <div class="login-link"><a href="login.php"><i class="fas fa-arrow-left"></i> Kembali ke Login</a></div>
</div>
</body>
</html>
