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

// Truy vấn tin nhắn liên hệ
$result_contacts = $conn->query("SELECT id, user_id, username, message, status, reply, created_at, replied_at FROM contacts ORDER BY created_at DESC");

// Thêm người dùng
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

// Xóa người dùng
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

// Thêm khóa học
if (isset($_POST['add_course'])) {
    try {
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $teacher_name = filter_var($_POST['teacher_name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $target_dir = "../uploads/";
        
        $image = handleFileUpload($_FILES["course_image"], $target_dir);
        $content_file = handleFileUpload($_FILES["course_content"], $target_dir);
        $video_file = handleFileUpload($_FILES["course_video"], $target_dir);

        if (!$image || !$content_file || !$video_file) throw new Exception("Lỗi upload file!");

        $stmt = $conn->prepare("INSERT INTO courses (title, description, price, teacher_name, image, content_file, video_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $title, $description, $price, $teacher_name, $image, $content_file, $video_file);
        $stmt->execute() ? $success = "Thêm khóa học thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xóa khóa học
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

// Truy vấn dữ liệu
$result_users = $conn->query("SELECT id, username, email FROM users WHERE role != 'admin'");
$total_users = $result_users->num_rows ?? 0;

$result_courses = $conn->query("SELECT id, title, description, price, teacher_name, image, content_file, video_file FROM courses");
$popular_courses = [];
while ($course = $result_courses->fetch_assoc()) {
    $popular_courses[$course['title']] = rand(10, 100); // Giả lập lượt thích
}
$result_courses->data_seek(0);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li onclick="showSection('dashboard')" class="active">Dashboard</li>
                <li onclick="showSection('users')">Quản Lý Người Dùng</li>
                <li onclick="showSection('products')">Quản Lý Khóa Học</li>
                <li onclick="showSection('test')">Quản Lý Bài Thi</li>
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

            <div id="products" class="content-section">
                <h1>Quản Lý Khóa Học</h1>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Tên khóa học" required>
                    <textarea name="description" placeholder="Mô tả" required></textarea>
                    <input type="text" name="teacher_name" placeholder="Giáo viên" required>
                    <input type="number" name="price" placeholder="Giá" required>
                    <input type="file" name="course_image" accept="image/*" required>
                    <input type="file" name="course_content" accept=".pdf" required>
                    <input type="file" name="course_video" accept="video/*" required>
                    <button type="submit" name="add_course">Thêm</button>
                </form>
                <table>
                    <tr><th>ID</th><th>Tiêu đề</th><th>Giá</th><th>Giáo viên</th><th>Hình ảnh</th><th>Tài liệu</th><th>Video</th><th>Hành động</th></tr>
                    <?php while ($course = $result_courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
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
                            <td><a class="delete-btn" href="?delete_course=<?php echo $course['id']; ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <?php $result_courses->close(); ?>
            </div>

            <div id="test" class="content-section">
                <?php // include '../test/admin.php'; ?>
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