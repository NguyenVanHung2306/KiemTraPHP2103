<?php require_once 'app/views/shares/header.php'; ?>

<div class="container">
    <h2 class="mb-4">HIỆU CHỈNH THÔNG TIN SINH VIÊN</h2>
    <div class="card">
        <div class="card-body">
            <form action="?action=update&id=<?php echo $sinhvien['MaSV']; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="MaSV" class="form-label">Mã SV</label>
                    <input type="text" class="form-control" id="MaSV" name="MaSV" value="<?php echo $sinhvien['MaSV']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="HoTen" class="form-label">Họ Tên</label>
                    <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo $sinhvien['HoTen']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="GioiTinh" class="form-label">Giới Tính</label>
                    <select class="form-control" id="GioiTinh" name="GioiTinh" required>
                        <option value="Nam" <?php echo ($sinhvien['GioiTinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ" <?php echo ($sinhvien['GioiTinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="NgaySinh" class="form-label">Ngày Sinh</label>
                    <input type="date" class="form-control" id="NgaySinh" name="NgaySinh"
                        value="<?php echo date('Y-m-d', strtotime($sinhvien['NgaySinh'])); ?>"
                        max="<?php echo date('Y-m-d'); ?>"
                        required>
                    <div class="form-text text-muted">Ngày sinh phải nhỏ hơn hoặc bằng ngày hiện tại</div>
                </div>

                <div class="mb-3">
                    <label for="Hinh" class="form-label">Hình</label>
                    <?php if ($sinhvien['Hinh']): ?>
                        <div class="mb-2">
                            <img src="<?php echo $sinhvien['Hinh']; ?>" alt="<?php echo $sinhvien['HoTen']; ?>" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="Hinh" name="Hinh" accept="image/*">
                    <input type="hidden" name="HinhCu" value="<?php echo $sinhvien['Hinh']; ?>">
                </div>

                <div class="mb-3">
                    <label for="MaNganh" class="form-label">Mã Ngành</label>
                    <select class="form-control" id="MaNganh" name="MaNganh" required>
                        <?php foreach ($nganhhoc as $nganh): ?>
                            <option value="<?php echo $nganh['MaNganh']; ?>"
                                <?php echo ($sinhvien['MaNganh'] == $nganh['MaNganh']) ? 'selected' : ''; ?>>
                                <?php echo $nganh['TenNganh']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="?action=index" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('NgaySinh').addEventListener('change', function() {
        var selectedDate = new Date(this.value);
        var today = new Date();

        if (selectedDate > today) {
            alert('Ngày sinh không thể lớn hơn ngày hiện tại!');
            this.value = '';
        }
    });
</script>

<?php require_once 'app/views/shares/footer.php'; ?>