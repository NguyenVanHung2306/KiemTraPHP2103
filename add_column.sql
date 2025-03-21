-- Thêm cột SoLuongDuKien vào bảng HocPhan nếu chưa tồn tại
ALTER TABLE HocPhan ADD COLUMN IF NOT EXISTS SoLuongDuKien INT DEFAULT 100;

-- Cập nhật giá trị mặc định cho các học phần hiện có
UPDATE HocPhan SET SoLuongDuKien = 100 WHERE SoLuongDuKien IS NULL;

-- Hiển thị dữ liệu sau khi cập nhật
SELECT MaHP, TenHP, SoTinChi, SoLuongDuKien FROM HocPhan; 