<?php
session_start();
require_once '../config/config.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Hàm xử lý upload file
function handleFileUpload($file, $targetDir) {
    $fileName = basename($file["name"]);
    $targetPath = $targetDir . $fileName;

    // Kiểm tra loại tệp
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception("Loại tệp không được hỗ trợ!");
    }

    // Kiểm tra kích thước tệp (tối đa 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Tệp quá lớn! Tối đa 5MB.");
    }

    return move_uploaded_file($file["tmp_name"], $targetPath) ? $targetPath : false;
}

// Xử lý phản hồi tin nhắn
if (isset($_POST['reply_message'])) {
    try {
        $contact_id = filter_var($_POST['contact_id'], FILTER_SANITIZE_NUMBER_INT);
        $reply = filter_var($_POST['reply'], FILTER_SANITIZE_STRING);

        $stmt = $conn->prepare("UPDATE contacts SET reply = ?, status = 'replied', replied_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $contact_id);
        $stmt->execute() ? $success = "Phản hồi thành công!" : throw new Exception($conn->error);
        $stmt->close();

        // Tải lại trang để cập nhật giao diện
        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    try {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute() ? $success = "Thêm người dùng thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user'])) {
    try {
        $user_id = filter_var($_GET['delete_user'], FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute() ? $success = "Xóa người dùng thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm khóa học
if (isset($_POST['add_course'])) {
    try {
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $teacher_name = filter_var($_POST['teacher_name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $target_dir = "../uploads/";
        
        $image = handleFileUpload($_FILES["image"], $target_dir);
        $content_file = handleFileUpload($_FILES["content_file"], $target_dir);
        $video_file = handleFileUpload($_FILES["video_file"], $target_dir);

        if (!$image || !$content_file || !$video_file) throw new Exception("Lỗi upload file!");

        $stmt = $conn->prepare("INSERT INTO courses (title, description, price, teacher_name, image, content_file, video_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $title, $description, $price, $teacher_name, $image, $content_file, $video_file);
        $stmt->execute() ? $success = "Thêm khóa học thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm bài học con
if (isset($_POST['add_sub_lesson'])) {
    try {
        $course_id = filter_var($_POST['course_id'], FILTER_SANITIZE_NUMBER_INT);
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $order_number = filter_var($_POST['order_number'], FILTER_SANITIZE_NUMBER_INT);
        $target_dir = "../uploads/";

        $video_file = handleFileUpload($_FILES["video_file"], $target_dir);
        $content_file = handleFileUpload($_FILES["content_file"], $target_dir);

        if (!$video_file || !$content_file) throw new Exception("Lỗi upload file!");

        $stmt = $conn->prepare("INSERT INTO sub_lessons (course_id, title, description, video_file, content_file, order_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $course_id, $title, $description, $video_file, $content_file, $order_number);
        $stmt->execute() ? $success = "Thêm bài học con thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm câu hỏi kiểm tra
if (isset($_POST['add_test_question'])) {
    try {
        $sub_lesson_id = filter_var($_POST['sub_lesson_id'], FILTER_SANITIZE_NUMBER_INT);
        $question_text = filter_var($_POST['question_text'], FILTER_SANITIZE_STRING);
        $option_a = filter_var($_POST['option_a'], FILTER_SANITIZE_STRING);
        $option_b = filter_var($_POST['option_b'], FILTER_SANITIZE_STRING);
        $option_c = filter_var($_POST['option_c'], FILTER_SANITIZE_STRING);
        $option_d = filter_var($_POST['option_d'], FILTER_SANITIZE_STRING);
        $correct_answer = filter_var($_POST['correct_answer'], FILTER_SANITIZE_STRING);

        $stmt = $conn->prepare("INSERT INTO sub_lesson_tests (sub_lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $sub_lesson_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);
        $stmt->execute() ? $success = "Thêm câu hỏi kiểm tra thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa khóa học
if (isset($_GET['delete_course'])) {
    try {
        $course_id = filter_var($_GET['delete_course'], FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute() ? $success = "Xóa khóa học thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa bài học con
if (isset($_GET['delete_sub_lesson'])) {
    try {
        $sub_lesson_id = filter_var($_GET['delete_sub_lesson'], FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("DELETE FROM sub_lessons WHERE id = ?");
        $stmt->bind_param("i", $sub_lesson_id);
        $stmt->execute() ? $success = "Xóa bài học con thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa câu hỏi kiểm tra
if (isset($_GET['delete_test_question'])) {
    try {
        $question_id = filter_var($_GET['delete_test_question'], FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("DELETE FROM sub_lessons WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute() ? $success = "Xóa câu hỏi kiểm tra thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Truy vấn dữ liệu
$result_users = $conn->query("SELECT id, username, email FROM users WHERE role != 'admin'");
$total_users = $result_users->num_rows ?? 0;

$result_courses = $conn->query("SELECT id, title, description, price, teacher_name, image, content_file, video_file FROM courses");
$popular_courses = [];
while ($course = $result_courses->fetch_assoc()) {
    $popular_courses[$course['title']] = rand(10, 100); // Giả lập lượt thích
}
$result_courses->data_seek(0);

$result_contacts = $conn->query("SELECT id, user_id, username, message, status, reply, created_at, replied_at FROM contacts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TOEIC Learning</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar ul li:hover {
            background-color: #34495e;
        }

        .sidebar ul li.active {
            background-color: #3498db;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: block;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #fff;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .main-content h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        form {
            margin-bottom: 30px;
        }

        form input, form textarea, form select {
            padding: 10px;
            margin: 5px 0;
            width: calc(100% - 22px);
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }

        table td img {
            max-width: 100px;
            border-radius: 5px;
        }

        table td a {
            color: #3498db;
            text-decoration: none;
        }

        table td a.delete-btn {
            color: #dc3545;
        }

        table td a:hover {
            text-decoration: underline;
        }

        .chart-container {
            max-width: 600px;
            margin: 20px 0;
        }

        .success {
            color: #28a745;
            padding: 10px;
            background-color: #e9f7ef;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            color: #dc3545;
            padding: 10px;
            background-color: #f8e1e1;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .reply-btn {
            padding: 5px 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .reply-btn:hover {
            background-color: #2980b9;
        }

        /* CSS cho accordion */
        .accordion-header {
            background: #f9fbfd;
            padding: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .accordion-header span {
            font-size: 18px;
            color: #3498db;
        }

        .accordion-content {
            display: none;
            padding: 15px;
            background: #fff;
        }

        .accordion-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li onclick="showSection('dashboard')" class="active">Dashboard</li>
                <li onclick="showSection('users')">Quản Lý Người Dùng</li>
                <li onclick="showSection('courses')">Quản Lý Khóa Học</li>
                <li onclick="showSection('contacts')">Quản Lý Tin Nhắn</li>
                <li><a href="../auth/logout.php">Đăng xuất</a></li>
            </ul>
        </div>

        <div class="main-content">
            <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
            <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

            <div id="dashboard" class="content-section active">
                <h1>Dashboard</h1>
                <div class="chart-container">
                    <canvas id="dashboardUserChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="popularCoursesChart"></canvas>
                </div>
            </div>

            <div id="users" class="content-section">
                <h1>Quản Lý Người Dùng</h1>
                <form method="POST">
                    <input type="text" name="username" placeholder="Tên người dùng" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <button type="submit" name="add_user">Thêm</button>
                </form>
                <table>
                    <tr><th>ID</th><th>Tên</th><th>Email</th><th>Hành động</th></tr>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><a class="delete-btn" href="?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div id="courses" class="content-section">
                <h1>Quản Lý Khóa Học</h1>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Tên khóa học" required>
                    <textarea name="description" placeholder="Mô tả" required></textarea>
                    <input type="text" name="teacher_name" placeholder="Giáo viên" required>
                    <input type="number" name="price" placeholder="Giá" required>
                    <input type="file" name="image" accept="image/*" required>
                    <input type="file" name="content_file" accept=".pdf" required>
                    <input type="file" name="video_file" accept="video/*" required>
                    <button type="submit" name="add_course">Thêm</button>
                </form>
                <table>
                    <tr><th>ID</th><th>Tiêu đề</th><th>Giá</th><th>Giáo viên</th><th>Hình ảnh</th><th>Tài liệu</th><th>Video</th><th>Hành động</th></tr>
                    <?php while ($course = $result_courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td>
                                <div class="accordion-header">
                                    <span><?php echo htmlspecialchars($course['title']); ?></span>
                                    <span>▼</span>
                                </div>
                                <div class="accordion-content">
                                    <!-- Hiển thị bài học con -->
                                    <h3 style="margin: 10px 0;">Bài học con</h3>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <input type="text" name="title" placeholder="Tiêu đề bài học" required>
                                        <textarea name="description" placeholder="Mô tả bài học" required></textarea>
                                        <input type="number" name="order_number" placeholder="Thứ tự bài học" required>
                                        <input type="file" name="video_file" accept="video/*" required>
                                        <input type="file" name="content_file" accept=".pdf" required>
                                        <button type="submit" name="add_sub_lesson">Thêm bài học con</button>
                                    </form>
                                    <table style="margin-top: 10px;">
                                        <tr><th>ID</th><th>Tiêu đề</th><th>Mô tả</th><th>Thứ tự</th><th>Tài liệu</th><th>Video</th><th>Hành động</th></tr>
                                        <?php
                                        $sub_lessons = $conn->query("SELECT * FROM sub_lessons WHERE course_id = {$course['id']} ORDER BY order_number ASC");
                                        while ($sub_lesson = $sub_lessons->fetch_assoc()):
                                        ?>
                                            <tr>
                                                <td><?php echo $sub_lesson['id']; ?></td>
                                                <td>
                                                    <div class="accordion-header">
                                                        <span><?php echo htmlspecialchars($sub_lesson['title']); ?></span>
                                                        <span>▼</span>
                                                    </div>
                                                    <div class="accordion-content">
                                                        <!-- Form thêm câu hỏi kiểm tra -->
                                                        <h4 style="margin: 10px 0;">Thêm câu hỏi kiểm tra</h4>
                                                        <form method="POST">
                                                            <input type="hidden" name="sub_lesson_id" value="<?php echo $sub_lesson['id']; ?>">
                                                            <textarea name="question_text" placeholder="Câu hỏi" required></textarea>
                                                            <input type="text" name="option_a" placeholder="Lựa chọn A" required>
                                                            <input type="text" name="option_b" placeholder="Lựa chọn B" required>
                                                            <input type="text" name="option_c" placeholder="Lựa chọn C" required>
                                                            <input type="text" name="option_d" placeholder="Lựa chọn D" required>
                                                            <select name="correct_answer" required>
                                                                <option value="A">A</option>
                                                                <option value="B">B</option>
                                                                <option value="C">C</option>
                                                                <option value="D">D</option>
                                                            </select>
                                                            <button type="submit" name="add_test_question">Thêm câu hỏi</button>
                                                        </form>
                                                        <!-- Danh sách câu hỏi kiểm tra -->
                                                        <h4 style="margin: 10px 0;">Danh sách câu hỏi kiểm tra</h4>
                                                        <table style="margin-top: 10px;">
                                                            <tr><th>ID</th><th>Câu hỏi</th><th>Lựa chọn A</th><th>Lựa chọn B</th><th>Lựa chọn C</th><th>Lựa chọn D</th><th>Đáp án đúng</th><th>Hành động</th></tr>
                                                            <?php
                                                            $questions = $conn->query("SELECT * FROM sub_lesson_tests WHERE sub_lesson_id = {$sub_lesson['id']}");
                                                            while ($question = $questions->fetch_assoc()):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $question['id']; ?></td>
                                                                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                                                    <td><?php echo htmlspecialchars($question['option_a']); ?></td>
                                                                    <td><?php echo htmlspecialchars($question['option_b']); ?></td>
                                                                    <td><?php echo htmlspecialchars($question['option_c']); ?></td>
                                                                    <td><?php echo htmlspecialchars($question['option_d']); ?></td>
                                                                    <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                                                                    <td>
                                                                        <a class="delete-btn" href="?delete_test_question=<?php echo $question['id']; ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </table>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($sub_lesson['description']); ?></td>
                                                <td><?php echo $sub_lesson['order_number']; ?></td>
                                                <td><a href="<?php echo $sub_lesson['content_file']; ?>" target="_blank">Xem</a></td>
                                                <td><a href="<?php echo $sub_lesson['video_file']; ?>" target="_blank">Xem</a></td>
                                                <td>
                                                    <a class="delete-btn" href="?delete_sub_lesson=<?php echo $sub_lesson['id']; ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </table>
                                </div>
                            </td>
                            <td><?php echo number_format($course['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                            <td>
                                <?php if (file_exists($course['image'])): ?>
                                    <img src="<?php echo $course['image']; ?>" alt="Ảnh">
                                <?php else: ?>
                                    <span>Hình ảnh không tồn tại</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?php echo $course['content_file']; ?>" target="_blank">Xem</a></td>
                            <td><a href="<?php echo $course['video_file']; ?>" target="_blank">Xem</a></td>
                            <td>
                                <a href="course_detail.php?id=<?php echo $course['id']; ?>">Xem chi tiết</a>
                                <a class="delete-btn" href="?delete_course=<?php echo $course['id']; ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <?php $result_courses->close(); ?>
            </div>

            <div id="contacts" class="content-section">
                <h1>Quản Lý Tin Nhắn Liên Hệ</h1>
                <table>
                    <tr><th>ID</th><th>Người gửi</th><th>Tin nhắn</th><th>Trạng thái</th><th>Phản hồi</th><th>Thời gian gửi</th><th>Hành động</th></tr>
                    <?php while ($contact = $result_contacts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $contact['id']; ?></td>
                            <td><?php echo htmlspecialchars($contact['username']); ?></td>
                            <td><?php echo htmlspecialchars($contact['message']); ?></td>
                            <td><?php echo $contact['status'] === 'pending' ? 'Chờ xử lý' : 'Đã phản hồi'; ?></td>
                            <td><?php echo $contact['reply'] ? htmlspecialchars($contact['reply']) : 'Chưa có'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                            <td>
                                <?php if ($contact['status'] === 'pending'): ?>
                                    <button class="reply-btn" data-id="<?php echo $contact['id']; ?>" data-message="<?php echo htmlspecialchars(json_encode($contact['message'], JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8'); ?>">Phản hồi</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>

                <div id="replyForm" style="display: none; margin-top: 20px;">
                    <form method="POST">
                        <input type="hidden" name="contact_id" id="contactId">
                        <p><strong>Tin nhắn:</strong> <span id="messagePreview"></span></p>
                        <textarea name="reply" placeholder="Nhập phản hồi của bạn" required></textarea>
                        <button type="submit" name="reply_message">Gửi phản hồi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.sidebar li').forEach(l => l.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            document.querySelector(`li[onclick="showSection('${sectionId}')"]`).classList.add('active');
        }

        function showReplyForm(id, message) {
            console.log('showReplyForm called with id:', id, 'message:', message); // Debug
            const replyForm = document.getElementById('replyForm');
            if (replyForm) {
                replyForm.style.display = 'block';
                document.getElementById('contactId').value = id;
                document.getElementById('messagePreview').textContent = message;
            } else {
                console.error('replyForm element not found'); // Debug
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Gán sự kiện cho các nút "Phản hồi"
            document.querySelectorAll('.reply-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const message = JSON.parse(button.getAttribute('data-message'));
                    showReplyForm(id, message);
                });
            });

            // Gán sự kiện cho tất cả các accordion (cả khóa học và bài học con)
            document.querySelectorAll('.accordion-header').forEach(header => {
                header.addEventListener('click', () => {
                    const content = header.nextElementSibling;
                    const arrow = header.querySelector('span:last-child');
                    content.classList.toggle('active');
                    arrow.textContent = content.classList.contains('active') ? '▲' : '▼';
                });
            });

            // Biểu đồ người dùng
            new Chart(document.getElementById('dashboardUserChart'), {
                type: 'bar',
                data: {
                    labels: ['Tổng người dùng', 'Người dùng mới', 'Hoạt động'],
                    datasets: [{
                        label: 'Số lượng',
                        data: [<?php echo $total_users; ?>, <?php echo rand(0, $total_users); ?>, <?php echo rand(0, $total_users); ?>],
                        backgroundColor: ['#36A2EB', '#4BC0C0', '#FF9F40']
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });

            // Biểu đồ khóa học phổ biến
            const courseLabels = <?php echo json_encode(array_keys($popular_courses)); ?>;
            const courseData = <?php echo json_encode(array_values($popular_courses)); ?>;
            new Chart(document.getElementById('popularCoursesChart'), {
                type: 'bar',
                data: {
                    labels: courseLabels.length ? courseLabels : ['Không có dữ liệu'],
                    datasets: [{
                        label: 'Lượt thích',
                        data: courseData.length ? courseData : [0],
                        backgroundColor: '#9966FF'
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>