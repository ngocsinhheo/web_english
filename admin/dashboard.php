    <?php
    session_start();
    require_once '../config/config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    $username = $_SESSION['username'];
    $result = $conn->query("SELECT * FROM courses");
    ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học phần <?php echo $username; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <div class="logo">TOEIC Learning</div>
        <div class="auth">
            <span><?php echo $username; ?></span>
            <a href="../profile.php">Hồ sơ</a> 
            <a href="../auth/logout.php">Đăng xuất</a>
            <a href="../contact.php">Liên hệ</a>
        </div>
    </header>
    <section class="hero">
        <h1>Chào mừng <?php echo $username; ?>!</h1>
        <p>Học TOEIC từ 0 đến 800+.</p>
        <a href="../test/select_test.php" class="btn">Thi thử</a>
    </section>
    <section class="courses">
        <h2>Chọn khóa học</h2>
        <div class="course-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="course">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <a href="course_detail.php?id=<?php echo $row['id']; ?>">Học ngay</a>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <footer>&copy; 2025 TOEIC Learning</footer>
</body>
</html>