<?php
// Kết nối đến cơ sở dữ liệu
require_once 'app/config/database.php';

// Hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Tạo kết nối
    $db = new Database();
    $conn = $db->connect();

    // Kiểm tra xem cột SoLuongDuKien đã tồn tại chưa
    $checkColumnQuery = "SHOW COLUMNS FROM HocPhan LIKE 'SoLuongDuKien'";
    $stmt = $conn->prepare($checkColumnQuery);
    $stmt->execute();
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$columnExists) {
        // Thêm cột SoLuongDuKien vào bảng HocPhan
        $alterQuery = "ALTER TABLE HocPhan ADD COLUMN SoLuongDuKien INT DEFAULT 100";
        $conn->exec($alterQuery);
        echo "<p style='color:green'>Đã thêm cột SoLuongDuKien vào bảng HocPhan thành công!</p>";

        // Cập nhật giá trị mặc định cho các học phần hiện có
        $updateQuery = "UPDATE HocPhan SET SoLuongDuKien = 100 WHERE SoLuongDuKien IS NULL";
        $conn->exec($updateQuery);
        echo "<p style='color:green'>Đã cập nhật giá trị mặc định 100 cho tất cả học phần!</p>";
    } else {
        echo "<p style='color:blue'>Cột SoLuongDuKien đã tồn tại trong bảng HocPhan!</p>";
    }

    // Hiển thị dữ liệu hiện tại của bảng HocPhan
    $selectQuery = "SELECT MaHP, TenHP, SoTinChi, SoLuongDuKien FROM HocPhan";
    $stmt = $conn->prepare($selectQuery);
    $stmt->execute();
    $hocphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Dữ liệu bảng HocPhan sau khi cập nhật:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Mã HP</th><th>Tên HP</th><th>Số Tín Chỉ</th><th>Số Lượng Dự Kiến</th></tr>";

    foreach ($hocphans as $hp) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($hp['MaHP']) . "</td>";
        echo "<td>" . htmlspecialchars($hp['TenHP']) . "</td>";
        echo "<td>" . htmlspecialchars($hp['SoTinChi']) . "</td>";
        echo "<td>" . htmlspecialchars($hp['SoLuongDuKien']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Lỗi: " . $e->getMessage() . "</p>";
}
