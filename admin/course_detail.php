<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$course_id = filter_var($_GET['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc() ?: die("Khóa học không tồn tại!");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>TOEIC Learning</header>
    <div class="container">
        <h1><?php echo htmlspecialchars($course['title']); ?></h1>
        <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="Ảnh khóa học" class="course-image">
        <div class="course-content">
            <h3>Mô tả:</h3>
            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            <p><strong>Giá:</strong> <?php echo number_format($course['price'], 0, ',', '.'); ?> VNĐ</p>
        </div>
        <div class="video-container">
            <h3>Video:</h3>
            <video controls><source src="<?php echo htmlspecialchars($course['video_file']); ?>" type="video/mp4"></video>
        </div>
        <div class="download-links">
            <h3>Tài liệu:</h3>
            <a href="<?php echo htmlspecialchars($course['content_file']); ?>" target="_blank">📄 Xem</a>
        </div>
        <a href="dashboard.php" class="back-btn">Quay lại</a>
    </div>
    <footer>&copy; 2025 TOEIC Learning</footer>
</body>
</html>