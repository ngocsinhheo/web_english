<?php
session_start();
// Sử dụng đường dẫn tương đối từ file profile.php
require_once 'config/config.php';

// Bật hiển thị lỗi để debug (chỉ nên dùng khi phát triển)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php"); // Đường dẫn đúng tới trang login
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Lấy username từ session

// Biến lưu thông báo (đổi tên, đổi mk, lỗi, thành công)
$message = ''; // Dùng chung cho các loại thông báo
$success_message = ''; // Riêng cho thành công để dễ style
$error_message = '';   // Riêng cho lỗi

// --- Xử lý yêu cầu cập nhật ---

// 1. Cập nhật Tên tài khoản
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_username'])) {
    $new_username = trim($_POST['new_username']); // Sử dụng trim thay vì filter deprecated

    if (empty($new_username)) {
        $error_message = "Tên hiển thị không được để trống!";
    } elseif ($new_username === $username) {
        $message = "Tên mới giống tên hiện tại."; // Thông báo thông tin
    } elseif (strlen($new_username) < 3 || strlen($new_username) > 100) {
         $error_message = "Tên hiển thị phải từ 3 đến 100 ký tự.";
    } else {
        // Cập nhật tên mới (Đã bỏ kiểm tra trùng tên tùy chọn)
        $sql_update_user = "UPDATE users SET username = ? WHERE id = ?";
        $stmt_update_user = $conn->prepare($sql_update_user);
        if ($stmt_update_user) {
            $stmt_update_user->bind_param("si", $new_username, $user_id);
            if ($stmt_update_user->execute()) {
                $_SESSION['username'] = $new_username; // Cập nhật session
                $username = $new_username; // Cập nhật biến cho trang hiện tại
                $success_message = "Đã cập nhật tên hiển thị thành công!";
            } else {
                $error_message = "Lỗi khi cập nhật tên.";
                error_log("Lỗi cập nhật username cho user ID $user_id: " . $stmt_update_user->error);
            }
            $stmt_update_user->close();
        } else {
            $error_message = "Lỗi hệ thống khi chuẩn bị cập nhật tên.";
            error_log("Lỗi chuẩn bị SQL cập nhật username: " . $conn->error);
        }
    }
}

// 2. Cập nhật Mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (empty($current_password) || empty($new_password)) {
        $error_message = "Vui lòng nhập đầy đủ mật khẩu hiện tại và mật khẩu mới.";
    } elseif (strlen($new_password) < 1) { // Sửa lại kiểm tra độ dài mật khẩu mới >= 6
         $error_message = "Mật khẩu mới phải có ít nhất 1 ký tự.";
    } else {
        // Lấy mật khẩu hiện tại từ DB
        $stmt_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if ($stmt_pass) {
            $stmt_pass->bind_param("i", $user_id);
            $stmt_pass->execute();
            $result_pass = $stmt_pass->get_result();
            $user_pass_data = $result_pass->fetch_assoc();
            $stmt_pass->close();

            if ($user_pass_data && password_verify($current_password, $user_pass_data['password'])) {
                // Mật khẩu hiện tại đúng, tiến hành cập nhật mật khẩu mới
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt_update_pass) {
                    $stmt_update_pass->bind_param("si", $hashed_password, $user_id);
                    if ($stmt_update_pass->execute()) {
                        $success_message = "Cập nhật mật khẩu thành công!";
                    } else {
                        $error_message = "Lỗi khi cập nhật mật khẩu.";
                        error_log("Lỗi cập nhật password cho user ID $user_id: " . $stmt_update_pass->error);
                    }
                    $stmt_update_pass->close();
                } else {
                     $error_message = "Lỗi hệ thống khi chuẩn bị cập nhật mật khẩu.";
                     error_log("Lỗi chuẩn bị SQL cập nhật password: " . $conn->error);
                }
            } else {
                $error_message = "Mật khẩu hiện tại không đúng!";
            }
        } else {
            $error_message = "Lỗi hệ thống khi kiểm tra mật khẩu.";
            error_log("Lỗi chuẩn bị SQL lấy password: " . $conn->error);
        }
    }
}

