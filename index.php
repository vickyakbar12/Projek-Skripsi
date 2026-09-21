<?php
require_once 'includes/config.php';
requireLogin(['super_admin', 'admin', 'user']);

$totalMahasiswa = $conn->query("SELECT COUNT(*) as c FROM calon_mahasiswa")->fetch_assoc()['c'];
$totalProdi     = $conn->query("SELECT COUNT(*) as c FROM program_studi")->fetch_assoc()['c'];
$totalKriteria  = $conn->query("SELECT COUNT(*) as c FROM kriteria")->fetch_assoc()['c'];
$totalHasil     = $conn->query("SELECT COUNT(DISTINCT id_mahasiswa) as c FROM hasil_topsis")->fetch_assoc()['c'];
$bobotDone      = $conn->query("SELECT COUNT(*) as c FROM bobot_kriteria")->fetch_assoc()['c'];
$pairwiseDone   = $conn->query("SELECT COUNT(*) as c FROM ahp_pairwise")->fetch_assoc()['c'];

// Recent mahasiswa
$recentMhs = $conn->query("SELECT * FROM calon_mahasiswa ORDER BY id_mahasiswa DESC LIMIT 5");

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<?php if (isAdmin() || isSuperAdmin()): ?> 
<!-- ======================== DASHBOARD ADMIN ======================== -->
<div class="hero-section">
    <div class="hero-badge"><i class="fas fa-star"></i> Sistem Pendukung Keputusan</div>
    <h1>Rekomendasi Program Studi<br>Calon Mahasiswa Baru</h1>
    <p>Menggunakan metode <strong>Analytical Hierarchy Process (AHP)</strong> untuk pembobotan kriteria dan <strong>TOPSIS</strong> untuk perankingan alternatif program studi terbaik.</p>
    <div class="hero-actions">
        <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>pages/calon_mahasiswa.php" class="btn-hero-primary"><i class="fas fa-user-plus"></i> Tambah Mahasiswa</a>
        <a href="<?= BASE_URL ?>pages/ahp.php" class="btn-hero-outline"><i class="fas fa-project-diagram"></i> Kelola AHP</a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>pages/datauser.php" class="btn-hero-primary"><i class="fas fa-users-cog"></i> Kelola Data User</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>pages/hasil.php" class="btn-hero-outline"><i class="fas fa-trophy"></i> Lihat Hasil</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="label">Calon Mahasiswa</div>
            <div class="value"><?= $totalMahasiswa ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-university"></i></div>
        <div class="stat-info">
            <div class="label">Program Studi</div>
            <div class="value"><?= $totalProdi ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-list-check"></i></div>
        <div class="stat-info">
            <div class="label">Kriteria Penilaian</div>
            <div class="value"><?= $totalKriteria ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-trophy"></i></div>
        <div class="stat-info">
            <div class="label">Sudah Diproses</div>
            <div class="value"><?= $totalHasil ?></div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <!-- Alur Sistem -->
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-route"></i></div>
            <h3>Alur Penggunaan Sistem</h3>
        </div>
        <div class="card-body">
            <?php
            $steps = [
                ['Atur Kriteria', 'Pastikan 5 kriteria sudah terisi', 'kriteria.php', $totalKriteria >= 5],
                ['Input Program Studi', 'Data prodi sebagai alternatif', 'program_studi.php', $totalProdi > 0],
                ['Pembobotan AHP', 'Isi matriks perbandingan kriteria', 'ahp.php', $pairwiseDone > 0 && $bobotDone > 0],
                ['Daftarkan Mahasiswa', 'Tambah data calon mahasiswa', 'calon_mahasiswa.php', $totalMahasiswa > 0],
                ['Lihat Hasil', 'Rekomendasi program studi', 'hasil.php', $totalHasil > 0],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;
                    background:<?= $step[3] ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $step[3] ? '#059669' : '#94a3b8' ?>;">
                    <?= $step[3] ? '✓' : ($i+1) ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= $step[0] ?></div>
                    <div style="font-size:11.5px;color:var(--text-muted)"><?= $step[1] ?></div>
                </div>
                <a href="<?= BASE_URL ?>pages/<?= $step[2] ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kriteria -->
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-list-check"></i></div>
            <h3>Kriteria Penilaian</h3>
        </div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr><th>#</th><th>Kode</th><th>Nama Kriteria</th><th>Jenis</th><th>Bobot</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $conn->query("SELECT k.*, b.bobot FROM kriteria k LEFT JOIN bobot_kriteria b ON k.id_kriteria = b.id_kriteria ORDER BY k.id_kriteria");
                    $no = 1;
                    while ($r = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge badge-blue"><?= $r['kode_kriteria'] ?></span></td>
                        <td><?= $r['nama_kriteria'] ?></td>
                        <td>
                            <?php if ($r['jenis']=='benefit'): ?>
                                <span class="badge badge-green">Benefit</span>
                            <?php else: ?>
                                <span class="badge badge-red">Cost</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $r['bobot'] ? '<strong>'.number_format($r['bobot'],4).'</strong>' : '<span class="text-muted">-</span>' ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--border);">
            <?php if ($bobotDone > 0): ?>
                <div class="alert alert-success" style="margin:0">
                    <i class="fas fa-check-circle"></i>
                    Bobot AHP sudah dihitung. Konsistensi tersimpan.
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="margin:0">
                    <i class="fas fa-triangle-exclamation"></i>
                    Bobot belum dihitung. <a href="<?= BASE_URL ?>pages/ahp.php">Isi matriks AHP</a> terlebih dahulu.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mahasiswa terbaru -->
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px">
            <div class="icon"><i class="fas fa-users"></i></div>
            <h3>Calon Mahasiswa Terbaru</h3>
        </div>
        <a href="<?= BASE_URL ?>pages/calon_mahasiswa.php" class="btn btn-sm btn-outline">Lihat Semua</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>#</th><th>Nama</th><th>Asal Sekolah</th><th>Jurusan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php if ($recentMhs->num_rows == 0): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:32px">Belum ada data mahasiswa</td></tr>
                <?php else: ?>
                    <?php $no = 1; while ($r = $recentMhs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                        <td><?= htmlspecialchars($r['asal_sekolah'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['jurusan'] ?? '-') ?></td>
                        <!-- Nilai akademik dihilangkan -->
                        <td>
                            <a href="<?= BASE_URL ?>pages/topsis.php?id=<?= $r['id_mahasiswa'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-calculator"></i> Proses
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ======================== DASHBOARD USER / MAHASISWA ======================== -->
<?php
// Ambil data mahasiswa terkait user yang login
$idMahasiswa = $_SESSION['id_mahasiswa'] ?? 0;
$dataMhs = null;
if ($idMahasiswa) {
    $dataMhs = $conn->query("SELECT * FROM calon_mahasiswa WHERE id_mahasiswa=$idMahasiswa")->fetch_assoc();
}

// Cek status kuesioner
$kuesionerDone = false;
$jumlahKuesioner = 0;
if ($idMahasiswa) {
    $jumlahKuesioner = $conn->query("SELECT COUNT(*) as c FROM nilai_kuesioner WHERE id_mahasiswa=$idMahasiswa")->fetch_assoc()['c'];
    $kuesionerDone = $jumlahKuesioner > 0;
}

// Cek status nilai alternatif
$nilaiAlternatifDone = false;
if ($idMahasiswa) {
    $jumlahAlternatif = $conn->query("SELECT COUNT(*) as c FROM nilai_alternatif WHERE id_mahasiswa=$idMahasiswa")->fetch_assoc()['c'];
    $nilaiAlternatifDone = $jumlahAlternatif > 0;
}

// Cek hasil TOPSIS
$hasilDone = false;
$hasilRekomendasi = [];
if ($idMahasiswa) {
    $resHasil = $conn->query("SELECT h.*, p.nama_prodi, p.jenjang FROM hasil_topsis h JOIN program_studi p ON h.id_prodi=p.id_prodi WHERE h.id_mahasiswa=$idMahasiswa ORDER BY h.ranking LIMIT 3");
    if ($resHasil && $resHasil->num_rows > 0) {
        $hasilDone = true;
        while ($rh = $resHasil->fetch_assoc()) $hasilRekomendasi[] = $rh;
    }
}
?>

<div class="hero-section">
    <div class="hero-badge"><i class="fas fa-user-graduate"></i> Selamat Datang, Mahasiswa</div>
    <h1>Halo, <?= loginName() ?>! 👋</h1>
    <p>Selamat datang di Sistem Pendukung Keputusan Rekomendasi Program Studi. Isi kuesioner, pilih program studi pilihanmu, lalu hitung TOPSIS untuk mendapatkan rekomendasi prodi terbaik!</p>
    <div class="hero-actions">
        <?php if ($idMahasiswa): ?>
            <a href="<?= BASE_URL ?>pages/kuesioner.php?id=<?= $idMahasiswa ?>" class="btn-hero-primary"><i class="fas fa-clipboard-question"></i> Isi Kuesioner</a>
            <a href="<?= BASE_URL ?>pages/hasil.php?id=<?= $idMahasiswa ?>" class="btn-hero-outline"><i class="fas fa-trophy"></i> Lihat Rekomendasi</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>pages/kuesioner.php" class="btn-hero-primary"><i class="fas fa-clipboard-question"></i> Isi Kuesioner</a>
            <a href="<?= BASE_URL ?>pages/hasil.php" class="btn-hero-outline"><i class="fas fa-trophy"></i> Lihat Rekomendasi</a>
        <?php endif; ?>
    </div>
</div>

<!-- Status Progress -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user"></i></div>
        <div class="stat-info">
            <div class="label">Status Akun</div>
            <div class="value" style="font-size:16px;color:var(--success)"><i class="fas fa-check-circle"></i> Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $kuesionerDone ? 'green' : 'amber' ?>"><i class="fas fa-clipboard-question"></i></div>
        <div class="stat-info">
            <div class="label">Kuesioner Profil</div>
            <div class="value" style="font-size:16px;color:<?= $kuesionerDone ? 'var(--success)' : 'var(--warning)' ?>">
                <?= $kuesionerDone ? '<i class="fas fa-check-circle"></i> Sudah Diisi' : '<i class="fas fa-clock"></i> Belum Diisi' ?>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $nilaiAlternatifDone ? 'green' : 'amber' ?>"><i class="fas fa-table-cells"></i></div>
        <div class="stat-info">
            <div class="label">Pilihan Prodi</div>
            <div class="value" style="font-size:16px;color:<?= $nilaiAlternatifDone ? 'var(--success)' : 'var(--warning)' ?>">
                <?= $nilaiAlternatifDone ? '<i class="fas fa-check-circle"></i> Sudah Dipilih' : '<i class="fas fa-clock"></i> Belum Dipilih' ?>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $hasilDone ? 'green' : 'purple' ?>"><i class="fas fa-trophy"></i></div>
        <div class="stat-info">
            <div class="label">Hasil Rekomendasi</div>
            <div class="value" style="font-size:16px;color:<?= $hasilDone ? 'var(--success)' : 'var(--text-muted)' ?>">
                <?= $hasilDone ? '<i class="fas fa-check-circle"></i> Tersedia' : '<i class="fas fa-hourglass-half"></i> Belum Diproses' ?>
            </div>
        </div>
    </div>
</div>

<!-- Alur untuk Mahasiswa -->
<div class="grid-2" style="margin-bottom:24px">
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-route"></i></div>
            <h3>Langkah yang Perlu Kamu Lakukan</h3>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:<?= $dataMhs ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $dataMhs ? '#059669' : '#94a3b8' ?>;">
                    <?= $dataMhs ? '✓' : '1' ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Data Diri Terdaftar</div>
                    <div style="font-size:12px;color:var(--text-muted)">Data kamu sudah terdaftar di sistem oleh admin</div>
                </div>
                <?php if ($dataMhs): ?>
                    <span class="badge badge-green">Selesai</span>
                <?php else: ?>
                    <span class="badge badge-amber">Menunggu Admin</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:<?= $nilaiAlternatifDone ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $nilaiAlternatifDone ? '#059669' : '#94a3b8' ?>;">
                    <?= $nilaiAlternatifDone ? '✓' : '2' ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Pilih Prodi & Beri Nilai Alternatif</div>
                    <div style="font-size:12px;color:var(--text-muted)">Pilih program studi yang diminati, lalu sesuaikan nilai ketertarikannya secara spesifik per prodi</div>
                </div>
                <?php if ($idMahasiswa): ?>
                    <a href="<?= BASE_URL ?>pages/nilai_alternatif.php" class="btn btn-sm <?= $nilaiAlternatifDone ? 'btn-outline' : 'btn-primary' ?>">
                        <i class="fas fa-<?= $nilaiAlternatifDone ? 'edit' : 'arrow-right' ?>"></i>
                        <?= $nilaiAlternatifDone ? 'Edit' : 'Pilih Prodi' ?>
                    </a>
                <?php else: ?>
                    <span class="badge badge-amber">Belum Terdaftar</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:<?= $kuesionerDone ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $kuesionerDone ? '#059669' : '#94a3b8' ?>;">
                    <?= $kuesionerDone ? '✓' : '3' ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Isi Profil Kuesioner Dasar</div>
                    <div style="font-size:12px;color:var(--text-muted)">Berikan skala penilaian umum mengenai minat, bakat, dan profil diri kamu secara keseluruhan</div>
                </div>
                <?php if ($idMahasiswa && $nilaiAlternatifDone): ?>
                    <a href="<?= BASE_URL ?>pages/kuesioner.php?id=<?= $idMahasiswa ?>" class="btn btn-sm <?= $kuesionerDone ? 'btn-outline' : 'btn-primary' ?>">
                        <i class="fas fa-<?= $kuesionerDone ? 'edit' : 'arrow-right' ?>"></i>
                        <?= $kuesionerDone ? 'Edit' : 'Mulai' ?>
                    </a>
                <?php elseif ($idMahasiswa): ?>
                    <span class="badge badge-amber">Pilih Prodi Dulu</span>
                <?php else: ?>
                    <span class="badge badge-amber">Belum Terdaftar</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:<?= $hasilDone ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $hasilDone ? '#059669' : '#94a3b8' ?>;">
                    <?= $hasilDone ? '✓' : '4' ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Hitung TOPSIS</div>
                    <div style="font-size:12px;color:var(--text-muted)">Proses perankingan prodi secara otomatis jika profil dan prodi sudah diset</div>
                </div>
                <?php if ($idMahasiswa && $kuesionerDone && $nilaiAlternatifDone): ?>
                    <a href="<?= BASE_URL ?>pages/topsis.php?auto=1" class="btn btn-sm <?= $hasilDone ? 'btn-outline' : 'btn-primary' ?>">
                        <i class="fas fa-calculator"></i>
                        <?= $hasilDone ? 'Hitung Ulang' : 'Hitung' ?>
                    </a>
                <?php elseif ($idMahasiswa): ?>
                    <span class="badge badge-amber">Selesaikan Step Sebelumnya</span>
                <?php else: ?>
                    <span class="badge badge-amber">Belum Terdaftar</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:<?= $hasilDone ? '#d1fae5' : '#f1f5f9' ?>;
                    color:<?= $hasilDone ? '#059669' : '#94a3b8' ?>;">
                    <?= $hasilDone ? '✓' : '5' ?>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Lihat Hasil Rekomendasi</div>
                    <div style="font-size:12px;color:var(--text-muted)">Hasil perankingan TOPSIS akan tampil setelah proses hitung selesai</div>
                </div>
                <?php if ($hasilDone): ?>
                    <a href="<?= BASE_URL ?>pages/hasil.php?id=<?= $idMahasiswa ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Lihat
                    </a>
                <?php else: ?>
                    <span class="badge badge-amber">Menunggu Proses</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;
                    background:#e8f0fe;
                    color:#1a56db;">
                    <i class="fas fa-external-link-alt"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:var(--dark)">Daftar Mahasiswa Baru (PMB)</div>
                    <div style="font-size:12px;color:var(--text-muted)">Lakukan pendaftaran resmi sebagai mahasiswa UMC</div>
                </div>
                <?php if ($kuesionerDone): ?>
                    <a href="https://pmb.umc.ac.id/signup" target="_blank" class="btn btn-sm" style="background:#10b981;color:#fff;border:none;">
                        <i class="fas fa-paper-plane"></i> Daftar Sekarang
                    </a>
                <?php else: ?>
                    <span class="badge badge-amber">Isi Kuesioner Dulu</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data Diri Mahasiswa -->
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-id-card"></i></div>
            <h3>Data Diri Kamu</h3>
        </div>
        <div class="card-body">
            <?php if ($dataMhs): ?>
                <div style="margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
                        <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--primary),#6366f1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:700;flex-shrink:0">
                            <?= strtoupper(substr($dataMhs['nama'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-size:17px;font-weight:700;color:var(--dark)"><?= htmlspecialchars($dataMhs['nama']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($dataMhs['asal_sekolah'] ?? '-') ?></div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px">
                        <tr><td style="padding:6px 0;color:var(--text-muted);width:130px">Jurusan</td><td style="padding:6px 0">: <strong><?= htmlspecialchars($dataMhs['jurusan'] ?? '-') ?></strong></td></tr>
                        <!-- Nilai akademik dihilangkan -->
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align:center;padding:24px;color:var(--text-muted)">
                    <i class="fas fa-user-xmark" style="font-size:36px;opacity:.3;margin-bottom:12px;display:block"></i>
                    <p style="font-size:13px">Data mahasiswa kamu belum terdaftar.<br>Hubungi admin untuk mendaftarkan data kamu.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($hasilDone && !empty($hasilRekomendasi)): ?>
<!-- Top 3 Rekomendasi untuk User -->
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px">
            <div class="icon"><i class="fas fa-trophy"></i></div>
            <h3>🏆 Rekomendasi Program Studi Untukmu</h3>
        </div>
        <a href="<?= BASE_URL ?>pages/hasil.php?id=<?= $idMahasiswa ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Detail Lengkap</a>
    </div>
    <div class="card-body">
        <div class="grid-3">
            <?php
            $rankStyles = [
                1 => ['bg'=>'linear-gradient(135deg,#f59e0b,#d97706)','icon'=>'🥇','label'=>'Rekomendasi Utama'],
                2 => ['bg'=>'linear-gradient(135deg,#94a3b8,#64748b)','icon'=>'🥈','label'=>'Rekomendasi Kedua'],
                3 => ['bg'=>'linear-gradient(135deg,#cd7f32,#a0522d)','icon'=>'🥉','label'=>'Rekomendasi Ketiga'],
            ];
            foreach ($hasilRekomendasi as $rk):
                $style = $rankStyles[$rk['ranking']] ?? $rankStyles[3];
                $pct = round((float)$rk['nilai_preferensi']*100, 1);
            ?>
            <div style="background:<?= $style['bg'] ?>;border-radius:var(--radius);padding:24px;color:#fff;text-align:center;box-shadow:var(--shadow-lg)">
                <div style="font-size:40px;margin-bottom:8px"><?= $style['icon'] ?></div>
                <div style="font-size:11px;opacity:.8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px"><?= $style['label'] ?></div>
                <div style="font-size:18px;font-weight:800;line-height:1.3;margin-bottom:6px"><?= htmlspecialchars($rk['nama_prodi']) ?></div>
                <div style="background:rgba(255,255,255,.25);border-radius:20px;padding:4px 12px;display:inline-block;font-size:12px;font-weight:700;margin-bottom:12px"><?= $rk['jenjang'] ?></div>
                <div style="background:rgba(0,0,0,.2);border-radius:8px;padding:8px 12px">
                    <div style="font-size:11px;opacity:.8">Nilai Preferensi</div>
                    <div style="font-size:24px;font-weight:800"><?= number_format((float)$rk['nilai_preferensi'],4) ?></div>
                    <div style="font-size:11px;opacity:.7"><?= $pct ?>%</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
