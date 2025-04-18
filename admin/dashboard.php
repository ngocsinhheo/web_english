<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'];
$result = $conn->query("SELECT * FROM courses");
$popular_courses = $conn->query("SELECT * FROM courses ORDER BY likes DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bảng điều khiển cá nhân để học tiếng Anh từ cơ bản đến nâng cao tại English Learning.">
    <title>Học phần - <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #e0f7fa;
            color: #333;
            line-height: 1.6;
        }

        header {
            position: sticky;
            top: 0;
            background: #006064;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        header .logo {
            font-size: 28px;
            font-weight: 700;
        }

        header nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        header nav a:hover {
            color: #4dd0e1;
        }

        .hero {
            position: relative;
            background: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            text-align: center;
            padding: 120px 20px;
            animation: fadeIn 1s ease-in;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            color: #fff;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .hero .btn {
            background: #00acc1;
            color: #fff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            margin: 10px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .hero .btn:hover {
            background: #00838f;
            transform: translateY(-3px);
        }

        .progress {
            padding: 60px 20px;
            background: #fff;
            text-align: center;
        }

        .progress h2 {
            font-size: 36px;
            margin-bottom: 40px;
            color: #006064;
        }

        .progress-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .progress-item {
            width: 300px;
            background: #e0f7fa;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .progress-item:hover {
            transform: translateY(-5px);
        }

        .progress-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .progress-item h3 {
            font-size: 22px;
            color: #006064;
            margin-bottom: 10px;
        }

        .progress-item p {
            font-size: 16px;
            color: #555;
        }

        .tips {
            padding: 60px 20px;
            background: #e0f7fa;
            text-align: center;
        }

        .tips h2 {
            font-size: 36px;
            margin-bottom: 40px;
            color: #006064;
        }

        .tips-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .tip {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .tip:hover {
            transform: translateY(-5px);
        }

        .tip img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .tip h3 {
            font-size: 20px;
            color: #006064;
            margin-bottom: 10px;
        }

        .tip p {
            font-size: 16px;
            color: #555;
        }

        .popular-courses {
            padding: 60px 20px;
            background: #fff;
            text-align: center;
        }

        .popular-courses h2 {
            font-size: 36px;
            margin-bottom: 40px;
            color: #006064;
        }

        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .course {
            background: #e0f7fa;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, border 0.3s ease;
        }

        .course:hover {
            transform: translateY(-5px);
            border: 2px solid #00acc1;
        }

        .course img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .course:hover img {
            transform: scale(1.05);
        }

        .course div {
            padding: 20px;
        }

        .course h3 {
            font-size: 24px;
            color: #006064;
            margin-bottom: 10px;
        }

        .course p {
            color: #555;
            margin-bottom: 15px;
        }

        .course a {
            display: inline-block;
            background: #00acc1;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 50px;
            transition: background 0.3s ease;
        }

        .course a:hover {
            background: #00838f;
        }

        .courses {
            padding: 60px 20px;
            background: #e0f7fa;
            text-align: center;
        }

        .courses h2 {
            font-size: 36px;
            margin-bottom: 40px;
            color: #006064;
        }

        .courses input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .community {
            padding: 60px 20px;
            background: #fff;
            text-align: center;
        }

        .community h2 {
            font-size: 36px;
            margin-bottom: 40px;
            color: #006064;
        }

        .community-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .community-item {
            width: 300px;
            background: #e0f7fa;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .community-item:hover {
            transform: translateY(-5px);
        }

        .community-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .community-item p {
            font-size: 16px;
            color: #555;
        }

        footer {
            background: #006064;
            color: #fff;
            padding: 40px 20px;
            text-align: center;
        }

        footer .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        footer h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        footer p, footer a {
            font-size: 16px;
            color: #fff;
            text-decoration: none;
            margin: 5px 0;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .scroll-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #00acc1;
            color: #fff;
            padding: 10px 15px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: none;
            transition: background 0.3s ease;
        }

        .scroll-top:hover {
            background: #00838f;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 10px 20px;
            }

            header .logo {
                margin-bottom: 10px;
            }

            header nav a {
                margin: 5px 10px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 18px;
            }

            .hero .btn {
                padding: 10px 20px;
                font-size: 16px;
            }

            .progress h2, .tips h2, .popular-courses h2, .courses h2, .community h2 {
                font-size: 28px;
            }

            .progress-item, .tip, .course, .community-item {
                width: 100%;
                max-width: 400px;
            }

            footer .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">English Learning</div>
        <nav>
            <a href="dashboard.php">Trang chủ</a>
            <a href="../test/select_test.php">Thi thử</a>
            <a href="../profile.php">Hồ sơ</a>
            <a href="../contact.php">Liên hệ</a>
            <a href="../auth/logout.php" style="color: #e74c3c;">Đăng xuất</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Chào mừng <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Học tiếng Anh từ cơ bản đến nâng cao. Cùng chinh phục mục tiêu của bạn!</p>
            <div>
                <a href="../test/select_test.php" class="btn">Thi thử</a>
                <a href="#courses" class="btn">Khám phá khóa học</a>
            </div>
        </div>
    </section>



    <section class="tips">
        <h2>Mẹo học tiếng Anh hiệu quả</h2>
        <div class="tips-container">
            <div class="tip">
                <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=2070&auto=format&fit=crop" alt="Luyện nghe">
                <h3>Luyện nghe mỗi ngày</h3>
                <p>Nghe podcast hoặc xem phim tiếng Anh để cải thiện kỹ năng nghe.</p>
            </div>
            <div class="tip">
                <img src="https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?q=80&w=2070&auto=format&fit=crop" alt="Giao tiếp">
                <h3>Thực hành giao tiếp</h3>
                <p>Tìm bạn học để luyện nói, đừng ngại mắc lỗi!</p>
            </div>
            <div class="tip">
                <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=2070&auto=format&fit=crop" alt="Ghi chú từ vựng">
                <h3>Ghi chú từ vựng</h3>
                <p>Sử dụng flashcard để ghi nhớ từ mới hiệu quả hơn.</p>
            </div>
        </div>
    </section>

    <section class="popular-courses">
        <h2>Khóa học nổi bật</h2>
        <div class="course-list">
            <?php while ($row = $popular_courses->fetch_assoc()): ?>
                <div class="course">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: '../images/default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                    <div>
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <a href="course_detail.php?id=<?php echo $row['id']; ?>">Học ngay</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="courses" id="courses">
        <h2>Chọn khóa học</h2>
        <div>
            <input type="text" id="searchInput" placeholder="Tìm kiếm khóa học...">
        </div>
        <div class="course-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="course">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: '../images/default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                    <div>
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p><strong>Giảng viên:</strong> <?php echo htmlspecialchars($row['teacher_name'] ?: 'Chưa cập nhật'); ?></p>
                        <p><strong>Giá:</strong> <?php echo number_format($row['price'] ?: 0, 0, ',', '.') . ' VNĐ'; ?></p>
                        <a href="course_detail.php?id=<?php echo $row['id']; ?>">Học ngay</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="community">
        <h2>Cộng đồng học viên</h2>
        <div class="community-container">
            <div class="community-item">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop" alt="Lớp học nhóm">
                <p>Tham gia các lớp học nhóm để cùng tiến bộ với bạn bè!</p>
            </div>
            <div class="community-item">
                <img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop" alt="Sự kiện">
                <p>Đăng ký sự kiện giao lưu tiếng Anh hàng tháng.</p>
            </div>
            <div class="community-item">
                <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=2070&auto=format&fit=crop" alt="Chia sẻ kinh nghiệm">
                <p>Chia sẻ kinh nghiệm học tập và nhận hỗ trợ từ cộng đồng.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
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
    </footer>

    <button class="scroll-top">↑</button>

    <script>
        // Tìm kiếm khóa học
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.course').forEach(course => {
                const title = course.querySelector('h3').textContent.toLowerCase();
                const desc = course.querySelector('p').textContent.toLowerCase();
                course.style.display = title.includes(search) || desc.includes(search) ? '' : 'none';
            });
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Nút quay lại đầu trang
        const scrollTopBtn = document.querySelector('.scroll-top');
        window.addEventListener('scroll', () => {
            scrollTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
        });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>