<?php
require_once 'app/models/SinhVienModel.php';

class SinhVienController
{
    private $sinhVienModel;

    public function __construct()
    {
        $this->sinhVienModel = new SinhVienModel();
    }

    public function index()
    {
        $sinhviens = $this->sinhVienModel->getAllSinhVien();
        require_once 'app/views/sinhvien/list.php';
    }

    public function add()
    {
        $nganhhoc = $this->sinhVienModel->getAllNganhHoc();
        require_once 'app/views/sinhvien/add.php';
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $sinhvien = $this->sinhVienModel->getSinhVienById($id);
            $nganhhoc = $this->sinhVienModel->getAllNganhHoc();
            if ($sinhvien) {
                require_once 'app/views/sinhvien/edit.php';
            } else {
                echo "<script>
                    alert('Không tìm thấy sinh viên!');
                    window.location.href = 'index.php?action=index';
                </script>";
            }
        }
    }

    public function delete()
    {
        require_once 'app/views/shares/header.php';

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            try {
                if ($this->sinhVienModel->deleteSinhVien($id)) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Xóa sinh viên thành công!',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = 'index.php?action=index';
                        });
                    </script>";
                } else {
                    throw new Exception('Có lỗi xảy ra khi xóa sinh viên!');
                }
            } catch (Exception $e) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '" . addslashes($e->getMessage()) . "',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = 'index.php?action=index';
                    });
                </script>";
            }
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không tìm thấy sinh viên cần xóa!',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'index.php?action=index';
                });
            </script>";
        }

        require_once 'app/views/shares/footer.php';
    }

    private function handleImageUpload($file, $oldImage = '')
    {
        try {
            // Kiểm tra và tạo thư mục nếu chưa tồn tại
            $uploadDir = 'public/img/sinhvien/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    error_log("Không thể tạo thư mục: " . $uploadDir);
                    throw new Exception("Không thể tạo thư mục upload");
                }
                chmod($uploadDir, 0777);
            }

            // Kiểm tra file upload
            if ($file['error'] !== 0) {
                throw new Exception("Lỗi upload file: " . $file['error']);
            }

            // Kiểm tra định dạng file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF");
            }

            // Kiểm tra kích thước file (giới hạn 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Kích thước file quá lớn. Giới hạn 5MB");
            }

            // Tạo tên file mới ngẫu nhiên để tránh trùng lặp
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;

            // Upload file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception("Không thể di chuyển file upload");
            }

            // Xóa file cũ nếu có
            if (!empty($oldImage) && file_exists($oldImage) && is_file($oldImage)) {
                unlink($oldImage);
            }

            return $uploadPath;
        } catch (Exception $e) {
            error_log("Lỗi xử lý file: " . $e->getMessage());
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'app/views/shares/header.php';

            try {
                // Kiểm tra dữ liệu đầu vào
                if (
                    empty($_POST['MaSV']) || empty($_POST['HoTen']) ||
                    empty($_POST['GioiTinh']) || empty($_POST['NgaySinh']) ||
                    empty($_POST['MaNganh'])
                ) {
                    throw new Exception('Vui lòng điền đầy đủ thông tin!');
                }

                // Kiểm tra ngày sinh
                $ngaySinh = strtotime($_POST['NgaySinh']);
                $today = strtotime(date('Y-m-d'));
                if ($ngaySinh > $today) {
                    throw new Exception('Ngày sinh không thể lớn hơn ngày hiện tại!');
                }

                $data = [
                    'MaSV' => $_POST['MaSV'],
                    'HoTen' => $_POST['HoTen'],
                    'GioiTinh' => $_POST['GioiTinh'],
                    'NgaySinh' => $_POST['NgaySinh'],
                    'Hinh' => $_POST['HinhCu'],
                    'MaNganh' => $_POST['MaNganh']
                ];

                // Xử lý upload hình ảnh mới nếu có
                if (isset($_FILES['Hinh']) && $_FILES['Hinh']['error'] === 0) {
                    $data['Hinh'] = $this->handleImageUpload($_FILES['Hinh'], $_POST['HinhCu']);
                }

                if ($this->sinhVienModel->updateSinhVien($data)) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Cập nhật sinh viên thành công!',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = 'index.php?action=index';
                        });
                    </script>";
                } else {
                    throw new Exception('Có lỗi xảy ra khi cập nhật sinh viên. Vui lòng kiểm tra lại thông tin!');
                }
            } catch (Exception $e) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '" . addslashes($e->getMessage()) . "',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.history.back();
                    });
                </script>";
            }
            require_once 'app/views/shares/footer.php';
        }
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'app/views/shares/header.php';

            try {
                // Kiểm tra dữ liệu đầu vào
                if (
                    empty($_POST['MaSV']) || empty($_POST['HoTen']) ||
                    empty($_POST['GioiTinh']) || empty($_POST['NgaySinh']) ||
                    empty($_POST['MaNganh'])
                ) {
                    throw new Exception('Vui lòng điền đầy đủ thông tin!');
                }

                // Kiểm tra ngày sinh
                $ngaySinh = strtotime($_POST['NgaySinh']);
                $today = strtotime(date('Y-m-d'));
                if ($ngaySinh > $today) {
                    throw new Exception('Ngày sinh không thể lớn hơn ngày hiện tại!');
                }

                $data = [
                    'MaSV' => $_POST['MaSV'],
                    'HoTen' => $_POST['HoTen'],
                    'GioiTinh' => $_POST['GioiTinh'],
                    'NgaySinh' => $_POST['NgaySinh'],
                    'MaNganh' => $_POST['MaNganh'],
                    'Hinh' => ''
                ];

                // Xử lý upload hình ảnh
                if (isset($_FILES['Hinh']) && $_FILES['Hinh']['error'] === 0) {
                    $data['Hinh'] = $this->handleImageUpload($_FILES['Hinh']);
                }

                if ($this->sinhVienModel->addSinhVien($data)) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Thêm sinh viên thành công!',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = 'index.php?action=index';
                        });
                    </script>";
                } else {
                    throw new Exception('Có lỗi xảy ra khi thêm sinh viên. Vui lòng kiểm tra lại thông tin!');
                }
            } catch (Exception $e) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '" . addslashes($e->getMessage()) . "',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.history.back();
                    });
                </script>";
            }
            require_once 'app/views/shares/footer.php';
        }
    }

    public function show()
    {
        require_once 'app/views/shares/header.php';

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            try {
                $sinhvien = $this->sinhVienModel->getSinhVienById($id);
                if ($sinhvien) {
                    require_once 'app/views/sinhvien/show.php';
                } else {
                    throw new Exception('Không tìm thấy thông tin sinh viên!');
                }
            } catch (Exception $e) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '" . addslashes($e->getMessage()) . "',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = 'index.php?action=index';
                    });
                </script>";
            }
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không tìm thấy mã sinh viên!',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'index.php?action=index';
                });
            </script>";
        }

        require_once 'app/views/shares/footer.php';
    }

    public function login()
    {
        // Extensive debug logging
        error_log("Login method called");
        error_log("Current GET parameters: " . print_r($_GET, true));

        // Forcefully destroy any existing session
        if (session_status() != PHP_SESSION_NONE) {
            error_log("Session status before destroy: " . session_status());

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
            error_log("Session destroyed");
        }

        // Start a new session
        session_start();
        error_log("New session started. Session ID: " . session_id());

        // Verify login view exists
        $loginViewPath = 'app/views/login/login.php';
        if (!file_exists($loginViewPath)) {
            error_log("ERROR: Login view not found at $loginViewPath");
            die("Login view not found");
        }

        // Trực tiếp hiển thị login form không qua header
        require_once $loginViewPath;
        exit();
    }

    public function logout()
    {
        // Start or resume the session
        session_start();

        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to login page
        header('Location: index.php?controller=sinhvien&action=login');
        exit();
    }

    public function processLogin()
    {
        // Start or resume the session
        session_start();

        // Ghi log về nỗ lực đăng nhập
        error_log("Attempt to login: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maSV = $_POST['MaSV'] ?? '';

            // Validate login
            $sinhVien = $this->sinhVienModel->getSinhVienById($maSV);

            if ($sinhVien) {
                // Clear any existing session data
                $_SESSION = array();

                // Set new session variables
                $_SESSION['maSV'] = $maSV;
                $_SESSION['hoTen'] = $sinhVien['HoTen'];

                // Ghi log đăng nhập thành công
                error_log("Login success for user: " . $maSV);

                // Create a hidden form for redirection
                echo '
                <!DOCTYPE html>
                <html>
                <body>
                    <form id="redirectForm" action="index.php" method="POST">
                        <input type="hidden" name="loginSuccess" value="1">
                    </form>
                    <script>
                        document.getElementById("redirectForm").submit();
                    </script>
                </body>
                </html>';
                exit();
            } else {
                // Ghi log đăng nhập thất bại
                error_log("Login failed for user: " . $maSV);

                // Login failed
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <title>Đăng nhập thất bại</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            Swal.fire({
                                icon: "error",
                                title: "Đăng nhập thất bại!",
                                text: "Mã sinh viên ' . htmlspecialchars($maSV) . ' không tồn tại trong hệ thống hoặc thông tin không chính xác!",
                                footer: "Vui lòng kiểm tra lại thông tin đăng nhập",
                                confirmButtonText: "Thử lại",
                                confirmButtonColor: "#3085d6",
                                showClass: {
                                    popup: "animate__animated animate__fadeInDown"
                                },
                                hideClass: {
                                    popup: "animate__animated animate__fadeOutUp"
                                }
                            }).then(function() {
                                window.location.href = "index.php?controller=sinhvien&action=login";
                            });
                        });
                    </script>
                </body>
                </html>';
                exit();
            }
        } else {
            // If not a POST request, redirect to login page
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }
    }
}
