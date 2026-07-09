<?php
// Cấu hình hiển thị lỗi để dễ kiểm tra nếu kết nối thất bại
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // 1. KIỂM TRA ĐƯỜNG DẪN FILE DATABASE
    // Hãy chắc chắn rằng tên file trên host/thư mục của bạn khớp chính xác từng chữ hoa/thường
    $db_path = __DIR__ . '/exam_scheduling.db'; 
    
    if (!file_exists($db_path)) {
        throw new Exception("Không tìm thấy file database tại đường dẫn: " . $db_path . ". Hãy kiểm tra lại tên file (chữ hoa/thường) hoặc vị trí file đặt đã đúng cạnh file connect.php chưa!");
    }
    
    // Khởi tạo kết nối PDO SQLite
    $conn = new PDO("sqlite:" . $db_path);
    
    // Cấu hình chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cấu hình trả về dữ liệu dạng mảng Associate (Key là tên cột)
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 2. BẬT RÀNG BUỘC KHÓA NGOẠI (Cực kỳ quan trọng đối với bài toán xếp lịch thi GA)
    $conn->exec('PRAGMA foreign_keys = ON;');
    
} catch (Exception $e) {
    // Xuất lỗi trực quan để bạn biết chính xác hệ thống đang bị lệch ở đâu
    die("<div style='color:red; padding:15px; border:1px solid red; background:#fff5f5; font-family:sans-serif;'>
            <strong>Kết nối CSDL thất bại:</strong> " . htmlspecialchars($e->getMessage()) . "
         </div>");
}
?>