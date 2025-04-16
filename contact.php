<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        $stmt = $conn->prepare("INSERT INTO contacts (user_id, username, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $username, $message);
        $stmt->execute() ? $success = "Tin nhắn đã được gửi!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #80deea);
            margin: 0;
            padding: 0;
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
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #006064;
        }
        .auth-form {
            display: flex;
            flex-direction: column;
        }
        .auth-form textarea {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
        }
        .auth-form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }
        .auth-form button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
            transform: translateY(-3px);
        }
        .success {
            color: green;
            text-align: center;
            margin: 10px 0;
        }
        .error {
            color: red;
            text-align: center;
            margin: 10px 0;
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
            footer div {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="admin/dashboard.php">English Learning</a></div>
        <nav>
            <a href="admin/dashboard.php">Trang chủ</a>
            <a href="test/select_test.php">Thi thử</a>
            <a href="profile.php">Hồ sơ</a>
            <a href="contact.php">Liên hệ</a>
            <a href="auth/logout.php" style="color: #ff5252;">Đăng xuất</a>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>Liên hệ</h1>
            <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
            <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <form method="POST" class="auth-form">
                <textarea name="message" placeholder="Nhập tin nhắn của bạn" required></textarea>
                <button type="submit">Gửi</button>
            </form>
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
                    <a href="about.php">Giới thiệu</a>
                    <a href="contact.php">Liên hệ</a>
                    <a href="policy.php">Chính sách</a>
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

<?php $conn->close(); ?>