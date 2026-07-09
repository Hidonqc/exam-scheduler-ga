
import sqlite3
import sys
from pathlib import Path
 
DB_FILE = Path(__file__).parent / "exam_tabling.db"
 
ALL_TABLES = ["lophocphan", "dslophocphan", "phongthi", "lichthi"]
 
 
def print_table(conn, table_name, limit=10):
    cur = conn.execute(f'SELECT * FROM {table_name} LIMIT {limit}')
    cols = [d[0] for d in cur.description]
    rows = cur.fetchall()
    total = conn.execute(f"SELECT COUNT(*) FROM {table_name}").fetchone()[0]
 
    print(f"\n=== Bang: {table_name}  (tong {total} dong, hien thi {len(rows)}) ===")
    if not rows:
        print("  (trong)")
        return
 
    widths = [max(len(str(c)), 12) for c in cols]
    header = " | ".join(str(c).ljust(w) for c, w in zip(cols, widths))
    print(header)
    print("-" * len(header))
    for row in rows:
        print(" | ".join(str(v).ljust(w) for v, w in zip(row, widths)))
 
 
if __name__ == "__main__":
    if not DB_FILE.exists():
        print(f"Chua co database tai {DB_FILE}. Hay chay `python db_init.py` truoc.")
        sys.exit(1)
 
    conn = sqlite3.connect(DB_FILE)
 
    if len(sys.argv) > 1:
        table = sys.argv[1]
        print_table(conn, table, limit=10_000)
    else:
        for t in ALL_TABLES:
            print_table(conn, t, limit=10)
 
    conn.close()
 
