<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống xếp lịch thi tự động - HUB</title>
    
    <!-- Thư viện Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Thư viện SheetJS xuất Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
        
        .banner-img { 
            width: 100%; 
            height: auto; 
            display: block; 
            margin-bottom: 35px; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0;
        }
        
        .system-title { 
            color: #0b4f6c; 
            font-size: 24px; 
            font-weight: bold;
            letter-spacing: 0.5px; 
            position: relative; 
            padding-bottom: 15px; 
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .system-title::after { 
            content: ''; 
            position: absolute; 
            bottom: 0; 
            left: 50%; 
            transform: translateX(-50%); 
            width: 60px; 
            height: 3px; 
            background-color: #651fff; 
            border-radius: 2px; 
        }

        .card-header-purple { background-color: #651fff !important; color: white; font-weight: 600; }
        .card-header-blue { background-color: #1976d2 !important; color: white; font-weight: 600; }
        
        .btn-submit-custom { 
            background: linear-gradient(135deg, #651fff 0%, #3d5afe 100%); 
            color: #ffffff; 
            border: none; 
            padding: 16px 50px; 
            font-weight: 700; 
            border-radius: 10px; 
            font-size: 16px; 
            letter-spacing: 0.5px; 
            width: 100%;
            box-shadow: 0 4px 14px rgba(101, 31, 255, 0.3); 
            transition: all 0.2s ease; 
            text-transform: uppercase;
        }
        
        .btn-submit-custom:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(101, 31, 255, 0.4); 
            color: #ffffff; 
        }

        .table-custom th { white-space: nowrap; }
        .table-custom td { vertical-align: middle; }

        #loadingOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.97);
            z-index: 9999; display: none; align-items: center; justify-content: center; flex-direction: column;
        }
        .progress-wraper { width: 80%; max-width: 600px; text-align: center; }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="progress-wraper">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem; color: #651fff !important;" role="status"></div>
        <h4 class="fw-bold text-dark mb-2">ĐANG KÍCH HOẠT THUẬT TOÁN TIẾN HÓA AI</h4>
        <p class="text-muted small mb-4">Hệ thống đang quét dữ liệu và lai ghép nhiễm sắc thể để tìm ra lịch thi tối ưu nhất (không xung đột)...</p>
    </div>
</div>

<div class="container-custom">    

    <img src="banner.png" alt="Banner HUB" class="banner-img" onerror="this.src='https://via.placeholder.com/950x120/dbeafe/1e3a8a?text=TRƯỜNG+ĐẠI+HỌC+NGÂN+HÀNG+TP.+HỒ+CHÍ+MINH'">
    
    <div class="system-title">HỆ THỐNG XẾP LỊCH THI TỰ ĐỘNG</div>

    <!-- KHỐI 1: THIẾT LẬP -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-header card-header-purple">
            <i class="bi bi-clock-history me-2"></i> 1. Thiết lập dải ngày thi
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary small">Chọn ngày Bắt đầu thi:</label>
                    <input type="date" class="form-control" id="startDate" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary small">Chọn ngày Kết thúc thi:</label>
                    <input type="date" class="form-control" id="endDate" required>
                </div>
            </div>
        </div>
    </div>

    <!-- NÚT CHẠY THUẬT TOÁN -->
    <button type="button" class="btn btn-submit-custom mb-5" onclick="runAlgorithm()">
        <i class="bi bi-robot me-2"></i> XẾP LỊCH THI TỰ ĐỘNG 
    </button>

    <!-- KHỐI 2: KẾT QUẢ BẢNG -->
    <div class="card shadow-sm border-0 mb-4" id="result-section" style="display: none;">
        <div class="card-header card-header-blue d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people-fill me-2"></i> 2. Danh sách Lịch thi đã xếp</span>
            <!-- Nút Xuất Excel thu nhỏ góc phải -->
            <button class="btn btn-sm btn-light text-primary fw-bold" onclick="exportToExcel()">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Xuất Excel
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                <table class="table table-sm table-hover table-striped mb-0 table-custom" id="scheduleTable" style="font-size: 13px;">
                    <thead class="table-light sticky-top shadow-sm">
                        <tr>
                            <th class="ps-3">Mã LHP</th>
                            <th>Tên môn học</th>
                            <th class="text-center">Sĩ số</th>
                            <th>Ngày thi</th>
                            <th>Ca</th>
                            <th>Phòng thi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light text-muted small text-center">
            <i class="bi bi-info-circle"></i> Hệ thống đã hoàn tất phân lịch không xung đột cho toàn bộ lớp học phần.
        </div>
    </div>

</div>

<!-- SCRIPT XỬ LÝ -->
<script>
    async function runAlgorithm() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;

        if(!start || !end) {
            alert("Vui lòng chọn ngày bắt đầu và kết thúc!");
            return;
        }

        document.getElementById('result-section').style.display = 'none';
        document.getElementById('loadingOverlay').style.display = 'flex';

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ startDate: start, endDate: end })
            });
            
            const rawText = await response.text(); 
            let result;
            try {
                result = JSON.parse(rawText);
            } catch (e) {
                console.error("LỖI DỮ LIỆU THÔ:", rawText);
                alert("Máy chủ bị lỗi. Vui lòng kiểm tra Console (F12).");
                document.getElementById('loadingOverlay').style.display = 'none';
                return;
            }

            if(result.status === 'success') {
                const tbody = document.getElementById('tableBody');
                tbody.innerHTML = ''; 
                
                if(result.data.length === 0) {
                    alert("Thuật toán chạy xong nhưng không sinh ra lịch! (Bị nghẽn không gian)");
                } else {
                    result.data.forEach(row => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="font-monospace text-secondary ps-3">${row.ma_lhp}</td>
                                <td class="fw-bold" style="color: #4a148c;">${row.ten_lhp}</td>
                                <td class="text-center fw-bold text-primary">${row.so_sv_phong}</td>
                                <td>${row.ngay_thi}</td>
                                <td>Ca ${row.ca_thi}</td>
                                <td class="fw-bold">${row.ma_phong}</td>
                            </tr>
                        `;
                    });
                    
                    document.getElementById('loadingOverlay').style.display = 'none';
                    document.getElementById('result-section').style.display = 'block';
                }
            } else {
                alert("Lỗi SQL: " + result.message);
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        } catch (error) {
            alert("Lỗi kết nối mạng: " + error);
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    }

    // Hàm xuất bảng ra file Excel
    function exportToExcel() {
        const tbody = document.getElementById('tableBody');
        if (!tbody || tbody.innerHTML.trim() === '') {
            alert("Bảng đang trống!");
            return;
        }

        let table = document.getElementById("scheduleTable");
        let wb = XLSX.utils.table_to_book(table, {sheet: "Lịch Thi"});
        
        let date = new Date();
        let fileName = "LichThi_HUB_" + date.getFullYear() + (date.getMonth()+1) + date.getDate() + ".xlsx";
        
        XLSX.writeFile(wb, fileName);
    }
</script>
</body>
</html>
