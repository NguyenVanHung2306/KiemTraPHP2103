<?php
// Kết nối đến cơ sở dữ liệu
require_once 'app/config/database.php';

// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tạo kết nối
$db = new Database();
$conn = $db->connect();

// Hàm kiểm tra cấu trúc bảng
function checkTableStructure($conn, $tableName)
{
    echo "<h2>Cấu trúc bảng $tableName:</h2>";
    $query = "DESCRIBE $tableName";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Hàm kiểm tra dữ liệu trong bảng
function checkTableData($conn, $tableName, $limit = 10)
{
    echo "<h2>Dữ liệu trong bảng $tableName (tối đa $limit dòng):</h2>";
    $query = "SELECT * FROM $tableName LIMIT $limit";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        echo "<p>Không có dữ liệu</p>";
        return;
    }

    echo "<table border='1' cellpadding='5'>";
    // Header
    echo "<tr>";
    foreach (array_keys($data[0]) as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }
    echo "</tr>";

    // Data
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Kiểm tra join giữa các bảng
function checkRegistrationData($conn, $maSV = null)
{
    echo "<h2>Kiểm tra dữ liệu đăng ký học phần" . ($maSV ? " của sinh viên $maSV" : "") . ":</h2>";

    $query = "SELECT dk.MaDK, dk.MaSV, dk.NgayDK, ctdk.ID as ChiTietID, ctdk.MaHP, hp.TenHP, hp.SoTinChi
              FROM DangKy dk
              LEFT JOIN ChiTietDangKy ctdk ON dk.MaDK = ctdk.MaDK
              LEFT JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP";

    if ($maSV) {
        $query .= " WHERE dk.MaSV = :maSV";
    }

    $query .= " ORDER BY dk.MaDK, ctdk.ID";

    $stmt = $conn->prepare($query);
    if ($maSV) {
        $stmt->bindParam(':maSV', $maSV);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        echo "<p>Không có dữ liệu đăng ký</p>";
        return;
    }

    echo "<table border='1' cellpadding='5'>";
    // Header
    echo "<tr>";
    foreach (array_keys($data[0]) as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }
    echo "</tr>";

    // Data
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Debug Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #2c3e50;
        }

        h2 {
            color: #3498db;
            margin-top: 30px;
        }

        table {
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f2f2f2;
        }

        td,
        th {
            padding: 8px;
            text-align: left;
        }

        .actions {
            margin: 20px 0;
        }

        .actions a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .clear-btn {
            background-color: #e74c3c !important;
        }
    </style>
</head>

<body>
    <h1>Debug Database Tool</h1>

    <div class="actions">
        <a href="?action=check_structure">Kiểm tra cấu trúc bảng</a>
        <a href="?action=check_data">Kiểm tra dữ liệu</a>
        <a href="?action=check_registration" class="clear-btn">Kiểm tra đăng ký học phần</a>
        <?php if (isset($_GET['maSV'])): ?>
            <a href="?action=check_registration&maSV=<?php echo htmlspecialchars($_GET['maSV']); ?>">
                Kiểm tra đăng ký của SV: <?php echo htmlspecialchars($_GET['maSV']); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php
    // Xử lý các action
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'check_structure':
            checkTableStructure($conn, 'SinhVien');
            checkTableStructure($conn, 'HocPhan');
            checkTableStructure($conn, 'DangKy');
            checkTableStructure($conn, 'ChiTietDangKy');
            break;

        case 'check_data':
            checkTableData($conn, 'SinhVien');
            checkTableData($conn, 'HocPhan');
            checkTableData($conn, 'DangKy');
            checkTableData($conn, 'ChiTietDangKy');
            break;

        case 'check_registration':
            $maSV = $_GET['maSV'] ?? null;
            checkRegistrationData($conn, $maSV);
            break;

        default:
            echo "<p>Chọn một hành động từ các tùy chọn trên.</p>";
    }
    ?>

    <script>
        // Thêm mã JavaScript nếu cần
    </script>
</body>

</html>