<?php
session_start();
require_once '../config/config.php';

// Hàm trích xuất ID video YouTube
function getYouTubeVideoId($url) {
    $video_id = '';
    $patterns = [
        '/youtube\.com\/watch\?v=([^\&\?\/]+)/i', // youtube.com/watch?v=abc123
        '/youtu\.be\/([^\&\?\/]+)/i',             // youtu.be/abc123
        '/youtube\.com\/embed\/([^\&\?\/]+)/i',   // youtube.com/embed/abc123
        '/youtube\.com\/v\/([^\&\?\/]+)/i'        // youtube.com/v/abc123
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $video_id = $matches[1];
            break;
        }
    }

    return $video_id;
}

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
            position: relative;
            padding-bottom: 56.25%; /* Tỷ lệ 16:9 */
            height: 0;
            overflow: hidden;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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
            .video-container {
                padding-bottom: 75%; /* Tỷ lệ 4:3 cho di động nếu cần */
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
                    <!-- Video của khóa học (mặc định) -->
                    <?php if (!empty($course['video_file'])): ?>
                        <?php $course_video_id = getYouTubeVideoId($course['video_file']); ?>
                        <?php if ($course_video_id): ?>
                            <div class="video-container" data-lesson-id="course-<?php echo $course['id']; ?>">
                                <iframe 
                                    width="100%" 
                                    height="100%" 
                                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($course_video_id); ?>" 
                                    title="YouTube video player" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </div>
                        <?php else: ?>
                            <div class="video-container" data-lesson-id="course-<?php echo $course['id']; ?>">
                                <p>Link video của khóa học không hợp lệ.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="video-container" data-lesson-id="course-<?php echo $course['id']; ?>">
                            <p>Không có video cho khóa học này.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Video của bài học con -->
                    <?php if ($sub_lessons->num_rows > 0): ?>
                        <?php $sub_lessons->data_seek(0); ?>
                        <?php while ($lesson = $sub_lessons->fetch_assoc()): ?>
                            <?php if (!empty($lesson['video_url'])): ?>
                                <?php $video_id = getYouTubeVideoId($lesson['video_url']); ?>
                                <?php if ($video_id): ?>
                                    <div class="video-container" data-lesson-id="<?php echo $lesson['id']; ?>" style="display: none;">
                                        <iframe 
                                            width="100%" 
                                            height="100%" 
                                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" 
                                            title="YouTube video player" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                <?php else: ?>
                                    <div class="video-container" data-lesson-id="<?php echo $lesson['id']; ?>" style="display: none;">
                                        <p>Link video không hợp lệ.</p>
                                    </div>
                                <?php endif; ?>
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

                // Ẩn tất cả các video
                document.querySelectorAll('.video-container').forEach(v => {
                    v.style.display = 'none';
                });

                if (isActive) {
                    content.classList.remove('active');
                    arrow.textContent = '▼';

                    // Hiển thị lại video của khóa học khi đóng bài học con
                    const courseVideo = document.querySelector(`.video-container[data-lesson-id="course-<?php echo $course['id']; ?>"]`);
                    if (courseVideo) {
                        courseVideo.style.display = 'block';
                        rightContent.classList.add('active');
                    }
                } else {
                    // Đóng tất cả các bài học con khác
                    document.querySelectorAll('.sub-lesson-content').forEach(c => {
                        c.classList.remove('active');
                    });
                    document.querySelectorAll('.sub-lesson-header span').forEach(s => {
                        s.textContent = '▼';
                    });

                    content.classList.add('active');
                    arrow.textContent = '▲';

                    // Hiển thị video của bài học con
                    const lessonId = header.closest('.sub-lesson').querySelector('.test-button')?.getAttribute('href')?.match(/sub_lesson_id=(\d+)/)?.[1];
                    if (lessonId) {
                        const videoContainer = document.querySelector(`.video-container[data-lesson-id="${lessonId}"]`);
                        if (videoContainer) {
                            videoContainer.style.display = 'block';
                            rightContent.classList.add('active');
                        } else {
                            // Nếu không có video bài học con, hiển thị video khóa học
                            const courseVideo = document.querySelector(`.video-container[data-lesson-id="course-<?php echo $course['id']; ?>"]`);
                            if (courseVideo) {
                                courseVideo.style.display = 'block';
                                rightContent.classList.add('active');
                            }
                        }
                    }
                }
            });
        });

        // Hiển thị video khóa học mặc định khi tải trang
        document.addEventListener('DOMContentLoaded', () => {
            const rightContent = document.querySelector('.right-content');
            const courseVideo = document.querySelector(`.video-container[data-lesson-id="course-<?php echo $course['id']; ?>"]`);
            if (courseVideo) {
                courseVideo.style.display = 'block';
                rightContent.classList.add('active');
            }
        });

        // Nút cuộn lên đầu trang
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