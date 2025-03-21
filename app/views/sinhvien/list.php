<?php require_once 'app/views/shares/header.php'; ?>

<div class="container">
    <h2 class="mb-4">TRANG SINH VIÊN</h2>
    <div class="mb-3">
        <a href="?action=add" class="btn btn-success">Thêm Sinh Viên</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>MaSV</th>
                    <th>Họ Tên</th>
                    <th>Giới Tính</th>
                    <th>Ngày Sinh</th>
                    <th>Hình</th>
                    <th>Mã Ngành</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sinhviens as $sv): ?>
                    <tr>
                        <td><?php echo $sv['MaSV']; ?></td>
                        <td><?php echo $sv['HoTen']; ?></td>
                        <td><?php echo $sv['GioiTinh']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($sv['NgaySinh'])); ?></td>
                        <td>
                            <?php if (!empty($sv['Hinh'])): ?>
                                <img src="<?php echo $sv['Hinh']; ?>" alt="<?php echo $sv['HoTen']; ?>" style="max-width: 100px;">
                            <?php else: ?>
                                <img src="public/img/sinhvien/default.png" alt="<?php echo $sv['HoTen']; ?>" style="max-width: 100px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $sv['TenNganh']; ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $sv['MaSV']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="?action=delete&id=<?php echo $sv['MaSV']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc muốn xóa?')">Delete</a>
                            <a href="?action=show&id=<?php echo $sv['MaSV']; ?>" class="btn btn-info btn-sm">Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>