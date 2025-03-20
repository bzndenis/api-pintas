-- sekolah - Tabel untuk menyimpan data sekolah
CREATE TABLE sekolah (
    id CHAR(36) PRIMARY KEY,
    nama_sekolah VARCHAR(255) NOT NULL,
    npsn VARCHAR(255) UNIQUE NOT NULL COMMENT 'Nomor Pokok Sekolah Nasional',
    alamat TEXT NOT NULL,
    kota VARCHAR(255) NOT NULL,
    provinsi VARCHAR(255) NOT NULL,
    kode_pos VARCHAR(255) NULL,
    no_telp VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    kepala_sekolah VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- users - Tabel untuk menyimpan data login pengguna
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'guru') NOT NULL,
    sekolah_id CHAR(36) NULL COMMENT 'Null untuk super_admin',
    nama_lengkap VARCHAR(255) NULL,
    no_telepon VARCHAR(20) NULL,
    alamat_sekolah TEXT NULL,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- guru - Tabel untuk menyimpan data guru
CREATE TABLE guru (
    id CHAR(36) PRIMARY KEY,
    nip VARCHAR(255) NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    no_telp VARCHAR(255) NULL,
    user_id CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (nip, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- tahun_ajaran - Tabel untuk menyimpan data tahun ajaran
CREATE TABLE tahun_ajaran (
    id CHAR(36) PRIMARY KEY,
    nama_tahun_ajaran VARCHAR(255) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (nama_tahun_ajaran, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- kelas - Tabel untuk menyimpan data kelas
CREATE TABLE kelas (
    id CHAR(36) PRIMARY KEY,
    nama_kelas VARCHAR(255) NOT NULL,
    tingkat VARCHAR(255) NOT NULL,
    tahun_ajaran_id CHAR(36) NOT NULL,
    guru_id CHAR(36) NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (nama_kelas, tahun_ajaran_id, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- siswa - Tabel untuk menyimpan data siswa
CREATE TABLE siswa (
    id CHAR(36) PRIMARY KEY,
    nis VARCHAR(255) NOT NULL,
    nisn VARCHAR(255) NULL,
    nama VARCHAR(255) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(255) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NULL,
    nama_ortu VARCHAR(255) NULL,
    no_telp_ortu VARCHAR(255) NULL,
    kelas_id CHAR(36) NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (nis, sekolah_id),
    UNIQUE (nisn)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- mata_pelajaran - Tabel untuk menyimpan data mata pelajaran
CREATE TABLE mata_pelajaran (
    id CHAR(36) PRIMARY KEY,
    kode_mapel VARCHAR(255) NOT NULL,
    nama_mapel VARCHAR(255) NOT NULL,
    tingkat VARCHAR(255) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (kode_mapel, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- capaian_pembelajaran - Tabel untuk menyimpan data CP
CREATE TABLE capaian_pembelajaran (
    id CHAR(36) PRIMARY KEY,
    kode_cp VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    mapel_id CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (kode_cp, mapel_id, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- tujuan_pembelajaran - Tabel untuk menyimpan data TP
CREATE TABLE tujuan_pembelajaran (
    id CHAR(36) PRIMARY KEY,
    kode_tp VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    bobot DECIMAL(5, 2) NOT NULL,
    cp_id CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cp_id) REFERENCES capaian_pembelajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (kode_tp, cp_id, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- nilai_siswa - Tabel untuk menyimpan nilai siswa per TP
CREATE TABLE nilai_siswa (
    id CHAR(36) PRIMARY KEY,
    siswa_id CHAR(36) NOT NULL,
    tp_id CHAR(36) NOT NULL,
    nilai DECIMAL(5, 2) NOT NULL,
    created_by CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (tp_id) REFERENCES tujuan_pembelajaran(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (siswa_id, tp_id, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- pertemuan_bulanan - Tabel untuk menyimpan total pertemuan per bulan
CREATE TABLE pertemuan_bulanan (
    id CHAR(36) PRIMARY KEY,
    kelas_id CHAR(36) NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    total_pertemuan INT NOT NULL,
    created_by CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (kelas_id, bulan, tahun, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- absensi_siswa - Tabel untuk menyimpan data absensi siswa
CREATE TABLE absensi_siswa (
    id CHAR(36) PRIMARY KEY,
    siswa_id CHAR(36) NOT NULL,
    pertemuan_id CHAR(36) NOT NULL,
    hadir INT NOT NULL,
    izin INT NOT NULL,
    sakit INT NOT NULL,
    absen INT NOT NULL,
    created_by CHAR(36) NOT NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (pertemuan_id) REFERENCES pertemuan_bulanan(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (siswa_id, pertemuan_id, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- user_activities - Tabel untuk menyimpan aktivitas pengguna
CREATE TABLE user_activities (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(255) NULL,
    user_agent VARCHAR(255) NULL,
    duration INT NULL,
    sekolah_id CHAR(36) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- user_sessions - Tabel untuk menyimpan sesi pengguna
CREATE TABLE user_sessions (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    login_time TIMESTAMP NOT NULL,
    last_activity TIMESTAMP NULL,
    duration INT NULL,
    status ENUM('active', 'expired') DEFAULT 'active',
    ip_address VARCHAR(255) NULL,
    user_agent VARCHAR(255) NULL,
    sekolah_id CHAR(36) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;

-- settings - Tabel untuk menyimpan pengaturan sekolah
CREATE TABLE settings (
    id CHAR(36) PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT NULL,
    `group` VARCHAR(255) NULL,
    sekolah_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE CASCADE,
    UNIQUE (`key`, sekolah_id)
) ENGINE=InnoDB CHARACTER SET latin1 COLLATE latin1_general_ci;