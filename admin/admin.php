<?php
session_start();
require_once '../config/config.php';

// Bật hiển thị lỗi để debug (tắt trên production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
// Logic quản lý bài kiểm tra
$testsDir = '../tests/';

function getTests($dir) {
    if (!is_dir($dir)) {
        return [];
    }
    return array_filter(glob($dir . '*'), 'is_dir');
}

// Xử lý action=list để trả về danh sách bài thi
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    $tests = getTests($testsDir);
    $testNames = array_map('basename', $tests);
    echo json_encode($testNames);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    try {
        $testId = preg_match('/^test\d+$/', trim($_POST['test_id'])) ? trim($_POST['test_id']) : 'test' . trim($_POST['test_id']);
        $uploadDir = "{$testsDir}{$testId}/";
        if (file_exists($uploadDir)) throw new Exception("ID '{$testId}' đã tồn tại!");

        $dirs = [$uploadDir, "{$uploadDir}uploads/", "{$uploadDir}img/", "{$uploadDir}audio/"];
        foreach ($dirs as $dir) if (!mkdir($dir, 0777, true)) throw new Exception("Không thể tạo thư mục!");

        $csvPath = "{$uploadDir}uploads/questions.csv";
        if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvPath)) throw new Exception("Lỗi upload CSV!");

        foreach (['images' => 'img', 'audios' => 'audio'] as $type => $folder) {
            if (isset($_FILES[$type]) && !empty($_FILES[$type]['name'][0])) {
                foreach ($_FILES[$type]['tmp_name'] as $key => $tmpName) {
                    if ($_FILES[$type]['error'][$key] === UPLOAD_ERR_OK) {
                        move_uploaded_file($tmpName, "{$uploadDir}{$folder}/" . $_FILES[$type]['name'][$key]);
                    }
                }
            }
        }
        $success = "Tạo bài kiểm tra {$testId} thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['test'])) {
    $testDir = "{$testsDir}{$_GET['test']}";
    function deleteDir($dir) {
        if (!file_exists($dir)) return true;
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            is_dir("$dir/$item") ? deleteDir("$dir/$item") : unlink("$dir/$item");
        }
        return rmdir($dir);
    }
    echo json_encode(deleteDir($testDir) ? ['status' => 'success'] : ['status' => 'error']);
    exit;
}

$tests = getTests($testsDir);

