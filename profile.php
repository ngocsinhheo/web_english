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

</head>
<style>body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Header */
/* --- Header --- */
header {
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: #fff;
    padding: 20px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 28px;
    font-weight: bold;
    text-transform: uppercase;
}

.auth a {
    color: #fff;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 500;
    position: relative;
}

.auth a::after {
    content: '';
    display: block;
    width: 0;
    height: 2px;
    background: #ffcc00;
    transition: width 0.3s ease;
    position: absolute;
    bottom: -4px;
    left: 0;
}

.auth a:hover::after {
    width: 100%;
}


.container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
}

h1 {
    text-align: center;
    color: #333;
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
    text-align: center;
    padding: 10px;
    background-color: #333;
    color: #fff;
}
</style>
<body>
    <header>
        <div class="logo">TOEIC Learning</div>
        <div class="auth">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../web_english/admin/dashboard.php">Dashboard</a>
            <a href="../web_english/auth/logout.php">Đăng xuất</a>
        </div>
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
    <footer>© 2025 TOEIC Learning</footer>
</body>
</html>

<?php
$stmt->close();
$result->close();
$conn->close();
?>
