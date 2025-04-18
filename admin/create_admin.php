<?php
// Kết nối đến cơ sở dữ liệu
$conn = mysqli_connect('localhost', 'root', '', 'toeicdb');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem tài khoản admin đã tồn tại chưa
$sql_check = "SELECT COUNT(*) AS count FROM users WHERE email = 'admin@123'";
$result_check = $conn->query($sql_check);
$row = $result_check->fetch_assoc();

if ($row['count'] == 0) {
    // Tạo tài khoản admin nếu chưa tồn tại
    $username = 'adminphu';
    $email = 'admin@123';
    $password = '123'; // Mật khẩu gốc
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu

    // Thêm tài khoản admin vào cơ sở dữ liệu
    $sql = "INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, 'admin', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Tạo tài khoản admin thành công!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Tài khoản admin đã tồn tại!";
}

// Đóng kết nối
$conn->close();
?>
