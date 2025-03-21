<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['maSV'])) {
    header('Location: index.php?controller=dangnhap&action=index');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Sinh Viên Mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #333;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .header-nav {
            display: flex;
            gap: 20px;
        }

        .header-nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .header-nav a:hover {
            background-color: #555;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            padding: 3px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary {
            background-color: #f44336;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>Test1</div>
        <div class="header-nav">
            <a href="index.php?controller=sinhvien&action=index">Sinh Viên</a>
            <a href="index.php?controller=hocphan&action=index">Học Phần</a>
            <a href="index.php?controller=dangnhap&action=dangXuat">Đăng Xuất</a>
        </div>
    </div>

    <div class="container">
        <h2>Thêm Sinh Viên Mới</h2>
        <form method="POST" action="index.php?controller=sinhvien&action=store" enctype="multipart/form-data">
            <div class="form-group">
                <label for="MaSV">Mã Sinh Viên</label>
                <input type="text" id="MaSV" name="MaSV" required>
            </div>

            <div class="form-group">
                <label for="HoTen">Họ Tên</label>
                <input type="text" id="HoTen" name="HoTen" required>
            </div>

            <div class="form-group">
                <label for="GioiTinh">Giới Tính</label>
                <select id="GioiTinh" name="GioiTinh" required>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>

            <div class="form-group">
                <label for="NgaySinh">Ngày Sinh</label>
                <input type="date" id="NgaySinh" name="NgaySinh" required>
            </div>

            <div class="form-group">
                <label for="MaNganh">Ngành Học</label>
                <select id="MaNganh" name="MaNganh" required>
                    <?php foreach ($nganhhoc as $nganh): ?>
                        <option value="<?php echo htmlspecialchars($nganh['MaNganh']); ?>">
                            <?php echo htmlspecialchars($nganh['TenNganh']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="Hinh">Ảnh Đại Diện</label>
                <input type="file" id="Hinh" name="Hinh" accept="image/*">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn">Thêm Sinh Viên</button>
                <a href="index.php?controller=sinhvien&action=index" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</body>

</html>