// --- Lấy dữ liệu hiển thị ---

// 1. Thông tin cơ bản của người dùng (Email, Ngày tham gia)
$sql_user = "SELECT email, created_at FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$user_info = null;
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_info = $result_user->fetch_assoc();
    $stmt_user->close();
} else {
    error_log("Lỗi chuẩn bị SQL lấy thông tin user: " . $conn->error);
    $error_message .= " Không thể tải thông tin tài khoản.";
}
if (!$user_info) { // Xử lý nếu không tìm thấy user
    $user_info = ['email' => 'Không thể tải', 'created_at' => date('Y-m-d H:i:s')];
}

// 2. Lịch sử làm bài test sub_lesson
$sub_lesson_test_history = [];
$sql_sub_history = "SELECT sl.title AS lesson_title, sltr.score, sltr.total_questions, sltr.completed_at FROM sub_lesson_test_results sltr JOIN sub_lessons sl ON sltr.sub_lesson_id = sl.id WHERE sltr.user_id = ? ORDER BY sltr.completed_at DESC";
$stmt_sub_history = $conn->prepare($sql_sub_history);
if ($stmt_sub_history) {
    $stmt_sub_history->bind_param("i", $user_id);
    if ($stmt_sub_history->execute()) {
        $result_sub_history = $stmt_sub_history->get_result();
        while ($row = $result_sub_history->fetch_assoc()) { $sub_lesson_test_history[] = $row; }
    } else { error_log("Lỗi thực thi SQL lấy lịch sử sub_lesson test: " . $stmt_sub_history->error); }
    $stmt_sub_history->close();
} else { error_log("Lỗi chuẩn bị SQL lấy lịch sử sub_lesson test: " . $conn->error); }

// 3. Lịch sử làm bài test tổng quát (Từ bảng test_results)
$general_test_history = [];
// LƯU Ý: Cột `test_id` trong bảng `test_results` là VARCHAR. Không có bảng `tests` để lấy tên bài test.
// Sẽ hiển thị test_id trực tiếp. Bạn nên tạo bảng `tests` và JOIN để hiển thị tên dễ hiểu hơn.
$sql_general_history = "SELECT test_id, score, total_questions, completed_at FROM test_results WHERE user_id = ? ORDER BY completed_at DESC";
$stmt_general_history = $conn->prepare($sql_general_history);
if ($stmt_general_history) {
    $stmt_general_history->bind_param("i", $user_id);
    if ($stmt_general_history->execute()) {
        $result_general_history = $stmt_general_history->get_result();
        while ($row = $result_general_history->fetch_assoc()) { $general_test_history[] = $row; }
    } else { error_log("Lỗi thực thi SQL lấy lịch sử general test: " . $stmt_general_history->error); }
    $stmt_general_history->close();
} else { error_log("Lỗi chuẩn bị SQL lấy lịch sử general test: " . $conn->error); }


// 4. Lịch sử liên hệ/phản hồi
$contact_history = [];
$sql_contact = "SELECT message, status, reply, created_at, replied_at FROM contacts WHERE user_id = ? ORDER BY created_at DESC";
$stmt_contact = $conn->prepare($sql_contact);
if ($stmt_contact) {
    $stmt_contact->bind_param("i", $user_id);
    if ($stmt_contact->execute()) {
        $result_contact = $stmt_contact->get_result();
        while ($row = $result_contact->fetch_assoc()) { $contact_history[] = $row; }
    } else { error_log("Lỗi thực thi SQL lấy lịch sử liên hệ: " . $stmt_contact->error); }
    $stmt_contact->close();
} else { error_log("Lỗi chuẩn bị SQL lấy lịch sử liên hệ: " . $conn->error); }

