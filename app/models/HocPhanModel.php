<?php
require_once 'app/config/database.php';

class HocPhanModel
{
    private $conn;
    private $table = 'HocPhan';
    private $lastErrorMessage = '';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();

        // Enable PDO error reporting
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    public function getAllHocPhan()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY MaHP";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function kiemTraDangKy($maSV, $maHP)
    {
        try {
            // Log input parameters và query chi tiết
            $debugMessage = "===== DEBUG: ĐANG KIỂM TRA ĐĂNG KÝ =====\n";
            $debugMessage .= "MaSV: $maSV\n";
            $debugMessage .= "MaHP: $maHP\n";

            // Truy vấn đơn giản và trực tiếp hơn để kiểm tra đăng ký
            $query = "SELECT ctdk.ID 
                     FROM ChiTietDangKy ctdk 
                     JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
                     WHERE dk.MaSV = :maSV AND ctdk.MaHP = :maHP";

            $debugMessage .= "Query: $query\n";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':maSV', $maSV);
            $stmt->bindParam(':maHP', $maHP);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $debugMessage .= "Kết quả kiểm tra đăng ký: " . ($result ? "Đã đăng ký" : "Chưa đăng ký") . "\n";
            if ($result) {
                $debugMessage .= "Chi tiết: " . print_r($result, true) . "\n";
            }
            $debugMessage .= "===== KẾT THÚC DEBUG =====\n";

            error_log($debugMessage);

            return $result ? true : false;
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi kiểm tra đăng ký: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    public function dangKyHocPhan($maSV, $maHP)
    {
        // Reset last error message
        $this->lastErrorMessage = '';

        try {
            // Kiểm tra sinh viên và học phần tồn tại
            $checkStudentQuery = "SELECT * FROM SinhVien WHERE MaSV = :maSV";
            $checkCourseQuery = "SELECT * FROM HocPhan WHERE MaHP = :maHP";

            $stmtStudent = $this->conn->prepare($checkStudentQuery);
            $stmtStudent->bindParam(':maSV', $maSV);
            $stmtStudent->execute();
            $studentData = $stmtStudent->fetch(PDO::FETCH_ASSOC);

            $stmtCourse = $this->conn->prepare($checkCourseQuery);
            $stmtCourse->bindParam(':maHP', $maHP);
            $stmtCourse->execute();
            $courseData = $stmtCourse->fetch(PDO::FETCH_ASSOC);

            if (!$studentData) {
                $this->lastErrorMessage = "Sinh viên không tồn tại: $maSV";
                error_log($this->lastErrorMessage);
                return false;
            }

            if (!$courseData) {
                $this->lastErrorMessage = "Học phần không tồn tại: $maHP";
                error_log($this->lastErrorMessage);
                return false;
            }

            // Đảm bảo không có đăng ký trùng lặp
            if ($this->kiemTraDangKy($maSV, $maHP)) {
                // Nếu đã đăng ký, thử xóa đăng ký cũ để đăng ký lại
                if (!$this->resetDangKy($maSV, $maHP)) {
                    $this->lastErrorMessage = "Bạn đã đăng ký học phần này rồi và không thể đăng ký lại";
                    error_log($this->lastErrorMessage);
                    return false;
                }
                error_log("Đã xóa đăng ký cũ, tiếp tục đăng ký mới");
            }

            // Start transaction
            $this->conn->beginTransaction();

            try {
                // First, check if there's already a registration record for this student
                $checkDangKyQuery = "SELECT MaDK FROM DangKy WHERE MaSV = :maSV";
                $stmtCheckDangKy = $this->conn->prepare($checkDangKyQuery);
                $stmtCheckDangKy->bindParam(':maSV', $maSV);
                $stmtCheckDangKy->execute();
                $existingDangKy = $stmtCheckDangKy->fetch(PDO::FETCH_ASSOC);

                $maDK = null;

                if ($existingDangKy) {
                    // Nếu đã có đăng ký, sử dụng MaDK hiện có
                    $maDK = $existingDangKy['MaDK'];
                    error_log("Using existing registration ID: $maDK");
                } else {
                    // Tạo bản ghi đăng ký mới
                    $queryDangKy = "INSERT INTO DangKy (MaSV, NgayDK) VALUES (:maSV, NOW())";
                    $stmtDangKy = $this->conn->prepare($queryDangKy);
                    $stmtDangKy->bindParam(':maSV', $maSV);
                    $resultDangKy = $stmtDangKy->execute();

                    if (!$resultDangKy) {
                        throw new PDOException("Không thể tạo đăng ký");
                    }

                    // Get the last inserted MaDK
                    $maDK = $this->conn->lastInsertId();
                    error_log("Created new registration with ID: $maDK");
                }

                // Insert course details
                $queryChiTiet = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (:maDK, :maHP)";
                $stmtChiTiet = $this->conn->prepare($queryChiTiet);
                $stmtChiTiet->bindParam(':maDK', $maDK);
                $stmtChiTiet->bindParam(':maHP', $maHP);
                $resultChiTiet = $stmtChiTiet->execute();

                if (!$resultChiTiet) {
                    throw new PDOException("Không thể thêm chi tiết đăng ký");
                }

                // Commit transaction
                $this->conn->commit();

                error_log("Registration successful - MaSV: $maSV, MaHP: $maHP, MaDK: $maDK");
                return true;
            } catch (PDOException $e) {
                // Rollback transaction
                $this->conn->rollBack();
                $this->lastErrorMessage = $e->getMessage();
                error_log("Transaction error: " . $this->lastErrorMessage);
                return false;
            }
        } catch (PDOException $e) {
            // Ensure transaction is rolled back
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = "Lỗi đăng ký: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    public function getHocPhanDaDangKy($maSV)
    {
        try {
            // Get the list of registered courses for a student
            $query = "SELECT hp.MaHP, hp.TenHP, hp.SoTinChi 
                      FROM HocPhan hp 
                      JOIN ChiTietDangKy ctdk ON hp.MaHP = ctdk.MaHP 
                      JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
                      WHERE dk.MaSV = :maSV
                      ORDER BY hp.TenHP";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':maSV', $maSV);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi lấy danh sách học phần đã đăng ký: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return [];
        }
    }

    public function xoaDangKyHocPhan($maSV, $maHP)
    {
        try {
            // First find the registration that matches both student and course
            $findQuery = "SELECT ctdk.MaDK, ctdk.ID 
                         FROM ChiTietDangKy ctdk 
                         JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
                         WHERE dk.MaSV = :maSV AND ctdk.MaHP = :maHP";

            $stmtFind = $this->conn->prepare($findQuery);
            $stmtFind->bindParam(':maSV', $maSV);
            $stmtFind->bindParam(':maHP', $maHP);
            $stmtFind->execute();

            $registration = $stmtFind->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                $this->lastErrorMessage = "Không tìm thấy đăng ký học phần";
                return false;
            }

            // Delete the course from ChiTietDangKy
            $deleteQuery = "DELETE FROM ChiTietDangKy WHERE ID = :id";
            $stmtDelete = $this->conn->prepare($deleteQuery);
            $stmtDelete->bindParam(':id', $registration['ID']);
            $result = $stmtDelete->execute();

            // Check if there are any courses left in this registration
            $checkQuery = "SELECT COUNT(*) FROM ChiTietDangKy WHERE MaDK = :maDK";
            $stmtCheck = $this->conn->prepare($checkQuery);
            $stmtCheck->bindParam(':maDK', $registration['MaDK']);
            $stmtCheck->execute();

            $coursesCount = $stmtCheck->fetchColumn();

            // If no courses left, delete the registration
            if ($coursesCount == 0) {
                $deleteRegQuery = "DELETE FROM DangKy WHERE MaDK = :maDK";
                $stmtDeleteReg = $this->conn->prepare($deleteRegQuery);
                $stmtDeleteReg->bindParam(':maDK', $registration['MaDK']);
                $stmtDeleteReg->execute();
            }

            return $result;
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi xóa đăng ký học phần: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    public function xoaTatCaDangKy($maSV)
    {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Find all registrations for this student
            $findQuery = "SELECT MaDK FROM DangKy WHERE MaSV = :maSV";
            $stmtFind = $this->conn->prepare($findQuery);
            $stmtFind->bindParam(':maSV', $maSV);
            $stmtFind->execute();

            $registrations = $stmtFind->fetchAll(PDO::FETCH_COLUMN);

            if (empty($registrations)) {
                $this->conn->commit();
                return true; // No registrations to delete
            }

            // Delete all course details first
            foreach ($registrations as $maDK) {
                $deleteDetailsQuery = "DELETE FROM ChiTietDangKy WHERE MaDK = :maDK";
                $stmtDeleteDetails = $this->conn->prepare($deleteDetailsQuery);
                $stmtDeleteDetails->bindParam(':maDK', $maDK);
                $stmtDeleteDetails->execute();
            }

            // Then delete all registrations
            $deleteRegQuery = "DELETE FROM DangKy WHERE MaSV = :maSV";
            $stmtDeleteReg = $this->conn->prepare($deleteRegQuery);
            $stmtDeleteReg->bindParam(':maSV', $maSV);
            $result = $stmtDeleteReg->execute();

            // Commit transaction
            $this->conn->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = "Lỗi xóa tất cả đăng ký học phần: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    // Phương thức làm mới trạng thái đăng ký để đảm bảo không có lỗi
    public function resetDangKy($maSV, $maHP)
    {
        try {
            // Ghi log debug
            error_log("Bắt đầu xóa đăng ký nếu có - MaSV: $maSV, MaHP: $maHP");

            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // Tìm đăng ký hiện có
            $findQuery = "SELECT ctdk.ID, ctdk.MaDK 
                         FROM ChiTietDangKy ctdk 
                         JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
                         WHERE dk.MaSV = :maSV AND ctdk.MaHP = :maHP";

            $stmt = $this->conn->prepare($findQuery);
            $stmt->bindParam(':maSV', $maSV);
            $stmt->bindParam(':maHP', $maHP);
            $stmt->execute();

            $registration = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($registration) {
                // Xóa chi tiết đăng ký
                $deleteDetailQuery = "DELETE FROM ChiTietDangKy WHERE ID = :id";
                $stmtDetail = $this->conn->prepare($deleteDetailQuery);
                $stmtDetail->bindParam(':id', $registration['ID']);
                $stmtDetail->execute();

                // Kiểm tra xem còn học phần nào trong đăng ký không
                $checkQuery = "SELECT COUNT(*) FROM ChiTietDangKy WHERE MaDK = :maDK";
                $stmtCheck = $this->conn->prepare($checkQuery);
                $stmtCheck->bindParam(':maDK', $registration['MaDK']);
                $stmtCheck->execute();

                if ($stmtCheck->fetchColumn() == 0) {
                    // Nếu không còn học phần nào, xóa đăng ký
                    $deleteRegQuery = "DELETE FROM DangKy WHERE MaDK = :maDK";
                    $stmtReg = $this->conn->prepare($deleteRegQuery);
                    $stmtReg->bindParam(':maDK', $registration['MaDK']);
                    $stmtReg->execute();
                }

                error_log("Đã xóa đăng ký hiện có");
            } else {
                error_log("Không tìm thấy đăng ký nào để xóa");
            }

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = "Lỗi khi reset đăng ký: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    public function getHocPhanByMaHP($maHP)
    {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE MaHP = :maHP";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':maHP', $maHP);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi lấy thông tin học phần: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    // Cập nhật số lượng học phần khi đăng ký
    public function giamSoLuongDangKy($maHP)
    {
        try {
            // Kiểm tra xem học phần có chỗ còn trống không
            $query = "SELECT SoLuongDuKien FROM HocPhan WHERE MaHP = :maHP";
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':maHP', $maHP);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$result || $result['SoLuongDuKien'] <= 0) {
                $this->lastErrorMessage = "Học phần $maHP đã hết chỗ hoặc không tồn tại";
                error_log($this->lastErrorMessage);
                return false;
            }

            // Giảm số lượng dự kiến đi 1
            $query = "UPDATE HocPhan SET SoLuongDuKien = SoLuongDuKien - 1 WHERE MaHP = :maHP AND SoLuongDuKien > 0";
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':maHP', $maHP);
            $result = $statement->execute();

            if ($result && $statement->rowCount() > 0) {
                error_log("Đã giảm số lượng dự kiến cho học phần $maHP");
                return true;
            } else {
                $this->lastErrorMessage = "Không thể giảm số lượng dự kiến cho học phần $maHP";
                error_log($this->lastErrorMessage);
                return false;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi khi giảm số lượng dự kiến: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    // Tăng số lượng học phần khi hủy đăng ký
    public function tangSoLuongDangKy($maHP)
    {
        try {
            // Tăng số lượng dự kiến lên 1
            $query = "UPDATE HocPhan SET SoLuongDuKien = SoLuongDuKien + 1 WHERE MaHP = :maHP";
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':maHP', $maHP);
            $result = $statement->execute();

            if ($result && $statement->rowCount() > 0) {
                error_log("Đã tăng số lượng dự kiến cho học phần $maHP");
                return true;
            } else {
                $this->lastErrorMessage = "Không thể tăng số lượng dự kiến cho học phần $maHP";
                error_log($this->lastErrorMessage);
                return false;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi khi tăng số lượng dự kiến: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return false;
        }
    }

    // Kiểm tra số lượng đăng ký còn lại
    public function kiemTraSoLuongConLai($maHP)
    {
        try {
            $query = "SELECT SoLuongDuKien FROM HocPhan WHERE MaHP = :maHP";
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':maHP', $maHP);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['SoLuongDuKien'];
            } else {
                return 0;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Lỗi khi kiểm tra số lượng còn lại: " . $e->getMessage();
            error_log($this->lastErrorMessage);
            return 0;
        }
    }
}
