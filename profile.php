<?php
session_start();
require_once '../web_english/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Cập nhật tên tài khoản
if (isset($_POST['update_username'])) {
    $new_username = filter_var($_POST['new_username'], FILTER_SANITIZE_STRING);
    if (!empty($new_username)) {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $new_username, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Cập nhật tên tài khoản thành công!";
            $_SESSION['username'] = $new_username;
        } else {
            $error = "Lỗi: " . $conn->error;
        }
    } else {
        $error = "Tên tài khoản không được để trống!";
    }
}

// Cập nhật mật khẩu
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (password_verify($current_password, $result['password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Cập nhật mật khẩu thành công!";
        } else {
            $error = "Lỗi: " . $conn->error;
        }
    } else {
        $error = "Mật khẩu hiện tại không đúng!";
    }
}

// Lấy lịch sử bài thi
$result = $conn->prepare("SELECT test_id, score, total_questions, completed_at FROM test_results WHERE user_id = ? ORDER BY completed_at DESC");
$result->bind_param("i", $_SESSION['user_id']);
$result->execute();
$test_history = $result->get_result();
// Lấy lịch sử tin nhắn liên hệ
$contact_stmt = $conn->prepare("SELECT id, message, status, reply, created_at, replied_at FROM contacts WHERE user_id = ? ORDER BY created_at DESC");
$contact_stmt->bind_param("i", $_SESSION['user_id']);
$contact_stmt->execute();
$contact_history = $contact_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #80deea); /* Gradient nền toàn trang */
            margin: 0;
            padding: 0;
        }

        /* Header */
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

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-section h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
        }

        .auth-form input {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
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
        <div class="logo"><a href="../web_english/admin/dashboard.php">English Learning</a></div>
        <nav>
            <a href="../web_english/admin/dashboard.php">Trang chủ</a>
            <a href="../web_english/test/select_test.php">Thi thử</a>
            <a href="../web_english/profile.php">Hồ sơ</a>
            <a href="../web_english/contact.php">Liên hệ</a>
            <a href="../web_english/auth/logout.php" style="color: #ff5252;">Đăng xuất</a>
        </nav>
    </header>

    <div class="container">
        <h1>Hồ sơ cá nhân</h1>
        <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

        <!-- Đổi tên tài khoản -->
        <div class="profile-section">
            <h2>Đổi tên tài khoản</h2>
            <form method="POST" class="auth-form">
                <input type="text" name="new_username" placeholder="Tên mới" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <button type="submit" name="update_username">Cập nhật</button>
            </form>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="profile-section">
            <h2>Đổi mật khẩu</h2>
            <form method="POST" class="auth-form">
                <input type="password" name="current_password" placeholder="Mật khẩu hiện tại" required>
                <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
                <button type="submit" name="update_password">Cập nhật</button>
            </form>
        </div>

        <!-- Lịch sử tin nhắn liên hệ -->
        <div class="profile-section">
            <h2>Lịch sử tin nhắn liên hệ</h2>
            <?php if ($contact_history->num_rows > 0): ?>
                <table>
                    <tr><th>Tin nhắn</th><th>Trạng thái</th><th>Phản hồi</th></tr>
                    <?php while ($contact = $contact_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contact['message']); ?></td>
                            <td><?php echo $contact['status'] === 'pending' ? 'Chờ xử lý' : 'Đã phản hồi'; ?></td>
                            <td><?php echo $contact['reply'] ? htmlspecialchars($contact['reply']) : 'Chưa có'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>Chưa có tin nhắn nào.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div>
                    <h3>English Learning</h3>
                    <p>Học tiếng Anh dễ dàng và hiệu quả!</p>
                </div>
                <div>
                    <h3>Liên kết</h3>
                    <a href="../web_english/about.php">Giới thiệu</a>
                    <a href="../web_english/contact.php">Liên hệ</a>
                    <a href="../web_english/policy.php">Chính sách</a>
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
        // Nút quay lại đầu trang
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

<?php
$stmt->close();
$result->close();
$conn->close();
?>