$conn->close(); // Đóng kết nối DB *TRƯỚC KHI* bắt đầu HTML
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS cơ bản - Bạn có thể tùy chỉnh hoặc đưa vào file CSS riêng */
        body {
            font-family: Arial, sans-serif; /* Giống code mẫu */
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2); /* Giữ gradient nền */
            color: #333;
            margin: 0;
            padding: 0;
            display: flex; /* Sử dụng flexbox cho layout chính */
            flex-direction: column; /* Xếp các phần tử theo cột */
            min-height: 100vh; /* Chiều cao tối thiểu là 100% viewport */
        }
        .container {
            padding: 25px;
            max-width: 850px; /* Rộng hơn chút */
            margin: 30px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            flex-grow: 1; /* Cho phép container chính giãn nở */
        }
        h1 {
            text-align: center;
            color: #006064; /* Giữ màu tiêu đề */
            margin-bottom: 30px;
        }

        /* Thông báo */
        .message-container { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .info-message { background-color: #cce5ff; color: #004085; border-color: #b8daff; }

        /* Sections */
        .profile-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee; /* Phân cách rõ hơn */
        }
        .profile-section:last-child { border-bottom: none; margin-bottom: 0; }
        .profile-section h2 {
            color: #00796b; /* Màu khác cho sub-heading */
            font-size: 1.3em; /* To hơn chút */
            margin-bottom: 20px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e0f2f1;
        }

        /* Thông tin tài khoản */
        .profile-info p { margin-bottom: 10px; line-height: 1.6; }
        .profile-info strong { display: inline-block; width: 120px; color: #555; font-weight: bold;}

        /* Form (auth-form style từ code mẫu) */
        .auth-form { display: flex; flex-direction: column; }
        .auth-form label { margin-bottom: 5px; font-weight: bold; color: #555; }
        .auth-form input[type="text"],
        .auth-form input[type="password"] {
            padding: 12px; /* Tăng padding */
            margin-bottom: 15px; /* Khoảng cách lớn hơn */
            border: 1px solid #ccc; /* Rõ hơn */
            border-radius: 4px;
            font-size: 1em;
        }
        .auth-form button {
            width: auto; /* Không full width */
            align-self: flex-start; /* Căn trái */
            padding: 10px 25px; /* Padding cân đối */
            background: linear-gradient(90deg, #007bff, #0056b3); /* Gradient button */
            color: #fff;
            border: none;
            border-radius: 25px; /* Bo tròn */
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
        }
        .auth-form button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
            transform: translateY(-2px); /* Hiệu ứng hover */
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
        }

        /* Bảng lịch sử */
        .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .history-table th, .history-table td { border: 1px solid #ddd; padding: 10px 12px; text-align: left; vertical-align: middle; }
        .history-table th { background-color: #e9ecef; color: #495057; font-weight: 600; white-space: nowrap; }
        .history-table tr:nth-child(even) { background-color: #f8f9fa; }
        .history-table tr:hover { background-color: #e2e6ea; }
        .history-table td .status { font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; text-transform: uppercase; color: white; }
        .history-table td .status.pending { background-color: #ffc107; color: #333; }
        .history-table td .status.replied { background-color: #28a745; }
        .history-table td .reply-content { background-color: #f1f3f5; padding: 8px; margin-top: 5px; border-left: 3px solid #007bff; font-style: italic; color: #495057; font-size: 0.95em; }
        .no-history { text-align: center; color: #6c757d; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border: 1px dashed #ced4da; border-radius: 4px; }

        /* Link button */
        .link-button {
            display: inline-block;
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }
        .link-button:hover { background-color: #218838; }
        .center-button { text-align: center; margin-top: 20px; }

         /* Header & Footer Styles */
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
            z-index: 1000;
            flex-shrink: 0; /* Ngăn header co lại */
        }
        header .logo {
             font-size: 24px;
             font-weight: bold;
        }
        header .logo a {
            color: #fff;
            text-decoration: none;
        }
        header nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s ease;
        }
        header nav a:hover {
            color: #e0f7fa;
        }
        header nav a[href="auth/logout.php"] { /* Màu khác cho logout */
            color: #ffdddd;
        }
         header nav a[href="auth/logout.php"]:hover {
            color: #ffffff;
         }
        footer {
            background: linear-gradient(90deg, #006064, #00acc1);
            color: #fff;
            padding: 20px;
            text-align: center;
            margin-top: 40px; /* Đẩy footer xuống */
            flex-shrink: 0; /* Ngăn footer co lại */
        }
        footer a { color: #fff; text-decoration: none; margin: 0 10px; }
        footer a:hover { text-decoration: underline; }
        footer .footer-content { /* Sử dụng class để target div chứa nội dung footer */
            max-width: 850px; /* Giữ rộng tương đồng container chính */
            margin: 0 auto;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-bottom: 15px;
            text-align: left;
        }
        footer .footer-section { /* Thêm class cho từng cột footer */
            flex: 1; /* Chia đều không gian */
            min-width: 150px; /* Chiều rộng tối thiểu để không bị bóp méo */
            margin: 10px;
        }
         footer h3 { margin-bottom: 10px; font-size: 1.1em;}
         footer p { margin: 5px 0; font-size: 0.95em;}
         footer .social-icons a {
             color: #fff;
             font-size: 1.2em;
             margin-right: 10px;
         }

    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="index.php">English Learning</a></div>
        <nav>
            <a href="./admin/dashboard.php">Trang chủ</a>
            <a href="test/select_test.php">Thi thử</a>
            <a href="contact.php">Liên hệ</a>
            <?php if (isset($_SESSION['username'])): ?>
                <span style="color: #e0f7fa; margin-left: 15px; font-weight: bold;"><i class="fas fa-user-circle"></i> Xin chào, <span id="header-username"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>!</span>
            <?php endif; ?>
            <a href="auth/logout.php">Đăng xuất</a>
        </nav>
    </header>

    <div class="container">
        <h1>Hồ sơ cá nhân</h1>

        <?php if (!empty($success_message)): ?>
            <div class="message-container success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message-container error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): // Thông báo thông tin chung ?>
             <div class="message-container info-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="profile-section profile-info">
            <h2>Chi tiết tài khoản</h2>
            <p><strong>Tên hiển thị:</strong> <span id="display-username"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Ngày tham gia:</strong> <?php echo isset($user_info['created_at']) ? date('d/m/Y H:i', strtotime($user_info['created_at'])) : 'N/A'; ?></p>
        </div>

        <div class="profile-section">
            <h2>Đổi tên hiển thị</h2>
            <form method="POST" action="profile.php" class="auth-form">
                 <label for="new_username">Tên hiển thị mới:</label>
                <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required minlength="3" maxlength="100">
                <button type="submit" name="update_username">Cập nhật tên</button>
            </form>
        </div>

         <div class="profile-section">
            <h2>Đổi mật khẩu</h2>
            <form method="POST" action="profile.php" class="auth-form">
                 <label for="current_password">Mật khẩu hiện tại:</label>
                <input type="password" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại" required>
                 <label for="new_password">Mật khẩu mới:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới (ít nhất 1 ký tự)" required minlength="1">
                <button type="submit" name="update_password">Cập nhật mật khẩu</button>
            </form>
        </div>


        <div class="profile-section">
            <h2>Lịch sử liên hệ & Phản hồi</h2>
            <?php if (!empty($contact_history)): ?>
                 <div style="overflow-x:auto;">
                    <table class="history-table">
                         <thead>
                            <tr><th>Nội dung gửi</th><th>Trạng thái</th><th>Ngày gửi</th><th>Phản hồi</th><th>Ngày phản hồi</th></tr>
                         </thead>
                         <tbody>
                            <?php foreach ($contact_history as $contact): ?>
                                <tr>
                                     <td><?php echo nl2br(htmlspecialchars($contact['message'] ?? '', ENT_QUOTES, 'UTF-8')); ?></td>
                                     <td>
                                         <span class="status <?php echo htmlspecialchars($contact['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>">
                                             <?php echo ($contact['status'] === 'replied') ? 'Đã phản hồi' : 'Đang chờ'; ?>
                                         </span>
                                     </td>
                                     <td><?php echo isset($contact['created_at']) ? date('d/m/Y H:i', strtotime($contact['created_at'])) : 'N/A'; ?></td>
                                     <td>
                                         <?php if (!empty($contact['reply'])): ?>
                                             <div class="reply-content">
                                                 <?php echo nl2br(htmlspecialchars($contact['reply'], ENT_QUOTES, 'UTF-8')); ?>
                                             </div>
                                         <?php else: ?>
                                             <i>Chưa có</i>
                                         <?php endif; ?>
                                     </td>
                                     <td><?php echo isset($contact['replied_at']) ? date('d/m/Y H:i', strtotime($contact['replied_at'])) : ''; ?></td>
                                </tr>
                            <?php endforeach; ?>
                         </tbody>
                    </table>
                 </div>
            <?php else: ?>
                <p class="no-history">Chưa có lịch sử liên hệ nào.</p>
            <?php endif; ?>
             <div class="center-button">
                 <a href="contact.php" class="link-button">Gửi liên hệ mới</a>
             </div>
        </div>

    </div>

    <footer>
        
        <p>&copy; <?php echo date('Y'); ?> English Learning. All rights reserved.</p>
    </footer>


    <script>
         // Cập nhật tên hiển thị trên header nếu thành công (Mã này chạy ngay khi script được tải)
         <?php if (strpos($success_message, 'tên hiển thị') !== false): ?>
             // Giả sử header có span với id="header-username"
             const headerUsernameSpan = document.getElementById('header-username');
             if (headerUsernameSpan) {
                 // Sử dụng json_encode để nhúng chuỗi PHP vào JavaScript an toàn
                 headerUsernameSpan.textContent = <?php echo json_encode($username); ?>;
             }
             // Cập nhật tiêu đề trang
              document.title = 'Hồ sơ cá nhân - ' + <?php echo json_encode($username); ?>;
              // Cập nhật span chào mừng trong trang
              const welcomeUsernameSpan = document.getElementById('display-username');
              if(welcomeUsernameSpan) {
                  // Sử dụng json_encode để nhúng chuỗi PHP vào JavaScript an toàn
                  welcomeUsernameSpan.textContent = <?php echo json_encode($username); ?>;
              }
         <?php endif; ?>

        // Gói mã xử lý DOM vào sự kiện DOMContentLoaded để đảm bảo trang đã sẵn sàng
        document.addEventListener('DOMContentLoaded', () => {
            // Nút quay lại đầu trang (Giữ nguyên từ code mẫu)
            const scrollTopBtn = document.createElement('button');
            scrollTopBtn.innerHTML = '&#8593;'; // Mũi tên lên
            scrollTopBtn.style.position = 'fixed';
            scrollTopBtn.style.right = '30px';
            scrollTopBtn.style.background = '#007bff'; // Màu khác
            scrollTopBtn.style.color = '#fff';
            scrollTopBtn.style.padding = '8px 12px'; // Điều chỉnh padding
            scrollTopBtn.style.border = 'none';
            scrollTopBtn.style.borderRadius = '50%';
            scrollTopBtn.style.cursor = 'pointer';
            scrollTopBtn.style.display = 'none'; // Ẩn ban đầu
            scrollTopBtn.style.fontSize = '18px';
            scrollTopBtn.style.zIndex = '999';
            scrollTopBtn.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';

            // Chỉ thêm nút vào body khi DOM đã tải xong
            document.body.appendChild(scrollTopBtn);

            window.addEventListener('scroll', () => {
                scrollTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none'; // Hiện khi cuộn xuống 300px
            });

            scrollTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            console.log("Script DOMContentLoaded đã chạy.");
        });

         console.log("Trang profile đã tải xong (script tag processed).");
     </script>
</body>
</html>