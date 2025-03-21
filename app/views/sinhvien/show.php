<?php require_once 'app/views/shares/header.php'; ?>

<div class="container">
    <h2 class="mb-4">THÔNG TIN CHI TIẾT SINH VIÊN</h2>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?php if (!empty($sinhvien['Hinh'])): ?>
                    <img src="<?php echo $sinhvien['Hinh']; ?>" alt="<?php echo $sinhvien['HoTen']; ?>"
                        class="img-fluid rounded mb-3">
                    <?php else: ?>
                    <img src="public/img/sinhvien/default.png" alt="Default Image" class="img-fluid rounded mb-3">
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Mã Sinh Viên</th>
                            <td><?php echo $sinhvien['MaSV']; ?></td>
                        </tr>
                        <tr>
                            <th>Họ Tên</th>
                            <td><?php echo $sinhvien['HoTen']; ?></td>
                        </tr>
                        <tr>
                            <th>Giới Tính</th>
                            <td><?php echo $sinhvien['GioiTinh']; ?></td>
                        </tr>
                        <tr>
                            <th>Ngày Sinh</th>
                            <td><?php echo date('d/m/Y', strtotime($sinhvien['NgaySinh'])); ?></td>
                        </tr>
                        <tr>
                            <th>Ngành Học</th>
                            <td>
                                <?php echo $sinhvien['MaNganh']; ?> - <?php echo $sinhvien['TenNganh']; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <a href="?action=edit&id=<?php echo $sinhvien['MaSV']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="?action=index" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>