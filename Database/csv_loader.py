
import sqlite3
import pandas as pd
from pathlib import Path
 
BASE_DIR = Path(__file__).parent
DB_FILE = BASE_DIR / "exam_tabling.db"
 
CSV_DANGKY = BASE_DIR / "input" / "danh_sach_dang_ky_hoc_phan.csv"
CSV_PHONGTHI = BASE_DIR / "input" / "danh_sach_phong_thi.csv"
 
MAP_DANGKY = {
    "Mã LHP": "ma_lhp",
    "Tên LHP": "ten_lhp",
    "STC": "stc",
    "Phòng học": "phong_hoc",
    "MSSV": "mssv",
    "Họ và tên": "ho_ten",
    "Chuyên ngành": "chuyen_nganh",
    "Ngày bắt đầu": "ngay_batdau",
    "Ngày kết thúc": "ngay_ketthuc",
}
 
MAP_PHONGTHI = {
    "Mã phòng": "ma_phong",
    "Giảng đường": "giang_duong",
    "Sức chứa": "suc_chua",
    "Loại phòng": "loai_phong",
}
 
 
def get_conn():
    conn = sqlite3.connect(DB_FILE)
    conn.execute("PRAGMA foreign_keys = ON;")
    return conn
 
 
def _rename_and_check(df, mapping, filename):
    """Doi ten cot theo mapping, bao loi ro rang neu thieu cot bat buoc."""
    missing = [c for c in mapping if c not in df.columns]
    if missing:
        raise ValueError(
            f"File {filename} thieu cac cot: {missing}\n"
            f"Cot hien co trong file: {list(df.columns)}\n"
            f"=> Kiem tra lai dong tieu de (header) cua file CSV, dam bao "
            f"khop chinh xac ten cot (ke ca dau tieng Viet va khoang trang)."
        )
    df = df.rename(columns=mapping)
    return df[list(mapping.values())]
 
 
def load_phongthi(conn):
    df = pd.read_csv(CSV_PHONGTHI, dtype=str)
    df = _rename_and_check(df, MAP_PHONGTHI, CSV_PHONGTHI.name)
    df["suc_chua"] = df["suc_chua"].astype(int)
 
    rows = df[["ma_phong", "giang_duong", "suc_chua", "loai_phong"]].values.tolist()
    conn.executemany(
        """INSERT OR REPLACE INTO phongthi
           (ma_phong, giang_duong, suc_chua, loai_phong)
           VALUES (?, ?, ?, ?)""",
        rows,
    )
    print(f"Da nap {len(rows)} phong thi tu {CSV_PHONGTHI.name}")
 
 
def load_dangky_hocphan(conn):
    df = pd.read_csv(CSV_DANGKY, dtype=str)
    df = _rename_and_check(df, MAP_DANGKY, CSV_DANGKY.name)
    df["stc"] = df["stc"].astype(int)
 
    cur = conn.cursor()
 
    # 1) LOPHOCPHAN - lay danh sach lop hoc phan duy nhat (theo ma_lhp)
    siso = df.groupby("ma_lhp").size().rename("so_sv_thucte")
    lhp_df = df[[
        "ma_lhp", "ten_lhp", "stc", "phong_hoc", "ngay_batdau", "ngay_ketthuc"
    ]].drop_duplicates(subset="ma_lhp")
    lhp_df = lhp_df.merge(siso, on="ma_lhp")
 
    for _, r in lhp_df.iterrows():
        cur.execute(
            """INSERT OR REPLACE INTO lophocphan
               (ma_lhp, ten_lhp, stc, phong_hoc, ngay_batdau, ngay_ketthuc, so_sv)
               VALUES (?, ?, ?, ?, ?, ?, ?)""",
            (r["ma_lhp"], r["ten_lhp"], int(r["stc"]), r["phong_hoc"],
             r["ngay_batdau"], r["ngay_ketthuc"], int(r["so_sv_thucte"])),
        )
 
    # 2) DSLOPHOCPHAN - tung dong dang ky cua sinh vien
    sv_df = df[["mssv", "ma_lhp", "ho_ten", "chuyen_nganh"]].drop_duplicates()
    cur.executemany(
        """INSERT OR IGNORE INTO dslophocphan
           (mssv, ma_lhp, ho_ten, chuyen_nganh)
           VALUES (?, ?, ?, ?)""",
        sv_df.values.tolist(),
    )
 
    conn.commit()
    print(f"Da nap {len(lhp_df)} lop hoc phan, {len(sv_df)} luot dang ky "
          f"tu {CSV_DANGKY.name}")
 
 
def print_summary(conn):
    print("\n--- TONG QUAN DU LIEU SAU KHI NAP ---")
    for table in ["lophocphan", "dslophocphan", "phongthi"]:
        n = conn.execute(f"SELECT COUNT(*) FROM {table}").fetchone()[0]
        print(f"  {table:<20} {n:>6} dong")
 
 
if __name__ == "__main__":
    conn = get_conn()
    try:
        if CSV_PHONGTHI.exists():
            load_phongthi(conn)
        else:
            print(f"[Bo qua] Khong tim thay {CSV_PHONGTHI}")
 
        if CSV_DANGKY.exists():
            load_dangky_hocphan(conn)
        else:
            print(f"[Bo qua] Khong tim thay {CSV_DANGKY}")
 
        conn.commit()
        print_summary(conn)
    finally:
        conn.close()
 
