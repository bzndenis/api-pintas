/*
 Navicat Premium Data Transfer

 Source Server         : koysmyid_api-mobile
 Source Server Type    : MySQL
 Source Server Version : 100522 (10.5.22-MariaDB-cll-lve)
 Source Host           : 103.247.8.134:3306
 Source Schema         : koym8232_api-mobile

 Target Server Type    : MySQL
 Target Server Version : 100522 (10.5.22-MariaDB-cll-lve)
 File Encoding         : 65001

 Date: 26/03/2025 00:26:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for absensi_siswa
-- ----------------------------
DROP TABLE IF EXISTS `absensi_siswa`;
CREATE TABLE `absensi_siswa`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `siswa_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `pertemuan_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `hadir` tinyint NOT NULL DEFAULT 0 CHECK (hadir >= 0),
  `izin` tinyint NOT NULL DEFAULT 0 CHECK (izin >= 0),
  `sakit` tinyint NOT NULL DEFAULT 0 CHECK (sakit >= 0),
  `absen` tinyint NOT NULL DEFAULT 0 CHECK (absen >= 0),
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `siswa_id`(`siswa_id` ASC, `pertemuan_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `pertemuan_id`(`pertemuan_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_absensi_siswa_pertemuan`(`siswa_id` ASC, `pertemuan_id` ASC) USING BTREE,
  CONSTRAINT `absensi_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_2` FOREIGN KEY (`pertemuan_id`) REFERENCES `pertemuan_bulanan` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_4` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for capaian_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `capaian_pembelajaran`;
CREATE TABLE `capaian_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_cp` varchar(20) NOT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mapel_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_cp`(`kode_cp` ASC, `mapel_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `mapel_id`(`mapel_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_cp_mapel`(`mapel_id` ASC) USING BTREE,
  CONSTRAINT `capaian_pembelajaran_ibfk_1` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `capaian_pembelajaran_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for guru
-- ----------------------------
DROP TABLE IF EXISTS `guru`;
CREATE TABLE `guru`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `nip`(`nip` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `guru_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for kelas
-- ----------------------------
DROP TABLE IF EXISTS `kelas`;
CREATE TABLE `kelas`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `tingkat` varchar(20) NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `guru_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nama_kelas`(`nama_kelas` ASC, `tahun` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `tahun`(`tahun` ASC) USING BTREE,
  INDEX `guru_id`(`guru_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_kelas_tahun`(`tahun` ASC) USING BTREE,
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `kelas_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for mata_pelajaran
-- ----------------------------
DROP TABLE IF EXISTS `mata_pelajaran`;
CREATE TABLE `mata_pelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_mapel` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `tingkat` varchar(20) NOT NULL,
  `guru_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_mapel`(`kode_mapel` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_mapel_sekolah_tingkat`(`sekolah_id` ASC, `tingkat` ASC) USING BTREE,
  CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `mata_pelajaran_ibfk_2` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for nilai_siswa
-- ----------------------------
DROP TABLE IF EXISTS `nilai_siswa`;
CREATE TABLE `nilai_siswa`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `siswa_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tp_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nilai` decimal(5, 2) NOT NULL CHECK (nilai >= 0 AND nilai <= 100),
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `siswa_id`(`siswa_id` ASC, `tp_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `tp_id`(`tp_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_nilai_siswa_tp`(`siswa_id` ASC, `tp_id` ASC) USING BTREE,
  CONSTRAINT `nilai_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `nilai_siswa_ibfk_2` FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `nilai_siswa_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `nilai_siswa_ibfk_4` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pertemuan_bulanan
-- ----------------------------
DROP TABLE IF EXISTS `pertemuan_bulanan`;
CREATE TABLE `pertemuan_bulanan`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kelas_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `bulan` tinyint NOT NULL CHECK (bulan >= 1 AND bulan <= 12),
  `tahun` smallint NOT NULL,
  `total_pertemuan` tinyint NOT NULL CHECK (total_pertemuan >= 0),
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kelas_id`(`kelas_id` ASC, `bulan` ASC, `tahun` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `pertemuan_bulanan_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_bulanan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_bulanan_ibfk_3` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sekolah
-- ----------------------------
DROP TABLE IF EXISTS `sekolah`;
CREATE TABLE `sekolah`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_sekolah` varchar(100) NOT NULL,
  `npsn` varchar(20) NOT NULL COMMENT 'Nomor Pokok Sekolah Nasional',
  `alamat` text NOT NULL,
  `kota` varchar(50) NOT NULL,
  `provinsi` varchar(50) NOT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `kepala_sekolah` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1 NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `npsn`(`npsn` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `key` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `value` text CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
  `group` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key`(`key` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for siswa
-- ----------------------------
DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `kelas_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nisn`(`nisn` ASC) USING BTREE,
  INDEX `kelas_id`(`kelas_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tahun_ajaran
-- ----------------------------
DROP TABLE IF EXISTS `tahun_ajaran`;
CREATE TABLE `tahun_ajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_tahun_ajaran` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nama_tahun_ajaran`(`nama_tahun_ajaran` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `tahun_ajaran_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tujuan_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `tujuan_pembelajaran`;
CREATE TABLE `tujuan_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_tp` varchar(20) NOT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `bobot` decimal(5,2) NOT NULL COMMENT 'Bobot dalam persentase',
  `cp_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_tp`(`kode_tp` ASC, `cp_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `cp_id`(`cp_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_tp_cp`(`cp_id` ASC) USING BTREE,
  CONSTRAINT `tujuan_pembelajaran_ibfk_1` FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `tujuan_pembelajaran_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_activities
-- ----------------------------
DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE `user_activities`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `duration` int NOT NULL COMMENT 'Dalam detik',
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Null untuk super_admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_activities_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_sessions
-- ----------------------------
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `duration` int NOT NULL COMMENT 'Dalam detik',
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Null untuk super_admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','guru') NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Null untuk super_admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1 NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
