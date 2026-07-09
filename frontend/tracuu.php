<!DOCTYPE html>
<html lang="vi">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu lịch thi cá nhân - HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .container-custom { max-width: 900px; margin: 30px auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .banner-img { width: 100%; border-radius: 8px; margin-bottom: 25px; }
        .info-badges .badge { background-color: #e9ecef; color: #495057; font-weight: 500; font-size: 12px; padding: 8px 15px; margin: 0 5px; border-radius: 20px; border: 1px solid #dee2e6; }
        .search-box { max-width: 500px; margin: 30px auto; position: relative; }
        .search-box input { padding: 12px 20px; border-radius: 30px; border: 2px solid #651fff; width: 100%; outline: none; box-shadow: 0 4px 10px rgba(101,31,255,0.1); }
        .search-box button { position: absolute; right: 5px; top: 5px; background: #651fff; color: white; border: none; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; }
        .result-card { border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; margin-top: 30px; display: none; }
        .table-custom th { font-size: 13px; text-transform: uppercase; color: #6c757d; border-bottom: 2px solid #f0f0f0; }
        .table-custom td { vertical-align: middle; font-size: 14px; border-bottom: 1px dashed #f0f0f0; padding: 12px 8px; }
        .btn-outline-purple { border: 1px solid #651fff; color: #651fff; font-weight: 500; }
        .btn-outline-purple:hover { background: #651fff; color: white; }
    </style>
</head>
<body>

<div class="container-custom">
<img src="assets/images/banner.png" alt="Banner HUB" class="banner-img">    
    <div class="text-center info-badges mb-4">
        <span class="badge">Năm học 2025-2026</span>
        <span class="badge">Học kì 2</span>
        <span class="badge">Ngành: Hệ thống thông tin quản lý </span>
        <span class="badge">Đợt thi: Cuối kì</span>
    </div>

    <div class="search-box text-center">
        <p class="text-muted small mb-2"><i class="bi bi-search"></i> Tra cứu lịch thi cá nhân:</p>
        <div style="position: relative;">
            <input type="text" id="mssvInput" placeholder="Nhập mã số sinh viên (VD: 30239230204)" onkeypress="if(event.key === 'Enter') searchStudent()">
            <button onclick="searchStudent()" type="button"><i class="bi bi-arrow-right"></i></button>       
         </div>
    </div>

    <a href="index.php" class="text-decoration-none text-primary small"><i class="bi bi-arrow-left"></i> Quay lại xem lịch toàn trường</a>

    <!-- KHU VỰC KẾT QUẢ -->
    <div class="result-card" id="resultCard">
        <h5 class="fw-bold mb-4" style="color: #651fff; text-transform: uppercase;">KẾT QUẢ TRA CỨU LỊCH THI: <span id="lblMssv" class="text-dark"></span></h5>
        
        <div class="table-responsive">
            <table class="table table-custom table-borderless" id="personalSchedule">
                <thead>
                    <tr>
                        <th>Lớp học phần</th>
                        <th>Tên học phần</th>
                        <th class="text-center">Ngày thi</th>
                        <th class="text-center">Ca thi</th>
                        <th class="text-center">Phòng thi</th>
                        <th class="text-center">Địa điểm</th>
                    </tr>
                </thead>
                <tbody id="searchBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function searchStudent() {
        const mssv = document.getElementById('mssvInput').value.trim();
        if(!mssv) { alert("Vui lòng nhập Mã số sinh viên!"); return; }

        const tbody = document.getElementById('searchBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-primary py-4"><div class="spinner-border spinner-border-sm"></div> Đang tìm kiếm dữ liệu...</td></tr>';
        document.getElementById('resultCard').style.display = 'block';
        document.getElementById('lblMssv').innerText = mssv;

        try {
            const res = await fetch('ajax.php?action=search_student&mssv=' + encodeURIComponent(mssv));
            const result = await res.json();

            if (result.status === 'success') {
                if(result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Không tìm thấy lịch thi nào cho MSSV này. Bạn có chắc đã đăng ký lớp học phần không?</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                result.data.forEach(row => {
                    let roomBadge = 'bg-secondary';
                    if(row.ma_phong.startsWith('C')) roomBadge = 'bg-danger';
                    
                    tbody.innerHTML += `
                        <tr>
                            <td class="fw-bold" style="color: #651fff;">${row.ma_lhp}</td>
                            <td class="text-muted fw-medium">${row.ten_lhp}</td>
                            <td class="text-center">${row.ngay_thi}</td>
                            <td class="text-center fw-bold">Ca ${row.ca_thi}</td>
                            <td class="text-center"><span class="badge ${roomBadge} rounded-pill px-3 py-2">${row.ma_phong}</span></td>
                            <td class="text-center text-muted small" style="line-height: 1.2;">
                                Cơ sở Thủ Đức<br>
                                <small>56 Hoàng Diệu 2, TP. Thủ Đức</small>
</td>                        
</tr>
                    `;
                });
            }
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Lỗi kết nối CSDL!</td></tr>';
        }
    }

    function exportPersonalExcel() {
        let table = document.getElementById("personalSchedule");
        let wb = XLSX.utils.table_to_book(table, {sheet: "Lịch Cá Nhân"});
        let mssv = document.getElementById('lblMssv').innerText;
        XLSX.writeFile(wb, "LichThi_" + mssv + ".xlsx");
    }
</script>
</div>
</body>
</html>
