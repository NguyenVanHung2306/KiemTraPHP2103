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

    if (!$conn) {
        die("Không thể kết nối đến cơ sở dữ liệu");
    }

    echo "<h2>Cập nhật cơ sở dữ liệu</h2>";

    // Kiểm tra xem cột SoLuongDuKien đã tồn tại chưa
    try {
        $checkColumnQuery = "SHOW COLUMNS FROM HocPhan LIKE 'SoLuongDuKien'";
        $stmt = $conn->prepare($checkColumnQuery);
        $stmt->execute();
        $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$columnExists) {
            echo "<p>Thêm cột SoLuongDuKien vào bảng HocPhan...</p>";
            $alterQuery = "ALTER TABLE HocPhan ADD COLUMN SoLuongDuKien INT DEFAULT 100";
            $conn->exec($alterQuery);
            echo "<p style='color:green'>Đã thêm cột SoLuongDuKien thành công!</p>";
        } else {
            echo "<p style='color:blue'>Cột SoLuongDuKien đã tồn tại!</p>";
        }

        // Cập nhật giá trị mặc định
        echo "<p>Cập nhật giá trị mặc định cho số lượng đăng ký...</p>";
        $updateQuery = "UPDATE HocPhan SET SoLuongDuKien = 100 WHERE SoLuongDuKien IS NULL OR SoLuongDuKien <= 0";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute();
        echo "<p style='color:green'>Đã cập nhật " . $stmt->rowCount() . " học phần!</p>";

        // Hiển thị dữ liệu hiện tại
        $selectQuery = "SELECT * FROM HocPhan";
        $stmt = $conn->prepare($selectQuery);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Dữ liệu bảng HocPhan sau khi cập nhật:</h3>";
        if (count($data) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
            echo "<tr>";
            foreach (array_keys($data[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";

            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Không có dữ liệu trong bảng HocPhan</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Lỗi truy vấn: " . $e->getMessage() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Lỗi: " . $e->getMessage() . "</p>";
}
