<?php require_once 'app/views/shares/header.php'; ?>

<div class="container">
    <!-- Hiển thị thông báo từ session -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>DANH SÁCH HỌC PHẦN</h2>
        <?php if (isset($_SESSION['maSV'])): ?>
            <a href="index.php?controller=hocphan&action=xemGioHang" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Xem giỏ đăng ký
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Mã Học Phần</th>
                    <th>Tên Học Phần</th>
                    <th>Số Tín Chỉ</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hocphans as $hp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hp['MaHP']); ?></td>
                        <td><?php echo htmlspecialchars($hp['TenHP']); ?></td>
                        <td><?php echo htmlspecialchars($hp['SoTinChi']); ?></td>
                        <td>
                            <?php if (isset($_SESSION['maSV'])): ?>
                                <?php
                                // Kiểm tra xem học phần đã có trong giỏ hàng chưa
                                $isInCart = isset($_SESSION['cart']) && in_array($hp['MaHP'], $_SESSION['cart']);
                                ?>
                                <a href="index.php?controller=hocphan&action=dangKy&maHP=<?php echo urlencode($hp['MaHP']); ?>"
                                    class="btn <?php echo $isInCart ? 'btn-secondary' : 'btn-success'; ?> btn-sm">
                                    <?php echo $isInCart ? 'Đã thêm vào giỏ' : 'Đăng Kí'; ?>
                                </a>
                            <?php else: ?>
                                <a href="index.php?controller=sinhvien&action=login" class="btn btn-warning btn-sm">
                                    Đăng nhập để đăng ký
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>