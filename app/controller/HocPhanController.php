<?php
require_once 'app/models/HocPhanModel.php';

class HocPhanController
{
    private $hocPhanModel;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->hocPhanModel = new HocPhanModel();
    }

    public function index()
    {
        $hocphans = $this->hocPhanModel->getAllHocPhan();
        require_once 'app/views/hocphan/list.php';
    }

    public function xemGioHang()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }

        // Lấy danh sách học phần từ giỏ hàng
        $danhSachHocPhan = $this->getCartItems();

        // Tính tổng số tín chỉ
        $tongSoTinChi = 0;
        foreach ($danhSachHocPhan as $hocPhan) {
            $tongSoTinChi += intval($hocPhan['SoTinChi']);
        }

        // Load view
        include 'app/views/hocphan/giohang.php';
    }

    public function xoaDangKy()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }

        // Kiểm tra mã học phần
        if (!isset($_GET['mahp']) || empty($_GET['mahp'])) {
            $_SESSION['error'] = "Không tìm thấy mã học phần cần xóa";
            header('Location: index.php?controller=hocphan&action=xemGioHang');
            exit();
        }

        $maHP = $_GET['mahp'];

        // Xóa học phần khỏi giỏ hàng
        $result = $this->removeFromCart($maHP);

        if ($result) {
            $_SESSION['success'] = "Đã xóa học phần khỏi giỏ đăng ký";
        } else {
            $_SESSION['error'] = "Không thể xóa học phần khỏi giỏ đăng ký";
        }

        // Chuyển hướng về trang giỏ hàng
        header('Location: index.php?controller=hocphan&action=xemGioHang');
        exit();
    }

    public function xoaTatCa()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }

        // Xóa toàn bộ giỏ hàng
        $this->clearCart();

        $_SESSION['success'] = "Đã xóa tất cả học phần khỏi giỏ đăng ký";

        // Chuyển hướng về trang giỏ hàng
        header('Location: index.php?controller=hocphan&action=xemGioHang');
        exit();
    }

    public function luuDangKy()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }

        $maSV = $_SESSION['maSV'];

        // Kiểm tra giỏ hàng có trống không
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['error'] = "Giỏ đăng ký của bạn đang trống";
            header('Location: index.php?controller=hocphan&action=xemGioHang');
            exit();
        }

        $success = true;
        $errorMessages = [];

        // Bắt đầu lưu từng học phần vào database
        foreach ($_SESSION['cart'] as $maHP) {
            $result = $this->hocPhanModel->dangKyHocPhan($maSV, $maHP);

            if (!$result) {
                $success = false;
                $errorMessages[] = "Lỗi đăng ký học phần " . $maHP . ": " . $this->hocPhanModel->getLastErrorMessage();
            }
        }

        if ($success) {
            // Xóa giỏ hàng sau khi lưu thành công
            $this->clearCart();
            $_SESSION['success'] = "Đã lưu đăng ký học phần thành công";
            header('Location: index.php?controller=hocphan&action=index');
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi lưu đăng ký: " . implode("<br>", $errorMessages);
            header('Location: index.php?controller=hocphan&action=xemGioHang');
        }

        exit();
    }

    public function dangKy()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
            $_SESSION['error'] = "Vui lòng đăng nhập để đăng ký học phần";
            header('Location: index.php?controller=sinhvien&action=login');
            exit();
        }

        // Kiểm tra mã học phần
        $maHP = isset($_GET['maHP']) ? $_GET['maHP'] : null;
        if (!$maHP) {
            $_SESSION['error'] = "Không tìm thấy mã học phần";
            header('Location: index.php?controller=hocphan&action=index');
            exit();
        }

        // Kiểm tra học phần có tồn tại không
        $hocPhan = $this->hocPhanModel->getHocPhanByMaHP($maHP);
        if (!$hocPhan) {
            $_SESSION['error'] = "Học phần không tồn tại";
            header('Location: index.php?controller=hocphan&action=index');
            exit();
        }

        // Thêm học phần vào giỏ hàng
        $result = $this->addToCart($maHP);

        if ($result) {
            $_SESSION['success'] = "Đã thêm học phần vào giỏ đăng ký";
        } else {
            $_SESSION['error'] = "Học phần đã có trong giỏ đăng ký";
        }

        // Chuyển hướng đến trang giỏ hàng
        header('Location: index.php?controller=hocphan&action=xemGioHang');
        exit();
    }

    // Phương thức thêm học phần vào giỏ hàng trong session
    private function addToCart($maHP)
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Kiểm tra nếu học phần đã có trong giỏ hàng
        if (!in_array($maHP, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $maHP;
            return true;
        }

        return false; // Học phần đã tồn tại trong giỏ hàng
    }

    // Phương thức xóa học phần khỏi giỏ hàng
    private function removeFromCart($maHP)
    {
        if (isset($_SESSION['cart'])) {
            $key = array_search($maHP, $_SESSION['cart']);
            if ($key !== false) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Sắp xếp lại mảng
                return true;
            }
        }

        return false;
    }

    // Phương thức lấy danh sách học phần trong giỏ hàng
    private function getCartItems()
    {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return [];
        }

        $result = [];
        foreach ($_SESSION['cart'] as $maHP) {
            $hocPhan = $this->hocPhanModel->getHocPhanByMaHP($maHP);
            if ($hocPhan) {
                $result[] = $hocPhan;
            }
        }

        return $result;
    }

    // Phương thức xóa toàn bộ giỏ hàng
    private function clearCart()
    {
        $_SESSION['cart'] = [];
    }
}