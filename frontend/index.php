<?php
// Cấu hình hiển thị lỗi tối đa để debug và đồng bộ DB thực tế
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$all_timeslots = [];
$exam_dates = [];
$rooms_by_building = [];
$sections_with_count = [];
$total_classes = 0;
$total_students = 0;
$error_msg = "";

try {
    // SỬ DỤNG FILE KẾT NỐI TRUNG TÂM
    if (!file_exists(__DIR__ . '/connect.php')) {
        throw new Exception("Không tìm thấy file connect.php đặt cùng thư mục!");
    }
    require_once __DIR__ . '/connect.php';
    
    if (!isset($conn)) {
        throw new Exception("Biến kết nối \$conn không tồn tại. Kiểm tra lại file connect.php!");
    }

    // 1. LẤY TẤT CẢ CÁC CA THI TỪ BẢNG Timeslot
    try {
        $query_ts = "SELECT timeslot_id, exam_date, start_time, end_time FROM Timeslot ORDER BY exam_date ASC, start_time ASC";
        $stmt_ts = $conn->query($query_ts);
        $all_timeslots = $stmt_ts->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_msg .= "Lỗi truy vấn bảng Timeslot: " . $e->getMessage() . "<br>";
    }

    // 2. LẤY CÁC NGÀY THI DUY NHẤT TỪ BẢNG Timeslot CHUẨN CSDL
    try {
        $query_dates = "SELECT DISTINCT exam_date FROM Timeslot WHERE exam_date IS NOT NULL AND exam_date != '' ORDER BY exam_date ASC";
        $stmt_dates = $conn->query($query_dates);
        $exam_dates = $stmt_dates->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_msg .= "Lỗi lọc ngày từ Timeslot: " . $e->getMessage() . "<br>";
    }

    // 3. LẤY PHÒNG THI GOM THEO DÃY NHÀ TỪ BẢNG ExamRoom
    try {
        $query_rooms = "SELECT room_id, building, capacity FROM ExamRoom WHERE capacity > 0 ORDER BY building ASC, room_id ASC";
        $stmt_rooms = $conn->query($query_rooms);
        $raw_rooms = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);
        foreach ($raw_rooms as $room) {
            $b = $room['building'] ? $room['building'] : "Khác";
            $rooms_by_building[$b][] = $room;
        }
    } catch (Exception $e) {
        $error_msg .= "Lỗi truy vấn bảng ExamRoom: " . $e->getMessage() . "<br>";
    }

    // 4. LẤY DANH SÁCH LỚP HỌC PHẦN KÈM SĨ SỐ ĐĂNG KÝ THỰC TẾ
    try {
        $query_sections = "SELECT cs.section_id, cs.course_id, c.course_name, COUNT(e.student_id) as si_so
                           FROM CourseSection cs
                           LEFT JOIN Course c ON cs.course_id = c.course_id
                           LEFT JOIN Enrollment e ON cs.section_id = e.section_id
                           GROUP BY cs.section_id
                           ORDER BY c.course_name ASC, cs.section_id ASC";
        $stmt_sections = $conn->query($query_sections);
        $sections_with_count = $stmt_sections->fetchAll(PDO::FETCH_ASSOC);
        
        $total_classes = count($sections_with_count);
        $total_students = $conn->query("SELECT COUNT(*) FROM Enrollment")->fetchColumn();
    } catch (Exception $e) {
        $error_msg .= "Lỗi đồng bộ danh sách Enrollment: " . $e->getMessage() . "<br>";
    }

} catch (Exception $e) {
    $error_msg .= "Lỗi hệ thống kết nối: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống xếp lịch thi tự động - HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .container-custom { 
            max-width: 950px; 
            margin: 40px auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.05); 
        }
        .banner-img { width: 100%; height: auto; display: block; margin-bottom: 35px; border-radius: 8px; }
        .system-title { color: #0b4f6c; font-size: 24px; letter-spacing: 0.5px; position: relative; padding-bottom: 12px; }
        .system-title::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 3px; background-color: #0d6efd; border-radius: 2px; }
        
        .btn-submit-custom { 
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #ffffff; border: none; padding: 18px 50px; 
            font-weight: 700; border-radius: 10px; font-size: 16px; letter-spacing: 0.5px; width: 100%;
            box-shadow: 0 4px 14px rgba(13, 110, 253, 0.3); transition: all 0.2s ease; 
        }
        .btn-submit-custom:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4); color: #ffffff; }

        #loadingOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.97);
            z-index: 9999; display: none; align-items: center; justify-content: center; flex-direction: column;
        }
        .progress-wraper { width: 80%; max-width: 600px; text-align: center; }
        .building-badge { background-color: #e1f5fe; color: #0288d1; font-weight: 600; padding: 4px 10px; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="progress-wraper">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
        <h4 class="fw-bold text-dark mb-2">ĐANG KÍCH HOẠT TIẾN TRÌNH TỐI ƯU LỊCH THI TOÀN TRƯỜNG</h4>
        <p class="text-muted small mb-4">Hệ thống đang bóc tách toàn bộ dữ liệu đăng ký lớp học phần để tự động phân phối ca thi và phòng thi tối ưu...</p>
        <div class="progress" style="height: 10px; border-radius: 5px;">
            <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
</div>

<div class="container-custom">
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger mb-4 shadow-sm">
            <h5 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Lỗi dữ liệu hệ thống:</h5>
            <div class="p-2 bg-white rounded border text-dark font-monospace" style="font-size: 13px;">
                <?php echo $error_msg; ?>
            </div>
        </div>
    <?php endif; ?>

    <img src="banner.png" alt="Banner HUB" class="banner-img">
    <h3 class="text-center fw-bold system-title mb-5">HỆ THỐNG XẾP LỊCH THI TỰ ĐỘNG</h3>

    <form action="process.php" method="POST" onsubmit="showLoading(event)">
        
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-clock-history me-2"></i> 1. Thiết lập ngày thi & Ca thi
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary small">Cấu hình ca thi mục tiêu:</label>
                        <select name="target_timeslot_id" class="form-select" required>
                            <option value="all">-- Áp dụng tất cả các ca thi (<?php echo count($all_timeslots); ?> ca) --</option>
                            <?php foreach ($all_timeslots as $ts): ?>
                                <option value="<?php echo htmlspecialchars($ts['timeslot_id']); ?>">
                                    Ca: <?php echo htmlspecialchars($ts['timeslot_id']); ?> | Ngày: <?php echo htmlspecialchars($ts['exam_date']); ?> (<?php echo htmlspecialchars($ts['start_time']); ?> - <?php echo htmlspecialchars($ts['end_time']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary small">Chọn ngày tổ chức thi:</label>
                        <select name="target_exam_date" class="form-select" required>
                            <option value="all">-- Hiển thị tất cả các ngày thi --</option>
                            <?php foreach ($exam_dates as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['exam_date']); ?>">
                                    Ngày: <?php echo htmlspecialchars(date('d/m/Y', strtotime($d['exam_date']))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-header bg-info text-white fw-bold" style="background-color: #0288d1 !important;">
                <i class="bi bi-people-fill me-2"></i> 2. Danh sách Sinh viên & Lớp học phần
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="table-responsive bg-white rounded border p-2" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                                <thead class="table-primary sticky-top">
                                    <tr>
                                        <th>Mã lớp học phần </th>
                                        <th>Tên môn học </th>
                                        <th class="text-center">Số lượng SV đăng ký</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections_with_count as $sec): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($sec['section_id']); ?></td>
                                            <td><?php echo htmlspecialchars($sec['course_name'] ? $sec['course_name'] : "Môn học chưa gán tên"); ?></td>
                                            <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($sec['si_so']); ?> SV</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Hệ thống tự động quét và phân lịch đồng bộ cho toàn bộ <strong><?php echo number_format($total_students); ?></strong> lượt sinh viên đăng ký học phần ở trên.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center" style="background-color: #455a64 !important;">
                <span><i class="bi bi-door-open-fill me-2"></i> 3. Danh sách Phòng thi sử dụng cho đợt thi</span>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleAllRooms()">Chọn / Bỏ chọn tất cả</button>
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                <?php foreach ($rooms_by_building as $building => $rooms): ?>
                    <div class="mb-3 border-bottom pb-2">
                        <div class="mb-2">
                            <span class="building-badge"><i class="bi bi-building me-1"></i> Tòa nhà: <?php echo htmlspecialchars($building); ?></span>
                        </div>
                        <div class="row g-2 px-2">
                            <?php foreach ($rooms as $r): ?>
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="form-check form-check-inline p-2 border rounded bg-white w-100 shadow-sm" style="font-size: 13px;">
                                        <input class="form-check-input ms-1 room-checkbox" type="checkbox" name="selected_rooms[]" 
                                               value="<?php echo htmlspecialchars($r['room_id']); ?>" id="room_<?php echo htmlspecialchars($r['room_id']); ?>" checked>
                                        <label class="form-check-label fw-bold text-dark ms-1" for="room_<?php echo htmlspecialchars($r['room_id']); ?>">
                                            <?php echo htmlspecialchars($r['room_id']); ?> <span class="text-secondary fw-normal font-monospace" style="font-size: 11px;">(Sl: <?php echo htmlspecialchars($r['capacity']); ?>)</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="alert alert-primary border shadow-sm mb-4 py-3 d-flex align-items-center justify-content-around text-center" style="background-color: #e3f2fd; border-color: #bbdefb;">
            <div>
                <span class="fw-bold text-primary fs-4 d-block"><?php echo number_format($total_classes); ?></span>
                <small class="text-secondary fw-semibold">Lớp học phần xử lý</small>
            </div>
            <div class="border-start h-100 mx-3" style="min-height: 40px; border-color: #90caf9 !important;"></div>
            <div>
                <span class="fw-bold text-primary fs-4 d-block"><?php echo number_format($total_students); ?></span>
                <small class="text-secondary fw-semibold">Tổng sinh viên thi toàn trường</small>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-submit-custom text-uppercase">
                <i></i> XẾP LỊCH THI TỰ ĐỘNG THEO LỚP HỌC PHẦN
            </button>
        </div>
    </form>
</div>

<script>
let isAllSelected = true;
function toggleAllRooms() {
    const checkboxes = document.querySelectorAll('.room-checkbox');
    isAllSelected = !isAllSelected;
    checkboxes.forEach(cb => cb.checked = !isAllSelected);
}

function showLoading(event) {
    const checkedRooms = document.querySelectorAll('.room-checkbox:checked');
    if (checkedRooms.length === 0) {
        event.preventDefault();
        alert('Lỗi: Bạn phải chọn ít nhất 1 phòng thi để đưa vào thuật toán!');
        return false;
    }

    event.preventDefault();
    const form = event.target;
    document.getElementById('loadingOverlay').style.display = 'flex';
    
    let width = 0;
    const loadingBar = document.getElementById('loadingBar');
    const interval = setInterval(() => {
        if (width >= 100) {
            clearInterval(interval);
            form.submit(); 
        } else {
            width += 5; 
            loadingBar.style.width = width + '%';
        }
    }, 25);
}
</script>
</body>
</html>