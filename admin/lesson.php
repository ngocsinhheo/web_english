<?php
session_start();
include 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy ID khóa học từ URL
$course_id = $_GET['id'] ?? 0;

// Truy vấn dữ liệu khóa học
$sql = "SELECT * FROM courses WHERE id = $course_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $course = $result->fetch_assoc();
} else {
    die("Khóa học không tồn tại!");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course['title']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo $course['title']; ?></h1>
        <a href="dashboard.php">Quay lại</a>
        <a href="logout.php">Đăng xuất</a>
    </header>

    <section class="lesson-content">
        <p><?php echo $course['description']; ?></p>
        <p>Nội dung bài học sẽ được cập nhật sau...</p>
    </section>
</body>
</html>
