<?php
session_start();
require_once '../config/config.php';


// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin người dùng từ session
$username = $_SESSION['username'];

// Lấy danh sách khóa học từ database
$sql = "SELECT * FROM courses";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học phần <?php echo $username; ?>!</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .course-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .course {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            width: 250px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .course h3 {
            color: #007bff;
        }
        .course a {
            display: inline-block;
            margin-top: 10px;
            color: #fff; /* Màu chữ trắng */
            text-decoration: none;
            font-weight: bold;
            background-color: #007bff; /* Màu nền xanh */
            padding: 10px 15px; /* Khoảng cách bên trong */
            border-radius: 5px; /* Bo góc */
        }
        .course a:hover {
            background-color: #0056b3; /* Màu nền khi hover */
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">TOEIC Learning</div>
        <div class="auth">
            <a href="#" class="login"><?php echo $username; ?>!</a>
            <a href="../auth/logout.php">Đăng xuất</a>
            <a href="#">Liên hệ</a>
        </div>        
    </header>

    <section class="hero">
        <h1>Chào mừng <?php echo $username; ?>! đến với TOEIC Learning</h1>
        <p>Học TOEIC dễ dàng với lộ trình từ 0 đến 800+.</p>
        <p>Đây là web học tiếng Anh miễn phí dành cho bạn</p>
        <a href="../test/select_test.php" class="btn">Thi thử luyện đề</a>
    </section>

    <section class="courses">
        <h2>Chọn chương trình bạn muốn học</h2>
        <div class="course-list" id="course-list">
            
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="course">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <a href="course_detail.php?id=<?php echo $row['id']; ?>">Học Ngay !</a>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 TOEIC Learning. All rights reserved.</p>
    </footer>
</body>
</html>
