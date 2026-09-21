<?php
require_once 'includes/config.php';

// Sudah login → redirect
if (isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
    $jurusan  = trim($_POST['jurusan'] ?? '');

    // Validasi
    if (!$nama)                          $errors[] = 'Nama lengkap wajib diisi.';
    if (!$asal_sekolah)                  $errors[] = 'Asal sekolah wajib diisi.';
    if (!$username)                      $errors[] = 'Username wajib diisi.';
    if (strlen($username) < 4)           $errors[] = 'Username minimal 4 karakter.';
    if (!$email)                         $errors[] = 'Email wajib diisi.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!$password)                      $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 6)           $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirm)          $errors[] = 'Konfirmasi password tidak cocok.';

    // Cek username sudah ada
    if (!$errors) {
        $chk = $conn->prepare("SELECT id_mahasiswa FROM calon_mahasiswa WHERE username = ? LIMIT 1");
        $chk->bind_param('s', $username);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Username sudah digunakan, pilih username lain.';
        }
        $chk->close();
        
        // Cek email duplikat
        $chkEmail = $conn->prepare("SELECT id_mahasiswa FROM calon_mahasiswa WHERE email = ? LIMIT 1");
        $chkEmail->bind_param('s', $email);
        $chkEmail->execute();
        if ($chkEmail->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar, gunakan email lain.';
        }
        $chkEmail->close();
    }

    if (!$errors) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert langsung ke calon_mahasiswa
        $stmtMhs = $conn->prepare("INSERT INTO calon_mahasiswa (nama, asal_sekolah, jurusan, username, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtMhs->bind_param('ssssss', $nama, $asal_sekolah, $jurusan, $username, $email, $hashed);
        if ($stmtMhs->execute()) {
            $success = true;
        } else {
            $errors[] = 'Gagal menyimpan akun mahasiswa.';
        }
        $stmtMhs->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — SPK Prodi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a56db;
            --primary-dark: #1042a8;
            --primary-light: #e8f0fe;
            --dark: #0f172a;
            --text: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --white: #ffffff;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 14px;
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1a56db 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .wrap { width: 100%; max-width: 460px; }
        /* Brand */
        .brand { text-align: center; margin-bottom: 28px; }
        .brand-icon {
            width: 58px; height: 58px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; color: #fff; margin-bottom: 12px;
            box-shadow: 0 8px 24px rgba(26,86,219,0.45);
        }
        .brand h1 { font-family:'Sora',sans-serif; font-size:21px; font-weight:700; color:#fff; margin-bottom:4px; }
        .brand p  { font-size:13px; color:rgba(255,255,255,0.55); }
        /* Card */
        .card { background:var(--white); border-radius:var(--radius); padding:36px 32px; box-shadow:var(--shadow-lg); }
        .card-title { font-size:18px; font-weight:800; color:var(--dark); margin-bottom:4px; }
        .card-sub   { font-size:13px; color:var(--text-muted); margin-bottom:24px; }
        /* Alert */
        .alert-err {
            background:#fef2f2; border:1px solid #fecaca;
            border-radius:10px; padding:12px 16px;
            font-size:13px; color:#b91c1c; margin-bottom:18px;
        }
        .alert-err ul { padding-left:16px; margin-top:4px; }
        .alert-ok {
            background:#d1fae5; border:1px solid #6ee7b7;
            border-radius:10px; padding:16px;
            font-size:14px; color:#065f46; margin-bottom:18px;
            text-align:center;
        }
        .alert-ok .ok-icon { font-size:32px; margin-bottom:8px; }
        .alert-ok strong   { display:block; font-size:16px; margin-bottom:4px; }
        /* Form */
        .form-group { margin-bottom:15px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:var(--text); margin-bottom:5px; }
        .input-wrap { position:relative; }
        .input-wrap i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; pointer-events:none; }
        .form-control {
            width:100%; padding:10px 14px 10px 38px;
            border:1.5px solid var(--border); border-radius:10px;
            font-family:'Plus Jakarta Sans',sans-serif; font-size:14px;
            color:var(--text); background:var(--white); outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(26,86,219,0.12); }
        .toggle-pass { position:absolute; right:13px; top:50%; transform:translateY(-50%); color:var(--text-muted); background:none; border:none; cursor:pointer; font-size:13px; padding:0; }
        /* Strength bar */
        .strength-bar { height:4px; border-radius:4px; margin-top:6px; background:var(--border); overflow:hidden; }
        .strength-fill { height:100%; border-radius:4px; width:0; transition:width .3s, background .3s; }
        /* Button */
        .btn-primary {
            width:100%; padding:13px;
            background:linear-gradient(135deg, var(--primary), #6366f1);
            color:#fff; border:none; border-radius:10px;
            font-family:'Plus Jakarta Sans',sans-serif; font-size:15px; font-weight:700;
            cursor:pointer; margin-top:8px;
            transition:opacity .2s, transform .1s;
        }
        .btn-primary:hover { opacity:.9; }
        .btn-primary:active { transform:scale(.98); }
        .btn-outline {
            display:block; width:100%; padding:11px;
            border:1.5px solid var(--border); border-radius:10px;
            font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:600;
            color:var(--primary); text-align:center; text-decoration:none;
            margin-top:10px; transition:background .2s;
        }
        .btn-outline:hover { background:var(--primary-light); }
        .divider { text-align:center; font-size:12px; color:var(--text-muted); margin:14px 0; }
        .footer { text-align:center; margin-top:18px; font-size:12px; color:var(--text-muted); }
        .footer a { color:var(--primary); font-weight:600; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <h1>SPK Prodi</h1>
        <p>Sistem Pendukung Keputusan Rekomendasi Program Studi</p>
    </div>

    <div class="card">
        <div class="card-title">Buat Akun Baru</div>
        <div class="card-sub">Daftarkan diri Anda sebagai Calon Mahasiswa</div>

        <?php if ($success): ?>
        <div class="alert-ok">
            <div class="ok-icon">✅</div>
            <strong>Akun berhasil dibuat!</strong>
            Silakan login dengan username dan password Anda.
        </div>
        <a href="login.php" class="btn-primary" style="display:block;text-align:center;text-decoration:none;padding:13px;border-radius:10px;color:#fff;">
            <i class="fas fa-right-to-bracket"></i> Pergi ke Halaman Login
        </a>

        <?php else: ?>

        <?php if ($errors): ?>
        <div class="alert-err">
            <i class="fas fa-circle-exclamation"></i> <strong>Perbaiki kesalahan berikut:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php">


            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-wrap">
                    <i class="fas fa-id-card"></i>
                    <input type="text" class="form-control" name="nama"
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                           placeholder="Nama lengkap Anda" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Minimal 4 karakter" required>
                </div>
            </div>

            <div class="form-group" id="email_group">
                <label class="form-label">Email Aktif</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="Untuk keperluan Lupa Password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Asal Sekolah</label>
                <div class="input-wrap">
                    <i class="fas fa-school"></i>
                    <input type="text" class="form-control" name="asal_sekolah"
                           value="<?= htmlspecialchars($_POST['asal_sekolah'] ?? '') ?>"
                           placeholder="Nama sekolah asal (SMA/SMK/MA)" required>
                </div>
            </div>

            <div class="form-group" id="jurusan_group">
                <label class="form-label">Jurusan <span style="color:var(--text-muted);font-weight:400">(opsional)</span></label>
                <div class="input-wrap">
                    <i class="fas fa-graduation-cap"></i>
                    <select name="jurusan" class="form-control" style="cursor:pointer">
                        <option value="" <?= empty($_POST['jurusan']) ? 'selected' : '' ?>>-- Pilih Jurusan --</option>
                        <option value="IPA"        <?= ($_POST['jurusan'] ?? '') === 'IPA'        ? 'selected' : '' ?>>IPA</option>
                        <option value="IPS"        <?= ($_POST['jurusan'] ?? '') === 'IPS'        ? 'selected' : '' ?>>IPS</option>
                        <option value="Bahasa"     <?= ($_POST['jurusan'] ?? '') === 'Bahasa'     ? 'selected' : '' ?>>Bahasa</option>
                        <option value="SMK Teknik" <?= ($_POST['jurusan'] ?? '') === 'SMK Teknik' ? 'selected' : '' ?>>SMK Teknik</option>
                        <option value="SMK Bisnis" <?= ($_POST['jurusan'] ?? '') === 'SMK Bisnis' ? 'selected' : '' ?>>SMK Bisnis</option>
                        <option value="Lainnya"    <?= ($_POST['jurusan'] ?? '') === 'Lainnya'    ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="pw" name="password"
                           placeholder="Minimal 6 karakter" oninput="checkStrength(this.value)" required>
                    <button type="button" class="toggle-pass" onclick="togglePw('pw','eye1')">
                        <i class="fas fa-eye" id="eye1"></i>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="sbar"></div></div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="pw2" name="confirm"
                           placeholder="Ulangi password" required>
                    <button type="button" class="toggle-pass" onclick="togglePw('pw2','eye2')">
                        <i class="fas fa-eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>

        <div class="divider">— atau —</div>
        <a href="login.php" class="btn-outline"><i class="fas fa-right-to-bracket"></i> Sudah punya akun? Login</a>

        <?php endif; ?>
    </div>

    <div class="footer">&copy; <?= date('Y') ?> SPK Prodi &middot; AHP + TOPSIS</div>
</div>

<script>
function togglePw(id, iconId) {
    const input = document.getElementById(id);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrength(pw) {
    const bar = document.getElementById('sbar');
    let score = 0;
    if (pw.length >= 6)  score++;
    if (pw.length >= 10) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    const colors = ['#ef4444','#f59e0b','#f59e0b','#10b981','#1a56db'];
    bar.style.width  = (score * 20) + '%';
    bar.style.background = colors[score - 1] || '#e2e8f0';
}


</script>
</body>
</html>
