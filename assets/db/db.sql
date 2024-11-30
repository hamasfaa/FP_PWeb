-- Created by Vertabelo (http://vertabelo.com)
-- Last modification date: 2024-11-30 12:28:52.987

-- tables
-- Table: Absen_Dosen
CREATE TABLE Absen_Dosen (
    AD_ID int  NOT NULL,
    AD_Pertemuan int  NOT NULL,
    AD_Kode char(6)  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    Dosen_D_ID int  NOT NULL,
    CONSTRAINT Absen_Dosen_pk PRIMARY KEY (AD_ID)
);

-- Table: Absen_Mahasiswa
CREATE TABLE Absen_Mahasiswa (
    AM_ID int  NOT NULL,
    AM_Status int  NOT NULL,
    Absen_Dosen_AD_ID int  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    Mahasiswa_M_ID int  NOT NULL,
    CONSTRAINT Absen_Mahasiswa_pk PRIMARY KEY (AM_ID)
);

-- Table: Dosen
CREATE TABLE Dosen (
    D_ID int  NOT NULL,
    D_Nama varchar(100)  NOT NULL,
    D_TanggalLahir date  NOT NULL,
    D_Email varchar(100)  NOT NULL,
    D_NomorPonsel varchar(15)  NOT NULL,
    D_Alamat varchar(255)  NOT NULL,
    D_Foto varchar(255)  NOT NULL,
    CONSTRAINT Dosen_pk PRIMARY KEY (D_ID)
);

-- Table: Kelas
CREATE TABLE Kelas (
    K_ID int  NOT NULL,
    K_MataKuliah varchar(100)  NOT NULL,
    K_NamaKelas varchar(50)  NOT NULL,
    K_TanggalDibuat date  NOT NULL,
    K_KodeKelas char(6)  NOT NULL,
    Dosen_D_ID int  NOT NULL,
    CONSTRAINT Kelas_pk PRIMARY KEY (K_ID)
);

-- Table: Mahasiswa
CREATE TABLE Mahasiswa (
    M_ID int  NOT NULL,
    M_Nama varchar(100)  NOT NULL,
    M_TanggalLahir date  NOT NULL,
    M_Email varchar(100)  NOT NULL,
    M_NomorPonsel varchar(15)  NOT NULL,
    M_Alamat varchar(255)  NOT NULL,
    M_Foto varchar(255)  NOT NULL,
    CONSTRAINT Mahasiswa_pk PRIMARY KEY (M_ID)
);

-- Table: Mahasiswa_Kelas
CREATE TABLE Mahasiswa_Kelas (
    Mahasiswa_M_ID int  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    CONSTRAINT Mahasiswa_Kelas_pk PRIMARY KEY (Mahasiswa_M_ID,Kelas_K_ID)
);

-- Table: Tugas_Dosen
CREATE TABLE Tugas_Dosen (
    TD_ID int  NOT NULL,
    TD_Judul varchar(255)  NOT NULL,
    TD_Deskripsi text  NOT NULL,
    TD_Deadline datetime  NOT NULL,
    TD_Status boolean  NOT NULL,
    TD_FileSoal varchar(255)  NOT NULL,
    Kelas_K_ID int  NOT NULL,
    Dosen_D_ID int  NOT NULL,
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
    Mahasiswa_M_ID int  NOT NULL,
    CONSTRAINT Tugas_Mahasiswa_pk PRIMARY KEY (TM_ID)
);

-- Table: User_Credential
CREATE TABLE User_Credential (
    UC_ID int  NOT NULL,
    UC_Email varchar(100)  NOT NULL,
    UC_Password varchar(255)  NOT NULL,
    Mahasiswa_M_ID int  NOT NULL,
    Dosen_D_ID int  NOT NULL,
    CONSTRAINT User_Credential_pk PRIMARY KEY (UC_ID)
);

-- foreign keys
-- Reference: Absen_Dosen_Absen_Mahasiswa (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Dosen_Absen_Mahasiswa FOREIGN KEY Absen_Dosen_Absen_Mahasiswa (Absen_Dosen_AD_ID)
    REFERENCES Absen_Dosen (AD_ID);

-- Reference: Absen_Dosen_Dosen (table: Absen_Dosen)
ALTER TABLE Absen_Dosen ADD CONSTRAINT Absen_Dosen_Dosen FOREIGN KEY Absen_Dosen_Dosen (Dosen_D_ID)
    REFERENCES Dosen (D_ID);

-- Reference: Absen_Dosen_Kelas (table: Absen_Dosen)
ALTER TABLE Absen_Dosen ADD CONSTRAINT Absen_Dosen_Kelas FOREIGN KEY Absen_Dosen_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Absen_Mahasiswa_Kelas (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Mahasiswa_Kelas FOREIGN KEY Absen_Mahasiswa_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Absen_Mahasiswa_Mahasiswa (table: Absen_Mahasiswa)
ALTER TABLE Absen_Mahasiswa ADD CONSTRAINT Absen_Mahasiswa_Mahasiswa FOREIGN KEY Absen_Mahasiswa_Mahasiswa (Mahasiswa_M_ID)
    REFERENCES Mahasiswa (M_ID);

-- Reference: Kelas_Dosen (table: Kelas)
ALTER TABLE Kelas ADD CONSTRAINT Kelas_Dosen FOREIGN KEY Kelas_Dosen (Dosen_D_ID)
    REFERENCES Dosen (D_ID);

-- Reference: Mahasiswa_Kelas_Kelas (table: Mahasiswa_Kelas)
ALTER TABLE Mahasiswa_Kelas ADD CONSTRAINT Mahasiswa_Kelas_Kelas FOREIGN KEY Mahasiswa_Kelas_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Mahasiswa_Kelas_Mahasiswa (table: Mahasiswa_Kelas)
ALTER TABLE Mahasiswa_Kelas ADD CONSTRAINT Mahasiswa_Kelas_Mahasiswa FOREIGN KEY Mahasiswa_Kelas_Mahasiswa (Mahasiswa_M_ID)
    REFERENCES Mahasiswa (M_ID);

-- Reference: Tugas_Dosen_Dosen (table: Tugas_Dosen)
ALTER TABLE Tugas_Dosen ADD CONSTRAINT Tugas_Dosen_Dosen FOREIGN KEY Tugas_Dosen_Dosen (Dosen_D_ID)
    REFERENCES Dosen (D_ID);

-- Reference: Tugas_Dosen_Kelas (table: Tugas_Dosen)
ALTER TABLE Tugas_Dosen ADD CONSTRAINT Tugas_Dosen_Kelas FOREIGN KEY Tugas_Dosen_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Tugas_Dosen_Tugas_Mahasiswa (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Dosen_Tugas_Mahasiswa FOREIGN KEY Tugas_Dosen_Tugas_Mahasiswa (Tugas_Dosen_TD_ID)
    REFERENCES Tugas_Dosen (TD_ID);

-- Reference: Tugas_Mahasiswa_Kelas (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Mahasiswa_Kelas FOREIGN KEY Tugas_Mahasiswa_Kelas (Kelas_K_ID)
    REFERENCES Kelas (K_ID);

-- Reference: Tugas_Mahasiswa_Mahasiswa (table: Tugas_Mahasiswa)
ALTER TABLE Tugas_Mahasiswa ADD CONSTRAINT Tugas_Mahasiswa_Mahasiswa FOREIGN KEY Tugas_Mahasiswa_Mahasiswa (Mahasiswa_M_ID)
    REFERENCES Mahasiswa (M_ID);

-- Reference: User_Credential_Dosen (table: User_Credential)
ALTER TABLE User_Credential ADD CONSTRAINT User_Credential_Dosen FOREIGN KEY User_Credential_Dosen (Dosen_D_ID)
    REFERENCES Dosen (D_ID);

-- Reference: User_Credential_Mahasiswa (table: User_Credential)
ALTER TABLE User_Credential ADD CONSTRAINT User_Credential_Mahasiswa FOREIGN KEY User_Credential_Mahasiswa (Mahasiswa_M_ID)
    REFERENCES Mahasiswa (M_ID);

-- End of file.