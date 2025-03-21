<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php
    // Check if user is logged in, but skip this for login page
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $currentPage = $_GET['controller'] ?? '';
    $currentAction = $_GET['action'] ?? '';
    $isLoggedIn = isset($_SESSION['maSV']) && !empty($_SESSION['maSV']);
    $userName = $_SESSION['hoTen'] ?? '';
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">QUẢN LÝ SINH VIÊN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=sinhvien&action=index">Sinh viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Ngành học</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=hocphan&action=index">Học phần</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=hocphan&action=xemGioHang">
                            <i class="fas fa-shopping-cart"></i> Giỏ đăng ký
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <span class="nav-link">Xin chào, <?php echo htmlspecialchars($userName); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=sinhvien&action=logout">Đăng xuất</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=sinhvien&action=login">Đăng Nhập</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php
        // Hiển thị thông báo lỗi và thành công từ session
        if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($_SESSION['error']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($_SESSION['success']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['success']);
        }

        // Debug logging to file
        file_put_contents('/tmp/header_debug.log', "\n" . date('Y-m-d H:i:s') . " - Header processing\n", FILE_APPEND);
        file_put_contents('/tmp/header_debug.log', "GET Parameters: " . print_r($_GET, true) . "\n", FILE_APPEND);
        file_put_contents('/tmp/header_debug.log', "Current Page: $currentPage\n", FILE_APPEND);
        file_put_contents('/tmp/header_debug.log', "Current Action: $currentAction\n", FILE_APPEND);
        file_put_contents('/tmp/header_debug.log', "Session MaSV: " . ($_SESSION['maSV'] ?? 'Not set') . "\n", FILE_APPEND);
        file_put_contents('/tmp/header_debug.log', "Session Status: " . session_status() . "\n", FILE_APPEND);

        // Check if we're on the login page
        $isLoginPage = ($currentPage === 'sinhvien' && $currentAction === 'login');

        // Redirect to login if not logged in and not on login page
        if (!$isLoginPage && (!isset($_SESSION['maSV']) || empty($_SESSION['maSV']))) {
            file_put_contents('/tmp/header_debug.log', "Redirecting to login page\n", FILE_APPEND);
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }
        ?>
    </div>
</body>

</html>