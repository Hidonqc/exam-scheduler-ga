<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {
    // TÍNH NĂNG 1: Lấy danh sách sinh viên của một lớp học phần
    if ($action === 'get_students') {
        $ma_lhp = $_GET['ma_lhp'] ?? '';
        
        // Truy vấn lấy trực tiếp MSSV, Họ tên và Chuyên ngành từ bảng dslophocphan
        $sql = "SELECT mssv, ho_ten, chuyen_nganh FROM dslophocphan WHERE ma_lhp = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ma_lhp]);
        $students = $stmt->fetchAll();
        
        $result_data = [];
        foreach ($students as $sv) {
            $result_data[] = [
                'mssv' => $sv['mssv'],
                'ho_ten' => $sv['ho_ten'],
                'nganh' => $sv['chuyen_nganh'] ? $sv['chuyen_nganh'] : 'Chưa cập nhật'
            ];
        }
        
        echo json_encode(["status" => "success", "data" => $result_data]);
        exit;
    }

    // TÍNH NĂNG 2: Tra cứu lịch thi cá nhân bằng MSSV
    if ($action === 'search_student') {
        $mssv = $_GET['mssv'] ?? '';
        
        $sql = "SELECT lhp.ma_lhp, lhp.ten_lhp, lt.ngay_thi, lt.ca_thi, lt.ma_phong 
                FROM dslophocphan d
                JOIN lichthi lt ON d.ma_lhp = lt.ma_lhp
                JOIN lophocphan lhp ON d.ma_lhp = lhp.ma_lhp
                WHERE d.mssv = ?
                ORDER BY lt.ngay_thi ASC, lt.ca_thi ASC";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute([$mssv]);
        $schedule = $stmt->fetchAll();
        
        echo json_encode(["status" => "success", "data" => $schedule]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
