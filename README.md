# 🚀 Hệ Thống Xếp Lịch Thi Tự Động - Genetic Algorithm

Dự án ứng dụng **Thuật toán Di truyền (Genetic Algorithm - GA)** để giải quyết bài toán tối ưu hóa lịch thi học kỳ. Hệ thống tự động phân bổ lớp học phần, sĩ số vào các phòng thi và ca thi phù hợp, đảm bảo không xảy ra xung đột (trùng lịch, tràn sức chứa phòng) và xuất kết quả ra định dạng Excel trực quan.

## 🎯 Chức Năng Chính
*   **Tối ưu hóa bằng AI:** Sử dụng Thuật toán Di truyền (Crossover, Mutation) để rải đều lịch thi, tránh điểm cực trị địa phương.
*   **Giao diện Web hiện đại:** Quản lý và thao tác trực tiếp trên trình duyệt, không cần chạy lệnh Terminal.
*   **Tự động nhận diện không gian:** Tự động tính toán số ca thi dựa trên khoảng thời gian (Ngày bắt đầu - Ngày kết thúc) do người dùng thiết lập.
*   **Xuất báo cáo:** Hỗ trợ xuất lịch thi hoàn chỉnh ra file `.xlsx` trực tiếp từ giao diện web (Sử dụng thư viện SheetJS).

## 🛠 Công Nghệ Sử Dụng
*   **Backend / AI Engine:** Python 3 (Thuật toán tiến hóa)
*   **Frontend:** PHP, HTML5, Bootstrap 5, JavaScript (Fetch API, DOM Manipulation)
*   **Database:** SQLite3
*   **Giao tiếp hệ thống:** Tích hợp gọi Python script ngầm từ PHP (Subprocess / Shell Execute)

## 📂 Cấu Trúc Thư Mục
```text
exam-scheduler-ga/
├── .backend/ga_engine/   # Chứa các module cốt lõi của Thuật toán di truyền (Crossover, Mutation, Fitness...)
├── Database/             # Chứa file cơ sở dữ liệu SQLite (VD: exam_tabling.db)
├── frontend/             # Giao diện Web (index.php, connect.php, api.php, assets...)
├── generation/           # Chứa các log tiến trình hoặc file tạm sinh ra trong lúc chạy
└── README.md             # Tài liệu dự án





#⚙️ Hướng Dẫn Cài Đặt & Khởi Chạy
Yêu cầu hệ thống
Đã cài đặt Python 3.8+

Đã cài đặt môi trường chạy PHP (như XAMPP, MAMP, hoặc dùng PHP Built-in Server).

Các bước chạy dự án
Clone repository về máy:

Bash
git clone [https://github.com/Hidonqc/exam-scheduler-ga.git](https://github.com/Hidonqc/exam-scheduler-ga.git)
cd exam-scheduler-ga
Khởi động Local Server (PHP):
Di chuyển vào thư mục frontend và chạy server mô phỏng:

Bash
cd frontend
php -S localhost:8000
Sử dụng hệ thống:

Mở trình duyệt và truy cập: http://localhost:8000

Nhập Ngày bắt đầu và Ngày kết thúc kỳ thi.

Nhấn Tạo Lịch Thi và đợi hệ thống AI phân bổ (khoảng 2-5 giây tùy cấu hình máy).

Bấm Xuất Excel để lưu file lịch thi về máy.

👥 Nhóm Thực Hiện
Tập thể: Nhóm 8
Thành viên: 
Đơn vị: Trường Đại học Ngân hàng TP. Hồ Chí Minh (HUB)
