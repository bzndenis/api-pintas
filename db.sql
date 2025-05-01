/*
 Navicat Premium Dump SQL

 Source Server         : koysmyid_api-mobile
 Source Server Type    : MySQL
 Source Server Version : 100522 (10.5.22-MariaDB-cll-lve)
 Source Host           : 103.247.8.134:3306
 Source Schema         : koym8232_api-mobile

 Target Server Type    : MySQL
 Target Server Version : 100522 (10.5.22-MariaDB-cll-lve)
 File Encoding         : 65001

 Date: 01/05/2025 11:41:18
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
  `hadir` tinyint NOT NULL DEFAULT 0,
  `izin` tinyint NOT NULL DEFAULT 0,
  `sakit` tinyint NOT NULL DEFAULT 0,
  `absen` tinyint NOT NULL DEFAULT 0,
  `keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `siswa_id`(`siswa_id` ASC, `pertemuan_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `pertemuan_id`(`pertemuan_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_absensi_siswa_pertemuan`(`siswa_id` ASC, `pertemuan_id` ASC) USING BTREE,
  INDEX `idx_absensi_rekap`(`siswa_id` ASC, `created_at` ASC) USING BTREE,
  CONSTRAINT `absensi_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_2` FOREIGN KEY (`pertemuan_id`) REFERENCES `pertemuan_bulanan` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `absensi_siswa_ibfk_4` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of absensi_siswa
-- ----------------------------
INSERT INTO `absensi_siswa` VALUES ('3efadb74-fd15-4edd-80d9-c5c0c37eeace', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '1e417cdf-6c66-4386-8a22-56b8366672b5', 4, 0, 1, 0, '', '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:33:15', '2025-05-01 11:33:15');
INSERT INTO `absensi_siswa` VALUES ('b4d80796-b1fc-4dfb-99ed-c7a5c78cfb54', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '1e417cdf-6c66-4386-8a22-56b8366672b5', 5, 0, 0, 0, '', '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:33:15', '2025-05-01 11:33:15');
INSERT INTO `absensi_siswa` VALUES ('d25b254c-1b58-46a7-a4ee-11928e62eab8', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '1e417cdf-6c66-4386-8a22-56b8366672b5', 3, 1, 1, 0, '', '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:33:15', '2025-05-01 11:33:15');
INSERT INTO `absensi_siswa` VALUES ('d31364a1-d278-499f-9720-d84315d130d7', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '1e417cdf-6c66-4386-8a22-56b8366672b5', 5, 0, 0, 0, '', '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:33:15', '2025-05-01 11:33:15');

-- ----------------------------
-- Table structure for capaian_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `capaian_pembelajaran`;
CREATE TABLE `capaian_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_cp` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mapel_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_cp`(`kode_cp` ASC, `mapel_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `mapel_id`(`mapel_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_cp_mapel`(`mapel_id` ASC) USING BTREE,
  CONSTRAINT `capaian_pembelajaran_ibfk_1` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `capaian_pembelajaran_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of capaian_pembelajaran
-- ----------------------------
INSERT INTO `capaian_pembelajaran` VALUES ('3d2b98cd-eaf0-4b07-ac27-1834261c5d9d', 'CP.Matematika.02', 'Pecahan', 'pada akhir pase A peserta didik dapat menganalisi pecahan yg disajikan pada doal cerita dengan benar', '370922b8-e62a-406a-b212-aec4f4b35a73', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:08:58', '2025-05-01 11:08:58', NULL);
INSERT INTO `capaian_pembelajaran` VALUES ('5a0b4d38-71dc-4b9a-9f31-689767bc8bab', 'CP.Matematika.03', 'Membandingkan', 'pada akhir fase A peserta didik dapat menganalisis bilangan paling besar dan paling kecil', '370922b8-e62a-406a-b212-aec4f4b35a73', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:11:00', '2025-05-01 11:11:00', NULL);
INSERT INTO `capaian_pembelajaran` VALUES ('e0d1dadc-9d43-4580-9046-2c69997b9f89', 'CP.Matematika.01', 'Bilangan cacah', 'Pada akhir fase A peserta didik dapat menhitung bilangan cacah 1-100', '370922b8-e62a-406a-b212-aec4f4b35a73', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:48:40', '2025-05-01 11:30:49', '2025-05-01 11:30:49');

-- ----------------------------
-- Table structure for guru
-- ----------------------------
DROP TABLE IF EXISTS `guru`;
CREATE TABLE `guru`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nip` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `no_telp` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `nip`(`nip` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `guru_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of guru
-- ----------------------------
INSERT INTO `guru` VALUES ('12a6c2bd-3dd1-4341-96bf-4d61a13c5a75', NULL, 'Silvya Aubert', 'silvya@tadika.sch', NULL, '153961f4-3592-45a4-9544-7853762ede66', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `guru` VALUES ('1953463c-5995-465c-b808-115549aed338', '0052', 'Guru Kelas 5B', 'gurukelas5b@gmail.com', '081234567890', '177e32a3-fef5-48ef-8aec-8a7fb74f68f2', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('1e219113-0189-43c3-a73f-f6423d764efc', '', 'Putri Rahmawati, S.Pd', 'putrirahmawati1004@gmail.com', '', '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:10:26', '2025-05-01 07:10:51', NULL);
INSERT INTO `guru` VALUES ('2a5108fa-bc32-4f85-a4c4-e8ad18be3833', NULL, 'Banri Seijo', 'banri@tadika.sch', NULL, 'e820164a-5cdd-4770-b90f-6d41e854f54a', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:39:11', '2025-05-01 11:39:11', NULL);
INSERT INTO `guru` VALUES ('2ccb0812-c79c-426d-8f34-fc5dc0d21411', '0061', 'Guru Kelas 6A', 'gurukelas6a@gmail.com', '081234567890', '1e45a317-6ee6-4ede-ab7c-082489b00e44', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('4379787e-3eef-46db-9cde-1326e20b4c3d', '0021', 'Guru Kelas 2A', 'gurukelas2a@gmail.com', '081234567890', '18ea2b57-e505-446e-9725-56fa7219b6d9', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('67b7f131-4bc0-4344-86a6-abba390b841b', '124', 'Guru A', 'gurua@gmail.com', '0800', '22387c12-9ce9-4331-ab4d-150c1d2f3a7e', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:40:10', '2025-05-01 10:40:59', '2025-05-01 10:40:59');
INSERT INTO `guru` VALUES ('7c37da91-6b5c-4b15-964c-6edff0c2553d', '0032', 'Guru Kelas 3B', 'gurukelas3b@gmail.com', '081234567890', '57c0f954-c929-4fdc-99e9-eef325368be6', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('81ca6d03-3f9c-4f35-a53f-1875f7c0fc69', '0042', 'Guru Kelas 4B', 'gurukelas4b@gmail.com', '081234567890', 'b99ebdd9-1a18-4555-ad7b-ccebcbc18eb7', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('913d8517-6ecc-4b7b-825c-98fd78bf20c2', '0011', 'Guru Kelas 1A', 'gurukelas1a@gmail.com', '081234567890', '41ae619e-88bb-41c9-85d1-f4926d62a502', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('a13bbee2-eab4-46bc-b25f-5395f0a4246e', '0031', 'Guru Kelas 3A', 'gurukelas3a@gmail.com', '081234567890', 'fd249631-cfe9-4bcf-86ee-3ffb6872099d', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('ab424348-8c3c-4ffc-8170-81b99583483e', '0062', 'Guru Kelas 6B', 'gurukelas6b@gmail.com', '081234567890', '0d08a4e2-2ea5-4a38-a179-cfb05f386706', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('caf300fb-f4ac-41cd-8026-61dd086392b7', '0041', 'Guru Kelas 4A', 'gurukelas4a@gmail.com', '081234567890', 'b1f5efed-4ddd-4320-ac9c-fd1c71c047de', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('cc4b059e-7e1a-4bd3-af64-ec3d7eaa7cf6', '0051', 'Guru Kelas 5A', 'gurukelas5a@gmail.com', '081234567890', 'd37c577b-75bc-44b5-b487-cb5e364bd078', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('d399511c-e0f2-456d-8131-8c88ca3ebf90', NULL, 'Gerald Levert', 'gerald@tadika.sch', NULL, '2322fbd9-ea4a-4825-a05f-fb4b68fc82bc', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `guru` VALUES ('f1f0adb6-4a8b-4f5c-86f3-3563a847b8ba', NULL, 'Kanami Seiha', 'kanami@tadika.sch', NULL, 'b7ffb61d-42f2-47b5-b4c6-eca9afff60a1', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `guru` VALUES ('f815c20c-c0d0-443c-a97f-8379ac62406b', '0022', 'Guru Kelas 2B', 'gurukelas2b@gmail.com', '081234567890', 'c2d41e49-aeac-487f-bf41-0a9d5da92943', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `guru` VALUES ('fa095eea-d6ae-4eeb-b3dc-d7e51d9fc1de', '0012', 'Guru Kelas 1B', 'gurukelas1b@gmail.com', '081234567890', 'b538076f-3048-45a3-81a8-8e2784b03db8', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);

-- ----------------------------
-- Table structure for kelas
-- ----------------------------
DROP TABLE IF EXISTS `kelas`;
CREATE TABLE `kelas`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_kelas` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tingkat` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tahun` varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `guru_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `tahun`(`tahun` ASC) USING BTREE,
  INDEX `guru_id`(`guru_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_kelas_tahun`(`tahun` ASC) USING BTREE,
  INDEX `nama_kelas`(`nama_kelas` ASC, `tahun` ASC, `sekolah_id` ASC) USING BTREE,
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `kelas_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of kelas
-- ----------------------------
INSERT INTO `kelas` VALUES ('913f7fa7-d171-499f-b251-e307ccdfe686', 'Kelas 1 A', 'Kelas 1', '2024', '1e219113-0189-43c3-a73f-f6423d764efc', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:34:08', '2025-05-01 07:34:08', NULL);

-- ----------------------------
-- Table structure for mata_pelajaran
-- ----------------------------
DROP TABLE IF EXISTS `mata_pelajaran`;
CREATE TABLE `mata_pelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_mapel` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_mapel` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tingkat` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `guru_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_mapel`(`kode_mapel` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_mapel_sekolah_tingkat`(`sekolah_id` ASC, `tingkat` ASC) USING BTREE,
  INDEX `mata_pelajaran_ibfk_2`(`guru_id` ASC) USING BTREE,
  CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `mata_pelajaran_ibfk_2` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mata_pelajaran
-- ----------------------------
INSERT INTO `mata_pelajaran` VALUES ('370922b8-e62a-406a-b212-aec4f4b35a73', 'XQYI866', 'Matematika 1 A', 'Kelas 1 A', '1e219113-0189-43c3-a73f-f6423d764efc', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:35:19', '2025-05-01 07:35:19', NULL);

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of migrations
-- ----------------------------

-- ----------------------------
-- Table structure for nilai_siswa
-- ----------------------------
DROP TABLE IF EXISTS `nilai_siswa`;
CREATE TABLE `nilai_siswa`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `siswa_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tp_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nilai` decimal(5, 2) NOT NULL,
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
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
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nilai_siswa
-- ----------------------------
INSERT INTO `nilai_siswa` VALUES ('01189b55-b68e-4ea4-bcaf-99d66f761c8e', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '6ee59b27-412f-4bb0-99c3-cebfbffcdad1', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:47', '2025-05-01 11:12:47');
INSERT INTO `nilai_siswa` VALUES ('09e00bef-83a1-46bb-a4c2-642cbee87b1a', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '6ee59b27-412f-4bb0-99c3-cebfbffcdad1', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:47', '2025-05-01 11:12:47');
INSERT INTO `nilai_siswa` VALUES ('0c579805-0d34-473d-a68a-a43ea4e9cc77', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '6f2f973e-1aa7-4b61-afa2-2c000c10675e', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:06', '2025-05-01 11:13:06');
INSERT INTO `nilai_siswa` VALUES ('0d406435-155d-48cb-ae06-306da3b705d2', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', 'd5c038dd-6e18-4dc0-860b-b2a943e7ce3b', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:40', '2025-05-01 11:14:40');
INSERT INTO `nilai_siswa` VALUES ('288ad1d9-e760-44cd-9462-7e17b2a7ed35', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '98ed8323-05c1-414c-bb7a-baf064835c09', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:09', '2025-05-01 11:14:09');
INSERT INTO `nilai_siswa` VALUES ('451f5731-99eb-44f1-bb72-cf87bfb93d02', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '86b3e7fa-bde3-4ffb-9f1a-c13cb9a2256c', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:24', '2025-05-01 11:13:24');
INSERT INTO `nilai_siswa` VALUES ('5ae902fc-5668-433f-b7d0-0bb3795bafc2', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '98ed8323-05c1-414c-bb7a-baf064835c09', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:09', '2025-05-01 11:14:09');
INSERT INTO `nilai_siswa` VALUES ('5bc8135f-5a02-4963-b4ff-34e6e795345d', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '287224b0-9f54-4fd0-9c62-b84304795e90', 90.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:53:16', '2025-05-01 10:54:13');
INSERT INTO `nilai_siswa` VALUES ('5d5d46db-0f7e-4e90-9721-6e03ba3dea99', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '6f2f973e-1aa7-4b61-afa2-2c000c10675e', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:06', '2025-05-01 11:13:06');
INSERT INTO `nilai_siswa` VALUES ('5d670d59-007c-4b04-a264-d8e23944b798', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', 'a4a1c95e-38e5-4350-a4d3-247f4acb3643', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:22', '2025-05-01 11:14:22');
INSERT INTO `nilai_siswa` VALUES ('6490b9be-2a5f-409c-baca-43104bee9eab', 'b88412bc-cf28-4704-a3d8-e16615b43f29', 'd5c038dd-6e18-4dc0-860b-b2a943e7ce3b', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:40', '2025-05-01 11:14:40');
INSERT INTO `nilai_siswa` VALUES ('7d3a63a3-235c-48a4-b062-35915c258d49', 'b88412bc-cf28-4704-a3d8-e16615b43f29', 'a4a1c95e-38e5-4350-a4d3-247f4acb3643', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:22', '2025-05-01 11:14:22');
INSERT INTO `nilai_siswa` VALUES ('818464f7-4673-44c6-b82b-e44f9b1bbc58', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', 'a4a1c95e-38e5-4350-a4d3-247f4acb3643', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:22', '2025-05-01 11:14:22');
INSERT INTO `nilai_siswa` VALUES ('81dfd74d-52f4-4ca1-abb3-0aca6c23102f', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '6f2f973e-1aa7-4b61-afa2-2c000c10675e', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:06', '2025-05-01 11:13:06');
INSERT INTO `nilai_siswa` VALUES ('8d9f5fc5-e88e-496a-ad38-4a8c77b759af', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '6ee59b27-412f-4bb0-99c3-cebfbffcdad1', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:47', '2025-05-01 11:12:47');
INSERT INTO `nilai_siswa` VALUES ('a7c08928-c76e-46f5-9b9c-a5dfdcbc7c9c', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '98ed8323-05c1-414c-bb7a-baf064835c09', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:09', '2025-05-01 11:14:09');
INSERT INTO `nilai_siswa` VALUES ('b20b9e0f-104f-4312-b29c-2a13b7fc303d', '3a2d55ac-0a67-4955-80b2-4a9eb0657125', '287224b0-9f54-4fd0-9c62-b84304795e90', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:53:16', '2025-05-01 10:54:13');
INSERT INTO `nilai_siswa` VALUES ('b2f45c2c-9eb4-45f7-aa8d-19817053be34', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '86b3e7fa-bde3-4ffb-9f1a-c13cb9a2256c', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:24', '2025-05-01 11:13:24');
INSERT INTO `nilai_siswa` VALUES ('b9e872e6-17d6-42a2-9a38-8862e2c3ba53', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', 'd5c038dd-6e18-4dc0-860b-b2a943e7ce3b', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:40', '2025-05-01 11:14:40');
INSERT INTO `nilai_siswa` VALUES ('be8826be-b5c1-42da-9403-d5bd83271d3a', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '287224b0-9f54-4fd0-9c62-b84304795e90', 50.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:53:16', '2025-05-01 10:54:13');
INSERT INTO `nilai_siswa` VALUES ('c22c8294-622f-4fd1-9b87-51cae1fcbb1f', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', 'a4a1c95e-38e5-4350-a4d3-247f4acb3643', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:22', '2025-05-01 11:14:22');
INSERT INTO `nilai_siswa` VALUES ('d28f796f-e44c-4b55-8f65-e9d66d240eb0', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '287224b0-9f54-4fd0-9c62-b84304795e90', 60.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:53:16', '2025-05-01 10:54:13');
INSERT INTO `nilai_siswa` VALUES ('df1b5502-a0f3-4fe4-a92d-18f3d16f76a8', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '6f2f973e-1aa7-4b61-afa2-2c000c10675e', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:06', '2025-05-01 11:13:06');
INSERT INTO `nilai_siswa` VALUES ('e7813bf1-f311-4724-877d-b15e2a90f38a', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '86b3e7fa-bde3-4ffb-9f1a-c13cb9a2256c', 70.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:24', '2025-05-01 11:13:24');
INSERT INTO `nilai_siswa` VALUES ('e7990a35-5bb7-4104-8d94-818b660e117a', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '86b3e7fa-bde3-4ffb-9f1a-c13cb9a2256c', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:13:24', '2025-05-01 11:13:24');
INSERT INTO `nilai_siswa` VALUES ('eb6c7bcb-b5e5-4ff7-9069-eeb67ba1261b', 'f39b6509-74e3-4523-9f6c-14d5f6cb0dde', 'd5c038dd-6e18-4dc0-860b-b2a943e7ce3b', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:40', '2025-05-01 11:14:40');
INSERT INTO `nilai_siswa` VALUES ('f04caecb-4e37-4c7d-a2d0-10ab0f5f4845', 'b88412bc-cf28-4704-a3d8-e16615b43f29', '6ee59b27-412f-4bb0-99c3-cebfbffcdad1', 85.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:47', '2025-05-01 11:12:47');
INSERT INTO `nilai_siswa` VALUES ('f2afbafa-0f9a-4954-9dca-f4a26382fe6d', 'b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '98ed8323-05c1-414c-bb7a-baf064835c09', 80.00, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:14:09', '2025-05-01 11:14:09');

-- ----------------------------
-- Table structure for pertemuan
-- ----------------------------
DROP TABLE IF EXISTS `pertemuan`;
CREATE TABLE `pertemuan`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kelas_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mata_pelajaran_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `guru_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `pertemuan_ke` int NOT NULL,
  `materi` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `kelas_id`(`kelas_id` ASC) USING BTREE,
  INDEX `mata_pelajaran_id`(`mata_pelajaran_id` ASC) USING BTREE,
  INDEX `guru_id`(`guru_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `idx_pertemuan_tanggal`(`tanggal` ASC) USING BTREE,
  CONSTRAINT `pertemuan_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_ibfk_2` FOREIGN KEY (`mata_pelajaran_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_ibfk_4` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of pertemuan
-- ----------------------------

-- ----------------------------
-- Table structure for pertemuan_bulanan
-- ----------------------------
DROP TABLE IF EXISTS `pertemuan_bulanan`;
CREATE TABLE `pertemuan_bulanan`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kelas_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mata_pelajaran_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` smallint NOT NULL,
  `total_pertemuan` tinyint NOT NULL,
  `created_by` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `pertemuan_bulanan_unique`(`kelas_id` ASC, `mata_pelajaran_id` ASC, `bulan` ASC, `tahun` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `mata_pelajaran_id`(`mata_pelajaran_id` ASC) USING BTREE,
  CONSTRAINT `pertemuan_bulanan_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_bulanan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pertemuan_bulanan_ibfk_3` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of pertemuan_bulanan
-- ----------------------------
INSERT INTO `pertemuan_bulanan` VALUES ('1e417cdf-6c66-4386-8a22-56b8366672b5', '913f7fa7-d171-499f-b251-e307ccdfe686', '370922b8-e62a-406a-b212-aec4f4b35a73', 5, 2025, 5, '43e8e411-13f3-4895-8062-ca76a31782b2', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:31:43', '2025-05-01 11:31:43');

-- ----------------------------
-- Table structure for sekolah
-- ----------------------------
DROP TABLE IF EXISTS `sekolah`;
CREATE TABLE `sekolah`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama_sekolah` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `npsn` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Nomor Pokok Sekolah Nasional',
  `alamat` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kota` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `provinsi` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_pos` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `no_telp` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `website` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `kepala_sekolah` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `npsn`(`npsn` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sekolah
-- ----------------------------
INSERT INTO `sekolah` VALUES ('008dc782-2991-4880-8b71-fdcf14c125f0', 'SDN Lawangintung 2', 'TMP3627660', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:10', '2025-05-01 04:54:10', NULL);
INSERT INTO `sekolah` VALUES ('034afa38-2268-47d8-bfb3-dc0bba047513', 'SDN CONTOH ', 'TMP6542838', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082212345678', 'sekolahcontoh@gmail.com', NULL, 'sekolahcontoh', NULL, 1, '2025-05-01 11:27:28', '2025-05-01 11:27:28', NULL);
INSERT INTO `sekolah` VALUES ('037bf89a-ea6d-4c66-9053-57a909ae978d', 'Sekolah Contoh', 'TMP5857836', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:46', '2025-05-01 11:23:46', NULL);
INSERT INTO `sekolah` VALUES ('06c54315-5699-4dbe-a46b-8959fb958a81', 'SDN Lawangintung 2', 'TMP6990672', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:09', '2025-05-01 04:54:09', NULL);
INSERT INTO `sekolah` VALUES ('088f7932-d8cc-438d-8aec-b6b3c1224467', 'sd ceger 1', 'TMP7872401', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:18:21', '2025-05-01 05:18:21', NULL);
INSERT INTO `sekolah` VALUES ('0ff8b6d1-7e45-4061-af91-00981e6354ef', 'SDN Lawangintung 2', 'TMP2792258', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:16', '2025-05-01 04:54:16', NULL);
INSERT INTO `sekolah` VALUES ('10146098-d073-4d7d-bf0a-3cf8d951d385', 'uwjhshssb', 'TMP9811236', 'hdjshsjsb', 'Jakarta', 'DKI Jakarta', '12345', '0808080909', 'bekoy@mail.com', NULL, 'bekoy', NULL, 1, '2025-05-01 08:46:58', '2025-05-01 08:46:58', NULL);
INSERT INTO `sekolah` VALUES ('1103161c-b47c-423a-b350-22e127b2da21', 'Sekolah Contoh', 'TMP3030962', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:18', '2025-05-01 11:23:18', NULL);
INSERT INTO `sekolah` VALUES ('25292bc0-71b8-421e-9e21-a2ec8d54785f', 'uwjhshssb', 'TMP3951295', 'hdjshsjsb', 'Jakarta', 'DKI Jakarta', '12345', '0808080909', 'bekoy@mail.com', NULL, 'bekoy', NULL, 1, '2025-05-01 08:47:01', '2025-05-01 08:47:01', NULL);
INSERT INTO `sekolah` VALUES ('298d4b32-7a89-412b-8412-7ad5480c3fe6', 'SDN Lawangintung 2', 'TMP6819357', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:07', '2025-05-01 04:54:07', NULL);
INSERT INTO `sekolah` VALUES ('3865e51c-b897-4a29-9827-4fb495dba4ff', 'Sekolah Contoh', 'TMP8275663', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:38', '2025-05-01 11:23:38', NULL);
INSERT INTO `sekolah` VALUES ('38dfdff9-4033-41ba-92d9-f5d64003d779', 'SDN Ceger 1', 'TMP2459155', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:17:41', '2025-05-01 05:17:41', NULL);
INSERT INTO `sekolah` VALUES ('3e4fd98a-e50d-43da-8656-e65998e5cca1', 'SDN Ceger 1', 'TMP8175584', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:17:39', '2025-05-01 05:17:39', NULL);
INSERT INTO `sekolah` VALUES ('42358715-959d-410d-a6c7-ed056ac92327', 'Sekolah Contoh', 'TMP9227802', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:35', '2025-05-01 11:23:35', NULL);
INSERT INTO `sekolah` VALUES ('495e915f-fdcf-470d-962a-168bc99ce126', 'SDN Lawangintung 2', 'TMP6241365', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:14', '2025-05-01 04:54:14', NULL);
INSERT INTO `sekolah` VALUES ('4ab1465d-2dbd-40fa-bc2d-758768923e13', 'SDN Lawangintung 2', 'TMP3241693', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:13', '2025-05-01 04:54:13', NULL);
INSERT INTO `sekolah` VALUES ('54029065-bf6f-429e-b946-0a560e99f01c', 'SDN Lawangintung 2', 'TMP7303532', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:53:57', '2025-05-01 04:53:57', NULL);
INSERT INTO `sekolah` VALUES ('54886510-fd24-4936-909d-2da35e8bea07', 'Sekolah Contoh', 'TMP4681129', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:14', '2025-05-01 11:23:14', NULL);
INSERT INTO `sekolah` VALUES ('559d55bd-e4c3-4437-91bf-9805f36c3261', 'SDN Tadika Mesra', 'TMP8469590', 'jl. tok dalang', 'Jakarta', 'DKI Jakarta', '12345', '08012308124', 'admin@tadika.sch', NULL, 'adminadmin', NULL, 1, '2025-05-01 11:38:12', '2025-05-01 11:38:12', NULL);
INSERT INTO `sekolah` VALUES ('5884baf6-30ce-4277-a55f-3b2a0b9040a1', 'Sekolah Contoh', 'TMP1038418', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:37', '2025-05-01 11:23:37', NULL);
INSERT INTO `sekolah` VALUES ('59222263-6763-46a2-b6f3-76e4aa089684', 'SDN Lawangintung 2', 'TMP8392840', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:24', '2025-05-01 04:54:24', NULL);
INSERT INTO `sekolah` VALUES ('5f96f027-a4a8-40c8-8c0c-6b9214e6d789', 'Pakuan Ceria', 'TMP1915764', 'Bogor Tengah', 'Jakarta', 'DKI Jakarta', '12345', '089654973318', 'septianakirani201@gmail.com', NULL, 'Septiana', NULL, 1, '2025-05-01 11:34:49', '2025-05-01 11:34:49', NULL);
INSERT INTO `sekolah` VALUES ('69c3bc4e-b657-406c-b403-46834871f2f0', 'SDN Lawangintung 2', 'TMP3376040', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:53:59', '2025-05-01 04:53:59', NULL);
INSERT INTO `sekolah` VALUES ('7d6ec0fa-28bd-489e-b104-112fce418b7f', 'Sd Ceger 1', 'TMP3475010', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:17:54', '2025-05-01 05:17:54', NULL);
INSERT INTO `sekolah` VALUES ('82d196dd-1615-4941-97a2-3e2c11c7bf94', 'SDN Ceger 1', 'TMP7479707', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '080800000012', 'bimalaabim@gmail.com', NULL, 'Rany', NULL, 1, '2025-05-01 05:17:14', '2025-05-01 05:17:14', NULL);
INSERT INTO `sekolah` VALUES ('82d2300b-fc80-4e98-957a-3d745cfc1fe1', 'uwjhshssb', 'TMP3974450', 'hdjshsjsb', 'Jakarta', 'DKI Jakarta', '12345', '0808080909', 'bekoy@mail.com', NULL, 'bekoy', NULL, 1, '2025-05-01 08:47:03', '2025-05-01 08:47:03', NULL);
INSERT INTO `sekolah` VALUES ('89b42ccb-2a2b-4f1e-bde1-93911dfe0736', 'Sd Ceger 1', 'TMP9727651', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:18:03', '2025-05-01 05:18:03', NULL);
INSERT INTO `sekolah` VALUES ('89c02811-4ec8-48b3-a3e8-cc40566d515e', 'SDN Lawangintung 2', 'TMP2929544', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:08', '2025-05-01 04:54:08', NULL);
INSERT INTO `sekolah` VALUES ('9cfa0de2-2f3e-498c-a20e-154013dfa494', 'Sd Ceger 1', 'TMP4107743', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:18:05', '2025-05-01 05:18:05', NULL);
INSERT INTO `sekolah` VALUES ('a87f433d-4ee5-4406-8ad0-25d59816db2a', 'Sekolah Contoh', 'TMP1195224', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:24:01', '2025-05-01 11:24:01', NULL);
INSERT INTO `sekolah` VALUES ('ac1437ef-68d1-4cfa-b0c1-cb1b2f9d4838', 'SDN Lawangintung 2', 'TMP7513057', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:06', '2025-05-01 04:54:06', NULL);
INSERT INTO `sekolah` VALUES ('ae28587d-fd87-43b3-95d0-8ffaa2dfd3ba', 'SDN Ceger 1', 'TMP7845536', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '080800000012', 'bimalaabim@gmail.com', NULL, 'Rany', NULL, 1, '2025-05-01 05:17:18', '2025-05-01 05:17:18', NULL);
INSERT INTO `sekolah` VALUES ('b344c937-f9d8-4c36-bfa6-b50ea89aec59', 'Sd Ceger 1', 'TMP3205738', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:17:58', '2025-05-01 05:17:58', NULL);
INSERT INTO `sekolah` VALUES ('b733918c-d189-41f7-b8dd-29d60859bff1', 'uwjhshssb', 'TMP4249672', 'hdjshsjsb', 'Jakarta', 'DKI Jakarta', '12345', '0808080909', 'bekoy@mail.com', NULL, 'bekoy', NULL, 1, '2025-05-01 08:46:57', '2025-05-01 08:46:57', NULL);
INSERT INTO `sekolah` VALUES ('bbe7ae26-c986-4a7b-93cb-f6b707b99d00', 'SDN Lawangintung 2', 'TMP8770782', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:23', '2025-05-01 04:54:23', NULL);
INSERT INTO `sekolah` VALUES ('c7c170fa-45b2-4408-aaa7-c0549f1b644f', 'SDN Lawangintung 2', 'TMP6719877', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:15', '2025-05-01 04:54:15', NULL);
INSERT INTO `sekolah` VALUES ('c7e91985-8db5-4682-a99c-fc0f5bbb2a96', 'SDN Ceger 1', 'TMP8404072', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '080800000012', 'bimalaabim@gmail.com', NULL, 'Rany', NULL, 1, '2025-05-01 05:17:20', '2025-05-01 05:17:20', NULL);
INSERT INTO `sekolah` VALUES ('d45b8834-d616-4469-b1d6-e9ba657e996d', 'SDN Lawangintung 2', 'TMP1813430', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:17', '2025-05-01 04:54:17', NULL);
INSERT INTO `sekolah` VALUES ('dbdc498d-c7bb-4e6a-bc90-3fae0a93b36c', 'Sd Ceger 1', 'TMP9909314', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:18:04', '2025-05-01 05:18:04', NULL);
INSERT INTO `sekolah` VALUES ('e0a036d8-8529-4f36-ae6c-38a2144a3a29', 'SDN Lawangintung 2', 'TMP3819511', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto', NULL, 1, '2025-05-01 06:57:36', '2025-05-01 06:57:36', NULL);
INSERT INTO `sekolah` VALUES ('e442ecc8-ee39-4310-9530-e568d9dc8b19', 'SDN Lawangintung 2', 'TMP3840096', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:21', '2025-05-01 04:54:21', NULL);
INSERT INTO `sekolah` VALUES ('ea4666cd-6207-45ac-8205-304e840c0f52', 'Sd Ceger 1', 'TMP9302434', 'Jl Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08080001', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:18:09', '2025-05-01 05:18:09', NULL);
INSERT INTO `sekolah` VALUES ('ee4c628f-0a42-4f9a-aaa2-3e518c9ed4f9', 'SDN Lawangintung 2', 'TMP8219108', 'Bogor Selatan', 'Jakarta', 'DKI Jakarta', '12345', '082258021982', 'kepemimpinanproyek03@gmail.com', NULL, 'Widiyanto Wibowo, S.Pd', NULL, 1, '2025-05-01 04:54:17', '2025-05-01 04:54:17', NULL);
INSERT INTO `sekolah` VALUES ('f15f4db6-9e14-4add-9a6c-339261e0cecb', 'Sekolah Contoh', 'TMP9936967', 'Pakuan', 'Jakarta', 'DKI Jakarta', '12345', '082223456789', 'sekolahcontoh@mail.com', NULL, 'Admin Sekolah Contoh ', NULL, 1, '2025-05-01 11:23:47', '2025-05-01 11:23:47', NULL);
INSERT INTO `sekolah` VALUES ('f8ff481c-8b35-49fd-9259-a819ee9540bc', 'SDN Ceger 1', 'TMP1823350', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '08000012', 'bimalaabim@gmail.com', NULL, 'renren11', NULL, 1, '2025-05-01 05:22:34', '2025-05-01 05:22:34', NULL);
INSERT INTO `sekolah` VALUES ('fbfde9b5-f501-4a00-89e1-696d7762f8c0', 'SDN Ceger 1', 'TMP5887208', 'Jl. Flamboyan Bogor', 'Jakarta', 'DKI Jakarta', '12345', '080800000012', 'bimalaabim@gmail.com', NULL, 'Rany Ren', NULL, 1, '2025-05-01 05:17:34', '2025-05-01 05:17:34', NULL);

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
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of settings
-- ----------------------------

-- ----------------------------
-- Table structure for siswa
-- ----------------------------
DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nisn` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kelas_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nisn`(`nisn` ASC) USING BTREE,
  INDEX `kelas_id`(`kelas_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of siswa
-- ----------------------------
INSERT INTO `siswa` VALUES ('3a2d55ac-0a67-4955-80b2-4a9eb0657125', '0000002', 'Rachel Amanda', 'P', '913f7fa7-d171-499f-b251-e307ccdfe686', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:38:04', '2025-05-01 07:38:04', NULL);
INSERT INTO `siswa` VALUES ('b63ee91b-8c9c-4745-9d4f-7ca2c0b253a1', '0000003', 'Najla Vanya', 'P', '913f7fa7-d171-499f-b251-e307ccdfe686', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:38:41', '2025-05-01 07:38:41', NULL);
INSERT INTO `siswa` VALUES ('b88412bc-cf28-4704-a3d8-e16615b43f29', '0000004', 'Putra Mahesa', 'L', '913f7fa7-d171-499f-b251-e307ccdfe686', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:39:41', '2025-05-01 07:39:41', NULL);
INSERT INTO `siswa` VALUES ('f39b6509-74e3-4523-9f6c-14d5f6cb0dde', '0000001', 'Yasmine Laksono', 'P', '913f7fa7-d171-499f-b251-e307ccdfe686', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:37:06', '2025-05-01 07:37:06', NULL);

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
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tahun_ajaran
-- ----------------------------

-- ----------------------------
-- Table structure for tujuan_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `tujuan_pembelajaran`;
CREATE TABLE `tujuan_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_tp` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nama` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `bobot` decimal(5, 2) NOT NULL COMMENT 'Bobot dalam persentase',
  `cp_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kode_tp`(`kode_tp` ASC, `cp_id` ASC, `sekolah_id` ASC) USING BTREE,
  INDEX `cp_id`(`cp_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  INDEX `idx_tp_cp`(`cp_id` ASC) USING BTREE,
  CONSTRAINT `tujuan_pembelajaran_ibfk_1` FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `tujuan_pembelajaran_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tujuan_pembelajaran
-- ----------------------------
INSERT INTO `tujuan_pembelajaran` VALUES ('287224b0-9f54-4fd0-9c62-b84304795e90', 'TP.CP.Matematika.01.01', 'C4', 'Peserta didik dapat mengitung bilangan 1 -100 dengan benar', 1.00, 'e0d1dadc-9d43-4580-9046-2c69997b9f89', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:52:32', '2025-05-01 11:30:49', '2025-05-01 11:30:49');
INSERT INTO `tujuan_pembelajaran` VALUES ('6ee59b27-412f-4bb0-99c3-cebfbffcdad1', 'TP.CP.Matematika.02.03', 'C3', 'Peserta didik dapat menghitung', 1.00, '3d2b98cd-eaf0-4b07-ac27-1834261c5d9d', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:28', '2025-05-01 11:12:28', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('6f2f973e-1aa7-4b61-afa2-2c000c10675e', 'TP.CP.Matematika.02.01', 'C1', 'Peserta didik dapat menghitung', 1.00, '3d2b98cd-eaf0-4b07-ac27-1834261c5d9d', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:14', '2025-05-01 11:12:14', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('7416de19-b5a8-43bf-bfd8-b27671503eed', 'TP.CP.Matematika.01.04', 'C2', 'Peserta didik dapat mengitung bilangan 1 -100 dengan benar', 1.00, 'e0d1dadc-9d43-4580-9046-2c69997b9f89', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:05:57', '2025-05-01 11:30:49', '2025-05-01 11:30:49');
INSERT INTO `tujuan_pembelajaran` VALUES ('86b3e7fa-bde3-4ffb-9f1a-c13cb9a2256c', 'TP.CP.Matematika.02.02', 'C2', 'Peserta didik dapat menghitung', 1.00, '3d2b98cd-eaf0-4b07-ac27-1834261c5d9d', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:12:28', '2025-05-01 11:12:28', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('98ed8323-05c1-414c-bb7a-baf064835c09', 'TP.CP.Matematika.03.01', 'C1', 'Peserta didik dapat menghitung', 1.00, '5a0b4d38-71dc-4b9a-9f31-689767bc8bab', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:11:31', '2025-05-01 11:11:31', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('a4a1c95e-38e5-4350-a4d3-247f4acb3643', 'TP.CP.Matematika.03.03', 'C3', 'Peserta didik dapat menghitung', 1.00, '5a0b4d38-71dc-4b9a-9f31-689767bc8bab', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:11:47', '2025-05-01 11:11:47', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('b3f4fc5c-eff5-4086-9dae-3c2161bfaad5', 'TP.CP.Matematika.01.02', 'C5', 'Peserta didik dapat mengitung bilangan 1 -100 dengan benar', 1.00, 'e0d1dadc-9d43-4580-9046-2c69997b9f89', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:05:37', '2025-05-01 11:30:49', '2025-05-01 11:30:49');
INSERT INTO `tujuan_pembelajaran` VALUES ('d5c038dd-6e18-4dc0-860b-b2a943e7ce3b', 'TP.CP.Matematika.03.02', 'C2', 'Peserta didik dapat menghitung', 1.00, '5a0b4d38-71dc-4b9a-9f31-689767bc8bab', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:11:47', '2025-05-01 11:11:47', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('e75a33b3-a9a5-4664-ac4d-b55672951ba5', 'TP.CP.Matematika.01.03', 'C1', 'Peserta didik dapat mengitung bilangan 1 -100 dengan benar', 1.00, 'e0d1dadc-9d43-4580-9046-2c69997b9f89', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 11:05:57', '2025-05-01 11:30:49', '2025-05-01 11:30:49');

-- ----------------------------
-- Table structure for user_activities
-- ----------------------------
DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE `user_activities`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `action` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `duration` int NOT NULL COMMENT 'Dalam detik',
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL COMMENT 'Null untuk super_admin',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_activities_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_activities
-- ----------------------------
INSERT INTO `user_activities` VALUES ('12906937-b7e4-42a4-b78e-ce8ae2c48e5c', 'c079cca6-2b26-4e99-98df-362f8cf94d80', 'logout', '172.70.208.139', 'Dart/3.7 (dart:io)', 143, 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:25:43', '2025-05-01 05:25:43');
INSERT INTO `user_activities` VALUES ('1e48786c-1d68-4063-9670-9f74f37c0b74', 'c079cca6-2b26-4e99-98df-362f8cf94d80', 'login', '162.158.108.158', 'Dart/3.7 (dart:io)', 0, 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:23:20', '2025-05-01 05:23:20');
INSERT INTO `user_activities` VALUES ('23eacd50-0c84-4557-b406-2f2cc9f227b6', '993700c4-3885-4469-bdc5-087bb6282046', 'logout', '172.69.176.9', 'Dart/3.7 (dart:io)', 2240, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:40:06', '2025-05-01 07:40:06');
INSERT INTO `user_activities` VALUES ('258452a9-ae39-4320-a9b8-d7a96fce86ab', 'c079cca6-2b26-4e99-98df-362f8cf94d80', 'login', '172.69.166.19', 'Dart/3.7 (dart:io)', 0, 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:22:47', '2025-05-01 05:22:47');
INSERT INTO `user_activities` VALUES ('449875cb-86a3-4af7-8663-088a91bf891b', 'c079cca6-2b26-4e99-98df-362f8cf94d80', 'daily_usage', '172.71.124.48', 'Dart/3.7 (dart:io)', 420, 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:22:49', '2025-05-01 05:23:28');
INSERT INTO `user_activities` VALUES ('49e124c3-46be-4aa6-950e-7aa9ebad0731', '993700c4-3885-4469-bdc5-087bb6282046', 'login', '162.158.108.23', 'Dart/3.7 (dart:io)', 0, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:02:46', '2025-05-01 07:02:46');
INSERT INTO `user_activities` VALUES ('9070d2a3-acc5-4de1-a455-5e1f43add8b4', '43e8e411-13f3-4895-8062-ca76a31782b2', 'login', '104.23.175.169', 'Dart/3.7 (dart:io)', 0, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:41:51', '2025-05-01 10:41:51');
INSERT INTO `user_activities` VALUES ('a7dc0ad4-8f66-4520-9191-b407d3b1e7c8', '6905cfde-0d8d-48e8-b668-f92dee8e85cb', 'login', '104.23.175.225', 'Dart/3.7 (dart:io)', 0, '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:38:19', '2025-05-01 11:38:19');
INSERT INTO `user_activities` VALUES ('b33968ca-9972-42c6-925a-82ec7e9f414f', 'c079cca6-2b26-4e99-98df-362f8cf94d80', 'logout', '162.158.108.92', 'Dart/3.7 (dart:io)', 11, 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:22:58', '2025-05-01 05:22:58');
INSERT INTO `user_activities` VALUES ('b9951a11-71a5-4e9d-83ce-002e454c7bfd', 'f054d110-31ef-479b-a579-0afd58c16284', 'daily_usage', '162.158.108.13', 'Dart/3.7 (dart:io)', 420, '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:27:59', '2025-05-01 11:41:09');
INSERT INTO `user_activities` VALUES ('c4e4f177-af9e-4172-ac5d-81c1eb56dba3', '6905cfde-0d8d-48e8-b668-f92dee8e85cb', 'daily_usage', '172.69.166.106', 'Dart/3.7 (dart:io)', 420, '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:38:21', '2025-05-01 11:41:09');
INSERT INTO `user_activities` VALUES ('e3012d9c-fae6-4383-b52f-e4e5c800b584', '993700c4-3885-4469-bdc5-087bb6282046', 'login', '104.23.175.212', 'Dart/3.7 (dart:io)', 0, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:42:53', '2025-05-01 07:42:53');
INSERT INTO `user_activities` VALUES ('e4f48f19-1613-4696-be81-f7bccfaff9a1', '7130f9eb-e896-4899-9745-0d6ec693a302', 'daily_usage', '162.158.162.216', 'Dart/3.7 (dart:io)', 1380, '5f96f027-a4a8-40c8-8c0c-6b9214e6d789', '2025-05-01 11:34:54', '2025-05-01 11:41:14');
INSERT INTO `user_activities` VALUES ('e7c3cfdb-9eaf-4674-a577-af6c3f02e687', 'f054d110-31ef-479b-a579-0afd58c16284', 'login', '172.68.164.75', 'Dart/3.7 (dart:io)', 0, '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:27:59', '2025-05-01 11:27:59');
INSERT INTO `user_activities` VALUES ('ebf9d223-0bfb-486b-96d3-1274bd219b70', '993700c4-3885-4469-bdc5-087bb6282046', 'login', '162.158.108.6', 'Dart/3.7 (dart:io)', 0, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:39:14', '2025-05-01 10:39:14');
INSERT INTO `user_activities` VALUES ('f0636040-8802-45cd-ade0-05aca24b47ec', '993700c4-3885-4469-bdc5-087bb6282046', 'logout', '104.23.175.197', 'Dart/3.7 (dart:io)', 110, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:41:04', '2025-05-01 10:41:04');
INSERT INTO `user_activities` VALUES ('f5f22e3d-8083-43b5-bac3-23985bac867a', '7130f9eb-e896-4899-9745-0d6ec693a302', 'login', '162.158.108.44', 'Dart/3.7 (dart:io)', 0, '5f96f027-a4a8-40c8-8c0c-6b9214e6d789', '2025-05-01 11:34:53', '2025-05-01 11:34:53');
INSERT INTO `user_activities` VALUES ('f78c5214-924e-44dd-8781-e35c3911b161', '993700c4-3885-4469-bdc5-087bb6282046', 'daily_usage', '172.70.189.78', 'Dart/3.7 (dart:io)', 5400, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:02:47', '2025-05-01 10:40:59');
INSERT INTO `user_activities` VALUES ('fbcb3749-ea79-4e96-9d4b-0cbecf4f2029', '43e8e411-13f3-4895-8062-ca76a31782b2', 'daily_usage', '172.68.164.88', 'Dart/3.7 (dart:io)', 7800, 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:41:51', '2025-05-01 11:35:27');

-- ----------------------------
-- Table structure for user_sessions
-- ----------------------------
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration` int NOT NULL COMMENT 'Dalam detik',
  `status` enum('active','expired') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'active',
  `ip_address` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL COMMENT 'Null untuk super_admin',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_sessions
-- ----------------------------
INSERT INTO `user_sessions` VALUES ('49f4ef01-fec9-40b5-8d45-cb7ee67f9406', 'c079cca6-2b26-4e99-98df-362f8cf94d80', '2025-05-01 05:23:20', '2025-05-01 05:25:43', 143, 'expired', '162.158.108.158', 'Dart/3.7 (dart:io)', 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:23:20', '2025-05-01 05:25:43');
INSERT INTO `user_sessions` VALUES ('4b106a87-7191-43fb-8e37-bcfa7d72e7f3', '6905cfde-0d8d-48e8-b668-f92dee8e85cb', '2025-05-01 11:38:19', '2025-05-01 11:41:09', 170, 'active', '104.23.175.225', 'Dart/3.7 (dart:io)', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:38:19', '2025-05-01 11:41:09');
INSERT INTO `user_sessions` VALUES ('691de1bd-751a-4999-9d55-557a728e3f9b', '43e8e411-13f3-4895-8062-ca76a31782b2', '2025-05-01 10:41:51', '2025-05-01 11:35:27', 3216, 'active', '104.23.175.169', 'Dart/3.7 (dart:io)', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:41:51', '2025-05-01 11:35:27');
INSERT INTO `user_sessions` VALUES ('7fd1cd2e-8edf-4547-8992-a3ab8aa62441', 'c079cca6-2b26-4e99-98df-362f8cf94d80', '2025-05-01 05:22:47', '2025-05-01 05:22:58', 11, 'expired', '172.69.166.19', 'Dart/3.7 (dart:io)', 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:22:47', '2025-05-01 05:22:58');
INSERT INTO `user_sessions` VALUES ('8a403489-30e2-4521-bf05-e7accb841c22', 'f054d110-31ef-479b-a579-0afd58c16284', '2025-05-01 11:27:59', '2025-05-01 11:41:09', 790, 'active', '172.68.164.75', 'Dart/3.7 (dart:io)', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:27:59', '2025-05-01 11:41:09');
INSERT INTO `user_sessions` VALUES ('9380bc35-68f7-42a0-b613-f884fd1aa1b2', '993700c4-3885-4469-bdc5-087bb6282046', '2025-05-01 07:42:53', '2025-05-01 10:39:14', 10581, 'expired', '104.23.175.212', 'Dart/3.7 (dart:io)', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:42:53', '2025-05-01 10:39:14');
INSERT INTO `user_sessions` VALUES ('c74b154c-13e9-4bdd-ba32-c3c9d8552a4f', '7130f9eb-e896-4899-9745-0d6ec693a302', '2025-05-01 11:34:53', '2025-05-01 11:41:14', 381, 'active', '162.158.108.44', 'Dart/3.7 (dart:io)', '5f96f027-a4a8-40c8-8c0c-6b9214e6d789', '2025-05-01 11:34:53', '2025-05-01 11:41:14');
INSERT INTO `user_sessions` VALUES ('d596d796-9806-46c9-a55c-09bcdf3539e8', '993700c4-3885-4469-bdc5-087bb6282046', '2025-05-01 10:39:14', '2025-05-01 10:41:04', 110, 'expired', '162.158.108.6', 'Dart/3.7 (dart:io)', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:39:14', '2025-05-01 10:41:04');
INSERT INTO `user_sessions` VALUES ('f67966f6-53d7-4b43-838c-6f316b1f9ee5', '993700c4-3885-4469-bdc5-087bb6282046', '2025-05-01 07:02:46', '2025-05-01 07:40:06', 2240, 'expired', '162.158.108.23', 'Dart/3.7 (dart:io)', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 07:02:47', '2025-05-01 07:40:06');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fullname` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `role` enum('super_admin','admin','guru') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL COMMENT 'Null untuk super_admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('0d08a4e2-2ea5-4a38-a179-cfb05f386706', 'gurukelas6b@gmail.com', '$2y$10$tZfH/zkB5SBv/7BmHAzl4eMWPJcBtRdDtfpP0B94I.ny3X4/OyXD2', 'Guru Kelas 6B', 'gurukelas6b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('153961f4-3592-45a4-9544-7853762ede66', 'silvya@tadika.sch', '$2y$10$GnILCSx7btj1xgPXIOup/eWJ3uirdFrT5r60/PWVRf0vt6pDLfpam', 'Silvya Aubert', 'silvya@tadika.sch', 'guru', '559d55bd-e4c3-4437-91bf-9805f36c3261', NULL, 1, NULL, '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `users` VALUES ('177e32a3-fef5-48ef-8aec-8a7fb74f68f2', 'gurukelas5b@gmail.com', '$2y$10$QEqlLooa6zcVnqksyXVui.xDLYjzmrpiSrVKaULmg8OnqYozvLp/K', 'Guru Kelas 5B', 'gurukelas5b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('18ea2b57-e505-446e-9725-56fa7219b6d9', 'gurukelas2a@gmail.com', '$2y$10$4XvTxKsW5oxLtoRDHJbsEuUq.3n3TNmpGCWKAPElaglYEYLpqvXUm', 'Guru Kelas 2A', 'gurukelas2a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('1e45a317-6ee6-4ede-ab7c-082489b00e44', 'gurukelas6a@gmail.com', '$2y$10$cbN8onNSiXT5gSE0N3INQ.zKuxn2EZrSLLZL8JtKsr/nJ/h0OUHaq', 'Guru Kelas 6A', 'gurukelas6a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('22387c12-9ce9-4331-ab4d-150c1d2f3a7e', 'gurua@gmail.com', '$2y$10$hdN7fOViRGXV2CS3qPPhwuEKNR5qBFMdzTGtdU0m7LzwaRCV/FMIG', 'Guru A', 'gurua@gmail.com', 'guru', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', NULL, 1, NULL, '2025-05-01 10:40:10', '2025-05-01 10:40:59', '2025-05-01 10:40:59');
INSERT INTO `users` VALUES ('2322fbd9-ea4a-4825-a05f-fb4b68fc82bc', 'gerald@tadika.sch', '$2y$10$QJj9EjBooLRBiJEIdcD41eiut9/95PvESxxIyXyDnVYQ8nd0FLSym', 'Gerald Levert', 'gerald@tadika.sch', 'guru', '559d55bd-e4c3-4437-91bf-9805f36c3261', NULL, 1, NULL, '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `users` VALUES ('41ae619e-88bb-41c9-85d1-f4926d62a502', 'gurukelas1a@gmail.com', '$2y$10$tTF5l7mxQ9O1teqhm9qBn.AeJBTlH/h8DzhaVic3lTHqs9MqslJ6a', 'Guru Kelas 1A', 'gurukelas1a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('43e8e411-13f3-4895-8062-ca76a31782b2', 'putrirahmawati1004@gmail.com', '$2y$10$3c9BkTfrZE/oGNFqWcz8Se1Jtnx8q0O8pgYTdWAQSe9pF6dQ5SScK', 'Putri Rahmawati, S.Pd', 'putrirahmawati1004@gmail.com', 'guru', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:41:51', 1, 'Tpf57HKoQlhEpykGZrEYvBdk9k8o2TYDnuoBydBooWW8wVZVqm4F5ZSCGILXbbxaBMnD5Y0ds02dSAe1', '2025-05-01 07:10:26', '2025-05-01 10:41:51', NULL);
INSERT INTO `users` VALUES ('57c0f954-c929-4fdc-99e9-eef325368be6', 'gurukelas3b@gmail.com', '$2y$10$t4ACwvHE1gzGAbJlowKi7ufP.wn.pDhMpTzimm7OqGMJz23Nx18wS', 'Guru Kelas 3B', 'gurukelas3b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('6905cfde-0d8d-48e8-b668-f92dee8e85cb', 'adminadmin', '$2y$10$hN.mwHNJv4r6a2QEcwCpWuU5/Kl9uj6jXUsatCoKR9doQwg24GXjm', 'Administrator', 'admin@tadika.sch', 'admin', '559d55bd-e4c3-4437-91bf-9805f36c3261', '2025-05-01 11:38:19', 1, 'urlgvGzyg4fFruJlULNgchX2yjVFIpi1Ed1MgSkzgvPBr0IgZkXcVjCA9U5mlZaQRljv2fwBR7iRz8RK', '2025-05-01 11:38:12', '2025-05-01 11:38:19', NULL);
INSERT INTO `users` VALUES ('7130f9eb-e896-4899-9745-0d6ec693a302', 'septiana', '$2y$10$GTQvb5uEiGEmcHbx5BIMEeYIWO/ChE3/ExjQvgOePhD451JbfxgLC', 'Septiana Kirani', 'septianakirani201@gmail.com', 'admin', '5f96f027-a4a8-40c8-8c0c-6b9214e6d789', '2025-05-01 11:34:53', 1, 'ApYzIPZanWB0rJagnPGf1TRYMkqmvqVMiZwDBR2ScdwhTAN90EEyQQ1JSQa8QxqieoZwxwwGnQJsdhxL', '2025-05-01 11:34:49', '2025-05-01 11:34:53', NULL);
INSERT INTO `users` VALUES ('993700c4-3885-4469-bdc5-087bb6282046', 'widiyanto', '$2y$10$sFmdNmUBoDQVmAUaoBUqe.csLEQ4dCydYvelGJHIgeh60E9hdrrMi', 'Widiyanto Wibowo, S.Pd', 'kepemimpinanproyek03@gmail.com', 'admin', 'e0a036d8-8529-4f36-ae6c-38a2144a3a29', '2025-05-01 10:39:14', 1, NULL, '2025-05-01 06:57:36', '2025-05-01 10:41:04', NULL);
INSERT INTO `users` VALUES ('b1f5efed-4ddd-4320-ac9c-fd1c71c047de', 'gurukelas4a@gmail.com', '$2y$10$0GPDP5jlj4mB1BleuYuvNOmmYSy65xMp7QlwymjbJ6TfYNRRx4HKm', 'Guru Kelas 4A', 'gurukelas4a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('b538076f-3048-45a3-81a8-8e2784b03db8', 'gurukelas1b@gmail.com', '$2y$10$kheZy7pVxOKsoqPSmDnym.XHBLCw8CmQKLOJ1k1gDb2DoNYc5aKkW', 'Guru Kelas 1B', 'gurukelas1b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('b7ffb61d-42f2-47b5-b4c6-eca9afff60a1', 'kanami@tadika.sch', '$2y$10$QsUffrWFAnCQBrTrcyAzjOYX46aXt8bhuG8RbEgzn7LQSSP/XLtyK', 'Kanami Seiha', 'kanami@tadika.sch', 'guru', '559d55bd-e4c3-4437-91bf-9805f36c3261', NULL, 1, NULL, '2025-05-01 11:41:05', '2025-05-01 11:41:05', NULL);
INSERT INTO `users` VALUES ('b99ebdd9-1a18-4555-ad7b-ccebcbc18eb7', 'gurukelas4b@gmail.com', '$2y$10$erAO0QVQv18/oo3olmivQ.JRV1QfESuhea3tgNGo33rKHWJXG7Mgi', 'Guru Kelas 4B', 'gurukelas4b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('c079cca6-2b26-4e99-98df-362f8cf94d80', 'renren11', '$2y$10$3PjHrkcTt/4hGAE6vjOY5OpDCfQ9RjMzjRDfTzbqIDfdO3.zDirAm', 'Rany Ren', 'bimalaabim@gmail.com', 'admin', 'f8ff481c-8b35-49fd-9259-a819ee9540bc', '2025-05-01 05:23:20', 1, NULL, '2025-05-01 05:22:34', '2025-05-01 05:25:43', NULL);
INSERT INTO `users` VALUES ('c2d41e49-aeac-487f-bf41-0a9d5da92943', 'gurukelas2b@gmail.com', '$2y$10$H7TBRV2LCtJXEh5iXoPA8O27.1oikfiUMf1rPbrFLaozEKgms5pJy', 'Guru Kelas 2B', 'gurukelas2b@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('d37c577b-75bc-44b5-b487-cb5e364bd078', 'gurukelas5a@gmail.com', '$2y$10$SPDm.qqxJ9fKt56hnCBKEukN4R32USZY7lGzvYRrASmzQUB5xeETq', 'Guru Kelas 5A', 'gurukelas5a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);
INSERT INTO `users` VALUES ('e820164a-5cdd-4770-b90f-6d41e854f54a', 'banri@tadika.sch', '$2y$10$m.opKeIQjedcVj.wZpL/eelDcb/C4McbRtIwJSuJdvPaGU/Gz2Mhu', 'Banri Seijo', 'banri@tadika.sch', 'guru', '559d55bd-e4c3-4437-91bf-9805f36c3261', NULL, 1, NULL, '2025-05-01 11:39:11', '2025-05-01 11:39:11', NULL);
INSERT INTO `users` VALUES ('f054d110-31ef-479b-a579-0afd58c16284', 'sekolahcontoh', '$2y$10$YX2Fq/ETiu7s/VxxlONfOu4eNYkLdgtcI1a/YJ.xGwmsxlbonMaCG', 'Admin Sekolah Contoh', 'sekolahcontoh@gmail.com', 'admin', '034afa38-2268-47d8-bfb3-dc0bba047513', '2025-05-01 11:27:59', 1, 'j4g2W0DcTTv0W4NNudRmCjvkFyKjwGxMpbfzGCU0VChpXu7SJhMjepTXj8O9IcDQbRcKNrveoaCIbkJO', '2025-05-01 11:27:28', '2025-05-01 11:27:59', NULL);
INSERT INTO `users` VALUES ('fd249631-cfe9-4bcf-86ee-3ffb6872099d', 'gurukelas3a@gmail.com', '$2y$10$d6PSsjR77.tTNlMnRU06MOp3OTZYQJBrr/A6LNbyatbKX.GySUnf6', 'Guru Kelas 3A', 'gurukelas3a@gmail.com', 'guru', '034afa38-2268-47d8-bfb3-dc0bba047513', NULL, 1, NULL, '2025-05-01 11:41:09', '2025-05-01 11:41:09', NULL);

SET FOREIGN_KEY_CHECKS = 1;
