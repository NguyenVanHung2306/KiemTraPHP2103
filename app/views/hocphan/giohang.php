<?php
// Check if the user is logged in
if (!isset($_SESSION['maSV']) || empty($_SESSION['maSV'])) {
    // Redirect to login page if not logged in
    header('Location: index.php?controller=sinhvien&action=login');
    exit();
}

// Include CSS for the cart page
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Đăng Ký Học Phần</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .cart-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .cart-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .cart-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .total-credits {
            font-size: 1.2rem;
            font-weight: bold;
            color: #495057;
        }

        .empty-cart {
            text-align: center;
            padding: 30px 0;
            color: #6c757d;
        }

        .btn-remove {
            color: #dc3545;
        }

        .credits-badge {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .course-count {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include('app/views/shares/header.php'); ?>

    <div class="container mt-4 mb-5">
        <!-- Hiển thị thông báo từ session -->
        <?php if (isset($_SESSION['success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: '<?php echo $_SESSION['success']; ?>',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: '<?php echo $_SESSION['error']; ?>',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="cart-container">
                    <div class="cart-header d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-shopping-cart me-2"></i> Giỏ Đăng Ký Học Phần</h2>
                        <div>
                            <a href="index.php?controller=hocphan&action=index" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách học phần
                            </a>
                        </div>
                    </div>

                    <?php if (empty($danhSachHocPhan)): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <h3>Giỏ đăng ký của bạn đang trống</h3>
                            <p>Hãy quay lại danh sách học phần để đăng ký các môn học bạn quan tâm.</p>
                            <a href="index.php?controller=hocphan&action=index" class="btn btn-primary mt-2">
                                <i class="fas fa-book me-1"></i> Xem danh sách học phần
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end">
                                    <a href="index.php?controller=hocphan&action=xoaTatCa" class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả học phần đã đăng ký?')">
                                        <i class="fas fa-trash-alt me-1"></i> Xóa tất cả
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?php foreach ($danhSachHocPhan as $hocPhan): ?>
                                    <div class="cart-item">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($hocPhan['MaHP']); ?></span>
                                            </div>
                                            <div class="col-md-7">
                                                <h4><?php echo htmlspecialchars($hocPhan['TenHP']); ?></h4>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <span class="credits-badge">
                                                    <i class="fas fa-book me-1"></i> <?php echo intval($hocPhan['SoTinChi']); ?> tín chỉ
                                                </span>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <a href="index.php?controller=hocphan&action=xoaDangKy&mahp=<?php echo urlencode($hocPhan['MaHP']); ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa học phần này khỏi danh sách đăng ký?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="total-credits mb-2">
                                                    <i class="fas fa-calculator me-2"></i> Tổng số tín chỉ: <span class="text-primary"><?php echo $tongSoTinChi; ?></span>
                                                </div>
                                                <div>
                                                    <i class="fas fa-book me-2"></i> Số học phần: <span class="course-count"><?php echo count($danhSachHocPhan); ?></span>
                                                </div>
                                            </div>
                                            <a href="index.php?controller=hocphan&action=luuDangKy" class="btn btn-success"
                                                onclick="return confirm('Bạn có chắc chắn muốn lưu đăng ký các học phần này?')">
                                                <i class="fas fa-check-circle me-1"></i> Lưu đăng ký
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include('app/views/shares/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>