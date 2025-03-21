<?php
// Forcefully destroy any existing session
if (session_status() != PHP_SESSION_NONE) {
    // Unset all session variables
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

// Start a new session
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Sinh Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f0f2f5;
            padding-top: 50px;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            margin-top: 50px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #333;
            font-weight: bold;
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
        }

        .btn-login {
            background-color: #007bff;
            color: white;
            border: none;
            height: 50px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            text-decoration: none;
        }

        .back-link:hover {
            color: #007bff;
        }

        .navbar {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">QUẢN LÝ SINH VIÊN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <h2>ĐĂNG NHẬP HỆ THỐNG</h2>
                        <p class="text-muted">Vui lòng nhập Mã Sinh Viên để đăng nhập</p>
                    </div>
                    <form action="index.php?controller=sinhvien&action=processLogin" method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="MaSV" name="MaSV"
                                placeholder="Nhập Mã Sinh Viên" required autofocus>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-login w-100">Đăng Nhập</button>
                        </div>
                    </form>
                    <a href="index.php" class="back-link">Quay lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
exit();