function handleFileUpload($file, $targetDir) {
    // Kiểm tra file có tồn tại và hợp lệ
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE || $file['size'] === 0) {
        return false; // Không có file hoặc file rỗng
    }

    $fileName = basename($file["name"] ?? '');
    if (empty($fileName)) {
        return false;
    }

    $targetPath = $targetDir . time() . '_' . $fileName;

    // Kiểm tra loại tệp
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception("Loại tệp không được hỗ trợ!");
    }

    return move_uploaded_file($file["tmp_name"], $targetPath) ? $targetPath : false;
}
// Xử lý thêm nhiều câu hỏi kiểm tra
if (isset($_POST['add_test_questions'])) {
    try {
        $sub_lesson_id = filter_var($_POST['sub_lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        if ($sub_lesson_id <= 0) {
            throw new Exception("Vui lòng chọn bài học con hợp lệ!");
        }

        if (!isset($_POST['questions']) || !is_array($_POST['questions'])) {
            throw new Exception("Không có câu hỏi nào được gửi!");
        }

        $conn->begin_transaction(); // Bắt đầu giao dịch để đảm bảo toàn bộ câu hỏi được thêm hoặc không thêm gì cả
        $stmt = $conn->prepare("INSERT INTO sub_lesson_tests (sub_lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($_POST['questions'] as $question) {
            $question_text = trim(htmlspecialchars($question['question_text'] ?? '', ENT_QUOTES, 'UTF-8'));
            $option_a = trim(htmlspecialchars($question['option_a'] ?? '', ENT_QUOTES, 'UTF-8'));
            $option_b = trim(htmlspecialchars($question['option_b'] ?? '', ENT_QUOTES, 'UTF-8'));
            $option_c = trim(htmlspecialchars($question['option_c'] ?? '', ENT_QUOTES, 'UTF-8'));
            $option_d = trim(htmlspecialchars($question['option_d'] ?? '', ENT_QUOTES, 'UTF-8'));
            $correct_answer = htmlspecialchars($question['correct_answer'] ?? '', ENT_QUOTES, 'UTF-8');

            if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || !in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
                throw new Exception("Dữ liệu câu hỏi không hợp lệ!");
            }

            $stmt->bind_param("issssss", $sub_lesson_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);
            if (!$stmt->execute()) {
                throw new Exception($conn->error);
            }
        }

        $conn->commit();
        $stmt->close();
        $success = "Thêm tất cả câu hỏi kiểm tra thành công!";
        header("Location: admin.php#list-test-questions");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Lỗi: " . $e->getMessage();
    }
}
// Xử lý phản hồi tin nhắn
if (isset($_POST['reply_message'])) {
    try {
        $contact_id = filter_var($_POST['contact_id'], FILTER_SANITIZE_NUMBER_INT);
        $reply = trim(htmlspecialchars($_POST['reply'], ENT_QUOTES, 'UTF-8'));

        $stmt = $conn->prepare("UPDATE contacts SET reply = ?, status = 'replied', replied_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $contact_id);
        $stmt->execute() ? $success = "Phản hồi thành công!" : throw new Exception($conn->error);
        $stmt->close();

        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    try {
        $username = trim(htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8'));
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
        // Kiểm tra các trường bắt buộc
        if (!isset($_POST['course_name'], $_POST['title'], $_POST['description'], $_POST['teacher_name'], $_POST['price'], $_FILES['image'], $_FILES['content_file'])) {
            throw new Exception("Vui lòng điền đầy đủ các trường bắt buộc!");
        }

        $course_name = trim(htmlspecialchars($_POST['course_name'] ?? '', ENT_QUOTES, 'UTF-8'));
        $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
        $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
        $teacher_name = trim(htmlspecialchars($_POST['teacher_name'] ?? '', ENT_QUOTES, 'UTF-8'));
        $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $youtube_link = filter_var($_POST['youtube_link'] ?? '', FILTER_SANITIZE_URL);
        $category = htmlspecialchars($_POST['category'] ?? 'free', ENT_QUOTES, 'UTF-8');
        $media_type = htmlspecialchars($_POST['media_type'] ?? 'video', ENT_QUOTES, 'UTF-8');
        $learning_outcomes = trim(htmlspecialchars($_POST['learning_outcomes'] ?? '', ENT_QUOTES, 'UTF-8'));
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $target_dir = "../Uploads/";

        // Validate dữ liệu
        if (empty($course_name) || empty($title) || empty($description) || empty($teacher_name) || $price === false) {
            throw new Exception("Các trường bắt buộc không được để trống hoặc giá không hợp lệ!");
        }

        // Upload file ảnh và tài liệu
        $image = handleFileUpload($_FILES["image"] ?? null, $target_dir);
        

        // Kiểm tra giá trị category
        $valid_categories = ['grammar', 'reading', 'listening', 'pronunciation', 'free'];
        if (!in_array($category, $valid_categories)) {
            $category = 'free';
        }

        // Kiểm tra giá trị media_type
        $valid_media_types = ['audio', 'video'];
        if (!in_array($media_type, $valid_media_types)) {
            $media_type = 'video';
        }

        $stmt = $conn->prepare("INSERT INTO courses (course_name, title, description, price, teacher_name, image, content_file, video_file, category, media_type, learning_outcomes, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdsssssssss", $course_name, $title, $description, $price, $teacher_name, $image, $content_file, $youtube_link, $category, $media_type, $learning_outcomes, $start_date, $end_date);
        $stmt->execute() ? $success = "Thêm khóa học thành công!" : throw new Exception($conn->error);
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
        $stmt = $conn->prepare("DELETE FROM sub_lesson_tests WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute() ? $success = "Xóa câu hỏi kiểm tra thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
// Xử lý thêm câu hỏi kiểm tra
if (isset($_POST['add_test_question'])) {
    try {
        $sub_lesson_id = filter_var($_POST['sub_lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $question_text = trim(htmlspecialchars($_POST['question_text'] ?? '', ENT_QUOTES, 'UTF-8'));
        $option_a = trim(htmlspecialchars($_POST['option_a'] ?? '', ENT_QUOTES, 'UTF-8'));
        $option_b = trim(htmlspecialchars($_POST['option_b'] ?? '', ENT_QUOTES, 'UTF-8'));
        $option_c = trim(htmlspecialchars($_POST['option_c'] ?? '', ENT_QUOTES, 'UTF-8'));
        $option_d = trim(htmlspecialchars($_POST['option_d'] ?? '', ENT_QUOTES, 'UTF-8'));
        $correct_answer = htmlspecialchars($_POST['correct_answer'] ?? '', ENT_QUOTES, 'UTF-8');

        if ($sub_lesson_id <= 0 || empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || !in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
            throw new Exception("Vui lòng điền đầy đủ và chọn đáp án đúng hợp lệ!");
        }

        $stmt = $conn->prepare("INSERT INTO sub_lesson_tests (sub_lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $sub_lesson_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);
        $stmt->execute() ? $success = "Thêm câu hỏi kiểm tra thành công!" : throw new Exception($conn->error);
        $stmt->close();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa câu hỏi kiểm tra
if (isset($_GET['delete_test_question'])) {
    try {
        $question_id = filter_var($_GET['delete_test_question'], FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("DELETE FROM sub_lesson_tests WHERE id = ?");
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

$result_courses = $conn->query("SELECT id, course_name, title, description, price, teacher_name, image, content_file, video_file, category, media_type FROM courses");
$popular_courses = [];
while ($course = $result_courses->fetch_assoc()) {
    $popular_courses[$course['title']] = rand(10, 100); // Giả lập lượt thích
}
$result_courses->data_seek(0);

$result_contacts = $conn->query("SELECT id, user_id, username, message, status, reply, created_at, replied_at FROM contacts ORDER BY created_at DESC");

// Lấy danh sách khóa học cho dropdown
$courses_for_dropdown = $conn->query("SELECT id, course_name FROM courses");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TOEIC Learning</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body>
    <div class="container">
    <div class="sidebar">
            <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            <ul>
                <li onclick="showSection('dashboard')" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                <li onclick="showSection('users')"><i class="fas fa-users"></i> Quản Lý Người Dùng</li>
                <li onclick="showSection('courses')"><i class="fas fa-book"></i> Quản Lý Khóa Học</li>
                <li onclick="showSection('contacts')"><i class="fas fa-envelope"></i> Quản Lý Tin Nhắn</li>
                <li onclick="showSection('tests')"><i class="fas fa-question-circle"></i> Quản Lý Bài Tests</li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
            </ul>
        </div>

        <div class="main-content">
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
                    <div class="form-group">
                        <label for="username">Tên người dùng</label>
                        <input type="text" name="username" id="username" placeholder="Nhập tên người dùng" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
                    </div>
                    <button type="submit" name="add_user"><i class="fas fa-plus"></i> Thêm người dùng</button>
                </form>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Tên</th><th>Email</th><th>Hành động</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><a class="delete-btn" href="?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div id="courses" class="content-section">
    <h1>Quản Lý Khóa Học</h1>
    <div class="course-sub-menu">
        <button class="sub-menu-btn active" onclick="showCourseSection('add-course')"><i class="fas fa-plus"></i> Thêm khóa học</button>
        <button class="sub-menu-btn" onclick="showCourseSection('list-courses')"><i class="fas fa-list"></i> Danh sách khóa học</button>
        <button class="sub-menu-btn" onclick="showCourseSection('add-sub-lesson')"><i class="fas fa-book"></i> Thêm bài học con</button>
        <button class="sub-menu-btn" onclick="showCourseSection('manage-sub-lessons')"><i class="fas fa-book-open"></i> Quản lý bài học con</button>
        <button class="sub-menu-btn" onclick="showCourseSection('add-test')"><i class="fas fa-question-circle"></i> Thêm bài kiểm tra</button>
        <button class="sub-menu-btn" onclick="showCourseSection('list-test-questions')"><i class="fas fa-list-ul"></i> Danh sách câu hỏi kiểm tra</button>
    </div>

    <!-- Tab Thêm khóa học -->
    <div id="add-course" class="course-section active">
    <form method="POST" enctype="multipart/form-data" class="course-form">
        <div class="form-row">
            <div class="form-group">
                <label for="course_name">Tên khóa học</label>
                <input type="text" name="course_name" id="course_name" placeholder="Nhập tên khóa học" required>
            </div>
            <div class="form-group">
                <label for="title">Tiêu đề</label>
                <input type="text" name="title" id="title" placeholder="Nhập tiêu đề" required>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea name="description" id="description" placeholder="Nhập mô tả khóa học" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="teacher_name">Giáo viên</label>
                <input type="text" name="teacher_name" id="teacher_name" placeholder="Nhập tên giáo viên" required>
            </div>
            <div class="form-group">
                <label for="price">Giá (VNĐ)</label>
                <input type="number" name="price" id="price" step="0.01" placeholder="Nhập giá" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="image">Hình ảnh</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="content_file">Tài liệu (PDF)</label>
                <input type="file" name="content_file" id="content_file" accept=".pdf" required>
            </div>
        </div>

        <button type="submit" name="add_course"><i class="fas fa-plus"></i> Thêm khóa học</button>
    </form>
</div>
    <!-- Tab Danh sách khóa học -->
    <div id="list-courses" class="course-section">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên khóa học</th>
                <th>Giá</th>
                <th>Giáo viên</th>
                <th>Hình ảnh</th>
                <th>Tài liệu</th>
                <th>Video</th>
                <th>Danh mục</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Đặt lại con trỏ kết quả
            $result_courses->data_seek(0);
            if ($result_courses->num_rows === 0) {
                echo '<tr><td colspan="9">Không có khóa học nào!</td></tr>';
            } else {
                while ($course = $result_courses->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $course['id']; ?></td>
                    <td>
                        <div class="accordion-header" data-course-id="<?php echo $course['id']; ?>">
                            <span><?php echo htmlspecialchars($course['course_name']); ?></span>
                            <span><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="accordion-content" id="course-content-<?php echo $course['id']; ?>">
                            <div class="sub-lesson-header">
                                <h3>Bài học con</h3>
                                <button class="add-sub-lesson-btn" data-course-id="<?php echo $course['id']; ?>"><i class="fas fa-plus"></i> Thêm bài học con</button>
                            </div>
                            <div class="sub-lessons-list" data-course-id="<?php echo $course['id']; ?>">
                                <!-- Danh sách bài học con sẽ được tải bằng AJAX hoặc PHP -->
                                <?php
                                $sub_lessons = $conn->query("SELECT * FROM sub_lessons WHERE course_id = {$course['id']} ORDER BY order_number ASC");
                                while ($sub_lesson = $sub_lessons->fetch_assoc()):
                                ?>
                                    <div class="sub-lesson-card" data-sub-lesson-id="<?php echo $sub_lesson['id']; ?>">
                                        <div class="sub-lesson-info">
                                            <h4><?php echo htmlspecialchars($sub_lesson['title']); ?> (Thứ tự: <?php echo $sub_lesson['order_number']; ?>)</h4>
                                            <p><?php echo htmlspecialchars($sub_lesson['description']); ?></p>
                                        </div>
                                        <div class="sub-lesson-actions">
                                            <a href="<?php echo $sub_lesson['content_file']; ?>" target="_blank" class="action-btn"><i class="fas fa-file-pdf"></i> Tài liệu</a>
                                            <a href="<?php echo $sub_lesson['video_url']; ?>" target="_blank" class="action-btn"><i class="fas fa-video"></i> Video</a>
                                            <button class="edit-sub-lesson-btn" data-sub-lesson-id="<?php echo $sub_lesson['id']; ?>" data-course-id="<?php echo $course['id']; ?>" data-title="<?php echo htmlspecialchars($sub_lesson['title']); ?>" data-description="<?php echo htmlspecialchars($sub_lesson['description']); ?>" data-order-number="<?php echo $sub_lesson['order_number']; ?>" data-youtube-link="<?php echo htmlspecialchars($sub_lesson['video_url']); ?>"><i class="fas fa-edit"></i> Sửa</button>
                                            <a class="delete-btn" href="?delete_sub_lesson=<?php echo $sub_lesson['id']; ?>" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo number_format($course['price'], 2, ',', '.'); ?> VNĐ</td>
                    <td><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                    <td>
                        <?php if (file_exists($course['image'])): ?>
                            <img src="<?php echo $course['image']; ?>" alt="Ảnh" style="max-width: 100px;">
                        <?php else: ?>
                            <span>Hình ảnh không tồn tại</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo $course['content_file']; ?>" target="_blank"><i class="fas fa-file-pdf"></i> Xem</a></td>
                    <td><a href="<?php echo $course['video_file']; ?>" target="_blank"><i class="fas fa-video"></i> Xem</a></td>
                    <td><?php echo htmlspecialchars($course['category']); ?></td>
                    <td>
                        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="action-btn"><i class="fas fa-eye"></i> Xem</a>
                        <a class="delete-btn" href="?delete_course=<?php echo $course['id']; ?>" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>

    <!-- Tab Thêm bài học con -->
    <div id="add-sub-lesson" class="course-section">
        <form id="select-course-form">
            <div class="form-group">
                <label for="course_select_sub_lesson">Chọn khóa học</label>
                <select name="course_id" id="course_select_sub_lesson" required>
                    <option value="">Chọn khóa học</option>
                    <?php
                    $courses_for_dropdown->data_seek(0);
                    while ($course = $courses_for_dropdown->fetch_assoc()):
                    ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="button" id="open-sub-lesson-modal"><i class="fas fa-plus"></i> Mở form thêm bài học con</button>
        </form>
    </div>

    <div id="manage-sub-lessons" class="course-section">
    <h2>Danh sách bài học con</h2>
    <form id="filter-sub-lesson-form">
        <div class="form-group">
            <label for="course_filter_sub_lesson">Lọc theo khóa học</label>
            <select name="course_id" id="course_filter_sub_lesson">
                <option value="">Tất cả khóa học</option>
                <?php
                $courses_for_dropdown->data_seek(0);
                while ($course = $courses_for_dropdown->fetch_assoc()):
                ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Khóa học</th>
                <th>Tiêu đề</th>
                <th>Thứ tự</th>
                <th>Tài liệu</th>
                <th>Video</th>
                
            </tr>
        </thead>
        <tbody id="sub-lesson-table-body">
            <?php
            $sub_lessons_query = $conn->query("SELECT sl.id, sl.title, sl.order_number, sl.content_file, sl.video_url, c.course_name FROM sub_lessons sl JOIN courses c ON sl.course_id = c.id ORDER BY c.id, sl.order_number");
            if ($sub_lessons_query->num_rows === 0) {
                echo '<tr><td colspan="7">Không có bài học con nào!</td></tr>';
            } else {
                while ($sub_lesson = $sub_lessons_query->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $sub_lesson['id']; ?></td>
                    <td><?php echo htmlspecialchars($sub_lesson['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($sub_lesson['title']); ?></td>
                    <td><?php echo $sub_lesson['order_number']; ?></td>
                    <td><a href="<?php echo $sub_lesson['content_file']; ?>" target="_blank"><i class="fas fa-file-pdf"></i> Xem</a></td>
                    <td><a href="<?php echo $sub_lesson['video_url']; ?>" target="_blank"><i class="fas fa-video"></i> Xem</a></td>
                </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>
    <!-- Tab Thêm bài kiểm tra -->
    <div id="add-test" class="course-section">
    <h2>Thêm bài kiểm tra</h2>
    <form id="select-test-form">
        <div class="form-row">
            <div class="form-group">
                <label for="course_select_test">Chọn khóa học</label>
                <select name="course_id" id="course_select_test" required>
                    <option value="">Chọn khóa học</option>
                    <?php
                    $courses_for_dropdown->data_seek(0);
                    while ($course = $courses_for_dropdown->fetch_assoc()):
                    ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sub_lesson_select_test">Chọn bài học con</label>
                <select name="sub_lesson_id" id="sub_lesson_select_test" required>
                    <option value="">Chọn bài học con</option>
                </select>
            </div>
        </div>
    </form>
    <form method="POST" id="test-form" style="display: none;">
        <input type="hidden" name="sub_lesson_id" id="test_sub_lesson_id">
        <div id="question-list">
            <div class="question-item" data-index="0">
                <h3>Câu hỏi 1</h3>
                <div class="form-group">
                    <label for="question_text_0">Câu hỏi</label>
                    <textarea name="questions[0][question_text]" id="question_text_0" placeholder="Nhập câu hỏi" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="option_a_0">Lựa chọn A</label>
                        <input type="text" name="questions[0][option_a]" id="option_a_0" placeholder="Nhập lựa chọn A" required>
                    </div>
                    <div class="form-group">
                        <label for="option_b_0">Lựa chọn B</label>
                        <input type="text" name="questions[0][option_b]" id="option_b_0" placeholder="Nhập lựa chọn B" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="option_c_0">Lựa chọn C</label>
                        <input type="text" name="questions[0][option_c]" id="option_c_0" placeholder="Nhập lựa chọn C" required>
                    </div>
                    <div class="form-group">
                        <label for="option_d_0">Lựa chọn D</label>
                        <input type="text" name="questions[0][option_d]" id="option_d_0" placeholder="Nhập lựa chọn D" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="correct_answer_0">Đáp án đúng</label>
                    <select name="questions[0][correct_answer]" id="correct_answer_0" required>
                        <option value="">Chọn đáp án đúng</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="button" id="add-question-btn"><i class="fas fa-plus"></i> Thêm câu hỏi khác</button>
        <button type="submit" name="add_test_questions"><i class="fas fa-save"></i> Lưu tất cả câu hỏi</button>
    </form>
</div>
</div>
<!-- Tab Danh sách câu hỏi kiểm tra -->
<div id="list-test-questions" class="course-section">
    <h2>Danh sách câu hỏi kiểm tra</h2>
    <form id="filter-test-question-form">
        <div class="form-row">
            <div class="form-group">
                <label for="course_filter_test_question">Lọc theo khóa học</label>
                <select name="course_id" id="course_filter_test_question">
                    <option value="">Tất cả khóa học</option>
                    <?php
                    $courses_for_dropdown->data_seek(0);
                    while ($course = $courses_for_dropdown->fetch_assoc()):
                    ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sub_lesson_filter_test_question">Lọc theo bài học con</label>
                <select name="sub_lesson_id" id="sub_lesson_filter_test_question">
                    <option value="">Tất cả bài học con</option>
                </select>
            </div>
        </div>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Khóa học</th>
                <th>Bài học con</th>
                <th>Câu hỏi</th>
                <th>Lựa chọn A</th>
                <th>Lựa chọn B</th>
                <th>Lựa chọn C</th>
                <th>Lựa chọn D</th>
                <th>Đáp án đúng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody id="test-question-table-body">
            <?php
            $test_questions_query = $conn->query("SELECT t.id, t.sub_lesson_id, t.question_text, t.option_a, t.option_b, t.option_c, t.option_d, t.correct_answer, c.course_name, s.title AS sub_lesson_title 
                                                 FROM sub_lesson_tests t 
                                                 JOIN sub_lessons s ON t.sub_lesson_id = s.id 
                                                 JOIN courses c ON s.course_id = c.id 
                                                 ORDER BY c.id, s.order_number, t.id");
            if ($test_questions_query->num_rows === 0) {
                echo '<tr><td colspan="10">Không có câu hỏi kiểm tra nào!</td></tr>';
            } else {
                while ($question = $test_questions_query->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $question['id']; ?></td>
                    <td><?php echo htmlspecialchars($question['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($question['sub_lesson_title']); ?></td>
                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_a']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_b']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_c']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_d']); ?></td>
                    <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                    <td>
                        <a class="delete-btn" href="?delete_test_question=<?php echo $question['id']; ?>" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>
<div id="tests" class="content-section">
    <h1>Quản Lý Bài Kiểm Tra</h1>
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="test_id">ID Bài Kiểm Tra</label>
            <input type="text" name="test_id" id="test_id" placeholder="ID (VD: 2)" required>
        </div>
        <div class="form-group">
            <label for="csv_file">File CSV Câu Hỏi</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        </div>
        <div class="form-group">
            <label for="images">Hình Ảnh (Nhiều File)</label>
            <input type="file" name="images[]" id="images" accept="image/*" multiple>
        </div>
        <div class="form-group">
            <label for="audios">Âm Thanh (Nhiều File)</label>
            <input type="file" name="audios[]" id="audios" accept="audio/*" multiple>
        </div>
        <button type="submit" name="create_test"><i class="fas fa-plus"></i> Tạo Bài Kiểm Tra</button>
    </form>
    <table>
        <thead>
            <tr><th>ID</th><th>Hành động</th></tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
                <tr>
                    <td><?php echo htmlspecialchars(basename($test)); ?></td>
                    <td><button class="delete-btn" onclick="deleteTest('<?php echo htmlspecialchars(basename($test)); ?>')"><i class="fas fa-trash"></i> Xóa</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
            <!-- Tab Quản lý tin nhắn liên hệ -->
            <div id="contacts" class="content-section">
                <h1>Quản Lý Tin Nhắn Liên Hệ</h1>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Người gửi</th><th>Tin nhắn</th><th>Trạng thái</th><th>Phản hồi</th><th>Thời gian gửi</th><th>Hành động</th></tr>
                    </thead>
                    <tbody>
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
                                        <button class="reply-btn" data-id="<?php echo $contact['id']; ?>" data-message="<?php echo htmlspecialchars(json_encode($contact['message'], JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-reply"></i> Phản hồi</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div id="replyForm" style="display: none; margin-top: 20px;">
                    <form method="POST">
                        <input type="hidden" name="contact_id" id="contactId">
                        <div class="form-group">
                            <label>Tin nhắn</label>
                            <p id="messagePreview" style="background: #f9fbfd; padding: 10px; border-radius: 8px;"></p>
                        </div>
                        <div class="form-group">
                            <label>Phản hồi</label>
                            <textarea name="reply" placeholder="Nhập phản hồi của bạn" required></textarea>
                        </div>
                        <button type="submit" name="reply_message"><i class="fas fa-paper-plane"></i> Gửi phản hồi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thêm/chỉnh sửa bài học con -->
    <div id="subLessonModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h2 id="modalTitle">Thêm bài học con</h2>
            <form id="subLessonForm" enctype="multipart/form-data">
                <input type="hidden" name="course_id" id="subLessonCourseId">
                <input type="hidden" name="sub_lesson_id" id="subLessonId">
                <div class="form-group">
                    <label for="sub_lesson_title">Tiêu đề</label>
                    <input type="text" name="title" id="sub_lesson_title" placeholder="Nhập tiêu đề bài học" required>
                </div>
                <div class="form-group">
                    <label for="sub_lesson_description">Mô tả</label>
                    <textarea name="description" id="sub_lesson_description" placeholder="Nhập mô tả bài học" required></textarea>
                </div>
                <div class="form-group">
                    <label for="sub_lesson_order_number">Thứ tự</label>
                    <input type="number" name="order_number" id="sub_lesson_order_number" placeholder="Nhập thứ tự bài học" required>
                </div>
                <div class="form-group">
                    <label for="sub_lesson_youtube_link">Link YouTube</label>
                    <input type="text" name="youtube_link" id="sub_lesson_youtube_link" placeholder="Nhập link YouTube (ví dụ: https://www.youtube.com/watch?v=abc123)" required>
                </div>
                <div class="form-group">
                    <label for="sub_lesson_content_file">Tài liệu (PDF)</label>
                    <input type="file" name="content_file" id="sub_lesson_content_file" accept=".pdf">
                </div>
                <button type="submit" id="subLessonSubmitBtn"><i class="fas fa-save"></i> Lưu</button>
            </form>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.sidebar li').forEach(l => l.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            document.querySelector(`li[onclick="showSection('${sectionId}')"]`).classList.add('active');
            if (sectionId === 'courses') {
                showCourseSection('add-course'); // Mặc định mở tab Thêm khóa học
            }
        }

        function showCourseSection(sectionId) {
    document.querySelectorAll('.course-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sub-menu-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
    document.querySelector(`button[onclick="showCourseSection('${sectionId}')"]`).classList.add('active');
}

        function showReplyForm(id, message) {
            const replyForm = document.getElementById('replyForm');
            if (replyForm) {
                replyForm.style.display = 'block';
                document.getElementById('contactId').value = id;
                document.getElementById('messagePreview').textContent = message;
            }
        }

        // Xử lý accordion
        function initializeAccordions() {
            document.querySelectorAll('.accordion-header').forEach(header => {
                header.addEventListener('click', () => {
                    const content = header.nextElementSibling;
                    const arrow = header.querySelector('i');
                    content.classList.toggle('active');
                    arrow.classList.toggle('fa-chevron-down');
                    arrow.classList.toggle('fa-chevron-up');
                });
            });
        }

        // Xử lý modal
        const modal = document.getElementById('subLessonModal');
        const modalClose = document.querySelector('.modal-close');
        const subLessonForm = document.getElementById('subLessonForm');
        const modalTitle = document.getElementById('modalTitle');

        function openModal(courseId, subLesson = null) {
            document.getElementById('subLessonCourseId').value = courseId;
            document.getElementById('subLessonId').value = subLesson ? subLesson.id : '';
            document.getElementById('sub_lesson_title').value = subLesson ? subLesson.title : '';
            document.getElementById('sub_lesson_description').value = subLesson ? subLesson.description : '';
            document.getElementById('sub_lesson_order_number').value = subLesson ? subLesson.orderNumber : '';
            document.getElementById('sub_lesson_youtube_link').value = subLesson ? subLesson.youtubeLink : '';
            modalTitle.textContent = subLesson ? 'Chỉnh sửa bài học con' : 'Thêm bài học con';
            document.getElementById('sub_lesson_content_file').required = !subLesson;
            modal.style.display = 'block';
        }

        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
            subLessonForm.reset();
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                subLessonForm.reset();
            }
        });

        // Xử lý thêm/chỉnh sửa bài học con bằng AJAX
        subLessonForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const courseId = document.getElementById('subLessonCourseId').value;
            const subLessonId = document.getElementById('subLessonId').value;
            const formData = new FormData(subLessonForm);
            const url = subLessonId ? 'edit_sub_lesson.php' : 'add_sub_lesson.php';
            const submitBtn = document.getElementById('subLessonSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

            try {
                // Kiểm tra định dạng link YouTube
                const youtubeLink = document.getElementById('sub_lesson_youtube_link').value;
                if (!youtubeLink.match(/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/)) {
                    throw new Error('Link YouTube không hợp lệ!');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    Toastify({
                        text: result.message,
                        duration: 3000,
                        backgroundColor: 'linear-gradient(to right, #28a745, #218838)',
                        className: 'toast-success'
                    }).showToast();

                    // Làm mới danh sách bài học con trong tab Danh sách khóa học
                    const subLessonsList = document.querySelector(`.sub-lessons-list[data-course-id="${courseId}"]`);
                    if (subLessonsList) {
                        const accordionContent = document.querySelector(`#course-content-${courseId}`);
                        accordionContent.classList.add('active');
                        const response = await fetch(`get_sub_lessons.php?course_id=${courseId}`);
                        const subLessons = await response.text();
                        subLessonsList.innerHTML = subLessons;

                        // Cuộn đến bài học mới
                        if (!subLessonId) {
                            const newLesson = subLessonsList.lastElementChild;
                            if (newLesson) newLesson.scrollIntoView({ behavior: 'smooth' });
                        }
                    }

                    modal.style.display = 'none';
                    subLessonForm.reset();
                    initializeAccordions();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Toastify({
                    text: 'Lỗi: ' + error.message,
                    duration: 3000,
                    backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                    className: 'toast-error'
                }).showToast();
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu';
            }
        });

        // Gán sự kiện cho nút thêm bài học con
        document.addEventListener('click', (e) => {
            if (e.target.closest('.add-sub-lesson-btn')) {
                const btn = e.target.closest('.add-sub-lesson-btn');
                const courseId = btn.getAttribute('data-course-id');
                openModal(courseId);
            }
        });

        // Gán sự kiện cho nút chỉnh sửa bài học con
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-sub-lesson-btn')) {
                const btn = e.target.closest('.edit-sub-lesson-btn');
                const courseId = btn.getAttribute('data-course-id');
                const subLesson = {
                    id: btn.getAttribute('data-sub-lesson-id'),
                    title: btn.getAttribute('data-title'),
                    description: btn.getAttribute('data-description'),
                    orderNumber: btn.getAttribute('data-order-number'),
                    youtubeLink: btn.getAttribute('data-youtube-link')
                };
                openModal(courseId, subLesson);
            }
        });

        // Xử lý chọn khóa học để thêm bài học con
        document.getElementById('open-sub-lesson-modal').addEventListener('click', () => {
            const courseId = document.getElementById('course_select_sub_lesson').value;
            if (courseId) {
                openModal(courseId);
            } else {
                Toastify({
                    text: 'Vui lòng chọn khóa học!',
                    duration: 3000,
                    backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                    className: 'toast-error'
                }).showToast();
            }
        });

        // Xử lý chọn khóa học để lấy danh sách bài học con (Thêm bài kiểm tra)
        document.getElementById('course_select_test').addEventListener('change', async () => {
    const courseId = document.getElementById('course_select_test').value;
    const subLessonSelect = document.getElementById('sub_lesson_select_test');
    const testForm = document.getElementById('test-form');

    // Reset dropdown và ẩn form
    subLessonSelect.innerHTML = '<option value="">Chọn bài học con</option>';
    testForm.style.display = 'none';

    if (courseId) {
        try {
            const response = await fetch(`get_sub_lessons_list.php?course_id=${courseId}`);
            if (!response.ok) {
                throw new Error('Lỗi mạng hoặc file không tồn tại');
            }
            const subLessons = await response.json();
            if (subLessons.length === 0) {
                Toastify({
                    text: 'Không có bài học con nào cho khóa học này!',
                    duration: 3000,
                    backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                    className: 'toast-error'
                }).showToast();
            } else {
                subLessons.forEach(subLesson => {
                    const option = document.createElement('option');
                    option.value = subLesson.id;
                    option.textContent = subLesson.title;
                    subLessonSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Toastify({
                text: 'Lỗi khi tải danh sách bài học con: ' + error.message,
                duration: 3000,
                backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                className: 'toast-error'
            }).showToast();
        }
    }
});

document.getElementById('sub_lesson_select_test').addEventListener('change', () => {
    const subLessonId = document.getElementById('sub_lesson_select_test').value;
    const testForm = document.getElementById('test-form');
    if (subLessonId) {
        document.getElementById('test_sub_lesson_id').value = subLessonId;
        testForm.style.display = 'block';
    } else {
        testForm.style.display = 'none';
    }
});

        // Xử lý chọn bài học con để hiển thị form thêm bài kiểm tra
        document.getElementById('sub_lesson_select_test').addEventListener('change', () => {
            const subLessonId = document.getElementById('sub_lesson_select_test').value;
            const testForm = document.getElementById('test-form');
            if (subLessonId) {
                document.getElementById('test_sub_lesson_id').value = subLessonId;
                testForm.style.display = 'block';
            } else {
                testForm.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Khởi tạo accordion
            initializeAccordions();

            // Gán sự kiện cho nút phản hồi
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

            // Hiển thị thông báo nếu có
            <?php if (isset($success)): ?>
                Toastify({
                    text: "<?php echo $success; ?>",
                    duration: 3000,
                    backgroundColor: "linear-gradient(to right, #28a745, #218838)",
                    className: "toast-success"
                }).showToast();
            <?php endif; ?>
            <?php if (isset($error)): ?>
                Toastify({
                    text: "<?php echo $error; ?>",
                    duration: 3000,
                    backgroundColor: "linear-gradient(to right, #dc3545, #c82333)",
                    className: "toast-error"
                }).showToast();
            <?php endif; ?>
        });
        document.getElementById('course_filter_test_question').addEventListener('change', async () => {
    const courseId = document.getElementById('course_filter_test_question').value;
    const tableBody = document.getElementById('test-question-table-body');
    try {
        const response = await fetch(`get_test_questions.php?course_id=${courseId}`);
        const questions = await response.json();
        tableBody.innerHTML = questions.length === 0 
            ? '<tr><td colspan="10">Không có câu hỏi kiểm tra nào!</td></tr>'
            : questions.map(q => `
                <tr>
                    <td>${q.id}</td>
                    <td>${q.course_name}</td>
                    <td>${q.sub_lesson_title}</td>
                    <td>${q.question_text}</td>
                    <td>${q.option_a}</td>
                    <td>${q.option_b}</td>
                    <td>${q.option_c}</td>
                    <td>${q.option_d}</td>
                    <td>${q.correct_answer}</td>
                    <td>
                        <a class="delete-btn" href="?delete_test_question=${q.id}" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            `).join('');
    } catch (error) {
        Toastify({
            text: 'Lỗi khi tải danh sách câu hỏi kiểm tra!',
            duration: 3000,
            backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
            className: 'toast-error'
        }).showToast();
    }
});
// Xử lý chọn khóa học để tải danh sách bài học con trong bộ lọc câu hỏi kiểm tra
document.getElementById('course_filter_test_question').addEventListener('change', async () => {
    const courseId = document.getElementById('course_filter_test_question').value;
    const subLessonFilter = document.getElementById('sub_lesson_filter_test_question');
    
    // Reset dropdown bài học con
    subLessonFilter.innerHTML = '<option value="">Tất cả bài học con</option>';

    if (courseId) {
        try {
            const response = await fetch(`get_sub_lessons_list.php?course_id=${courseId}`);
            if (!response.ok) {
                throw new Error('Lỗi mạng hoặc file không tồn tại');
            }
            const subLessons = await response.json();
            
            if (subLessons.error) {
                throw new Error(subLessons.error);
            }

            if (subLessons.length === 0) {
                Toastify({
                    text: 'Không có bài học con nào cho khóa học này!',
                    duration: 3000,
                    backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                    className: 'toast-error'
                }).showToast();
            } else {
                subLessons.forEach(subLesson => {
                    const option = document.createElement('option');
                    option.value = subLesson.id;
                    option.textContent = subLesson.title;
                    subLessonFilter.appendChild(option);
                });
            }
        } catch (error) {
            Toastify({
                text: `Lỗi khi tải danh sách bài học con: ${error.message}`,
                duration: 3000,
                backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
                className: 'toast-error'
            }).showToast();
        }
    }

    // Tải danh sách câu hỏi với courseId, không có subLessonId
    await filterTestQuestions(courseId, '');
});

// Xử lý lọc câu hỏi khi chọn bài học con
document.getElementById('sub_lesson_filter_test_question').addEventListener('change', async () => {
    const courseId = document.getElementById('course_filter_test_question').value;
    const subLessonId = document.getElementById('sub_lesson_filter_test_question').value;
    await filterTestQuestions(courseId, subLessonId);
});

// Hàm lọc câu hỏi
async function filterTestQuestions(courseId, subLessonId) {
    const tableBody = document.getElementById('test-question-table-body');
    try {
        const params = new URLSearchParams();
        if (courseId) params.append('course_id', courseId);
        if (subLessonId) params.append('sub_lesson_id', subLessonId);
        
        const response = await fetch(`get_test_questions.php?${params.toString()}`);
        if (!response.ok) {
            throw new Error('Lỗi khi tải câu hỏi');
        }
        
        const questions = await response.json();
        
        if (questions.error) {
            throw new Error(questions.error);
        }

        tableBody.innerHTML = questions.length === 0 
            ? '<tr><td colspan="10">Không có câu hỏi kiểm tra nào!</td></tr>'
            : questions.map(q => `
                <tr>
                    <td>${q.id}</td>
                    <td>${q.course_name}</td>
                    <td>${q.sub_lesson_title}</td>
                    <td>${q.question_text}</td>
                    <td>${q.option_a}</td>
                    <td>${q.option_b}</td>
                    <td>${q.option_c}</td>
                    <td>${q.option_d}</td>
                    <td>${q.correct_answer}</td>
                    <td>
                        <a class="delete-btn" href="?delete_test_question=${q.id}" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            `).join('');
    } catch (error) {
        Toastify({
            text: `Lỗi khi tải danh sách câu hỏi: ${error.message}`,
            duration: 3000,
            backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
            className: 'toast-error'
        }).showToast();
    }
}

// Hàm lọc câu hỏi
async function filterTestQuestions(courseId, subLessonId) {
    const tableBody = document.getElementById('test-question-table-body');
    try {
        const params = new URLSearchParams();
        if (courseId) params.append('course_id', courseId);
        if (subLessonId) params.append('sub_lesson_id', subLessonId);
        const response = await fetch(`get_test_questions.php?${params.toString()}`);
        const questions = await response.json();
        tableBody.innerHTML = questions.length === 0 
            ? '<tr><td colspan="10">Không có câu hỏi kiểm tra nào!</td></tr>'
            : questions.map(q => `
                <tr>
                    <td>${q.id}</td>
                    <td>${q.course_name}</td>
                    <td>${q.sub_lesson_title}</td>
                    <td>${q.question_text}</td>
                    <td>${q.option_a}</td>
                    <td>${q.option_b}</td>
                    <td>${q.option_c}</td>
                    <td>${q.option_d}</td>
                    <td>${q.correct_answer}</td>
                    <td>
                        <a class="delete-btn" href="?delete_test_question=${q.id}" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
            `).join('');
    } catch (error) {
        Toastify({
            text: 'Lỗi khi tải danh sách câu hỏi kiểm tra!',
            duration: 3000,
            backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
            className: 'toast-error'
        }).showToast();
    }
}
document.getElementById('course_filter_sub_lesson').addEventListener('change', async () => {
    const courseId = document.getElementById('course_filter_sub_lesson').value;
    const tableBody = document.getElementById('sub-lesson-table-body');
    try {
        const response = await fetch(`get_sub_lessons_all.php?course_id=${courseId}`);
        const subLessons = await response.json();
        tableBody.innerHTML = '';
        if (subLessons.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7">Không có bài học con nào!</td></tr>';
        } else {
            subLessons.forEach(sl => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sl.id}</td>
                    <td>${sl.course_name}</td>
                    <td>${sl.title}</td>
                    <td>${sl.order_number}</td>
                    <td><a href="${sl.content_file}" target="_blank"><i class="fas fa-file-pdf"></i> Xem</a></td>
                    <td><a href="${sl.video_url}" target="_blank"><i class="fas fa-video"></i> Xem</a></td>
                    <td>
                        <button class="edit-sub-lesson-btn" data-sub-lesson-id="${sl.id}" data-course-id="${sl.course_id}" data-title="${sl.title}" data-description="${sl.description}" data-order-number="${sl.order_number}" data-youtube-link="${sl.video_url}"><i class="fas fa-edit"></i> Sửa</button>
                        <a class="delete-btn" href="?delete_sub_lesson=${sl.id}" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
    } catch (error) {
        Toastify({
            text: 'Lỗi khi tải danh sách bài học con!',
            duration: 3000,
            backgroundColor: 'linear-gradient(to right, #dc3545, #c82333)',
            className: 'toast-error'
        }).showToast();
    }
});
document.getElementById('add-question-btn').addEventListener('click', () => {
    const questionList = document.getElementById('question-list');
    const index = questionList.querySelectorAll('.question-item').length;
    const questionItem = document.createElement('div');
    questionItem.classList.add('question-item');
    questionItem.setAttribute('data-index', index);
    questionItem.innerHTML = `
        <h3>Câu hỏi ${index + 1}</h3>
        <div class="form-group">
            <label for="question_text_${index}">Câu hỏi</label>
            <textarea name="questions[${index}][question_text]" id="question_text_${index}" placeholder="Nhập câu hỏi" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="option_a_${index}">Lựa chọn A</label>
                <input type="text" name="questions[${index}][option_a]" id="option_a_${index}" placeholder="Nhập lựa chọn A" required>
            </div>
            <div class="form-group">
                <label for="option_b_${index}">Lựa chọn B</label>
                <input type="text" name="questions[${index}][option_b]" id="option_b_${index}" placeholder="Nhập lựa chọn B" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="option_c_${index}">Lựa chọn C</label>
                <input type="text" name="questions[${index}][option_c]" id="option_c_${index}" placeholder="Nhập lựa chọn C" required>
            </div>
            <div class="form-group">
                <label for="option_d_${index}">Lựa chọn D</label>
                <input type="text" name="questions[${index}][option_d]" id="option_d_${index}" placeholder="Nhập lựa chọn D" required>
            </div>
        </div>
        <div class="form-group">
            <label for="correct_answer_${index}">Đáp án đúng</label>
            <select name="questions[${index}][correct_answer]" id="correct_answer_${index}" required>
                <option value="">Chọn đáp án đúng</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
        <button type="button" class="remove-question-btn" onclick="this.parentElement.remove()">Xóa câu hỏi</button>
    `;
    questionList.appendChild(questionItem);
});
function showSection(sectionId) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar li').forEach(l => l.classList.remove('active'));
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
        document.querySelector(`li[onclick="showSection('${sectionId}')"]`).classList.add('active');
        if (sectionId === 'courses') {
            showCourseSection('add-course'); // Mặc định mở tab Thêm khóa học
        }
    } else {
        console.error(`Section with ID ${sectionId} not found`);
    }
}
    </script>
</body>
</html>
<?php $conn->close(); ?>