<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$course_id = filter_var($_GET['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

// Lấy thông tin khóa học
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc() ?: die("Khóa học không tồn tại!");

// Lấy danh sách bài học con
$stmt = $conn->prepare("SELECT * FROM sub_lessons WHERE course_id = ? ORDER BY order_number ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sub_lessons = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - English Learning</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #80deea);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        header {
            position: sticky;
            top: 0;
            background: linear-gradient(90deg, #006064, #00acc1);
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .logo a {
            color: #fff;
            text-decoration: none;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #00bcd4;
        }
        .course-detail-container {
            width: 98%;
            max-width: none;
            margin: 20px auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            box-sizing: border-box;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .course-header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }
        .course-header h1 {
            font-size: 36px;
            font-weight: 700;
            color: #006064;
            margin-bottom: 10px;
        }
        .course-header p {
            font-size: 18px;
            color: #7f8c8d;
            max-width: 800px;
            margin: 0 auto;
        }
        .content-wrapper {
            display: flex;
            width: 100%;
            gap: 20px;
        }
        .left-content {
            flex: 1;
            min-width: 300px;
        }
        .right-content {
            width: 800px;
            position: sticky;
            top: 20px;
            align-self: flex-start;
            display: none;
        }
        .right-content.active {
            display: block;
        }
        .sub-lesson {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            background: #f9fbfd;
        }
        .sub-lesson-header {
            background: #e0f7fa;
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sub-lesson-header h3 {
            font-size: 20px;
            color: #34495e;
        }
        .sub-lesson-header span {
            font-size: 24px;
            color: #3498db;
        }
        .sub-lesson-content {
            display: none;
            padding: 20px;
            background: #fff;
        }
        .sub-lesson-content.active {
            display: block;
        }
        .sub-lesson-content p {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .document-button {
            display: inline-block;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .document-button:hover {
            background: linear-gradient(90deg, #27ae60, #219653);
        }
        .video-container {
            margin-bottom: 15px;
        }
        .video-container video {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .test-button {
            display: inline-block;
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .test-button:hover {
            background: linear-gradient(90deg, #2980b9, #2471a3);
        }
        .back-btn {
            display: block;
            width: 200px;
            margin: 30px auto;
            text-align: center;
            background: linear-gradient(90deg, #dc3545, #c82333);
            color: #fff;
            padding: 12px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
        }
        .back-btn:hover {
            background: linear-gradient(90deg, #c82333, #bd2130);
        }
        footer {
            background: linear-gradient(90deg, #006064, #00acc1);
            color: #fff;
            padding: 20px;
            text-align: center;
            width: 100%;
        }
        footer a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin: 5px 0;
        }
        footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            header {
                flex-direction: column;
            }
            nav {
                margin-top: 10px;
            }
            nav a {
                margin: 5px;
            }
            .course-detail-container {
                width: 100%;
                margin: 10px auto;
                padding: 10px;
            }
            .content-wrapper {
                flex-direction: column;
            }
            .right-content {
                width: 100%;
                position: static;
            }
            .video-container video {
                max-width: 100%;
            }
            footer div {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
<?php include '../searchbar/index.php'; ?>
<script src="../searchbar/js/script.js"></script>
    <header>
        <div class="logo"><a href="../admin/dashboard.php">English Learning</a></div>
        <nav>
            <a href="../admin/dashboard.php">Trang chủ</a>
            <a href="../test/select_test.php">Thi thử</a>
            <a href="../profile.php">Hồ sơ</a>
            <a href="../contact.php">Liên hệ</a>
            <a href="../auth/logout.php" style="color: #ff5252;">Đăng xuất</a>
        </nav>
    </header>

    <main>
        <div class="course-detail-container">
            <div class="course-header">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p><?php echo htmlspecialchars($course['description']); ?></p>
            </div>

            <div class="content-wrapper">
                <div class="left-content">
                    <div class="sub-lessons">
                        <?php if ($sub_lessons->num_rows > 0): ?>
                            <?php while ($lesson = $sub_lessons->fetch_assoc()): ?>
                                <div class="sub-lesson">
                                    <div class="sub-lesson-header">
                                        <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                        <span>▼</span>
                                    </div>
                                    <div class="sub-lesson-content">
                                        <?php if ($lesson['content_file']): ?>
                                            <a href="<?php echo htmlspecialchars($lesson['content_file']); ?>" class="document-button" target="_blank">Tài liệu học</a>
                                        <?php endif; ?>
                                        <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                                        <a href="test_page.php?sub_lesson_id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="test-button">Làm bài kiểm tra</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Chưa có bài học con nào cho khóa học này.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="right-content">
                    <?php if ($sub_lessons->num_rows > 0): ?>
                        <?php $sub_lessons->data_seek(0); ?>
                        <?php while ($lesson = $sub_lessons->fetch_assoc()): ?>
                            <?php if ($lesson['video_file']): ?>
                                <div class="video-container" data-lesson-id="<?php echo $lesson['id']; ?>" style="display: none;">
                                    <video controls>
                                        <source src="<?php echo htmlspecialchars($lesson['video_file']); ?>" type="video/mp4">
                                        Trình duyệt không hỗ trợ video.
                                    </video>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <a href="../admin/dashboard.php" class="back-btn">Quay lại Dashboard</a>
        </div>
    </main>

    <footer>
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div>
                    <h3>English Learning</h3>
                    <p>Học tiếng Anh dễ dàng và hiệu quả!</p>
                </div>
                <div>
                    <h3>Liên kết</h3>
                    <a href="../about.php">Giới thiệu</a>
                    <a href="../contact.php">Liên hệ</a>
                    <a href="../policy.php">Chính sách</a>
                </div>
                <div>
                    <h3>Liên hệ</h3>
                    <p>Email: support@englishlearning.com</p>
                    <p>Hotline: 0123 456 789</p>
                </div>
            </div>
            <p>© 2025 English Learning. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.sub-lesson-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const arrow = header.querySelector('span');
                const isActive = content.classList.contains('active');
                const rightContent = document.querySelector('.right-content');

                if (isActive) {
                    content.classList.remove('active');
                    arrow.textContent = '▼';

                    const lessonId = header.closest('.sub-lesson').querySelector('.test-button')?.getAttribute('href')?.match(/sub_lesson_id=(\d+)/)?.[1];
                    if (lessonId) {
                        const videoContainer = document.querySelector(`.video-container[data-lesson-id="${lessonId}"]`);
                        if (videoContainer && videoContainer.style.display === 'block') {
                            const video = videoContainer.querySelector('video');
                            if (video) {
                                video.pause();
                            }
                            videoContainer.style.display = 'none';
                            rightContent.classList.remove('active');
                        }
                    }

                    const anyActive = document.querySelector('.sub-lesson-content.active');
                    if (!anyActive) {
                        rightContent.classList.remove('active');
                    }
                } else {
                    content.classList.add('active');
                    arrow.textContent = '▲';

                    document.querySelectorAll('.video-container').forEach(v => {
                        const video = v.querySelector('video');
                        if (video) {
                            video.pause();
                        }
                        v.style.display = 'none';
                    });

                    const lessonId = header.closest('.sub-lesson').querySelector('.test-button')?.getAttribute('href')?.match(/sub_lesson_id=(\d+)/)?.[1];
                    if (lessonId) {
                        const videoContainer = document.querySelector(`.video-container[data-lesson-id="${lessonId}"]`);
                        if (videoContainer) {
                            videoContainer.style.display = 'block';
                            rightContent.classList.add('active');
                        }
                    }
                }
            });
        });

        const scrollTopBtn = document.createElement('button');
        scrollTopBtn.textContent = '↑';
        scrollTopBtn.style.position = 'fixed';
        scrollTopBtn.style.bottom = '20px';
        scrollTopBtn.style.right = '20px';
        scrollTopBtn.style.background = '#0288d1';
        scrollTopBtn.style.color = '#fff';
        scrollTopBtn.style.padding = '10px';
        scrollTopBtn.style.border = 'none';
        scrollTopBtn.style.borderRadius = '50%';
        scrollTopBtn.style.cursor = 'pointer';
        scrollTopBtn.style.display = 'none';
        document.body.appendChild(scrollTopBtn);

        window.addEventListener('scroll', () => {
            scrollTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
        });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>

<?php $stmt->close(); $conn->close(); ?>