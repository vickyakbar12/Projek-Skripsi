<?php
require_once 'includes/config.php';

// Kalau sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        $account  = null;
        $foundRole = null;

        // ── 1. Cari di tabel admin (mencakup role: admin & super_admin) ──────
        $stmt = $conn->prepare("SELECT id_admin AS id, nama, username, password, role FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $account = $result->fetch_assoc();
        }
        $stmt->close();

        // ── 2. Jika tidak ditemukan di admin, cari di calon_mahasiswa ─────────
        if (!$account) {
            $stmt = $conn->prepare("SELECT id_mahasiswa AS id, nama, username, password, 'user' AS role FROM calon_mahasiswa WHERE username = ? LIMIT 1");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $account = $result->fetch_assoc();
            }
            $stmt->close();
        }

        // ── 3. Validasi password ──────────────────────────────────────────────
        if ($account && password_verify($password, $account['password'])) {
            // Set session sesuai role yang terdeteksi dari DB
            $_SESSION['role']     = $account['role'];   // 'super_admin' | 'admin' | 'user'
            $_SESSION['id']       = $account['id'];
            $_SESSION['nama']     = $account['nama'];
            $_SESSION['username'] = $account['username'];

            // Khusus user/mahasiswa, simpan juga id_mahasiswa
            if ($account['role'] === 'user') {
                $_SESSION['id_mahasiswa'] = $account['id'];
            }

            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SPK Prodi</title>
    <meta name="description" content="Masuk ke Sistem Pendukung Keputusan Rekomendasi Program Studi menggunakan akun Anda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a56db;
            --primary-dark: #1042a8;
            --primary-light: #e8f0fe;
            --dark: #0f172a;
            --dark2: #1e293b;
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
            overflow: hidden;
        }

        /* Background image */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: url('assets/img/bg-login.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.70;
            z-index: 0;
        }

        /* Overlay gradient */
        body::after {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,95,0.75) 50%, rgba(26,86,219,0.65) 100%);
            z-index: 1;
        }

        /* Animated blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            z-index: 1;
            animation: drift 8s ease-in-out infinite alternate;
        }
        .blob-1 { width: 400px; height: 400px; background: #1a56db; top: -120px; right: -80px; animation-delay: 0s; }
        .blob-2 { width: 300px; height: 300px; background: #6366f1; bottom: -80px; left: -60px; animation-delay: 3s; }
        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(20px, 30px) scale(1.05); }
        }

        .login-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 68px; height: 68px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            border-radius: 20px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px; color: #fff;
            margin-bottom: 16px;
            box-shadow: 0 12px 30px rgba(26,86,219,0.5);
            position: relative;
            overflow: hidden;
        }
        .brand-icon::after {
            content: '';
            position: absolute;
            top: -40%;
            left: -40%;
            width: 80%;
            height: 80%;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
        }
        .brand h1 {
            font-family: 'Sora', sans-serif;
            font-size: 24px; font-weight: 700;
            color: #fff; margin-bottom: 6px;
            letter-spacing: -0.3px;
        }
        .brand p {
            font-size: 13px; color: rgba(255,255,255,0.55);
            line-height: 1.5;
        }

        /* Card */
        .card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            padding: 40px 36px 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .card-header {
            margin-bottom: 28px;
            text-align: center;
        }
        .card-title {
            font-family: 'Sora', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }
        .card-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
        }
        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            background: #fafbfc;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .form-control:hover {
            border-color: #c7d2fe;
        }
        .form-control:focus {
            background: var(--white);
            border-color: var(--primary);
            box-shadow: 0 0 0 3.5px rgba(26,86,219,0.10);
        }
        .form-control:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--primary);
        }
        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            background: none; border: none;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
        }
        .toggle-pass:hover {
            color: var(--primary);
            background: var(--primary-light);
        }

        /* Alert */
        .alert-error {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 11px;
            padding: 13px 16px;
            font-size: 13px;
            color: #b91c1c;
            margin-bottom: 22px;
            display: flex; align-items: flex-start; gap: 10px;
            animation: shake 0.4s ease;
        }
        .alert-error i { margin-top: 1px; flex-shrink: 0; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-5px); }
            40%       { transform: translateX(5px); }
            60%       { transform: translateX(-3px); }
            80%       { transform: translateX(3px); }
        }

        /* Submit */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
            color: #fff;
            border: none;
            border-radius: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            margin-top: 6px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 6px 20px rgba(26,86,219,0.35);
            letter-spacing: 0.1px;
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover { opacity: .92; box-shadow: 0 8px 28px rgba(26,86,219,0.45); }
        .btn-login:hover::before { left: 100%; }
        .btn-login:active { transform: scale(.98); }

        /* Loading state */
        .btn-login.loading .btn-text { display: none; }
        .btn-login .btn-spinner { display: none; }
        .btn-login.loading .btn-spinner { display: flex; align-items: center; gap: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-ring {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 20px 0;
            font-size: 12px; color: var(--text-muted);
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--border);
        }

        .btn-register {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 12px;
            border: 1.5px solid var(--border); border-radius: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 600;
            color: var(--primary); text-align: center; text-decoration: none;
            transition: background .2s, border-color .2s, transform .15s;
            background: transparent;
        }
        .btn-register:hover {
            background: var(--primary-light);
            border-color: #c7d2fe;
        }
        .btn-register:active { transform: scale(.98); }

        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: rgba(255,255,255,0.45);
        }
    </style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="login-wrap">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <h1>SPK Prodi</h1>
        <p>Sistem Pendukung Keputusan Rekomendasi Program Studi</p>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Selamat Datang Calon Mahasiswa UMC 👋</div>
        </div>

        <?php if ($error): ?>
        <div class="alert-error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="login-form" novalidate>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Masukkan username Anda"
                           autocomplete="username"
                           required autofocus>
                </div>
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">
                    <label class="form-label" for="password" style="margin-bottom:0;">Password</label>
                    <a href="forgot_password.php" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">Lupa Password?</a>
                </div>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Masukkan password Anda"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-pass" id="toggle-pass" onclick="togglePassword()" aria-label="Tampilkan / sembunyikan password">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="btn-login">
                <span class="btn-text">
                    <i class="fas fa-right-to-bracket"></i> Masuk
                </span>
                <span class="btn-spinner">
                    <span class="spinner-ring"></span> Memverifikasi...
                </span>
            </button>
        </form>

        <div class="divider">atau</div>

        <a href="register.php" class="btn-register" id="btn-register">
            <i class="fas fa-user-plus"></i> Register
        </a>
    </div>

    <div class="login-footer">
        &copy; SPK Prodi &middot; AHP + TOPSIS
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Loading state saat submit
document.getElementById('login-form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) return; // biarkan browser validasi native

    const btn = document.getElementById('btn-login');
    btn.classList.add('loading');
    btn.disabled = true;
});
</script>
</body>
</html>
