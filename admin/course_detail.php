<?php
session_start();
require_once '../config/config.php';


// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra tham số id có hợp lệ không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Khóa học không hợp lệ.";
    exit();
}

$course_id = intval($_GET['id']);

// Lấy thông tin khóa học từ database
$sql = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Không tìm thấy khóa học.";
    exit();
}

$course = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 22px;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            text-align: center;
        }
        .course-image {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            border-radius: 8px;
        }
        .course-content {
            margin-top: 20px;
        }
        .video-container {
            text-align: center;
            margin-top: 20px;
        }
        video {
            width: 100%;
            border-radius: 8px;
        }
        .download-links {
            text-align: center;
            margin-top: 20px;
        }
        .download-links a {
            display: inline-block;
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 5px;
            font-weight: bold;
        }
        .download-links a:hover {
            background: #218838;
        }
        .back-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            text-align: center;
            background: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <header>TOEIC Learning</header>

    <div class="container">
        <h1><?php echo htmlspecialchars($course['title']); ?></h1>

        <?php if (!empty($course['image'])): ?>
            <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="Ảnh khóa học" class="course-image">
        <?php endif; ?>

        <div class="course-content">
            <h3>Mô tả khóa học:</h3>
            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>

            <?php if (!empty($course['price'])): ?>
                <p><strong>Giá: </strong><?php echo number_format($course['price'], 0, ',', '.'); ?> VNĐ</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($course['video_file'])): ?>
            <div class="video-container">
                <h3>Bài giảng video:</h3>
                <video controls>
                    <source src="<?php echo htmlspecialchars($course['video_file']); ?>" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
        <?php endif; ?>

        <?php if (!empty($course['content_file'])): ?>
            <div class="download-links">
                <h3>Tài liệu học tập:</h3>
                <a href="<?php echo htmlspecialchars($course['content_file']); ?>" target="_blank">📄 Xem tài liệu</a>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="back-btn">Quay lại</a>
    </div>

    <footer>
        <p style="text-align: center;">&copy; 2025 TOEIC Learning. All rights reserved.</p>
    </footer>
</body>
</html>
