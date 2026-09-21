<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

if (isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$success = '';
$step = $_POST['step'] ?? 1;
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // Proses validasi email dan kirim OTP
        $email = trim($_POST['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email tidak valid.";
        } else {
            $stmt = $conn->prepare("SELECT id_mahasiswa, nama FROM calon_mahasiswa WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate OTP 6 digit
                $otp = sprintf("%06d", mt_rand(1, 999999));
                // Waktu expired 15 menit
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $upd = $conn->prepare("UPDATE calon_mahasiswa SET reset_otp = ?, reset_otp_expires = ? WHERE email = ?");
                $upd->bind_param('sss', $otp, $expires, $email);
                
                if ($upd->execute()) {
                    // Kirim email
                    if (sendOtpEmail($email, $otp)) {
                        $step = 2; // Lanjut ke step 2
                        $success = "Kode OTP telah dikirim ke email Anda.";
                    } else {
                        $error = "Gagal mengirim email. Pastikan konfigurasi SMTP di file .env sudah benar.";
                    }
                } else {
                    $error = "Terjadi kesalahan pada database.";
                }
                $upd->close();
            } else {
                // Jangan beritahu secara eksplisit jika email tidak terdaftar demi keamanan,
                // tapi karena ini sistem internal, kita beritahu agar user tidak bingung.
                $error = "Email tidak terdaftar sebagai Calon Mahasiswa.";
            }
            $stmt->close();
        }
    } elseif ($step == 2) {
        // Proses verifikasi OTP
        $otp_input = trim($_POST['otp'] ?? '');
        if (!$otp_input) {
            $error = "Masukkan kode OTP.";
        } else {
            $stmt = $conn->prepare("SELECT id_mahasiswa, reset_otp_expires FROM calon_mahasiswa WHERE email = ? AND reset_otp = ? LIMIT 1");
            $stmt->bind_param('ss', $email, $otp_input);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (strtotime($user['reset_otp_expires']) >= time()) {
                    $step = 3; // Lanjut ke step 3
                    $success = "Kode OTP valid. Silakan buat password baru.";
                } else {
                    $error = "Kode OTP sudah kadaluarsa. Silakan ulangi proses lupa password.";
                    $step = 1; // Kembali ke step 1
                }
            } else {
                $error = "Kode OTP salah.";
            }
            $stmt->close();
        }
    } elseif ($step == 3) {
        // Proses reset password
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        
        if (strlen($password) < 6) {
            $error = "Password minimal 6 karakter.";
        } elseif ($password !== $confirm) {
            $error = "Konfirmasi password tidak cocok.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE calon_mahasiswa SET password = ?, reset_otp = NULL, reset_otp_expires = NULL WHERE email = ?");
            $stmt->bind_param('ss', $hashed, $email);
            if ($stmt->execute()) {
                $success = "Password berhasil diubah! Anda akan dialihkan ke halaman login...";
                $step = 4; // Berhasil
            } else {
                $error = "Gagal mengubah password.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — SPK Prodi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a56db;
            --primary-light: #e8f0fe;
            --dark: #0f172a;
            --text: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --white: #ffffff;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 16px;
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.25);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
        }

        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('assets/img/bg-login.jpg'); background-size: cover; background-position: center;
            opacity: 0.70; z-index: 0;
        }
        body::after {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,95,0.75) 50%, rgba(26,86,219,0.65) 100%);
            z-index: 1;
        }

        .login-wrap { position: relative; z-index: 2; width: 100%; max-width: 440px; }
        
        .card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            padding: 40px 36px 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .card-header { margin-bottom: 24px; text-align: center; }
        .card-title { font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
        .card-subtitle { font-size: 13px; color: var(--text-muted); line-height: 1.5; }

        /* Alert */
        .alert {
            border-radius: 11px; padding: 13px 16px; font-size: 13px; margin-bottom: 22px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-error { background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; }
        .alert-success { background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #047857; }

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; pointer-events: none; }
        .form-control {
            width: 100%; padding: 12px 14px 12px 42px; border: 1.5px solid var(--border); border-radius: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; color: var(--text); background: #fafbfc; outline: none;
        }
        .form-control:focus { background: var(--white); border-color: var(--primary); box-shadow: 0 0 0 3.5px rgba(26,86,219,0.10); }
        
        .otp-input {
            text-align: center; letter-spacing: 10px; font-size: 24px; font-weight: 700; padding: 12px; padding-left: 12px;
        }

        /* Button */
        .btn-primary {
            width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
            color: #fff; border: none; border-radius: 11px; font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px; font-weight: 700; cursor: pointer; transition: opacity .2s; margin-top: 6px;
        }
        .btn-primary:hover { opacity: .92; }

        .btn-back {
            display: block; width: 100%; text-align: center; margin-top: 16px; font-size: 13px; color: var(--text-muted); text-decoration: none; font-weight: 600;
        }
        .btn-back:hover { color: var(--primary); }

        .step-indicator {
            display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;
        }
        .step-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1;
        }
        .step-dot.active { background: var(--primary); width: 24px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="card">
        <div class="card-header">
            <?php if ($step == 1): ?>
                <div class="card-title">Lupa Password? 🔐</div>
                <div class="card-subtitle">Masukkan email terdaftar Anda untuk menerima kode OTP.</div>
            <?php elseif ($step == 2): ?>
                <div class="card-title">Masukkan OTP ✉️</div>
                <div class="card-subtitle">Kami telah mengirimkan 6-digit kode OTP ke <strong><?= htmlspecialchars($email) ?></strong>.</div>
            <?php elseif ($step == 3): ?>
                <div class="card-title">Buat Password Baru 🛡️</div>
                <div class="card-subtitle">Silakan masukkan password baru Anda.</div>
            <?php elseif ($step == 4): ?>
                <div class="card-title">Berhasil! 🎉</div>
                <div class="card-subtitle">Password Anda telah berhasil diubah.</div>
            <?php endif; ?>
        </div>

        <?php if ($step <= 3): ?>
            <div class="step-indicator">
                <div class="step-dot <?= $step == 1 ? 'active' : '' ?>"></div>
                <div class="step-dot <?= $step == 2 ? 'active' : '' ?>"></div>
                <div class="step-dot <?= $step == 3 ? 'active' : '' ?>"></div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation" style="margin-top:2px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="margin-top:2px;"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="POST">
                <input type="hidden" name="step" value="1">
                <div class="form-group">
                    <label class="form-label">Email Anda</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Kirim Kode OTP</button>
                <a href="login.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
            </form>

        <?php elseif ($step == 2): ?>
            <form method="POST">
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label class="form-label">Kode OTP 6 Digit</label>
                    <div class="input-wrap">
                        <input type="text" class="form-control otp-input" name="otp" maxlength="6" autocomplete="off" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Verifikasi OTP</button>
                
                <div style="text-align: center; margin-top: 16px;">
                    <button type="submit" name="step" value="1" style="background:none; border:none; color:var(--text-muted); text-decoration:underline; cursor:pointer; font-size:13px;">Belum menerima kode? Kirim ulang.</button>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <form method="POST">
                <input type="hidden" name="step" value="3">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" minlength="6" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Ulangi Password Baru</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="confirm" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Simpan Password Baru</button>
            </form>

        <?php elseif ($step == 4): ?>
            <a href="login.php" class="btn-primary" style="display:block; text-align:center; text-decoration:none;">Pergi ke Halaman Login</a>
            
            <script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            </script>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
