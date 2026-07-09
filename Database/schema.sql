 
PRAGMA foreign_keys = ON;
 
-- ----------------------------------------------------------------------------
-- 1. LOPHOCPHAN - lop hoc phan (don vi can xep lich thi)
--    "Phong hoc" o day la phong HOC LY THUYET (khac voi phong THI o bang
--    phongthi ben duoi) - chi luu de tham khao, KHONG dung cho GA xep lich.
-- ----------------------------------------------------------------------------
CREATE TABLE lophocphan (
    ma_lhp          TEXT PRIMARY KEY,
    ten_lhp         TEXT NOT NULL,
    stc             INTEGER NOT NULL,
    phong_hoc       TEXT,
    ngay_batdau     DATE,
    ngay_ketthuc    DATE,
    so_sv           INTEGER
);
 
-- ----------------------------------------------------------------------------
-- 2. DSLOPHOCPHAN - danh sach sinh vien dang ky tung lop hoc phan
-- ----------------------------------------------------------------------------
CREATE TABLE dslophocphan (
    mssv            TEXT NOT NULL,
    ma_lhp          TEXT NOT NULL,
    ho_ten          TEXT NOT NULL,
    chuyen_nganh    TEXT,
    PRIMARY KEY (mssv, ma_lhp),
    FOREIGN KEY (ma_lhp) REFERENCES lophocphan(ma_lhp)
);
 
-- ----------------------------------------------------------------------------
-- 3. PHONGTHI - danh muc phong thi
-- ----------------------------------------------------------------------------
CREATE TABLE phongthi (
    ma_phong        TEXT PRIMARY KEY,
    giang_duong     TEXT,
    suc_chua        INTEGER NOT NULL,
    loai_phong      TEXT
);
 
-- ----------------------------------------------------------------------------
-- 4. LICHTHI - bang trung tam: KET QUA XEP LICH (DAU RA CUA GIAI THUAT GA)
--    Moi dong = 1 cap (lop hoc phan, phong thi).
-- ----------------------------------------------------------------------------
CREATE TABLE lichthi (
    id_lichthi      INTEGER PRIMARY KEY AUTOINCREMENT,
    ma_lhp          TEXT NOT NULL,
    ngay_thi        DATE NOT NULL,
    ca_thi          INTEGER NOT NULL,
    ma_phong        TEXT NOT NULL,
    so_sv_phong     INTEGER NOT NULL,
    FOREIGN KEY (ma_lhp)   REFERENCES lophocphan(ma_lhp),
    FOREIGN KEY (ma_phong) REFERENCES phongthi(ma_phong),
    UNIQUE (ngay_thi, ca_thi, ma_phong)
);

-- ----------------------------------------------------------------------------
-- 5. THOIGIANTHI - Quản lý khung thời gian tổ chức kỳ thi
-- ----------------------------------------------------------------------------
CREATE TABLE thoigianthi (
    id_thoigian     INTEGER PRIMARY KEY AUTOINCREMENT,
    ngay_batdau     DATE NOT NULL,
    ngay_ketthuc    DATE NOT NULL,
    ngay_thi        DATE -- Dùng để lưu trữ các ngày thi cụ thể nếu cần
);
 
-- ----------------------------------------------------------------------------
-- INDEX ho tro truy van nhanh khi GA kiem tra rang buoc
-- ----------------------------------------------------------------------------
CREATE INDEX idx_lichthi_ngayca  ON lichthi(ngay_thi, ca_thi);
CREATE INDEX idx_lichthi_lhp     ON lichthi(ma_lhp);
CREATE INDEX idx_ds_mssv         ON dslophocphan(mssv);
