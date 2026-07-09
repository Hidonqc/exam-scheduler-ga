
import sqlite3
from pathlib import Path
 
BASE_DIR = Path(__file__).parent
SCHEMA_FILE = BASE_DIR / "schema.sql"
DB_FILE = BASE_DIR / "exam_tabling.db"
 
 
def init_database():
    # Xóa DB cũ nếu tồn tại để đảm bảo schema luôn mới nhất
    if DB_FILE.exists():
        DB_FILE.unlink()
        print(f"Đã xóa database cũ: {DB_FILE}")
 
    conn = sqlite3.connect(DB_FILE)
    conn.execute("PRAGMA foreign_keys = ON;")
 
    with open(SCHEMA_FILE, "r", encoding="utf-8") as f:
        schema_sql = f.read()
 
    conn.executescript(schema_sql)
    conn.commit()
 
    # In danh sách bảng đã tạo để xác nhận
    cursor = conn.execute(
        "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;"
    )
    tables = [row[0] for row in cursor.fetchall()]
 
    print(f"\nĐã khởi tạo database: {DB_FILE}")
    print(f"Số bảng đã tạo: {len(tables)}")
    for t in tables:
        print(f"  - {t}")
 
    conn.close()
 
 
if __name__ == "__main__":
    init_database()
 
