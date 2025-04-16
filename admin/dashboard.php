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
    <title>Học phần <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="../style.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        body {
            background: linear-gradient(135deg, #e0f7fa, #80deea); /* Gradient nền toàn trang */
        }
        header a:hover { color: #00bcd4; }
        .hero .btn:hover { opacity: 0.8; }
        .course:hover { transform: translateY(-5px); border: 2px solid #00bcd4; }
        .course img { transition: transform 0.3s; }
        .course:hover img { transform: scale(1.05); }
        footer a:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            header { flex-direction: column; }
            nav { margin-top: 10px; }
            nav a { margin: 5px; }
            footer div { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <header style="position: sticky; top: 0; background: linear-gradient(90deg, #006064, #00acc1); color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div class="logo" style="font-size: 24px; font-weight: bold;">English Learning</div>
        <nav>
            <a href="dashboard.php" style="color: #fff; text-decoration: none; margin: 0 15px; transition: color 0.3s;">Trang chủ</a>
            <a href="../test/select_test.php" style="color: #fff; text-decoration: none; margin: 0 15px; transition: color 0.3s;">Thi thử</a>
            <a href="../profile.php" style="color: #fff; text-decoration: none; margin: 0 15px; transition: color 0.3s;">Hồ sơ</a>
            <a href="../contact.php" style="color: #fff; text-decoration: none; margin: 0 15px; transition: color 0.3s;">Liên hệ</a>
            <a href="../auth/logout.php" style="color: #e74c3c; text-decoration: none; margin: 0 15px; transition: color 0.3s;">Đăng xuất</a>
        </nav>
    </header>

    <section class="hero" style="background: linear-gradient(135deg, #0288d1, #4fc3f7); color: #fff; text-align: center; padding: 100px 20px;">
        <h1 style="font-size: 48px; animation: fadeIn 1s;">Chào mừng <?php echo htmlspecialchars($username); ?>!</h1>
        <p style="font-size: 24px; margin: 20px 0;">Học TIẾNG ANH từ cơ bản đến nâng cao. Cùng nhau chinh phục!</p>
        <div>
            <a href="../test/select_test.php" class="btn" style="background: #3498db; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px;">Thi thử</a>
            <a href="#courses" class="btn" style="background: #2ecc71; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px;">Khám phá khóa học</a>
        </div>
    </section>

    <section class="popular-courses" style="padding: 50px 20px; background: #e0f7fa;">
        <h2 style="text-align: center; font-size: 32px; margin-bottom: 30px;">Khóa học nổi bật</h2>
        <div class="course-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto;">
            <?php while ($row = $popular_courses->fetch_assoc()): ?>
                <div class="course" style="background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: '../images/default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;" loading="lazy">
                    <div style="padding: 20px;">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <a href="course_detail.php?id=<?php echo $row['id']; ?>" style="display: inline-block; background: #3498db; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Học ngay</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="courses" id="courses" style="padding: 50px 20px; background: linear-gradient(135deg, #b3e5fc, #e0f7fa);">
        <h2 style="text-align: center; font-size: 32px; margin-bottom: 30px;">Chọn khóa học</h2>
        <div style="text-align: center; margin-bottom: 20px;">
            <input type="text" id="searchInput" placeholder="Tìm kiếm khóa học..." style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 5px;">
        </div>
        <div class="course-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto;">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="course" style="background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden;">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: '../images/default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;" loading="lazy">
                    <div style="padding: 20px;">
                        <h3 style="font-size: 24px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p style="color: #666;"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p><strong>Giảng viên:</strong> <?php echo htmlspecialchars($row['teacher_name'] ?: 'Chưa cập nhật'); ?></p>
                        <p><strong>Giá:</strong> <?php echo number_format($row['price'] ?: 0, 0, ',', '.') . ' VNĐ'; ?></p>
                        <a href="course_detail.php?id=<?php echo $row['id']; ?>" style="display: inline-block; background: #3498db; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Học ngay</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer style="background: linear-gradient(90deg, #006064, #00acc1); color: #fff; padding: 20px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div>
                    <h3>English Learning</h3>
                    <p>Học tiếng Anh dễ dàng và hiệu quả!</p>
                </div>
                <div>
                    <h3>Liên kết</h3>
                    <a href="../about.php" style="color: #fff; text-decoration: none; display: block; margin: 5px 0;">Giới thiệu</a>
                    <a href="../contact.php" style="color: #fff; text-decoration: none; display: block; margin: 5px 0;">Liên hệ</a>
                    <a href="../policy.php" style="color: #fff; text-decoration: none; display: block; margin: 5px 0;">Chính sách</a>
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
        const scrollTopBtn = document.createElement('button');
        scrollTopBtn.textContent = '↑';
        scrollTopBtn.style.position = 'fixed';
        scrollTopBtn.style.bottom = '20px';
        scrollTopBtn.style.right = '20px';
        scrollTopBtn.style.background = '#3498db';
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