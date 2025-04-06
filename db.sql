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

 Date: 06/04/2025 14:32:04
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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `absensi_siswa` VALUES ('26de7795-0047-4dcc-baf5-d36cc1fd53d1', 'a994306e-dd15-403c-84b4-2ed4e0058bfc', 'ec049be3-d476-4679-b4da-0d8b4fb06dcc', 8, 0, 0, 20, NULL, '26cd0a68-ab90-4397-8c0c-af35496a13ca', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 14:25:13', '2025-04-06 14:25:13');
INSERT INTO `absensi_siswa` VALUES ('937fccde-1f9d-4fec-b9dd-723673645cb0', '384e1ab5-97c9-4ce5-8e83-011f67e10a1d', 'ec049be3-d476-4679-b4da-0d8b4fb06dcc', 6, 1, 1, 20, NULL, '26cd0a68-ab90-4397-8c0c-af35496a13ca', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 14:25:13', '2025-04-06 14:25:13');

-- ----------------------------
-- Table structure for capaian_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `capaian_pembelajaran`;
CREATE TABLE `capaian_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_cp` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mapel_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `capaian_pembelajaran` VALUES ('a804febb-a9cf-4518-8d34-d68350c14851', 'CP-MTK-03', 'Memahami konsep dasar aljabar', '4bfafb2b-78a9-4b39-8543-32e4844c51c8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 20:27:22', '2025-03-28 20:27:22', NULL);
INSERT INTO `capaian_pembelajaran` VALUES ('d2ec61b6-22fb-4d72-9f3e-d677bcf64ef5', 'CP-MTK-02', 'Memahami konsep dasar aljabar', '4bfafb2b-78a9-4b39-8543-32e4844c51c8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 20:27:22', '2025-03-28 20:27:22', NULL);
INSERT INTO `capaian_pembelajaran` VALUES ('dcf46faf-33b4-4714-b5cc-7c4dfac38da8', 'CP-MTK-01', 'Memahami konsep dasar aljabar', '4bfafb2b-78a9-4b39-8543-32e4844c51c8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 19:11:24', '2025-03-28 19:11:24', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `guru` VALUES ('0b027efa-0566-4ffa-b7aa-66be06723da1', '198703212011012003', 'Siti Rahayu', 'siti.rahayu@example.com', '082345678901', '26cd0a68-ab90-4397-8c0c-af35496a13ca', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 05:49:34', '2025-03-26 05:49:34', NULL);
INSERT INTO `guru` VALUES ('167a8e26-5717-47a6-bbac-829f96440cc9', NULL, 'Janice', 'janice@mail.com', NULL, 'd33149af-fdd2-4982-bf79-902b039441a9', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:04:49', '2025-03-31 20:46:27', '2025-03-31 20:46:27');
INSERT INTO `guru` VALUES ('20204231-25d7-4c6e-8182-393bcf359886', NULL, 'Nama Guru', 'guruzzz@example.com', NULL, 'fc2ef630-4172-4bba-9c8e-0aa5ed424c69', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 13:56:18', '2025-03-26 14:01:36', '2025-03-26 14:01:36');
INSERT INTO `guru` VALUES ('4bd6d7f5-9459-4fa3-af35-7df0337daf5f', NULL, 'Elena', 'elena@mail.com', NULL, 'e5019dd5-a2c5-4b57-8cd2-c200e284fe79', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:00:39', '2025-03-28 13:00:39', NULL);
INSERT INTO `guru` VALUES ('631ad29a-f327-4b77-9da6-015b7a82b616', '', 'Screwllum', 'screwllum@mail.com', '', '6e032b23-5da2-4131-b58b-06fb23873172', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:01:28', '2025-04-05 11:30:38', '2025-04-05 11:30:38');
INSERT INTO `guru` VALUES ('6da04278-c497-4c90-a8a3-8f7b65ea4587', '1234567894567489', 'Cipher', 'cipher@mail.com', '085612345678945', '8f4a50bc-994f-4356-b282-89d325eab40d', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:09:56', '2025-04-05 10:48:26', NULL);
INSERT INTO `guru` VALUES ('7655d0c9-7d55-40ca-bb36-6272aaa2fbc0', NULL, 'John Doe', 'johndoe@example.com', NULL, 'e98ecfe5-c902-4ed4-8feb-271bf20c1297', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 08:04:31', '2025-03-28 08:04:31', NULL);
INSERT INTO `guru` VALUES ('857f43e9-1a8d-488f-8d4b-b5843b560e30', NULL, 'Jane Doe', 'janedoe@example.com', NULL, '84ffdc06-e21a-4fbc-950c-576323b0d31a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 08:04:12', '2025-03-28 08:04:12', NULL);
INSERT INTO `guru` VALUES ('877a42b4-6224-4fd6-9307-ee9abd52cf75', NULL, 'Clara', 'clara@mail.com', NULL, '7d75d064-f305-42d6-b175-15fa7d698844', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:06:57', '2025-03-28 13:06:57', NULL);
INSERT INTO `guru` VALUES ('8b966853-1503-4b50-acb0-ed5f0756e2df', '199001012012011002', 'Ahmad Hidayat', 'ahmad.hidayat@example.com', '083456789012', '01db3bcd-a4f4-4667-a4bf-c0328c1f248a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 05:49:34', '2025-03-26 05:49:34', NULL);
INSERT INTO `guru` VALUES ('948d9071-3119-4cd6-9812-9d7712a1a968', '1.9850115201001E+17', 'Budi Santoso', 'budi@example.com', '81234567890', '7e0fd796-6ef2-4c13-b643-c3b6d580c647', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 14:23:04', '2025-03-26 14:23:04', NULL);
INSERT INTO `guru` VALUES ('b30eca6e-bffd-47fc-931e-f9cfeb95c348', NULL, 'Stevia', 'stevia@mail.com', NULL, 'bb4b73c0-86f0-4320-9ba0-9834a5e69ea2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:01:28', '2025-03-28 13:01:28', NULL);
INSERT INTO `guru` VALUES ('b97567ee-74ce-4687-8068-409147e0481e', NULL, 'testing2', 'testing2@mail.com', NULL, '7feb514b-149a-4bb9-9b2d-6da0f91a2604', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 20:46:10', '2025-03-31 20:46:18', '2025-03-31 20:46:18');
INSERT INTO `guru` VALUES ('bab8101d-ffb4-4da4-aaad-039e72ec6732', NULL, 'Guru 1', 'guru1@mail.com', NULL, 'b46bed26-a975-4605-b8bb-be6732fa60f9', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 11:09:30', '2025-03-30 21:57:07', '2025-03-30 21:57:07');
INSERT INTO `guru` VALUES ('c498c945-400d-49b6-a05d-7db808fe36c7', '2344124234521', 'Hyacine', 'hyacine@mail.com', '0856123456789', '43865cbb-d950-4498-ba96-1264ae8032f2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:47:41', '2025-04-01 21:54:24', NULL);
INSERT INTO `guru` VALUES ('cc390687-c4c1-4874-ae9a-a6bd296fae61', NULL, 'Testing', 'testing@mail.com', NULL, '0afaeb8e-2476-4066-ab15-4362ca412dec', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 20:41:10', '2025-03-31 20:46:20', '2025-03-31 20:46:20');
INSERT INTO `guru` VALUES ('f34b4419-9656-4f8e-adaa-6964888079f1', NULL, 'Nama Guru', 'guru@example.com', NULL, 'e8e040eb-0604-4522-99e9-5105a05a2666', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 13:55:05', '2025-03-26 13:55:05', NULL);
INSERT INTO `guru` VALUES ('f99c7401-bfec-41fe-ae86-461a1f8c595a', NULL, 'Maurice', 'maurice@mail.com', NULL, 'a35c05e0-9d49-4c05-8c2b-cb868bad9503', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:00:39', '2025-03-30 21:58:24', '2025-03-30 21:58:24');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `kelas` VALUES ('01964bc7-6336-4809-9b3e-566bd17451be', 'Kelas 1 A', 'Kelas 1', '2025', '6da04278-c497-4c90-a8a3-8f7b65ea4587', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:06:25', '2025-04-05 11:34:00', '2025-04-05 11:34:00');
INSERT INTO `kelas` VALUES ('029e1b59-3894-4638-9ada-3f562849e106', 'Kelas 1', 'X', '2025', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:33:20', '2025-03-26 16:36:51', '2025-03-26 16:36:51');
INSERT INTO `kelas` VALUES ('0da5dbf8-7447-4d4f-9fc6-0a3e1d92b4db', 'Kelas 1', 'C', '2026', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:28:21', '2025-03-26 16:28:21', NULL);
INSERT INTO `kelas` VALUES ('2debb3ef-139c-4701-b77a-4f55e0e3e750', 'Kelas 1 A', 'Kelas 1', '2025', 'c498c945-400d-49b6-a05d-7db808fe36c7', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:03:26', '2025-04-04 17:05:56', '2025-04-04 17:05:56');
INSERT INTO `kelas` VALUES ('310bd2bd-7511-4fb8-a303-ad1ffd0a7294', 'A', 'Kelas 2', '2025', 'c498c945-400d-49b6-a05d-7db808fe36c7', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:14:46', '2025-04-04 16:53:36', '2025-04-04 16:53:36');
INSERT INTO `kelas` VALUES ('341d5eb8-651c-4520-901c-ff69a5bbb5cf', 'X IPA 1', 'Z', '2026', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-04-04 16:43:12', '2025-04-04 16:43:12', NULL);
INSERT INTO `kelas` VALUES ('34ab0b7b-ebd3-465d-a2d0-b0ec592c2bc4', 'Kelas 2 A', 'Kelas 2', '2025', 'b30eca6e-bffd-47fc-931e-f9cfeb95c348', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:28:23', '2025-04-05 11:28:37', '2025-04-05 11:28:37');
INSERT INTO `kelas` VALUES ('3bad4dcf-bc3b-4ee5-a6f0-7b63675d301a', 'Kelas 1 - B', 'Kelas 1', '2025', '6da04278-c497-4c90-a8a3-8f7b65ea4587', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:05:38', '2025-04-04 17:05:59', '2025-04-04 17:05:59');
INSERT INTO `kelas` VALUES ('4143e707-6e60-4804-a327-d3909fa6a242', 'Kelas 1', 'Z', '2025', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:23:06', '2025-03-26 16:23:06', NULL);
INSERT INTO `kelas` VALUES ('5270e9cd-cba1-45c7-8fbd-906d0b4c665f', 'X IPS 1', 'Z', '2026', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-04-04 16:43:12', '2025-04-04 16:43:12', NULL);
INSERT INTO `kelas` VALUES ('5df82509-312a-46d9-ba0d-3d046909ce8f', 'Kelas 1', 'D', '2025', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:33:43', '2025-03-26 16:33:43', NULL);
INSERT INTO `kelas` VALUES ('7537f57a-349c-4ec8-92c7-de4152206dc3', 'Kelas 2 A', 'Kelas 2', '2025', '6da04278-c497-4c90-a8a3-8f7b65ea4587', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:34:54', '2025-04-05 11:44:03', '2025-04-05 11:44:03');
INSERT INTO `kelas` VALUES ('7826690f-ddcf-4286-9b57-d40bbfa2b268', 'Kelas 1 A', 'Kelas 1', '2025', NULL, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:50:30', '2025-04-04 16:55:16', '2025-04-04 16:55:16');
INSERT INTO `kelas` VALUES ('84da1cb7-752c-4b47-8d57-58755feac1cb', 'X TKJ', 'Z', '2025', 'f34b4419-9656-4f8e-adaa-6964888079f1', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-30 20:49:23', '2025-03-30 20:49:23', NULL);
INSERT INTO `kelas` VALUES ('945dad1b-d87c-43c5-97b7-fa33cf2760e5', 'Kelas 1', 'A', '2025', 'c498c945-400d-49b6-a05d-7db808fe36c7', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:14:08', '2025-04-04 16:53:38', '2025-04-04 16:53:38');
INSERT INTO `kelas` VALUES ('b540dd97-6e3a-4fbe-ab5f-e4839953219d', 'X IPA', 'V', '2025', 'f34b4419-9656-4f8e-adaa-6964888079f1', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-30 20:49:23', '2025-03-30 20:49:23', NULL);
INSERT INTO `kelas` VALUES ('bdc3fc93-3bdb-4daf-90c5-60a74d4c8195', 'Kelas 3 - A', 'Kelas 3', '2025', '7655d0c9-7d55-40ca-bb36-6272aaa2fbc0', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:44:18', '2025-04-05 11:48:32', NULL);
INSERT INTO `kelas` VALUES ('beed53f5-3b37-4879-8a1c-fd2a1802155a', 'Kelas 1 - C', 'Kelas 1', '2025', '4bd6d7f5-9459-4fa3-af35-7df0337daf5f', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:20:35', '2025-04-05 12:00:32', NULL);
INSERT INTO `kelas` VALUES ('c26c2a84-a553-4be5-b359-6f1d62e347e4', 'X IPS', 'G', '2025', 'f34b4419-9656-4f8e-adaa-6964888079f1', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-30 20:49:23', '2025-03-30 20:49:23', NULL);
INSERT INTO `kelas` VALUES ('c4e20701-f4c5-4454-9668-8df839d091eb', 'Kelas 1 - C', 'Kelas 1', '2025', '877a42b4-6224-4fd6-9307-ee9abd52cf75', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:06:25', '2025-04-05 10:40:31', '2025-04-05 10:40:31');
INSERT INTO `kelas` VALUES ('e96b60c5-3302-4346-b278-d68bae824d22', 'Kelas 1 - C', 'Kelas 1', '2025', '877a42b4-6224-4fd6-9307-ee9abd52cf75', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:05:38', '2025-04-04 17:05:58', '2025-04-04 17:05:58');
INSERT INTO `kelas` VALUES ('f95eb218-7dec-4d07-8dba-50ba349a0462', 'Kelas 1 - B', 'Kelas 1', '2025', '6da04278-c497-4c90-a8a3-8f7b65ea4587', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 17:06:25', '2025-04-04 17:06:25', NULL);
INSERT INTO `kelas` VALUES ('fb336ea3-9733-4da6-8158-91cb1d01978d', 'Kelas 1', 'F', '2025', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:33:48', '2025-03-26 16:33:48', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `mata_pelajaran` VALUES ('3aff052c-0768-403c-ad42-9f1ad58a0074', 'MTK1', 'Matematika Kelas 1', 'Kelas 1', 'c498c945-400d-49b6-a05d-7db808fe36c7', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:41:03', '2025-04-05 21:03:39', '2025-04-05 21:03:39');
INSERT INTO `mata_pelajaran` VALUES ('4ae0ab25-26b1-464d-9758-f1786398cb39', 'MTK1', 'Matematika Kelas 1', 'A', '948d9071-3119-4cd6-9812-9d7712a1a968', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-28 18:13:20', '2025-03-28 18:13:20', NULL);
INSERT INTO `mata_pelajaran` VALUES ('4bfafb2b-78a9-4b39-8543-32e4844c51c8', '4ae0ab25-26b1-464d-9758-f1786398cb39', 'CP-MTK-01', 'CP-MTK-01', '0b027efa-0566-4ffa-b7aa-66be06723da1', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-28 19:04:04', '2025-03-28 19:11:19', NULL);
INSERT INTO `mata_pelajaran` VALUES ('579fbdaa-cdf4-4ec0-abb3-2e54966b8eb8', 'IPA1', 'IPA Kelas 1', 'Kelas 1', 'c498c945-400d-49b6-a05d-7db808fe36c7', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:53:14', '2025-04-05 21:04:53', '2025-04-05 21:04:53');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pertemuan
-- ----------------------------
INSERT INTO `pertemuan` VALUES ('4ae0ab25-26b1-464d-9758-f1786398cb99', '029e1b59-3894-4638-9ada-3f562849e106', '4ae0ab25-26b1-464d-9758-f1786398cb39', '0b027efa-0566-4ffa-b7aa-66be06723da1', '2025-03-30', 1, '1', '0fdfed18-5034-4c5d-987b-7a9684bc32c2', '01db3bcd-a4f4-4667-a4bf-c0328c1f248a', '2025-03-30 05:41:35', '2025-03-30 05:42:21');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kelas_id`(`kelas_id` ASC, `bulan` ASC, `tahun` ASC, `sekolah_id` ASC) USING BTREE,
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
INSERT INTO `pertemuan_bulanan` VALUES ('ec049be3-d476-4679-b4da-0d8b4fb06dcc', 'beed53f5-3b37-4879-8a1c-fd2a1802155a', '4ae0ab25-26b1-464d-9758-f1786398cb39', 1, 2025, 28, '26cd0a68-ab90-4397-8c0c-af35496a13ca', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 14:25:13', '2025-04-06 14:25:13');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `npsn`(`npsn` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sekolah
-- ----------------------------
INSERT INTO `sekolah` VALUES ('0fdfed18-5034-4c5d-987b-7a9684bc32c2', 'SD Contoh 1', 'TMP3098661', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin1@mail.com', NULL, 'testadmin1', NULL, 1, '2025-03-26 01:05:52', '2025-03-26 01:05:52', NULL);
INSERT INTO `sekolah` VALUES ('1cd7cabb-7c1d-427d-842d-68574c526b56', 'SD Contoh 1', 'TMP6910902', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin1@mail.com', NULL, 'testadmin1', NULL, 1, '2025-03-26 01:10:02', '2025-03-26 01:10:02', NULL);
INSERT INTO `sekolah` VALUES ('75a0787f-586e-44a8-9728-7e9d958e9f8e', 'SD Contoh 1', 'TMP6453447', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin1@mail.com', NULL, NULL, NULL, 1, '2025-03-26 01:10:36', '2025-03-26 01:10:36', NULL);
INSERT INTO `sekolah` VALUES ('98267294-eb49-438b-9a9b-9aabd24bb98d', 'SD Contoh 1', 'TMP5941680', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin1@mail.com', NULL, 'testadmin1', NULL, 1, '2025-03-26 01:13:09', '2025-03-26 01:13:09', NULL);
INSERT INTO `sekolah` VALUES ('ad0bf9df-d212-4070-8f5e-584fa72051ac', 'SD Contoh 1', 'TMP8120297', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin1@mail.com', NULL, 'testadmin1', NULL, 1, '2025-03-26 01:11:42', '2025-03-26 01:11:42', NULL);
INSERT INTO `sekolah` VALUES ('e8546a78-7a5a-47fc-86b9-f2b233a89e8b', 'SD Contoh 1', 'TMP5036691', 'Alamat SD Contoh 1', 'Jakarta', 'DKI Jakarta', '12345', '120142510', 'testadmin11@mail.com', NULL, 'testadmin11', NULL, 1, '2025-03-26 13:44:55', '2025-03-26 13:44:55', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `siswa` VALUES ('0ebd0cb8-59ee-44b4-a388-43ca01abe245', '666', 'Nama Siswa', 'L', '4143e707-6e60-4804-a327-d3909fa6a242', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 16:51:41', '2025-03-26 16:58:22', NULL);
INSERT INTO `siswa` VALUES ('320977b0-3a96-4233-9233-02c84de765e3', '6664', 'Nama Siswa4', 'L', '0da5dbf8-7447-4d4f-9fc6-0a3e1d92b4db', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:38:01', '2025-03-26 17:39:11', '2025-03-26 17:39:11');
INSERT INTO `siswa` VALUES ('384e1ab5-97c9-4ce5-8e83-011f67e10a1d', 'Budi Santoso2', '9876543220', 'L', 'beed53f5-3b37-4879-8a1c-fd2a1802155a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:38:01', '2025-03-26 17:38:01', NULL);
INSERT INTO `siswa` VALUES ('a994306e-dd15-403c-84b4-2ed4e0058bfc', '6661', 'Nama Siswa1', 'L', 'beed53f5-3b37-4879-8a1c-fd2a1802155a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:21:18', '2025-03-26 17:21:18', NULL);
INSERT INTO `siswa` VALUES ('c0c01275-e345-4709-8bdd-32d77b0df52f', '6662', 'Nama Siswa2', 'L', 'beed53f5-3b37-4879-8a1c-fd2a1802155a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:21:18', '2025-03-26 17:21:18', NULL);
INSERT INTO `siswa` VALUES ('cc1cc49a-aa97-46cd-95d1-8971b57f7f21', 'Budi Santoso3', '9876543230', 'L', 'beed53f5-3b37-4879-8a1c-fd2a1802155a', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:38:01', '2025-03-26 17:38:01', NULL);

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
INSERT INTO `tahun_ajaran` VALUES ('01dae64d-b91d-4c68-950f-c05e792ebab0', 'Tahun Ajaran 2026/2027', '2025-07-18', '2026-06-30', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', 1, '2025-03-26 16:14:04', '2025-03-26 16:14:04', NULL);
INSERT INTO `tahun_ajaran` VALUES ('039c5b73-6b6d-41ae-88e6-b9c2c277ad55', 'Tahun Ajaran 2025/2026', '2025-07-17', '2026-07-30', '98267294-eb49-438b-9a9b-9aabd24bb98d', 1, '2025-03-26 01:15:41', '2025-03-26 05:18:04', NULL);

-- ----------------------------
-- Table structure for tujuan_pembelajaran
-- ----------------------------
DROP TABLE IF EXISTS `tujuan_pembelajaran`;
CREATE TABLE `tujuan_pembelajaran`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kode_tp` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `bobot` decimal(5, 2) NOT NULL COMMENT 'Bobot dalam persentase',
  `cp_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `tujuan_pembelajaran` VALUES ('38cefb2b-4166-4d7f-9175-1e7061ca4a85', 'TP-MTK-01-02', 'Menyelesaikan persamaan linear satu variabel', 25.00, 'dcf46faf-33b4-4714-b5cc-7c4dfac38da8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 20:38:40', '2025-03-28 20:38:40', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('af8a0130-b298-41d7-aba4-d35fbbdb554b', 'TP-MTK-01-03', 'Menyelesaikan persamaan linear satu variabel', 25.00, 'dcf46faf-33b4-4714-b5cc-7c4dfac38da8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 20:38:40', '2025-03-28 20:38:40', NULL);
INSERT INTO `tujuan_pembelajaran` VALUES ('bcdee4a0-9c90-495e-b91f-a073d513c4d5', 'TP-MTK-01-01', 'Menyelesaikan persamaan linear satu variabel', 25.00, 'dcf46faf-33b4-4714-b5cc-7c4dfac38da8', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 20:35:32', '2025-03-28 20:41:32', '2025-03-28 20:41:32');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_activities_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_activities
-- ----------------------------
INSERT INTO `user_activities` VALUES ('03232567-9824-4684-acb5-d2a5a151a670', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.44', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:36:13', '2025-04-05 20:36:13');
INSERT INTO `user_activities` VALUES ('0483081a-ca31-44df-9b0d-25475cc4f3af', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.150', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:15', '2025-03-26 21:14:15');
INSERT INTO `user_activities` VALUES ('04d75e45-793b-4c66-b176-acb882e517a8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.92.223', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:09:12', '2025-03-28 05:09:12');
INSERT INTO `user_activities` VALUES ('07b15d72-218e-46e8-b405-a85021d264bd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.143', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 20:22:59', '2025-04-04 20:22:59');
INSERT INTO `user_activities` VALUES ('08e4e250-da58-455f-8e7c-a0f128caacd9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.189.17', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:04:39', '2025-03-26 21:04:39');
INSERT INTO `user_activities` VALUES ('09275857-6198-41de-a353-c091ba3cba35', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.130', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:58:37', '2025-03-28 16:58:37');
INSERT INTO `user_activities` VALUES ('0b3c8dd5-ea81-424e-afd7-a13e26e04236', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.6', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:03:31', '2025-03-26 21:03:31');
INSERT INTO `user_activities` VALUES ('0c5e119f-9459-462f-b0c0-99533464aae0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '104.23.175.241', 'Dart/3.7 (dart:io)', 10140, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:38:13', '2025-04-05 22:36:24');
INSERT INTO `user_activities` VALUES ('0c66b3b0-668b-498c-a130-648a55db36a4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:03:49', '2025-03-26 18:03:49');
INSERT INTO `user_activities` VALUES ('0c9e8875-3dbe-4826-9832-0f9cea9aa295', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.162.217', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:56:26', '2025-04-05 10:56:26');
INSERT INTO `user_activities` VALUES ('10f7cf3f-37b0-42a7-be64-8f6bfc0a91ea', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.214', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 06:12:02', '2025-04-02 06:12:02');
INSERT INTO `user_activities` VALUES ('1236f757-5f59-4ed4-8967-cf58274e7dce', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.108.16', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:39:56', '2025-03-28 13:39:56');
INSERT INTO `user_activities` VALUES ('13e6eadd-eb59-41b4-b04e-dbdcfd77cb2f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.131', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:37:31', '2025-03-27 12:37:31');
INSERT INTO `user_activities` VALUES ('14ff3b15-9aa4-49f3-8cf1-ac51802d22b9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.54', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:23:39', '2025-03-30 19:23:39');
INSERT INTO `user_activities` VALUES ('1566927d-ccc5-4cd6-9abb-4fb75eba129a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.173', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:50:48', '2025-03-26 22:50:48');
INSERT INTO `user_activities` VALUES ('15ed2429-b186-4271-9288-ad24b69bbcc3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.189.133', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:59:24', '2025-03-28 05:59:24');
INSERT INTO `user_activities` VALUES ('1806e5ce-6fc2-4943-8602-301946cef7f4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.156', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:40:08', '2025-04-05 10:40:08');
INSERT INTO `user_activities` VALUES ('1812b42d-4c4b-467b-a7fa-4c67296b0a76', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.20', 'PostmanRuntime/7.43.0', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:58:37', '2025-03-28 12:58:37');
INSERT INTO `user_activities` VALUES ('198f056d-94ee-4e62-84a3-d3dd62b6f18c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.210', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:10:33', '2025-03-26 21:10:33');
INSERT INTO `user_activities` VALUES ('1b41abeb-54b6-4f62-b543-d7a6a2cdea8d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '108.162.227.39', 'Dart/3.7 (dart:io)', 4020, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:06:24', '2025-04-05 06:57:09');
INSERT INTO `user_activities` VALUES ('1b8e7c3a-c698-47fa-bcf6-68ff4482dbf7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.121', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 01:15:11', '2025-03-26 01:15:11');
INSERT INTO `user_activities` VALUES ('1e0fd5a3-19e0-4e14-bad8-881c69fd242f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.91', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:39:19', '2025-03-28 05:39:19');
INSERT INTO `user_activities` VALUES ('1eec22b4-f9ac-47e5-8371-273af895257e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.139', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:20:27', '2025-03-28 10:20:27');
INSERT INTO `user_activities` VALUES ('2156d3d3-64b6-4781-893c-db216193f78e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.189.100', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:31:22', '2025-03-26 22:31:22');
INSERT INTO `user_activities` VALUES ('220d78c6-5b5b-4d6e-9f25-6a734d69d892', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.116', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 21:47:35', '2025-03-30 21:47:35');
INSERT INTO `user_activities` VALUES ('234d3580-c5e4-47b8-ad7c-3d7d90b4e751', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.92.187', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:52:55', '2025-03-28 10:52:55');
INSERT INTO `user_activities` VALUES ('24daf9a0-05e0-4e9e-a33d-3a722fb85ba1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.13', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:44:01', '2025-03-26 22:44:01');
INSERT INTO `user_activities` VALUES ('2527916b-7399-4bf5-a68c-32a5ab5be543', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.143.135', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:13:18', '2025-03-26 21:13:18');
INSERT INTO `user_activities` VALUES ('2535c2eb-3c5d-40eb-b08f-4cd8ac63d694', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.108.131', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:37:27', '2025-04-05 20:37:27');
INSERT INTO `user_activities` VALUES ('2594b67d-4c8c-4510-a179-6e03d66ad22b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.81', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:54:38', '2025-04-04 16:54:38');
INSERT INTO `user_activities` VALUES ('286e2b47-f666-4a58-93f5-58baa0565459', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.59', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:27:41', '2025-03-28 13:27:41');
INSERT INTO `user_activities` VALUES ('28d9a4fe-bfe1-4b74-9e90-3a05aa2462ab', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '104.23.175.224', 'Dart/3.7 (dart:io)', 1140, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-01 21:43:55', '2025-04-02 06:23:32');
INSERT INTO `user_activities` VALUES ('29679e2e-c87f-4d83-b237-b37519a2bbf4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.107.56', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:54:51', '2025-03-27 08:54:51');
INSERT INTO `user_activities` VALUES ('29db8c6e-1286-457c-801e-0c250dd420ec', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'daily_usage', '172.70.189.61', 'PostmanRuntime/7.43.3', 120, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-04-04 16:42:34', '2025-04-04 16:43:12');
INSERT INTO `user_activities` VALUES ('2b78d2e6-e9bc-4aca-bc39-2e1731356cc1', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'login', '162.158.106.153', 'PostmanRuntime/7.43.2', 0, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 17:16:29', '2025-03-26 17:16:29');
INSERT INTO `user_activities` VALUES ('2d084808-8b40-4e00-9ee5-2eb484511bfb', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '172.68.164.96', 'Dart/3.7 (dart:io)', 8100, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 07:54:20', '2025-03-28 17:38:43');
INSERT INTO `user_activities` VALUES ('2f153723-03b3-4aee-80d2-6369bb9f51a9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.143.6', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:00:00', '2025-03-27 10:00:00');
INSERT INTO `user_activities` VALUES ('2fbcb6ce-ec6f-44c7-b20f-7e960ae803c6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.161', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:46:32', '2025-03-27 08:46:32');
INSERT INTO `user_activities` VALUES ('3295e888-e64c-4409-9165-4e6c7caa2a5d', '26cd0a68-ab90-4397-8c0c-af35496a13ca', 'daily_usage', '172.71.124.138', 'PostmanRuntime/7.43.2', 2100, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:56:16', '2025-03-28 01:06:25');
INSERT INTO `user_activities` VALUES ('3455737a-67a2-4e0c-bdf8-bdbcd7215f23', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.20', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 18:00:15', '2025-04-05 18:00:15');
INSERT INTO `user_activities` VALUES ('35cfe91a-187d-484d-9495-fffeeedce5d1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.241', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:42:07', '2025-04-05 20:42:07');
INSERT INTO `user_activities` VALUES ('366c3878-4d9e-42f8-92aa-4fa3fa7d62af', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.23', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 11:09:09', '2025-03-28 11:09:09');
INSERT INTO `user_activities` VALUES ('3797fb7f-d94a-41c6-88e6-a8bb4ef11b4e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.162.121', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:36:58', '2025-03-28 12:36:58');
INSERT INTO `user_activities` VALUES ('3898b16b-b7a3-4356-99d6-097ed58436d9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.19', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 21:39:45', '2025-03-30 21:39:45');
INSERT INTO `user_activities` VALUES ('389fd250-fdc4-4989-a438-97ac8a252927', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.29', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:15:09', '2025-03-26 21:15:09');
INSERT INTO `user_activities` VALUES ('38f2a25a-e629-454a-9418-40fdb18b773e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.92.249', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:04:05', '2025-03-26 18:04:05');
INSERT INTO `user_activities` VALUES ('3d3e88b4-9246-47ed-8f2f-0405c18d427b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.86', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:04:55', '2025-03-27 10:04:55');
INSERT INTO `user_activities` VALUES ('3eaeb462-37e0-4045-a04f-d548b0c43d17', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.152.56', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:49:42', '2025-03-26 22:49:42');
INSERT INTO `user_activities` VALUES ('3f414419-9dd2-4754-8bfd-2cb866574cdd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.13', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:03:54', '2025-03-26 23:03:54');
INSERT INTO `user_activities` VALUES ('418f3776-7b30-492d-a2ce-95dbbe8d620a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.134', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:37', '2025-03-26 21:09:37');
INSERT INTO `user_activities` VALUES ('41b194b9-0aad-40da-8724-e1569f5b0761', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.162.170', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:57:04', '2025-03-27 10:57:04');
INSERT INTO `user_activities` VALUES ('41d84181-73fc-453f-86d6-0f99d56bdf5a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.149', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:01:10', '2025-03-26 23:01:10');
INSERT INTO `user_activities` VALUES ('4235cef7-c078-4ae9-ad4e-f3e8399991a6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.171.31', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 17:08:12', '2025-03-28 17:08:12');
INSERT INTO `user_activities` VALUES ('4355c2f3-fcd4-469b-a742-774d4003d8a6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.107.2', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:32:47', '2025-03-28 13:32:47');
INSERT INTO `user_activities` VALUES ('457f1570-62a1-4dc9-a4bd-ee916a61ed64', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.18', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 09:24:56', '2025-04-03 09:24:56');
INSERT INTO `user_activities` VALUES ('48c5a944-4007-4a0c-abfd-48a4aa4de942', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '108.162.226.91', 'Dart/3.7 (dart:io)', 1800, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:18:05', '2025-03-30 21:58:24');
INSERT INTO `user_activities` VALUES ('4943023b-4a3a-4de4-87dd-8a93c7c9ce79', '26cd0a68-ab90-4397-8c0c-af35496a13ca', 'daily_usage', '172.70.208.13', 'PostmanRuntime/7.43.2', 1860, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:05:35', '2025-03-28 21:20:45');
INSERT INTO `user_activities` VALUES ('4a998fce-e4e8-41a6-a95f-cd26758aaeff', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.147.3', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 20:34:59', '2025-03-31 20:34:59');
INSERT INTO `user_activities` VALUES ('538a045c-6063-4b1e-b9fd-786f609c0f56', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '172.71.124.233', 'Dart/3.7 (dart:io)', 480, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 12:38:55', '2025-04-06 13:04:34');
INSERT INTO `user_activities` VALUES ('54538fbc-e6fa-4527-adf9-b54ba4b380a9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '162.158.189.35', 'PostmanRuntime/7.43.2', 3780, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 12:22:49', '2025-03-26 23:20:14');
INSERT INTO `user_activities` VALUES ('55689988-40db-4212-ab65-0960f16c4608', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.200', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:54:38', '2025-03-26 21:54:38');
INSERT INTO `user_activities` VALUES ('590ac0e8-b9c3-4c7d-89d1-f45bbf271393', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.244', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:20:00', '2025-03-26 17:20:00');
INSERT INTO `user_activities` VALUES ('5982f580-183e-4a11-aaf3-3e8091e03a75', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.200', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:15:18', '2025-03-26 23:15:18');
INSERT INTO `user_activities` VALUES ('5c23ea35-00e2-4d61-86b6-e1528a2d8f98', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.72', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:35:56', '2025-03-27 10:35:56');
INSERT INTO `user_activities` VALUES ('5da8aeb9-2778-4010-8b61-83c1bf0c9a12', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.20', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 04:19:11', '2025-03-28 04:19:11');
INSERT INTO `user_activities` VALUES ('5ea1c319-372a-427f-80d4-50215c4076fa', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.91', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:41', '2025-03-26 21:14:41');
INSERT INTO `user_activities` VALUES ('5fcd73ae-3268-4754-96ae-7b6228fac77e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.103', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:05:47', '2025-03-26 23:05:47');
INSERT INTO `user_activities` VALUES ('61cca048-57a2-4c37-80e2-2978af87a98c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.3', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:06:58', '2025-03-26 23:06:58');
INSERT INTO `user_activities` VALUES ('6214e631-7c22-4bad-83b1-eb92898b498d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.11', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:02:25', '2025-03-26 23:02:25');
INSERT INTO `user_activities` VALUES ('6376cab0-20dc-4e2a-98a8-92a704acb412', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.24', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:10:05', '2025-03-26 23:10:05');
INSERT INTO `user_activities` VALUES ('63a542ee-5faa-45bd-8863-1c7be24ec183', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '104.23.175.213', 'Dart/3.7 (dart:io)', 1620, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 07:05:21', '2025-04-02 09:54:41');
INSERT INTO `user_activities` VALUES ('64044885-6398-4dfc-8024-c6b010fdc79d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.3', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:33', '2025-03-26 21:14:33');
INSERT INTO `user_activities` VALUES ('6405da3a-36e1-486a-81da-b58e570660c6', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'daily_usage', '172.71.124.83', 'PostmanRuntime/7.43.2', 9060, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 13:45:29', '2025-03-26 23:34:22');
INSERT INTO `user_activities` VALUES ('64e54e43-6b68-452d-9408-f899faf70f33', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.159', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:54:06', '2025-04-05 11:54:06');
INSERT INTO `user_activities` VALUES ('66daee89-1306-40d4-9a64-7f70dd867e77', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 22:36:01', '2025-04-05 22:36:01');
INSERT INTO `user_activities` VALUES ('675ce330-a96f-4c46-8c7a-7f10a5128829', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.147.167', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 06:13:29', '2025-03-28 06:13:29');
INSERT INTO `user_activities` VALUES ('677e28fe-5c48-4aca-855a-b03ab5bcb6dd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '162.158.107.8', 'PostmanRuntime/7.43.2', 7140, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 05:09:35', '2025-03-26 06:20:31');
INSERT INTO `user_activities` VALUES ('678be56e-2900-4ebb-ba09-52d372bdd552', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 11:09:01', '2025-03-27 11:09:01');
INSERT INTO `user_activities` VALUES ('68af8814-bf55-4ab4-94a7-4c777916cd07', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.190.41', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:43:31', '2025-04-02 09:43:31');
INSERT INTO `user_activities` VALUES ('68e09e5c-46d8-44ad-b7db-3215b4a937c4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.189.82', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:51:32', '2025-04-02 09:51:32');
INSERT INTO `user_activities` VALUES ('6bdfd67c-7dd1-4f70-bc35-47c52b32197b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.139', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:13:27', '2025-04-04 16:13:27');
INSERT INTO `user_activities` VALUES ('6bee62c0-23df-4013-a9e7-2a0a91eef66a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.92.141', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 21:30:22', '2025-04-03 21:30:22');
INSERT INTO `user_activities` VALUES ('6d13a8ce-6dfd-440a-a3f4-ed4e47becc52', '26cd0a68-ab90-4397-8c0c-af35496a13ca', 'daily_usage', '172.71.82.37', 'PostmanRuntime/7.43.3', 480, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 14:09:02', '2025-04-06 14:31:37');
INSERT INTO `user_activities` VALUES ('6e9a91f4-abf8-483e-b82f-1db20793371a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.21', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:38:26', '2025-03-30 19:38:26');
INSERT INTO `user_activities` VALUES ('6fc60367-4c7d-47a8-80ec-2236c1729d5e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.165.29', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 13:30:25', '2025-03-26 13:30:25');
INSERT INTO `user_activities` VALUES ('72b3df47-1644-490c-a41d-5e55fb163ba1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.158', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:10:47', '2025-03-26 23:10:47');
INSERT INTO `user_activities` VALUES ('72d1e5f9-5d15-43fb-8698-8cb76149fcf8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.92.222', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:06:24', '2025-04-04 16:06:24');
INSERT INTO `user_activities` VALUES ('74099c5a-6afc-4e96-bdaf-a71770d60a7d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.125', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:10:58', '2025-03-28 13:10:58');
INSERT INTO `user_activities` VALUES ('752eb3e4-f5e5-4c2b-8253-0ae5ec23268a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.111', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:52:49', '2025-03-27 08:52:49');
INSERT INTO `user_activities` VALUES ('7b2422ca-2ad1-47ff-8d71-7639c3540910', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.228', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 06:04:39', '2025-03-28 06:04:39');
INSERT INTO `user_activities` VALUES ('7c942da3-eeda-4cc8-b23e-c26eeccf7208', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '162.158.190.103', 'Dart/3.7 (dart:io)', 960, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 20:35:00', '2025-03-31 21:46:29');
INSERT INTO `user_activities` VALUES ('7cd8857a-b8a3-470b-8d19-f3a4ee3a879d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.138', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 21:38:00', '2025-04-05 21:38:00');
INSERT INTO `user_activities` VALUES ('7ce9e8eb-5888-41ea-a95f-8809cbbc0853', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.212', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:51:39', '2025-03-28 12:51:39');
INSERT INTO `user_activities` VALUES ('7d0bc092-3190-4c2a-8d43-17aa28cb16d5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.242.96', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:54:48', '2025-03-26 21:54:48');
INSERT INTO `user_activities` VALUES ('7d52ce86-5aa1-4959-9ee0-4c3fe1ccda22', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.185', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:44:34', '2025-03-28 13:44:34');
INSERT INTO `user_activities` VALUES ('7e3ee1ee-1289-495a-970c-a426a728b1e1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.161', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:50', '2025-03-26 21:09:50');
INSERT INTO `user_activities` VALUES ('828bcf09-7949-476c-9df2-a3cefd73a961', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.108.168', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:05:18', '2025-03-27 10:05:18');
INSERT INTO `user_activities` VALUES ('8295a8d5-4fed-4b0a-ab89-43bb1efdcbe6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.108.152', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:53:06', '2025-03-26 22:53:06');
INSERT INTO `user_activities` VALUES ('832b81ad-cb95-4072-962d-aaaf2f1f92c9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.189.16', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:13:37', '2025-03-26 21:13:37');
INSERT INTO `user_activities` VALUES ('8371eb9d-06ea-443d-bda5-b264060176cf', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.173', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:02:42', '2025-03-26 18:02:42');
INSERT INTO `user_activities` VALUES ('840f9135-0997-48d3-be14-162c799ca252', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.152.5', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-01 21:53:23', '2025-04-01 21:53:23');
INSERT INTO `user_activities` VALUES ('84214de1-779f-436b-b5da-fb99a72e8d2a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.170.218', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:37:23', '2025-04-05 10:37:23');
INSERT INTO `user_activities` VALUES ('898ce77f-afe1-495e-be93-588c232ffe4d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.252', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:16:21', '2025-03-28 13:16:21');
INSERT INTO `user_activities` VALUES ('8c3dc6f6-d15e-4c5e-84bd-6f7ddeb85114', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.115', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:16:18', '2025-03-26 21:16:18');
INSERT INTO `user_activities` VALUES ('8f72b597-0e74-4816-ba8f-107eeeee7f9c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.143', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:04:15', '2025-03-26 21:04:15');
INSERT INTO `user_activities` VALUES ('9189378a-8cc0-4f0d-aa15-6ff12455a13b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.107.3', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:29:15', '2025-03-27 12:29:15');
INSERT INTO `user_activities` VALUES ('92343e67-1901-44a5-ae9b-26ddeb191fb4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.64', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:59:19', '2025-03-28 12:59:19');
INSERT INTO `user_activities` VALUES ('94188c3a-1b96-47ab-b075-64a1025429c5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:02', '2025-03-26 21:09:02');
INSERT INTO `user_activities` VALUES ('961adf66-0063-4a1a-b115-ba593838b910', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.7', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:20:03', '2025-03-27 10:20:03');
INSERT INTO `user_activities` VALUES ('9b978cf3-9e8b-4261-8db7-451b662a0de3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.189.100', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:18:22', '2025-03-26 23:18:22');
INSERT INTO `user_activities` VALUES ('9bbc09c9-184b-4d01-8ac9-d24724fb252e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.63', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:36:09', '2025-03-27 08:36:09');
INSERT INTO `user_activities` VALUES ('9eb5d262-a3d2-4fe8-ba44-60c02b70a05b', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'daily_usage', '162.158.108.6', 'PostmanRuntime/7.43.3', 1080, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-30 20:23:40', '2025-03-30 20:57:33');
INSERT INTO `user_activities` VALUES ('9ecd69f3-98f5-4d36-a755-4e5b80c55f90', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'daily_usage', '104.23.175.124', 'PostmanRuntime/7.43.2', 1980, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-28 13:33:56', '2025-03-28 19:07:32');
INSERT INTO `user_activities` VALUES ('a12bc340-e869-4246-b729-a31ab371ed10', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '162.158.163.180', 'Dart/3.7 (dart:io)', 2400, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:36:10', '2025-03-28 06:13:32');
INSERT INTO `user_activities` VALUES ('a1b6d835-b523-42ab-a89a-774c2a1f3da6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.190.103', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:05:04', '2025-03-26 21:05:04');
INSERT INTO `user_activities` VALUES ('a233f88b-c2a5-49c6-a289-fdb391f93727', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.56', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:57:03', '2025-03-26 22:57:03');
INSERT INTO `user_activities` VALUES ('a3db3fc1-2f61-439b-87d6-02b87ae3f14d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.163.133', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 07:54:19', '2025-03-28 07:54:19');
INSERT INTO `user_activities` VALUES ('aa31b0f6-8f49-4619-bf48-2441eeb6b79e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'daily_usage', '162.158.107.92', 'Dart/3.7 (dart:io)', 780, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 09:24:56', '2025-04-03 22:26:08');
INSERT INTO `user_activities` VALUES ('ac120ea8-8651-44c4-85e0-833cf62451db', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.82.23', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:28:52', '2025-03-28 13:28:52');
INSERT INTO `user_activities` VALUES ('ad0d42e3-d499-4667-967d-4558decf52b9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.189.139', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 12:38:53', '2025-04-06 12:38:53');
INSERT INTO `user_activities` VALUES ('aeeaab32-ffcd-44ec-93c2-76945c22b743', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.112', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:08:36', '2025-03-26 23:08:36');
INSERT INTO `user_activities` VALUES ('af589315-84d2-4886-b6e4-0e4410e5f37b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.33', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:45:17', '2025-03-28 16:45:17');
INSERT INTO `user_activities` VALUES ('b08a7c01-0fa0-4f97-abe2-e097e7949ff4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.24', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 21:20:01', '2025-03-31 21:20:01');
INSERT INTO `user_activities` VALUES ('b0a5d206-8ef8-468f-bdb4-7ef155f6543f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.143.134', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:06:45', '2025-03-28 13:06:45');
INSERT INTO `user_activities` VALUES ('b49bbb56-cc19-4fe5-b1fd-16014e9015b3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:36:56', '2025-04-05 11:36:56');
INSERT INTO `user_activities` VALUES ('b5959a39-e3fb-496d-8512-aa0b6230bf11', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.116', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:18:05', '2025-03-30 19:18:05');
INSERT INTO `user_activities` VALUES ('b6390876-d733-45b4-a55c-d98848b044a5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.189.237', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:19:55', '2025-03-26 23:19:55');
INSERT INTO `user_activities` VALUES ('b7d378ea-4331-4b33-9a75-d952072743da', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.147.9', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:39:26', '2025-03-27 08:39:26');
INSERT INTO `user_activities` VALUES ('b813656c-0f8b-4155-a497-58e56323b9d1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.170.95', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:07:07', '2025-03-28 05:07:07');
INSERT INTO `user_activities` VALUES ('b9847301-0902-43f1-8097-386c30860e6e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.46', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 17:14:00', '2025-03-28 17:14:00');
INSERT INTO `user_activities` VALUES ('bb3da65f-400b-4cc7-b21e-3d07b14729ca', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.165.13', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 07:27:54', '2025-04-02 07:27:54');
INSERT INTO `user_activities` VALUES ('bce94b22-998f-4d37-95f2-bf0840d7a14f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.26', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 06:56:01', '2025-04-05 06:56:01');
INSERT INTO `user_activities` VALUES ('bd5c5699-6ce6-40c3-a988-0fc9a09c86da', '26cd0a68-ab90-4397-8c0c-af35496a13ca', 'daily_usage', '172.70.147.44', 'PostmanRuntime/7.43.3', 540, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 05:11:48', '2025-03-30 05:40:32');
INSERT INTO `user_activities` VALUES ('c195e584-4f49-4f5a-9981-9c28d2bc4953', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.61', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:44:14', '2025-04-02 09:44:14');
INSERT INTO `user_activities` VALUES ('c4a32b7b-7822-4f1f-a2e4-21aeb1f369a3', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'login', '172.71.124.83', 'PostmanRuntime/7.43.2', 0, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 13:45:07', '2025-03-26 13:45:07');
INSERT INTO `user_activities` VALUES ('c8f81b29-c0bd-49ec-81da-58cda1d4287a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.161', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:32:24', '2025-03-27 12:32:24');
INSERT INTO `user_activities` VALUES ('cbd90695-9003-49eb-9ec9-3b498d7ef0c9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.143.246', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 21:35:52', '2025-04-03 21:35:52');
INSERT INTO `user_activities` VALUES ('cc70bcd6-c6d3-45b7-91d6-8fdde24fc7a1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.104', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:16:56', '2025-03-26 21:16:56');
INSERT INTO `user_activities` VALUES ('ceccca3e-a661-4a1e-98fa-2fa406ee1924', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.188.109', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-01 21:43:55', '2025-04-01 21:43:55');
INSERT INTO `user_activities` VALUES ('d1181354-d7b2-41c5-a20d-d49392b376f9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.181', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:44:57', '2025-03-26 22:44:57');
INSERT INTO `user_activities` VALUES ('d30d22aa-2408-405c-a1de-86ff77835e38', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.12', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:43:44', '2025-04-05 11:43:44');
INSERT INTO `user_activities` VALUES ('d3962cf1-d076-49c1-b8f3-baa842b7e965', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.142.189', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:34:42', '2025-04-05 11:34:42');
INSERT INTO `user_activities` VALUES ('d444038b-8648-4ebf-81c5-a0b6c1254b21', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '104.23.175.240', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:21:53', '2025-03-28 10:21:53');
INSERT INTO `user_activities` VALUES ('d4814d62-410a-44ae-b9f5-34181e5e1767', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'daily_usage', '162.158.170.206', 'PostmanRuntime/7.43.2', 120, 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-27 17:52:10', '2025-03-27 17:54:22');
INSERT INTO `user_activities` VALUES ('d491f7eb-a9ff-458d-b4a5-b4e5f6811c5a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '108.162.226.27', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 19:57:05', '2025-04-05 19:57:05');
INSERT INTO `user_activities` VALUES ('d62c5e48-c14f-49f6-93b8-2c06f6a50033', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.142.27', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:53:03', '2025-04-04 16:53:03');
INSERT INTO `user_activities` VALUES ('d94509f2-eac5-4115-aeef-5fbeba85cf86', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.18', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:13:17', '2025-03-27 12:13:17');
INSERT INTO `user_activities` VALUES ('da6fc195-0a42-4545-b638-50d44ceff4c8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.133', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:01:55', '2025-03-26 23:01:55');
INSERT INTO `user_activities` VALUES ('dd24e426-7c8d-4257-a4dc-78d763f37b42', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.249', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:19:35', '2025-03-30 19:19:35');
INSERT INTO `user_activities` VALUES ('dd9eb460-9463-40fe-b3d4-88461a4c8fb1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.101', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:37:51', '2025-03-27 08:37:51');
INSERT INTO `user_activities` VALUES ('debccabe-a2cd-4f3f-8f91-623d4226518a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.80', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 14:11:46', '2025-03-28 14:11:46');
INSERT INTO `user_activities` VALUES ('df2b24a1-8605-4820-a988-3962d1b64c6d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.68.164.60', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:43:34', '2025-03-26 22:43:34');
INSERT INTO `user_activities` VALUES ('e02a9f40-d1fa-4184-8ff4-eca2b8018c57', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.113', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:39:41', '2025-03-28 13:39:41');
INSERT INTO `user_activities` VALUES ('e07c6761-dc91-44d6-b172-26391a4e8ba4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.81.163', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:55:13', '2025-03-30 19:55:13');
INSERT INTO `user_activities` VALUES ('e27d9e12-350d-4f8d-8e1c-87ce4b2e5436', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.106.57', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:06:50', '2025-03-27 12:06:50');
INSERT INTO `user_activities` VALUES ('e325cea4-aefe-4f1a-9543-e9acad6eddac', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.163.222', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 15:49:57', '2025-03-28 15:49:57');
INSERT INTO `user_activities` VALUES ('e6bc7324-680c-4386-a881-dd2af314f153', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.13', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 17:00:16', '2025-04-05 17:00:16');
INSERT INTO `user_activities` VALUES ('e97df875-7152-42a3-9c00-983344f7aca1', '26cd0a68-ab90-4397-8c0c-af35496a13ca', 'login', '172.71.124.138', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:55:58', '2025-03-27 17:55:58');
INSERT INTO `user_activities` VALUES ('ecfc3187-6a3d-4b39-b363-be4503c92ed3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.143.165', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:09:32', '2025-03-26 23:09:32');
INSERT INTO `user_activities` VALUES ('ed7a623e-0505-4c4c-bea2-f0c56fa64cfe', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.102', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:39:27', '2025-03-30 19:39:27');
INSERT INTO `user_activities` VALUES ('ef0584cb-c2e5-4e82-a4ab-d6aa3873a4f2', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.71.124.138', 'PostmanRuntime/7.43.2', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:54:36', '2025-03-27 17:54:36');
INSERT INTO `user_activities` VALUES ('f1f5084d-d000-4dfd-aa5b-394117e16246', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.176.87', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:41:12', '2025-03-28 16:41:12');
INSERT INTO `user_activities` VALUES ('f28452eb-b0cd-4483-a0c1-4b19a9128c5b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.70.208.116', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 08:38:13', '2025-03-28 08:38:13');
INSERT INTO `user_activities` VALUES ('f4b35f16-465d-4ffd-80c5-2e14e2d54973', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.34', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:21:29', '2025-03-28 10:21:29');
INSERT INTO `user_activities` VALUES ('f68efdf8-e62a-4ea3-a9d2-0a62da1a8915', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.83', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 19:23:13', '2025-04-04 19:23:13');
INSERT INTO `user_activities` VALUES ('fb2cfb3f-e8db-4a11-a479-0634a11a5834', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '162.158.88.79', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:38:13', '2025-04-05 10:38:13');
INSERT INTO `user_activities` VALUES ('fe14b1ab-11e0-445a-ba53-753df3da6c62', '2239d434-7761-4aad-bb5b-e3e321ccdec4', 'login', '172.69.166.115', 'Dart/3.7 (dart:io)', 0, '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:03:19', '2025-03-26 21:03:19');

-- ----------------------------
-- Table structure for user_sessions
-- ----------------------------
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions`  (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp,
  `duration` int NOT NULL COMMENT 'Dalam detik',
  `status` enum('active','expired') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'active',
  `ip_address` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sekolah_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL COMMENT 'Null untuk super_admin',
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `sekolah_id`(`sekolah_id` ASC) USING BTREE,
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_sessions
-- ----------------------------
INSERT INTO `user_sessions` VALUES ('0381b760-193f-41c5-ba8d-8e9f50e03ed0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 12:32:24', '2025-03-27 12:37:31', 307, 'expired', '172.70.208.161', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:32:24', '2025-03-27 12:37:31');
INSERT INTO `user_sessions` VALUES ('040a3793-3e4d-49d4-90e0-918ac22bc7bd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 12:36:58', '2025-03-28 12:51:39', 881, 'expired', '162.158.162.121', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:36:58', '2025-03-28 12:51:39');
INSERT INTO `user_sessions` VALUES ('079e6dc7-ca38-4b3a-a19f-9e461f310416', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 05:07:07', '2025-03-28 05:09:12', 125, 'expired', '162.158.170.95', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:07:07', '2025-03-28 05:09:12');
INSERT INTO `user_sessions` VALUES ('0b6d0379-13f1-4afc-8c6e-8df62416399b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 08:38:13', '2025-03-28 10:20:27', 6134, 'expired', '172.70.208.116', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 08:38:13', '2025-03-28 10:20:27');
INSERT INTO `user_sessions` VALUES ('11036d56-6225-4b5a-ac91-3010c940dc56', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 13:30:25', '2025-03-26 17:20:00', 13775, 'expired', '172.69.165.29', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 13:30:25', '2025-03-26 17:20:00');
INSERT INTO `user_sessions` VALUES ('113821db-bb56-4b79-819f-1b826e5e08f2', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 07:54:19', '2025-03-28 08:38:13', 2634, 'expired', '162.158.163.133', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 07:54:19', '2025-03-28 08:38:13');
INSERT INTO `user_sessions` VALUES ('1407a51d-365a-491e-af43-072a06d11f47', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:37:51', '2025-03-27 08:39:26', 95, 'expired', '162.158.88.101', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:37:51', '2025-03-27 08:39:26');
INSERT INTO `user_sessions` VALUES ('17a81d9a-3ed0-4ae6-a797-3dffdacfc71d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:14:15', '2025-03-26 21:14:33', 18, 'expired', '172.68.164.150', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:15', '2025-03-26 21:14:33');
INSERT INTO `user_sessions` VALUES ('17e06338-a711-4ff9-8525-364b84c9d734', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:39:41', '2025-03-28 13:39:56', 15, 'expired', '172.71.124.113', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:39:41', '2025-03-28 13:39:56');
INSERT INTO `user_sessions` VALUES ('1832142e-1ce9-460a-932a-b10dae31aa76', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 17:14:00', '2025-03-30 19:18:05', 180245, 'expired', '172.68.164.46', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 17:14:00', '2025-03-30 19:18:05');
INSERT INTO `user_sessions` VALUES ('1957b6d7-f40f-4f1f-9a32-8d7cdb30f28b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:57:03', '2025-03-26 23:01:10', 247, 'expired', '172.71.81.56', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:57:03', '2025-03-26 23:01:10');
INSERT INTO `user_sessions` VALUES ('1bf430ba-4d7c-411a-b8e3-5b9a85eb45b0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:02:25', '2025-03-26 23:03:54', 89, 'expired', '172.71.124.11', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:02:25', '2025-03-26 23:03:54');
INSERT INTO `user_sessions` VALUES ('1c9f7505-c5d5-4462-8d96-531d97e2871c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 06:04:39', '2025-03-28 06:13:29', 530, 'expired', '172.71.81.228', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 06:04:39', '2025-03-28 06:13:29');
INSERT INTO `user_sessions` VALUES ('1e781d0f-3b70-425c-af70-4f5bf073285b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 19:23:13', '2025-04-04 20:22:59', 3586, 'expired', '162.158.88.83', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 19:23:13', '2025-04-04 20:22:59');
INSERT INTO `user_sessions` VALUES ('1e8bc622-d559-403d-8daf-e3ade937dfd5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-06 12:38:53', '2025-04-06 13:04:34', 1541, 'active', '162.158.189.139', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 12:38:53', '2025-04-06 13:04:34');
INSERT INTO `user_sessions` VALUES ('20942e56-b0d2-4e6a-955d-afe3f98dd08c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:44:01', '2025-03-26 22:44:57', 56, 'expired', '172.70.208.13', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:44:01', '2025-03-26 22:44:57');
INSERT INTO `user_sessions` VALUES ('20d46284-a027-4a4d-ac6e-f120f7806a7f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:14:41', '2025-03-26 21:15:09', 28, 'expired', '108.162.226.91', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:41', '2025-03-26 21:15:09');
INSERT INTO `user_sessions` VALUES ('23bbee2f-d00e-49c7-9363-cd0c7f98c890', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:35:56', '2025-03-27 10:57:04', 1268, 'expired', '108.162.226.72', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:35:56', '2025-03-27 10:57:04');
INSERT INTO `user_sessions` VALUES ('2505e6eb-805a-467b-90a7-59a908e0e8fa', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', '2025-03-26 17:16:29', '2025-04-04 16:43:12', 775603, 'active', '162.158.106.153', 'PostmanRuntime/7.43.2', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 17:16:29', '2025-04-04 16:43:12');
INSERT INTO `user_sessions` VALUES ('257884b0-9327-4683-bf35-8251f73a1f5e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:49:42', '2025-03-26 22:50:48', 66, 'expired', '172.71.152.56', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:49:42', '2025-03-26 22:50:48');
INSERT INTO `user_sessions` VALUES ('26a907a3-e0a9-4dc8-9912-509a6668aa66', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-03 21:35:52', '2025-04-04 16:06:24', 66632, 'expired', '172.70.143.246', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 21:35:52', '2025-04-04 16:06:24');
INSERT INTO `user_sessions` VALUES ('26f7cf80-3392-44ed-be99-f84dd29c7ea2', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:06:58', '2025-03-26 23:08:36', 98, 'expired', '172.68.164.3', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:06:58', '2025-03-26 23:08:36');
INSERT INTO `user_sessions` VALUES ('277cf121-f05a-4e08-98c0-359c6aa9129a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:15:09', '2025-03-26 21:16:18', 69, 'expired', '172.68.164.29', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:15:09', '2025-03-26 21:16:18');
INSERT INTO `user_sessions` VALUES ('293f6707-9a50-4291-9bd6-c559b7df34ac', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:54:38', '2025-03-26 21:54:48', 10, 'expired', '172.71.124.200', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:54:38', '2025-03-26 21:54:48');
INSERT INTO `user_sessions` VALUES ('29bd0dd4-ac44-4a4d-ac0f-c458bb296cd3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 21:38:00', '2025-04-05 22:36:01', 3481, 'expired', '172.70.208.138', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 21:38:00', '2025-04-05 22:36:01');
INSERT INTO `user_sessions` VALUES ('2d58ed0b-c5d3-45d2-9c87-da8fd6002d5c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:16:21', '2025-03-28 13:27:41', 680, 'expired', '104.23.175.252', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:16:21', '2025-03-28 13:27:41');
INSERT INTO `user_sessions` VALUES ('2e0ed6c3-d062-491d-a25d-c651c6ef4be0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-02 09:43:31', '2025-04-02 09:44:14', 43, 'expired', '162.158.190.41', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:43:31', '2025-04-02 09:44:14');
INSERT INTO `user_sessions` VALUES ('2e398435-bb28-4a40-b02d-30979cef90c6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 11:43:44', '2025-04-05 11:54:06', 622, 'expired', '172.69.176.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:43:44', '2025-04-05 11:54:06');
INSERT INTO `user_sessions` VALUES ('2ee07707-ec26-4526-8e75-d0713f1a8027', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 11:34:42', '2025-04-05 11:36:56', 134, 'expired', '172.70.142.189', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:34:42', '2025-04-05 11:36:56');
INSERT INTO `user_sessions` VALUES ('2fabd7bb-023c-448f-be9c-790592ed4b6c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 20:37:27', '2025-04-05 20:42:07', 280, 'expired', '162.158.108.131', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:37:27', '2025-04-05 20:42:07');
INSERT INTO `user_sessions` VALUES ('3195fa36-a454-47df-a0c6-56ad49a55121', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:39:27', '2025-03-30 19:55:13', 947, 'expired', '162.158.88.102', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:39:27', '2025-03-30 19:55:13');
INSERT INTO `user_sessions` VALUES ('32a0f9f1-bed5-488c-9278-e38dc4280730', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 19:57:05', '2025-04-05 20:36:13', 2348, 'expired', '108.162.226.27', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 19:57:05', '2025-04-05 20:36:13');
INSERT INTO `user_sessions` VALUES ('361bcaa3-d45d-4125-82cb-a83382233e45', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 05:39:19', '2025-03-28 05:59:24', 1205, 'expired', '172.68.164.91', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:39:19', '2025-03-28 05:59:24');
INSERT INTO `user_sessions` VALUES ('37b78f57-baf6-476f-8cdc-6e125e03e476', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-03 09:24:56', '2025-04-03 21:30:22', 43526, 'expired', '172.69.166.18', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 09:24:56', '2025-04-03 21:30:22');
INSERT INTO `user_sessions` VALUES ('38f09995-e934-495d-ad9b-6badab19c9d3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 20:42:07', '2025-04-05 21:38:00', 3353, 'expired', '104.23.175.241', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:42:07', '2025-04-05 21:38:00');
INSERT INTO `user_sessions` VALUES ('3aa08b15-2a11-4509-8eb9-5d7ef6e41020', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 10:40:08', '2025-04-05 10:56:26', 978, 'expired', '172.68.164.156', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:40:08', '2025-04-05 10:56:26');
INSERT INTO `user_sessions` VALUES ('3b4802cc-ffd7-491a-8dc3-260a387295b8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 17:00:16', '2025-04-05 18:00:15', 3599, 'expired', '172.70.208.13', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 17:00:16', '2025-04-05 18:00:15');
INSERT INTO `user_sessions` VALUES ('3b6eeba0-921e-4aaa-94df-ef3239476999', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:52:49', '2025-03-27 08:54:51', 122, 'expired', '162.158.88.111', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:52:49', '2025-03-27 08:54:51');
INSERT INTO `user_sessions` VALUES ('3cb83d45-6fd7-46bf-ad34-fc27d9872fdd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 10:56:26', '2025-04-05 11:34:42', 2296, 'expired', '162.158.162.217', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:56:26', '2025-04-05 11:34:42');
INSERT INTO `user_sessions` VALUES ('3d72b8b3-c61c-4cef-8ecc-87da39c2c0ab', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 12:51:39', '2025-03-28 12:58:37', 418, 'expired', '172.71.81.212', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:51:39', '2025-03-28 12:58:37');
INSERT INTO `user_sessions` VALUES ('3ef66c9b-c56d-4d26-ba05-87e3ce1a598f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 20:22:59', '2025-04-05 06:56:01', 37982, 'expired', '172.68.164.143', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 20:22:59', '2025-04-05 06:56:01');
INSERT INTO `user_sessions` VALUES ('437660c6-7334-429c-a93b-6c14638bd92a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 11:54:06', '2025-04-05 17:00:16', 18370, 'expired', '172.69.176.159', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:54:06', '2025-04-05 17:00:16');
INSERT INTO `user_sessions` VALUES ('476b5fef-bcd3-4905-99a5-921a247abae8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:10:33', '2025-03-26 21:13:18', 165, 'expired', '172.71.81.210', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:10:34', '2025-03-26 21:13:18');
INSERT INTO `user_sessions` VALUES ('47fa49d6-e656-4006-b51e-8c050eaffcb3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-02 07:27:54', '2025-04-02 09:43:31', 8137, 'expired', '172.69.165.13', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 07:27:54', '2025-04-02 09:43:31');
INSERT INTO `user_sessions` VALUES ('4c430a7f-e492-46fa-ae7f-2d329dba6c37', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 21:47:35', '2025-03-31 20:34:59', 82044, 'expired', '172.70.208.116', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 21:47:35', '2025-03-31 20:34:59');
INSERT INTO `user_sessions` VALUES ('4c72b445-101a-436f-9bdf-70fd4aa0aef9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:09:02', '2025-03-26 21:09:37', 35, 'expired', '172.70.208.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:02', '2025-03-26 21:09:37');
INSERT INTO `user_sessions` VALUES ('4eb271ff-a666-470e-a2ac-12bdcd908f9d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 18:04:05', '2025-03-26 21:03:19', 10754, 'expired', '172.70.92.249', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:04:05', '2025-03-26 21:03:19');
INSERT INTO `user_sessions` VALUES ('4fe196ed-16e7-4e78-86dc-1b30d596aecf', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 12:06:50', '2025-03-27 12:13:17', 387, 'expired', '162.158.106.57', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:06:50', '2025-03-27 12:13:17');
INSERT INTO `user_sessions` VALUES ('53e415d0-0fe9-43ec-94af-4177b10d4367', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:05:18', '2025-03-27 10:20:03', 885, 'expired', '162.158.108.168', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:05:18', '2025-03-27 10:20:03');
INSERT INTO `user_sessions` VALUES ('53f8fed1-a028-4267-853c-a88d1bdf25c8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 10:21:29', '2025-03-28 10:21:53', 24, 'expired', '172.69.166.34', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:21:29', '2025-03-28 10:21:53');
INSERT INTO `user_sessions` VALUES ('554d2074-7bf3-44e2-895a-36742acdc26a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 18:00:15', '2025-04-05 19:57:05', 7010, 'expired', '172.68.164.20', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 18:00:15', '2025-04-05 19:57:05');
INSERT INTO `user_sessions` VALUES ('5758b7c4-aa6b-408c-a532-7e7b14d62a1c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 16:54:38', '2025-04-04 19:23:13', 8915, 'expired', '172.71.124.81', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:54:38', '2025-04-04 19:23:13');
INSERT INTO `user_sessions` VALUES ('5841b097-4b63-4c31-8cf3-5c12b8df76c5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 16:41:12', '2025-03-28 16:45:17', 245, 'expired', '172.69.176.87', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:41:12', '2025-03-28 16:45:17');
INSERT INTO `user_sessions` VALUES ('587a26f9-0a62-4315-be50-232ba2ab7822', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 05:59:24', '2025-03-28 06:04:39', 315, 'expired', '172.70.189.133', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:59:24', '2025-03-28 06:04:39');
INSERT INTO `user_sessions` VALUES ('599e1fa8-2c72-41ca-bae3-df0d84fc67f2', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 12:29:15', '2025-03-27 12:32:24', 189, 'expired', '162.158.107.3', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:29:15', '2025-03-27 12:32:24');
INSERT INTO `user_sessions` VALUES ('59b7e708-de4e-4426-af13-25bc2170be25', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:43:34', '2025-03-26 22:44:01', 27, 'expired', '172.68.164.60', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:43:34', '2025-03-26 22:44:01');
INSERT INTO `user_sessions` VALUES ('5da84e30-0360-4e3d-be65-c8d936f7d95d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 12:37:31', '2025-03-27 17:54:36', 19025, 'expired', '108.162.226.131', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:37:31', '2025-03-27 17:54:36');
INSERT INTO `user_sessions` VALUES ('5f8bfe0f-a3f3-4876-a419-491cc7ff5c78', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 10:21:53', '2025-03-28 10:52:55', 1862, 'expired', '104.23.175.240', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:21:53', '2025-03-28 10:52:55');
INSERT INTO `user_sessions` VALUES ('62627fd7-c96b-493e-ac74-0512a98b9a8b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:44:34', '2025-03-28 14:11:46', 1632, 'expired', '104.23.175.185', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:44:34', '2025-03-28 14:11:46');
INSERT INTO `user_sessions` VALUES ('6472ce29-18a7-454e-a3b2-a8c00a57c23b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-01 21:43:55', '2025-04-01 21:53:23', 568, 'expired', '172.70.188.109', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-01 21:43:55', '2025-04-01 21:53:23');
INSERT INTO `user_sessions` VALUES ('6478863e-65d5-460d-8128-106610806c49', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-02 09:51:32', '2025-04-03 09:24:56', 84804, 'expired', '172.70.189.82', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:51:32', '2025-04-03 09:24:56');
INSERT INTO `user_sessions` VALUES ('6815ac9b-41d5-42f5-8edd-da5025d16462', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 21:39:45', '2025-03-30 21:47:35', 470, 'expired', '172.69.166.19', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 21:39:45', '2025-03-30 21:47:35');
INSERT INTO `user_sessions` VALUES ('68802e8f-c4b8-4bd1-ad58-215a6861768c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:57:04', '2025-03-27 11:09:01', 717, 'expired', '162.158.162.170', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:57:04', '2025-03-27 11:09:01');
INSERT INTO `user_sessions` VALUES ('688b631c-945d-4e88-952e-8dac143ffb8d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:01:10', '2025-03-26 23:01:55', 45, 'expired', '172.69.176.149', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:01:10', '2025-03-26 23:01:55');
INSERT INTO `user_sessions` VALUES ('71f6d25b-f84a-46d5-b6d8-202d14de3ec3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-01 21:53:23', '2025-04-02 06:12:02', 29919, 'expired', '172.71.152.5', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-01 21:53:23', '2025-04-02 06:12:02');
INSERT INTO `user_sessions` VALUES ('72a25611-8966-49f3-81e1-a1c491cd6862', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:38:26', '2025-03-30 19:39:27', 61, 'expired', '172.71.124.21', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:38:26', '2025-03-30 19:39:27');
INSERT INTO `user_sessions` VALUES ('73ff4353-f33e-442b-9e44-76ace60d2d6e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-31 21:20:01', '2025-04-01 21:43:55', 87834, 'expired', '172.69.166.24', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 21:20:01', '2025-04-01 21:43:55');
INSERT INTO `user_sessions` VALUES ('742bdf26-2e13-4064-bc17-ddc5d364e599', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 12:59:19', '2025-03-28 13:06:45', 446, 'expired', '162.158.88.64', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:59:19', '2025-03-28 13:06:45');
INSERT INTO `user_sessions` VALUES ('752d420b-3a5d-4ae8-8011-6249b8ce80c5', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:04:55', '2025-03-27 10:05:18', 23, 'expired', '162.158.88.86', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:04:55', '2025-03-27 10:05:18');
INSERT INTO `user_sessions` VALUES ('785bbcd6-4a72-49c9-b9b7-e2fff5589180', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:09:50', '2025-03-26 21:10:33', 43, 'expired', '172.70.208.161', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:50', '2025-03-26 21:10:33');
INSERT INTO `user_sessions` VALUES ('79be2501-612a-49ba-b738-70223b0c3f95', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:00:00', '2025-03-27 10:04:55', 295, 'expired', '172.70.143.6', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:00:00', '2025-03-27 10:04:55');
INSERT INTO `user_sessions` VALUES ('7d49c7ff-f6fc-4366-a544-91f365b34c53', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:10:05', '2025-03-26 23:10:47', 42, 'expired', '172.69.166.24', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:10:05', '2025-03-26 23:10:47');
INSERT INTO `user_sessions` VALUES ('7db3a055-0f18-462e-84ae-c675f5c3e6a3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:50:48', '2025-03-26 22:53:06', 138, 'expired', '108.162.226.173', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:50:48', '2025-03-26 22:53:06');
INSERT INTO `user_sessions` VALUES ('7ece21e0-b103-48cc-beac-312a9f9e82eb', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:05:47', '2025-03-26 23:06:58', 71, 'expired', '108.162.226.103', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:05:47', '2025-03-26 23:06:58');
INSERT INTO `user_sessions` VALUES ('8054ec22-c5d8-4edf-a124-851dc64eceac', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:53:06', '2025-03-26 22:57:03', 237, 'expired', '162.158.108.152', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:53:06', '2025-03-26 22:57:03');
INSERT INTO `user_sessions` VALUES ('81cdd35f-e593-4cac-b046-8668552759d3', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 16:13:27', '2025-04-04 16:53:03', 2376, 'expired', '172.70.208.139', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:13:27', '2025-04-04 16:53:03');
INSERT INTO `user_sessions` VALUES ('8273cf03-db17-4103-a49a-649b0234b48a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:08:36', '2025-03-26 23:09:32', 56, 'expired', '172.71.124.112', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:08:36', '2025-03-26 23:09:32');
INSERT INTO `user_sessions` VALUES ('83b8f3e1-4bd8-43cd-a50d-7040f4fc8e53', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 22:36:01', '2025-04-06 12:38:53', 50572, 'expired', '172.70.208.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 22:36:01', '2025-04-06 12:38:53');
INSERT INTO `user_sessions` VALUES ('84407092-8eb0-40b5-9758-2d0dd9b9f652', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-02 06:12:02', '2025-04-02 07:27:54', 4552, 'expired', '108.162.226.214', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 06:12:02', '2025-04-02 07:27:54');
INSERT INTO `user_sessions` VALUES ('849d694a-9908-476b-8ac8-425f46fab281', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 06:56:01', '2025-04-05 10:37:23', 13282, 'expired', '162.158.106.26', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 06:56:01', '2025-04-05 10:37:23');
INSERT INTO `user_sessions` VALUES ('887955f8-58e7-4ee8-9796-aba5190579bc', 'dba9b8f1-d12c-4429-8863-d4ee80fd9f34', '2025-03-26 13:45:07', '2025-03-26 17:16:29', 12682, 'expired', '172.71.124.83', 'PostmanRuntime/7.43.2', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 13:45:07', '2025-03-26 17:16:29');
INSERT INTO `user_sessions` VALUES ('889eb06a-9dc0-48ad-b074-062b20d5740b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 05:09:12', '2025-03-28 05:39:19', 1807, 'expired', '172.70.92.223', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 05:09:12', '2025-03-28 05:39:19');
INSERT INTO `user_sessions` VALUES ('8dca8410-96ac-4f85-9a27-1dbcb3173754', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-02 09:44:14', '2025-04-02 09:51:32', 438, 'expired', '172.71.81.61', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-02 09:44:14', '2025-04-02 09:51:32');
INSERT INTO `user_sessions` VALUES ('8f836b25-c697-466b-95b6-cdc21dce6197', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:36:09', '2025-03-27 08:37:51', 102, 'expired', '172.69.166.63', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:36:09', '2025-03-27 08:37:51');
INSERT INTO `user_sessions` VALUES ('8ff2d274-583a-4cc0-97c3-d885855fbcaf', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 01:15:11', '2025-03-26 13:30:25', 44114, 'expired', '172.68.164.121', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 01:15:11', '2025-03-26 13:30:25');
INSERT INTO `user_sessions` VALUES ('907f85e6-4cfc-40e9-bd94-8c6de8635bd0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:15:18', '2025-03-26 23:18:22', 184, 'expired', '172.71.124.200', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:15:18', '2025-03-26 23:18:22');
INSERT INTO `user_sessions` VALUES ('91466653-1ff4-4518-953f-78b587f031a6', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 10:37:23', '2025-04-05 10:38:13', 50, 'expired', '162.158.170.218', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:37:23', '2025-04-05 10:38:13');
INSERT INTO `user_sessions` VALUES ('94f2a2d2-6906-4a84-9595-6c5cdf417299', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 10:20:03', '2025-03-27 10:35:56', 953, 'expired', '162.158.88.7', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 10:20:03', '2025-03-27 10:35:56');
INSERT INTO `user_sessions` VALUES ('95b2625a-f6c6-4816-b4c6-bff6bf482e80', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 16:53:03', '2025-04-04 16:54:38', 95, 'expired', '172.70.142.27', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:53:03', '2025-04-04 16:54:38');
INSERT INTO `user_sessions` VALUES ('97be4b41-5a03-42f0-a00d-3372d6fd276c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 12:13:17', '2025-03-27 12:29:15', 958, 'expired', '172.69.166.18', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 12:13:17', '2025-03-27 12:29:15');
INSERT INTO `user_sessions` VALUES ('9825e262-6abb-4d42-ba07-b56809746e82', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:39:26', '2025-03-27 08:46:32', 426, 'expired', '172.70.147.9', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:39:26', '2025-03-27 08:46:32');
INSERT INTO `user_sessions` VALUES ('9ca36884-3b83-489e-852f-13d9ff3f3085', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:28:52', '2025-03-28 13:32:47', 235, 'expired', '172.71.82.23', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:28:53', '2025-03-28 13:32:47');
INSERT INTO `user_sessions` VALUES ('9d3b67e1-adb6-4bfb-8770-df823d7d51ab', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 20:36:13', '2025-04-05 20:37:27', 74, 'expired', '104.23.175.44', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 20:36:13', '2025-04-05 20:37:27');
INSERT INTO `user_sessions` VALUES ('9dc1f66e-dbd5-49e2-ad63-b4151e1c68bc', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 17:54:36', '2025-03-28 04:19:11', 37475, 'expired', '172.71.124.138', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:54:36', '2025-03-28 04:19:11');
INSERT INTO `user_sessions` VALUES ('9f043283-df81-4318-90fe-4a619fa83150', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:54:51', '2025-03-27 10:00:00', 3909, 'expired', '162.158.107.56', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:54:51', '2025-03-27 10:00:00');
INSERT INTO `user_sessions` VALUES ('9f3f744c-0489-4bcf-80ff-9666f8ad205c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:03:31', '2025-03-26 21:04:15', 44, 'expired', '162.158.88.6', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:03:31', '2025-03-26 21:04:15');
INSERT INTO `user_sessions` VALUES ('a0d4d4ed-4c14-4238-9c62-714cfc28dee7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:06:45', '2025-03-28 13:10:58', 253, 'expired', '172.70.143.134', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:06:45', '2025-03-28 13:10:58');
INSERT INTO `user_sessions` VALUES ('a3f5acfe-e118-43f2-96dc-17305df6e0ca', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:05:04', '2025-03-26 21:09:02', 238, 'expired', '162.158.190.103', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:05:04', '2025-03-26 21:09:02');
INSERT INTO `user_sessions` VALUES ('a42bb72f-a9bf-4567-9c50-196e15ef818c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:13:18', '2025-03-26 21:13:37', 19, 'expired', '172.70.143.135', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:13:18', '2025-03-26 21:13:37');
INSERT INTO `user_sessions` VALUES ('a539e39f-7957-45a0-8382-73c0f59fb1a4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 16:58:37', '2025-03-28 17:08:12', 575, 'expired', '108.162.226.130', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:58:37', '2025-03-28 17:08:12');
INSERT INTO `user_sessions` VALUES ('a6c1c307-985d-4a77-a5ac-7272b0d967dc', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 08:46:32', '2025-03-27 08:52:49', 377, 'expired', '172.70.208.161', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 08:46:32', '2025-03-27 08:52:49');
INSERT INTO `user_sessions` VALUES ('a868c0c3-3066-42d7-8556-73e53819145c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:19:35', '2025-03-30 19:23:39', 244, 'expired', '162.158.106.249', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:19:35', '2025-03-30 19:23:39');
INSERT INTO `user_sessions` VALUES ('a9a255b8-9032-4faa-86f5-b55b76f922ad', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:04:39', '2025-03-26 21:05:04', 25, 'expired', '162.158.189.17', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:04:39', '2025-03-26 21:05:04');
INSERT INTO `user_sessions` VALUES ('aa016fe2-3b71-4ff7-bf1e-e1cc734de292', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:13:37', '2025-03-26 21:14:15', 38, 'expired', '162.158.189.16', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:13:37', '2025-03-26 21:14:15');
INSERT INTO `user_sessions` VALUES ('abf34049-8cb8-4e13-898c-9aba7ab38a12', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:09:32', '2025-03-26 23:10:05', 33, 'expired', '172.70.143.165', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:09:32', '2025-03-26 23:10:05');
INSERT INTO `user_sessions` VALUES ('adc958e4-fc1a-4e9c-86fa-4b94f26b3609', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:10:58', '2025-03-28 13:16:21', 323, 'expired', '162.158.88.125', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:10:58', '2025-03-28 13:16:21');
INSERT INTO `user_sessions` VALUES ('ae5fe150-22b9-48e3-8c8a-8808cc8944fc', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 11:09:09', '2025-03-28 12:36:58', 5269, 'expired', '172.69.176.23', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 11:09:09', '2025-03-28 12:36:58');
INSERT INTO `user_sessions` VALUES ('af457200-a19b-43f5-8aa6-bd52f25c9b7d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:32:47', '2025-03-28 13:39:41', 414, 'expired', '162.158.107.2', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:32:47', '2025-03-28 13:39:41');
INSERT INTO `user_sessions` VALUES ('b01c5e3b-ae12-414a-96d5-f1162eb055b9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:18:05', '2025-03-30 19:19:35', 90, 'expired', '172.70.208.116', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:18:05', '2025-03-30 19:19:35');
INSERT INTO `user_sessions` VALUES ('b01d183f-84bc-49ea-b4d9-45cec6c5279b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:04:15', '2025-03-26 21:04:39', 24, 'expired', '172.71.124.143', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:04:15', '2025-03-26 21:04:39');
INSERT INTO `user_sessions` VALUES ('b1578d14-3acb-4587-be62-a81f22ac17f4', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:19:55', '2025-03-27 08:36:09', 33374, 'expired', '162.158.189.237', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:19:55', '2025-03-27 08:36:09');
INSERT INTO `user_sessions` VALUES ('b65e4d9a-5fc4-4404-90fc-cc1e528749d2', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 14:11:46', '2025-03-28 15:49:57', 5891, 'expired', '162.158.88.80', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 14:11:46', '2025-03-28 15:49:57');
INSERT INTO `user_sessions` VALUES ('b7af1c95-9cd1-47e0-8711-9556cf8e283f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:44:57', '2025-03-26 22:49:42', 285, 'expired', '162.158.106.181', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:44:57', '2025-03-26 22:49:42');
INSERT INTO `user_sessions` VALUES ('b7b8a4cc-fb18-4d33-9762-6cae73fd0c6d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-31 20:34:59', '2025-03-31 21:20:01', 2702, 'expired', '172.70.147.3', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-31 20:34:59', '2025-03-31 21:20:01');
INSERT INTO `user_sessions` VALUES ('b90bcdd3-e489-453d-b1f0-c54ebfd315f0', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:18:22', '2025-03-26 23:19:55', 93, 'expired', '172.70.189.100', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:18:22', '2025-03-26 23:19:55');
INSERT INTO `user_sessions` VALUES ('b90fdb11-3240-4e11-95c8-410779472d85', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-04 16:06:24', '2025-04-04 16:13:27', 423, 'expired', '172.70.92.222', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-04 16:06:24', '2025-04-04 16:13:27');
INSERT INTO `user_sessions` VALUES ('bdbe4aed-a3a0-4594-b26c-f129f5dcfed1', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-03 21:30:22', '2025-04-03 21:35:52', 330, 'expired', '172.70.92.141', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-03 21:30:22', '2025-04-03 21:35:52');
INSERT INTO `user_sessions` VALUES ('befe89fb-1612-4322-bac2-f0a2433c11c8', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:09:37', '2025-03-26 21:09:50', 13, 'expired', '172.69.176.134', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:09:37', '2025-03-26 21:09:50');
INSERT INTO `user_sessions` VALUES ('bfa456e6-e02b-402b-9ad0-862930ab0909', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 11:36:56', '2025-04-05 11:43:44', 408, 'expired', '172.70.208.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 11:36:56', '2025-04-05 11:43:44');
INSERT INTO `user_sessions` VALUES ('c1c80a71-61a6-41b4-ba8a-208ea3105afe', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:23:39', '2025-03-30 19:38:26', 887, 'expired', '104.23.175.54', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:23:39', '2025-03-30 19:38:26');
INSERT INTO `user_sessions` VALUES ('c1dc0908-265e-446d-aee1-8fa7ad562af7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 17:08:12', '2025-03-28 17:14:00', 348, 'expired', '162.158.171.31', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 17:08:12', '2025-03-28 17:14:00');
INSERT INTO `user_sessions` VALUES ('c3032ebd-ac70-4eee-a837-ecb08b790f5e', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 04:19:11', '2025-03-28 05:07:07', 2876, 'expired', '104.23.175.20', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 04:19:11', '2025-03-28 05:07:07');
INSERT INTO `user_sessions` VALUES ('c33f1150-68bb-4de4-be31-23281b857fdf', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 22:31:22', '2025-03-26 22:43:34', 732, 'expired', '172.70.189.100', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 22:31:22', '2025-03-26 22:43:34');
INSERT INTO `user_sessions` VALUES ('c4883035-ed1a-420c-900f-40353a0e0e7c', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 16:45:17', '2025-03-28 16:58:37', 800, 'expired', '162.158.88.33', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 16:45:17', '2025-03-28 16:58:37');
INSERT INTO `user_sessions` VALUES ('c4bb19bb-c8c4-4691-a207-502f0664551f', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 10:52:55', '2025-03-28 11:09:09', 974, 'expired', '172.70.92.187', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:52:55', '2025-03-28 11:09:09');
INSERT INTO `user_sessions` VALUES ('c66e56b6-1699-49ae-971e-13d227e8c2d7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 18:02:42', '2025-03-26 18:03:49', 67, 'expired', '108.162.226.173', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:02:42', '2025-03-26 18:03:49');
INSERT INTO `user_sessions` VALUES ('c9f94505-dc49-4229-a0b9-b06f78f0d29a', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-30 19:55:13', '2025-03-30 21:39:45', 6272, 'expired', '172.71.81.163', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-30 19:55:14', '2025-03-30 21:39:45');
INSERT INTO `user_sessions` VALUES ('cb561d6a-b286-4eb7-a795-675186bd4e15', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-27 11:09:01', '2025-03-27 12:06:50', 3469, 'expired', '162.158.106.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 11:09:01', '2025-03-27 12:06:50');
INSERT INTO `user_sessions` VALUES ('cb766ff8-6a2f-446f-be68-01bb9cbc991b', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:54:48', '2025-03-26 22:31:22', 2194, 'expired', '172.68.242.96', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:54:48', '2025-03-26 22:31:22');
INSERT INTO `user_sessions` VALUES ('d170c294-9135-42f3-9e84-fe4ddb33a85d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 10:20:27', '2025-03-28 10:21:29', 62, 'expired', '172.70.208.139', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 10:20:27', '2025-03-28 10:21:29');
INSERT INTO `user_sessions` VALUES ('d5075a57-af85-42f1-9366-4633ac2d62e7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 06:13:29', '2025-03-28 07:54:19', 6050, 'expired', '172.70.147.167', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 06:13:29', '2025-03-28 07:54:19');
INSERT INTO `user_sessions` VALUES ('d5a9f559-f1ae-4f2a-8b5a-ab986cce1d0e', '26cd0a68-ab90-4397-8c0c-af35496a13ca', '2025-03-27 17:55:58', '2025-04-06 14:31:37', 851736, 'active', '172.71.124.138', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:55:58', '2025-04-06 14:31:37');
INSERT INTO `user_sessions` VALUES ('d79d02bf-04e6-440d-a9bf-57ef8c8ca612', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:16:18', '2025-03-26 21:16:56', 38, 'expired', '172.69.166.115', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:16:18', '2025-03-26 21:16:56');
INSERT INTO `user_sessions` VALUES ('dcb4f578-5abd-4916-ae0d-cd7d4d059b9d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:01:55', '2025-03-26 23:02:25', 30, 'expired', '162.158.88.133', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:01:55', '2025-03-26 23:02:25');
INSERT INTO `user_sessions` VALUES ('dd6d181e-3112-4984-8a83-a286fe580caa', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 15:49:57', '2025-03-28 16:41:12', 3075, 'expired', '162.158.163.222', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 15:49:57', '2025-03-28 16:41:12');
INSERT INTO `user_sessions` VALUES ('de14af25-5ef4-4892-9ea1-96b2c6664488', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:03:54', '2025-03-26 23:05:47', 113, 'expired', '172.70.208.13', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:03:54', '2025-03-26 23:05:47');
INSERT INTO `user_sessions` VALUES ('e1be4b21-592d-497c-9251-e095f363bbee', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:14:33', '2025-03-26 21:14:41', 8, 'expired', '108.162.226.3', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:14:33', '2025-03-26 21:14:41');
INSERT INTO `user_sessions` VALUES ('e5fe2041-ed03-4c07-80b8-32d2da27d384', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:39:56', '2025-03-28 13:44:34', 278, 'expired', '162.158.108.16', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:39:56', '2025-03-28 13:44:34');
INSERT INTO `user_sessions` VALUES ('e6d0a34a-bc6f-47eb-aedd-c53c3c10becb', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-04-05 10:38:13', '2025-04-05 10:40:08', 115, 'expired', '162.158.88.79', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-05 10:38:13', '2025-04-05 10:40:08');
INSERT INTO `user_sessions` VALUES ('eaff8fed-6b97-4c9d-8ae5-0feb9dab82b9', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:03:19', '2025-03-26 21:03:31', 12, 'expired', '172.69.166.115', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:03:19', '2025-03-26 21:03:31');
INSERT INTO `user_sessions` VALUES ('eccae0a6-3cfb-460c-a31b-4af870221e01', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 13:27:41', '2025-03-28 13:28:52', 71, 'expired', '162.158.106.59', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 13:27:41', '2025-03-28 13:28:52');
INSERT INTO `user_sessions` VALUES ('ed248ef3-f390-4662-910b-1cee31748dd7', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 23:10:47', '2025-03-26 23:15:18', 271, 'expired', '172.71.124.158', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 23:10:47', '2025-03-26 23:15:18');
INSERT INTO `user_sessions` VALUES ('f2bb50f6-d9cb-4f63-9486-9045eafd945d', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-28 12:58:37', '2025-03-28 12:59:19', 42, 'expired', '172.68.164.20', 'PostmanRuntime/7.43.0', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-28 12:58:37', '2025-03-28 12:59:19');
INSERT INTO `user_sessions` VALUES ('f7033ff7-0ad5-4697-bdab-c5f1c9151c06', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 17:20:00', '2025-03-26 18:02:42', 2562, 'expired', '172.71.81.244', 'PostmanRuntime/7.43.2', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 17:20:00', '2025-03-26 18:02:42');
INSERT INTO `user_sessions` VALUES ('f79f6d64-95bd-4d36-b9c7-788ff55aa1dd', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 18:03:49', '2025-03-26 18:04:05', 16, 'expired', '172.70.208.12', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 18:03:49', '2025-03-26 18:04:05');
INSERT INTO `user_sessions` VALUES ('fa4c8f88-1e54-49f8-86d2-4d149ee26682', '2239d434-7761-4aad-bb5b-e3e321ccdec4', '2025-03-26 21:16:56', '2025-03-26 21:54:38', 2262, 'expired', '172.71.124.104', 'Dart/3.7 (dart:io)', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-26 21:16:56', '2025-03-26 21:54:38');

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
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `updated_at` timestamp NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
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
INSERT INTO `users` VALUES ('01db3bcd-a4f4-4667-a4bf-c0328c1f248a', 'ahmad.hidayat@example.com', '$2y$10$jm0xGytZc/.aldr0u6spmOwexwC/EXppqLMRJw5yHzCfwkOmj5O7W', 'Ahmad Hidayat', 'ahmad.hidayat@example.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-26 05:49:34', '2025-03-26 05:49:34', NULL);
INSERT INTO `users` VALUES ('0afaeb8e-2476-4066-ab15-4362ca412dec', 'testing@mail.com', '$2y$10$12cUBpGDE6ftMnnphl6l9ORmryWNsGDckwqEx7Ki7yjjDQYU/wWty', 'Testing', 'testing@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-31 20:41:10', '2025-03-31 20:46:20', '2025-03-31 20:46:20');
INSERT INTO `users` VALUES ('2239d434-7761-4aad-bb5b-e3e321ccdec4', 'testadmin1', '$2y$10$RLHymJwlQhQq97b.C86so.7Azu262JmuRA.1vxaVhyPD0nYeGutGS', 'testadmin1', 'testadmin1@mail.com', 'admin', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-04-06 12:38:53', 1, 'HmNxAFXzJBwbDs0tt0SDw5DBX6IgyxnnvhDwogTL1q9QYmO0aXYgiTV9XxTKYFYVr1LXw37bSVMT9MiD', '2025-03-26 01:13:09', '2025-04-06 12:38:53', NULL);
INSERT INTO `users` VALUES ('26cd0a68-ab90-4397-8c0c-af35496a13ca', 'siti.rahayu@example.com', '$2y$10$LK4iEvn0dEP2U6zD5RBzNOyRqfBBw.pZtCRmLf30R85asrK9YOmm2', 'Siti Rahayu', 'siti.rahayu@example.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', '2025-03-27 17:55:58', 1, '6KkDoGGJSCPIyJIEhHcnKHrNjd1d9mIBqjUfoSbMadMzJblrnNDvch1xwCfyGlliHm9Sd410ZA5JBUSR', '2025-03-26 05:49:34', '2025-03-27 17:55:58', NULL);
INSERT INTO `users` VALUES ('43865cbb-d950-4498-ba96-1264ae8032f2', 'hyacine@mail.com', '$2y$10$VAZnFIRjOK5NflEzA6RTq.hl5aUldsAQ.1rWuNYochiqqoq/fk.GC', 'Hyacine', 'hyacine@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:47:41', '2025-04-01 21:53:43', NULL);
INSERT INTO `users` VALUES ('6aa97ea0-801e-473f-97b4-471e35647808', 'budi.santoso@example.com', '$2y$10$AGkEz9H5c6v6hjgi0Cc1ZeJ9Ay46Bx5Ng5pl4CSalmQ2ERLQexNkG', 'Budi Santoso', 'budi.santoso@example.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-26 05:49:34', '2025-03-26 05:49:34', NULL);
INSERT INTO `users` VALUES ('6e032b23-5da2-4131-b58b-06fb23873172', 'screwllum@mail.com', '$2y$10$6O42AkfHnF31Ps9E6tX6nuySTrALonmAqso25EE8pH7mIKOVKcv7.', 'Screwllum', 'screwllum@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:01:28', '2025-04-05 11:30:38', '2025-04-05 11:30:38');
INSERT INTO `users` VALUES ('7d75d064-f305-42d6-b175-15fa7d698844', 'clara@mail.com', '$2y$10$c4JRM4UCJ8Zy7MslZfZ.o.CAgMXCGn7dEVj.J7jXv0xoj/A6uECiK', 'Clara', 'clara@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:06:57', '2025-03-28 13:06:57', NULL);
INSERT INTO `users` VALUES ('7e0fd796-6ef2-4c13-b643-c3b6d580c647', 'budi@example.com', '$2y$10$lrw0qKoFO6WiN0iETOM/2.Co4x6QG8FLeXsSXdTfKWO0h8W0zvnUC', 'Budi Santoso', 'budi@example.com', 'guru', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', NULL, 1, NULL, '2025-03-26 14:23:04', '2025-03-26 14:23:04', NULL);
INSERT INTO `users` VALUES ('7feb514b-149a-4bb9-9b2d-6da0f91a2604', 'testing2@mail.com', '$2y$10$xm3FLQDmZVhxAUU2sFPpuOZesCwf8jf6dPFF8avMYJc4J7CeEfoky', 'testing2', 'testing2@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-31 20:46:10', '2025-03-31 20:46:18', '2025-03-31 20:46:18');
INSERT INTO `users` VALUES ('84ffdc06-e21a-4fbc-950c-576323b0d31a', 'janedoe@example.com', '$2y$10$Wo0xYOxz86TFbTvi/U0rD.PY/HkdGye9CHijfP5gFRrAjzZ01Ruyq', 'Jane Doe', 'janedoe@example.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 08:04:12', '2025-03-28 08:04:12', NULL);
INSERT INTO `users` VALUES ('8f4a50bc-994f-4356-b282-89d325eab40d', 'cipher@mail.com', '$2y$10$Kpl4TmDZjdQ8DjfYZJ5qK.vwch.vq6yZfibDmGlN4xpUI.d3P4OcW', 'Cipher', 'cipher@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:09:56', '2025-04-05 10:48:26', NULL);
INSERT INTO `users` VALUES ('a35c05e0-9d49-4c05-8c2b-cb868bad9503', 'maurice@mail.com', '$2y$10$JG.Nnxg1p7pG8C585HUXTe7hga4I92ixpP7efZL/9KS.QTxBHLrbe', 'Maurice', 'maurice@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:00:39', '2025-03-30 21:58:24', '2025-03-30 21:58:24');
INSERT INTO `users` VALUES ('b46bed26-a975-4605-b8bb-be6732fa60f9', 'guru1@mail.com', '$2y$10$GvfL.FXmaCWAcWX.WfpUO.HOu5uzDIaA5LJ01ccmn8LJjRhY0Lm6S', 'Guru 1', 'guru1@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 11:09:30', '2025-03-30 21:57:07', '2025-03-30 21:57:07');
INSERT INTO `users` VALUES ('bb4b73c0-86f0-4320-9ba0-9834a5e69ea2', 'stevia@mail.com', '$2y$10$qnXuOs8d0EFO3tdkjr6R5.2awUmF3l5a3VuheAnbnGDiD2.RPXRWy', 'Stevia', 'stevia@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:01:28', '2025-03-28 13:01:28', NULL);
INSERT INTO `users` VALUES ('caf1bcc6-1218-4698-b8fe-cdf462b98678', 'budi.santoso1@example.com', '$2y$10$4vwYM9X6LTEYeyQtYS7IjuukrKYvmJHHEYn6mLLe4y7rSTAEzjQi.', 'Budi Santoso', 'budi.santoso1@example.com', 'guru', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', NULL, 1, NULL, '2025-03-26 13:56:18', '2025-03-26 13:56:18', NULL);
INSERT INTO `users` VALUES ('d33149af-fdd2-4982-bf79-902b039441a9', 'janice@mail.com', '$2y$10$xZb0q5RsV6V.SiRNhyzyK.XWKdDBkz7CwpqmNVnFbyXC6RIBe6RJu', 'Janice', 'janice@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:04:49', '2025-03-31 20:46:27', '2025-03-31 20:46:27');
INSERT INTO `users` VALUES ('dba9b8f1-d12c-4429-8863-d4ee80fd9f34', 'testadmin11', '$2y$10$1ZrTMOYguqhNw6tcyauRWeUHWlnoHHcowEJpTVhoSh6NLTYZOpNGe', 'testadmin11', 'testadmin11@mail.com', 'admin', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', '2025-03-26 17:16:29', 1, 'XbaquH4fhcD3T3Gp5eZv2pV4acgFP1Gbu2LB0ZRWTbydsDEDOPpVpLIVQ5Na74M5Toiyfv8uwoPuV2PW', '2025-03-26 13:44:55', '2025-03-26 17:16:29', NULL);
INSERT INTO `users` VALUES ('e5019dd5-a2c5-4b57-8cd2-c200e284fe79', 'elena@mail.com', '$2y$10$fiWG1I3adzs68vsDpvk8huiL/WCFx9DK/oNANnCj/lptWuQZ9RUU6', 'Elena', 'elena@mail.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 13:00:39', '2025-03-28 13:00:39', NULL);
INSERT INTO `users` VALUES ('e8e040eb-0604-4522-99e9-5105a05a2666', 'guru@example.com', '$2y$10$ppeftQw.Pg9/BPwL5//GR.d0x.AeXNl8.xheFh9QYKVkHNf.dKW4m', 'Nama Guru', 'guru@example.com', 'guru', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', NULL, 1, NULL, '2025-03-26 13:55:05', '2025-03-26 15:51:55', NULL);
INSERT INTO `users` VALUES ('e98ecfe5-c902-4ed4-8feb-271bf20c1297', 'johndoe@example.com', '$2y$10$WE.IuTn/g8BIlcnb2Ho7AOqxsPrGgD9XY2NAIzgvWaneiNsUKE6yK', 'John Doe', 'johndoe@example.com', 'guru', '98267294-eb49-438b-9a9b-9aabd24bb98d', NULL, 1, NULL, '2025-03-28 08:04:31', '2025-03-28 08:04:31', NULL);
INSERT INTO `users` VALUES ('fc2ef630-4172-4bba-9c8e-0aa5ed424c69', 'guruzzz@example.com', '$2y$10$1oNXieEpuYxJ5jST8YRpAu9iQfgacyD7Bldjy85hGznmeZvKHoS5m', 'Nama Guru', 'guruzzz@example.com', 'guru', 'e8546a78-7a5a-47fc-86b9-f2b233a89e8b', NULL, 1, NULL, '2025-03-26 13:56:18', '2025-03-26 14:01:36', '2025-03-26 14:01:36');

SET FOREIGN_KEY_CHECKS = 1;
