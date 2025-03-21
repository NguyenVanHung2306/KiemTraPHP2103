<?php
require_once 'app/controller/SinhVienController.php';
require_once 'app/controller/HocPhanController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'sinhvien';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($controller) {
    case 'sinhvien':
        $sinhVienController = new SinhVienController();
        switch ($action) {
            case 'index':
                $sinhVienController->index();
                break;
            case 'add':
                $sinhVienController->add();
                break;
            case 'store':
                $sinhVienController->store();
                break;
            case 'edit':
                $sinhVienController->edit();
                break;
            case 'update':
                $sinhVienController->update();
                break;
            case 'delete':
                $sinhVienController->delete();
                break;
            case 'show':
                $sinhVienController->show();
                break;
            case 'login':
                $sinhVienController->login();
                break;
            case 'processLogin':
                $sinhVienController->processLogin();
                break;
            case 'logout':
                $sinhVienController->logout();
                break;
            default:
                $sinhVienController->index();
                break;
        }
        break;
    case 'hocphan':
        $hocPhanController = new HocPhanController();
        switch ($action) {
            case 'index':
                $hocPhanController->index();
                break;
            case 'dangKy':
                $hocPhanController->dangKy();
                break;
            case 'xemGioHang':
                $hocPhanController->xemGioHang();
                break;
            case 'xoaDangKy':
                $hocPhanController->xoaDangKy();
                break;
            case 'xoaTatCa':
                $hocPhanController->xoaTatCa();
                break;
            case 'luuDangKy':
                $hocPhanController->luuDangKy();
                break;
            default:
                $hocPhanController->index();
                break;
        }
        break;
    default:
        $sinhVienController = new SinhVienController();
        $sinhVienController->index();
        break;
}
