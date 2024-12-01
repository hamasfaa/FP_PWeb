-- Created by Vertabelo (http://vertabelo.com)
-- Last modification date: 2024-11-30 17:13:33.425

-- tables
-- Table: Absen_Dosen
CREATE TABLE Absen_Dosen (
    AD_ID int  NOT NULL,
    AD_Pertemuan int  NOT NULL,
    AD_Kode char(6)  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    User_U_ID int  NOT NULL,
    CONSTRAINT Absen_Dosen_pk PRIMARY KEY (AD_ID)
);

-- Table: Absen_Mahasiswa
CREATE TABLE Absen_Mahasiswa (
    AM_ID int  NOT NULL,
    AM_Status int  NOT NULL,
    Absen_Dosen_AD_ID int  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    User_U_ID int  NOT NULL,
    CONSTRAINT Absen_Mahasiswa_pk PRIMARY KEY (AM_ID)
);

-- Table: Kelas
CREATE TABLE Kelas (
    K_ID int  NOT NULL,
    K_MataKuliah varchar(50)  NOT NULL,
    K_NamaKelas varchar(50)  NOT NULL,
    K_TanggalDibuat date  NOT NULL,
    K_KodeKelas char(6)  NOT NULL,
    CONSTRAINT Kelas_pk PRIMARY KEY (K_ID)
);

-- Table: Tugas_Dosen
CREATE TABLE Tugas_Dosen (
    TD_ID int  NOT NULL,
    TD_Judul varchar(100)  NOT NULL,
    TD_Deskripsi longtext  NOT NULL,
    TD_Deadline datetime  NOT NULL,
    TD_Status boolean  NOT NULL,
    TD_FileSoal varchar(255)  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    User_U_ID int  NOT NULL,
    CONSTRAINT Tugas_Dosen_pk PRIMARY KEY (TD_ID)
);

-- Table: Tugas_Mahasiswa
CREATE TABLE Tugas_Mahasiswa (
    TM_ID int  NOT NULL,
    TM_WaktuPengumpulan datetime  NOT NULL,
    TM_Status boolean  NOT NULL,
    TM_FileTugas varchar(255)  NOT NULL,
    TM_NilaiTugas int  NOT NULL,
    Tugas_Dosen_TD_ID int  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    User_U_ID int  NOT NULL,
    CONSTRAINT Tugas_Mahasiswa_pk PRIMARY KEY (TM_ID)
);

-- Table: User
CREATE TABLE User (
    U_ID int  NOT NULL,
    U_Nama varchar(100)  NOT NULL,
    U_Email varchar(100)  NOT NULL,
    U_Password varchar(255)  NOT NULL,
    U_Role ENUM('dosen', 'mahasiswa') NOT NULL
    U_TanggalLahir date  NOT NULL,
    U_NoPonsel varchar(15)  NOT NULL,
    U_Alamat varchar(255)  NOT NULL,
    U_Foto varchar(255)  NOT NULL,
    CONSTRAINT User_pk PRIMARY KEY (U_ID)
);

-- Table: User_Kelas
CREATE TABLE User_Kelas (
    User_U_ID int  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    CONSTRAINT User_Kelas_pk PRIMARY KEY (User_U_ID,Kelas_K_ID)
);

-- foreign keys
-- Reference: Absen_Dosen_Absen_Mahasiswa (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Dosen_Absen_Mahasiswa FOREIGN KEY Absen_Dosen_Absen_Mahasiswa (Absen_Dosen_AD_ID)
    REFERENCES Absen_Dosen (AD_ID);

-- Reference: Absen_Dosen_Kelas (table: Absen_Dosen)
ALTER TABLE Absen_Dosen ADD CONSTRAINT Absen_Dosen_Kelas FOREIGN KEY Absen_Dosen_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Absen_Dosen_User (table: Absen_Dosen)
ALTER TABLE Absen_Dosen ADD CONSTRAINT Absen_Dosen_User FOREIGN KEY Absen_Dosen_User (User_U_ID)
    REFERENCES User (U_ID);

-- Reference: Absen_Mahasiswa_Kelas (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Mahasiswa_Kelas FOREIGN KEY Absen_Mahasiswa_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Absen_Mahasiswa_User (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Mahasiswa_User FOREIGN KEY Absen_Mahasiswa_User (User_U_ID)
    REFERENCES User (U_ID);

-- Reference: Tugas_Dosen_Kelas (table: Tugas_Dosen)
ALTER TABLE Tugas_Dosen ADD CONSTRAINT Tugas_Dosen_Kelas FOREIGN KEY Tugas_Dosen_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Tugas_Dosen_Tugas_Mahasiswa (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Dosen_Tugas_Mahasiswa FOREIGN KEY Tugas_Dosen_Tugas_Mahasiswa (Tugas_Dosen_TD_ID)
    REFERENCES Tugas_Dosen (TD_ID);

-- Reference: Tugas_Dosen_User (table: Tugas_Dosen)
ALTER TABLE Tugas_Dosen ADD CONSTRAINT Tugas_Dosen_User FOREIGN KEY Tugas_Dosen_User (User_U_ID)
    REFERENCES User (U_ID);

-- Reference: Tugas_Mahasiswa_Kelas (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Mahasiswa_Kelas FOREIGN KEY Tugas_Mahasiswa_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Tugas_Mahasiswa_User (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Mahasiswa_User FOREIGN KEY Tugas_Mahasiswa_User (User_U_ID)
    REFERENCES User (U_ID);

-- Reference: User_Kelas_Kelas (table: User_Kelas)
ALTER TABLE User_Kelas ADD CONSTRAINT User_Kelas_Kelas FOREIGN KEY User_Kelas_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: User_Kelas_User (table: User_Kelas)
ALTER TABLE User_Kelas ADD CONSTRAINT User_Kelas_User FOREIGN KEY User_Kelas_User (User_U_ID)
    REFERENCES User (U_ID);

-- End of file.
