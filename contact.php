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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">TOEIC Learning</div>
        <div class="auth">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="admin/dashboard.php">Dashboard</a>
            <a href="auth/logout.php">Đăng xuất</a>
        </div>
    </header>
    <div class="container">
        <h1>Liên hệ</h1>
        <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST" class="auth-form">
            <textarea name="message" placeholder="Nhập tin nhắn của bạn" required></textarea>
            <button type="submit">Gửi</button>
        </form>
    </div>
    <footer>&copy; 2025 TOEIC Learning</footer>
</body>
</html>
<?php $conn->close(); ?>