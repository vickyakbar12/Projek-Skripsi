-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 16, 2026 at 02:05 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_spk_revisi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Di-hash dengan password_hash() PHP',
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'Vicky', 'vicky', '$2a$12$CMGpCFRcyCbm6JhCionMUeM3bTg8QQG.ixgtreUFtjVkkaggHqA66', 'vicky@gmail.com', '2026-04-16 12:22:53');

-- --------------------------------------------------------

--
-- Table structure for table `ahp_pairwise`
--

CREATE TABLE `ahp_pairwise` (
  `id_pairwise` int NOT NULL,
  `kriteria_1` int DEFAULT NULL,
  `kriteria_2` int DEFAULT NULL,
  `nilai` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bobot_kriteria`
--

CREATE TABLE `bobot_kriteria` (
  `id_bobot` int NOT NULL,
  `id_kriteria` int DEFAULT NULL,
  `bobot` decimal(8,5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calon_mahasiswa`
--

CREATE TABLE `calon_mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(100) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `nilai_matematika` int DEFAULT NULL,
  `nilai_binggris` int DEFAULT NULL,
  `nilai_tik` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `calon_mahasiswa`
--

INSERT INTO `calon_mahasiswa` (`id_mahasiswa`, `nama`, `asal_sekolah`, `jurusan`, `nilai_matematika`, `nilai_binggris`, `nilai_tik`) VALUES
(2, 'Akbar', 'SMK Muhammadiyah Kedawung', 'SMK Teknik', 87, 80, 85);

-- --------------------------------------------------------

--
-- Table structure for table `hasil_topsis`
--

CREATE TABLE `hasil_topsis` (
  `id_hasil` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `id_prodi` int DEFAULT NULL,
  `nilai_preferensi` decimal(8,5) DEFAULT NULL,
  `ranking` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id_kriteria` int NOT NULL,
  `kode_kriteria` varchar(5) DEFAULT NULL,
  `nama_kriteria` varchar(100) DEFAULT NULL,
  `jenis` enum('benefit','cost') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id_kriteria`, `kode_kriteria`, `nama_kriteria`, `jenis`) VALUES
(1, 'C1', 'Minat terhadap program studi', 'benefit'),
(2, 'C2', 'Bakat / kemampuan khusus', 'benefit'),
(3, 'C3', 'Biaya kuliah', 'cost'),
(4, 'C4', 'Prospek karir lulusan', 'benefit'),
(5, 'C5', 'Akreditasi program studi', 'benefit');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_alternatif`
--

CREATE TABLE `nilai_alternatif` (
  `id_nilai_alt` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `id_prodi` int DEFAULT NULL,
  `id_kriteria` int DEFAULT NULL,
  `nilai` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_kuesioner`
--

CREATE TABLE `nilai_kuesioner` (
  `id_nilai` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `id_kriteria` int DEFAULT NULL,
  `nilai` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id_prodi` int NOT NULL,
  `nama_prodi` varchar(100) DEFAULT NULL,
  `jenjang` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id_prodi`, `nama_prodi`, `jenjang`) VALUES
(1, 'Ilmu Pemerintahan', 'S1'),
(2, 'Ilmu Komunikasi', 'S1'),
(3, 'Hubungan Masyarakat', 'D3'),
(4, 'Manajemen', 'S1'),
(5, 'Akuntansi', 'S1'),
(6, 'Peternakan', 'S1'),
(7, 'Teknik Industri', 'S1'),
(8, 'Teknik Informatika', 'S1'),
(9, 'Teknik Informatika', 'D3'),
(10, 'Pendidikan Bahasa Inggris', 'S1'),
(11, 'Pendidikan Kimia', 'S1'),
(12, 'Pendidikan Matematika', 'S1'),
(13, 'PGSD', 'S1'),
(14, 'PAUD', 'S1'),
(15, 'Pendidikan IPA', 'S1'),
(16, 'Ilmu Keperawatan', 'S1'),
(17, 'Gizi', 'S1'),
(18, 'Ilmu Keolahragaan', 'S1'),
(19, 'Ilmu Hukum', 'S1'),
(20, 'Ilmu Alquran dan Tafsir', 'S1'),
(21, 'Tasawuf dan Psikoterapi', 'S1');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL COMMENT 'FK ke calon_mahasiswa, diisi setelah admin mendaftarkan mahasiswa',
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Di-hash dengan password_hash() PHP',
  `email` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `id_mahasiswa`, `nama`, `username`, `password`, `email`, `status`, `created_at`) VALUES
(1, NULL, 'Akbar', 'akbar', '$2y$10$AqbWeI4bVHRMMDrJcI9T8OUGKjTLTFevVZuz2o.27st7sRxWaXKx.', 'akbar@gmail.com', 'aktif', '2026-04-16 13:57:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ahp_pairwise`
--
ALTER TABLE `ahp_pairwise`
  ADD PRIMARY KEY (`id_pairwise`),
  ADD KEY `kriteria_1` (`kriteria_1`),
  ADD KEY `kriteria_2` (`kriteria_2`);

--
-- Indexes for table `bobot_kriteria`
--
ALTER TABLE `bobot_kriteria`
  ADD PRIMARY KEY (`id_bobot`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `calon_mahasiswa`
--
ALTER TABLE `calon_mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`);

--
-- Indexes for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  ADD PRIMARY KEY (`id_hasil`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_prodi` (`id_prodi`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id_kriteria`);

--
-- Indexes for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  ADD PRIMARY KEY (`id_nilai_alt`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `nilai_kuesioner`
--
ALTER TABLE `nilai_kuesioner`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id_prodi`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ahp_pairwise`
--
ALTER TABLE `ahp_pairwise`
  MODIFY `id_pairwise` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bobot_kriteria`
--
ALTER TABLE `bobot_kriteria`
  MODIFY `id_bobot` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calon_mahasiswa`
--
ALTER TABLE `calon_mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  MODIFY `id_hasil` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id_kriteria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  MODIFY `id_nilai_alt` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_kuesioner`
--
ALTER TABLE `nilai_kuesioner`
  MODIFY `id_nilai` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id_prodi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ahp_pairwise`
--
ALTER TABLE `ahp_pairwise`
  ADD CONSTRAINT `ahp_pairwise_ibfk_1` FOREIGN KEY (`kriteria_1`) REFERENCES `kriteria` (`id_kriteria`),
  ADD CONSTRAINT `ahp_pairwise_ibfk_2` FOREIGN KEY (`kriteria_2`) REFERENCES `kriteria` (`id_kriteria`);

--
-- Constraints for table `bobot_kriteria`
--
ALTER TABLE `bobot_kriteria`
  ADD CONSTRAINT `bobot_kriteria_ibfk_1` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`);

--
-- Constraints for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  ADD CONSTRAINT `hasil_topsis_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `calon_mahasiswa` (`id_mahasiswa`),
  ADD CONSTRAINT `hasil_topsis_ibfk_2` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`);

--
-- Constraints for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  ADD CONSTRAINT `nilai_alternatif_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`),
  ADD CONSTRAINT `nilai_alternatif_ibfk_2` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`),
  ADD CONSTRAINT `nilai_alternatif_ibfk_3` FOREIGN KEY (`id_mahasiswa`) REFERENCES `calon_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE;

--
-- Constraints for table `nilai_kuesioner`
--
ALTER TABLE `nilai_kuesioner`
  ADD CONSTRAINT `nilai_kuesioner_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `calon_mahasiswa` (`id_mahasiswa`),
  ADD CONSTRAINT `nilai_kuesioner_ibfk_2` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `calon_mahasiswa` (`id_mahasiswa`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
