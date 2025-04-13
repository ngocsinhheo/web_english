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
    <title><?php echo htmlspecialchars($course['title']); ?> - TOEIC Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
        }

        .course-header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        .course-header h1 {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
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
            background: #fff;
        }

        .sub-lesson-header {
            background: #f9fbfd;
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

        @media (max-width: 768px) {
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
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">TOEIC Learning</div>
        <div class="auth">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../admin/dashboard.php">Dashboard</a>
            <a href="../auth/logout.php">Đăng xuất</a>
        </div>
    </header>

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

    <footer>© 2025 TOEIC Learning</footer>

    <script>
        document.querySelectorAll('.sub-lesson-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const arrow = header.querySelector('span');
                const isActive = content.classList.contains('active');
                const rightContent = document.querySelector('.right-content');

                // Nếu bài học con đang mở, đóng nó và dừng video
                if (isActive) {
                    content.classList.remove('active');
                    arrow.textContent = '▼';

                    // Dừng video của bài học con này nếu nó đang hiển thị
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

                    // Kiểm tra nếu không còn bài học con nào mở, ẩn right-content
                    const anyActive = document.querySelector('.sub-lesson-content.active');
                    if (!anyActive) {
                        rightContent.classList.remove('active');
                    }
                } else {
                    // Mở bài học con
                    content.classList.add('active');
                    arrow.textContent = '▲';

                    // Dừng tất cả các video khác và ẩn chúng
                    document.querySelectorAll('.video-container').forEach(v => {
                        const video = v.querySelector('video');
                        if (video) {
                            video.pause();
                        }
                        v.style.display = 'none';
                    });

                    // Hiển thị video của bài học con này (nếu có)
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
    </script>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>