<?php
session_start();

// Xác định đích chuyển trang
$destination = $_GET['to'] ?? 'index.php';

// Kiểm tra và chuyển trang
header("Location: $destination");
exit();
