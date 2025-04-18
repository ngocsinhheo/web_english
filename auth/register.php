<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) throw new Exception("Email đã tồn tại!");

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute() ? $success = "Đăng ký thành công! <a href='login.php'>Đăng nhập</a>" : throw new Exception($conn->error);
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
    <meta name="description" content="Đăng ký tài khoản tại English Learning để học tiếng Anh từ cơ bản đến nâng cao.">
    <title>Đăng ký - English Learning</title>
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
            background: url('https://images.unsplash.com/photo-1503676260728-1f56a6e04b65?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 172, 193, 0.5); /* #00acc1 với opacity 0.5 */
            z-index: 1;
        }

        .auth-form {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 0.8s ease-in;
            position: relative;
            z-index: 2;
        }

        .auth-form h2 {
            font-size: 32px;
            font-weight: 700;
            color: #006064;
            margin-bottom: 20px;
        }

        .auth-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .auth-form input:focus {
            border-color: #00acc1;
            outline: none;
        }

        .auth-form button {
            width: 100%;
            padding: 12px;
            background: #00acc1;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .auth-form button:hover {
            background: #00838f;
            transform: translateY(-2px);
        }

        .register-link {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .register-link a {
            color: #00acc1;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }

        .success {
            color: #2ecc71;
            font-size: 14px;
            margin-top: 10px;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            width: 80px;
        }

        @media (max-width: 480px) {
            .auth-form {
                padding: 20px;
                max-width: 90%;
            }

            .auth-form h2 {
                font-size: 28px;
            }

            .auth-form input, .auth-form button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" class="auth-form">

        <h2>Đăng ký</h2>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng ký</button>
        <div class="register-link">Đã có tài khoản? <a href="login.php">Đăng nhập</a></div>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
</body>
</html>