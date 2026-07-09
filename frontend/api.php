<?php
ob_start(); 

$input = json_decode(file_get_contents('php://input'), true);
$startDate = $input['startDate'] ?? '';
$endDate = $input['endDate'] ?? '';
$python_log = "";

if ($startDate && $endDate) {
    $start_vn = date('d/m/Y', strtotime($startDate));
    $end_vn = date('d/m/Y', strtotime($endDate));

    $base_dir = dirname(__DIR__); 
    $cmd = "cd " . escapeshellarg($base_dir) . " && python3 main.py " . escapeshellarg($start_vn) . " " . escapeshellarg($end_vn) . " 2>&1";
    
    $python_log = shell_exec($cmd); 
}

require_once 'connect.php';

ob_clean(); 
header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT lt.ma_lhp, lhp.ten_lhp, lt.so_sv_phong, lt.ngay_thi, lt.ca_thi, lt.ma_phong 
            FROM lichthi lt
            JOIN lophocphan lhp ON lt.ma_lhp = lhp.ma_lhp
            ORDER BY lt.ngay_thi ASC, lt.ca_thi ASC";
            
    $stmt = $conn->query($sql);
    $results = $stmt->fetchAll();
    
    echo json_encode([
        "status" => "success", 
        "data" => $results,
        "python_log" => $python_log
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage(),
        "python_log" => $python_log
    ]);
}
?>
