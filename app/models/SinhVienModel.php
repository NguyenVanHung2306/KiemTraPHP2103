<?php
require_once 'app/config/database.php';

class SinhVienModel
{
    private $conn;
    private $table = 'SinhVien';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllSinhVien()
    {
        $query = "SELECT sv.*, nh.TenNganh 
                 FROM " . $this->table . " sv
                 LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh
                 ORDER BY sv.MaSV";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllNganhHoc()
    {
        $query = "SELECT * FROM NganhHoc ORDER BY TenNganh";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSinhVienById($id)
    {
        $query = "SELECT sv.*, nh.TenNganh 
                 FROM " . $this->table . " sv
                 LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh
                 WHERE sv.MaSV = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSinhVien($data)
    {
        $query = "UPDATE " . $this->table . " 
                 SET HoTen = :HoTen, 
                     GioiTinh = :GioiTinh, 
                     NgaySinh = :NgaySinh, 
                     Hinh = :Hinh, 
                     MaNganh = :MaNganh 
                 WHERE MaSV = :MaSV";

        try {
            $stmt = $this->conn->prepare($query);

            // Bind các giá trị
            $stmt->bindParam(':MaSV', $data['MaSV']);
            $stmt->bindParam(':HoTen', $data['HoTen']);
            $stmt->bindParam(':GioiTinh', $data['GioiTinh']);
            $stmt->bindParam(':NgaySinh', $data['NgaySinh']);
            $stmt->bindParam(':Hinh', $data['Hinh']);
            $stmt->bindParam(':MaNganh', $data['MaNganh']);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteSinhVien($id)
    {
        try {
            // Lấy thông tin sinh viên trước khi xóa để có đường dẫn hình ảnh
            $sinhvien = $this->getSinhVienById($id);

            if (!$sinhvien) {
                error_log("Không tìm thấy sinh viên với ID: " . $id);
                return false;
            }

            // Thực hiện xóa sinh viên
            $query = "DELETE FROM " . $this->table . " WHERE MaSV = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                // Nếu xóa thành công và có hình ảnh, xóa file hình
                if ($sinhvien && !empty($sinhvien['Hinh']) && file_exists($sinhvien['Hinh'])) {
                    if (!unlink($sinhvien['Hinh'])) {
                        error_log("Không thể xóa file hình: " . $sinhvien['Hinh']);
                    }
                }
                return true;
            }
            error_log("Không thể xóa sinh viên từ database. ID: " . $id);
            return false;
        } catch (PDOException $e) {
            error_log("Lỗi khi xóa sinh viên: " . $e->getMessage());
            return false;
        }
    }

    public function addSinhVien($data)
    {
        try {
            // Thêm giá trị mặc định cho trường Hinh nếu không có
            if (!isset($data['Hinh'])) {
                $data['Hinh'] = '';
            }

            $query = "INSERT INTO " . $this->table . " 
                     (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh) 
                     VALUES 
                     (:MaSV, :HoTen, :GioiTinh, :NgaySinh, :Hinh, :MaNganh)";

            $stmt = $this->conn->prepare($query);

            // Bind các giá trị
            $stmt->bindParam(':MaSV', $data['MaSV']);
            $stmt->bindParam(':HoTen', $data['HoTen']);
            $stmt->bindParam(':GioiTinh', $data['GioiTinh']);
            $stmt->bindParam(':NgaySinh', $data['NgaySinh']);
            $stmt->bindParam(':Hinh', $data['Hinh']);
            $stmt->bindParam(':MaNganh', $data['MaNganh']);

            // Log dữ liệu trước khi thêm
            error_log("Dữ liệu thêm sinh viên: " . print_r($data, true));

            $result = $stmt->execute();
            if (!$result) {
                error_log("Lỗi SQL: " . print_r($stmt->errorInfo(), true));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi thêm sinh viên: " . $e->getMessage());
            return false;
        }
    }
}
