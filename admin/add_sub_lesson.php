<?php
header('Content-Type: application/json');
require_once '../config/config.php';

// Hàm xử lý upload file
function handleFileUpload($file, $targetDir) {
    $fileName = basename($file["name"]);
    $targetPath = $targetDir . time() . '_' . $fileName;

    // Kiểm tra loại tệp
    $allowedTypes = ['application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception("Chỉ hỗ trợ tệp PDF!");
    }

    return move_uploaded_file($file["tmp_name"], $targetPath) ? $targetPath : false;
}

try {
    $course_id = filter_var($_POST['course_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $order_number = filter_var($_POST['order_number'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $youtube_link = filter_var($_POST['youtube_link'] ?? '', FILTER_SANITIZE_URL);
    $target_dir = "../Uploads/";

    // Upload file tài liệu
    $content_file = handleFileUpload($_FILES["content_file"], $target_dir);

    // Kiểm tra hợp lệ
    if (empty($youtube_link) || !$content_file || empty($title) || empty($description) || $course_id <= 0) {
        throw new Exception("Link YouTube, file tài liệu, tiêu đề, mô tả và khóa học không được để trống!");
    }

    // Kiểm tra định dạng link YouTube
    if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube_link)) {
        throw new Exception("Link YouTube không hợp lệ!");
    }

    $stmt = $conn->prepare("INSERT INTO sub_lessons (course_id, title, description, video_url, content_file, order_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $course_id, $title, $description, $youtube_link, $content_file, $order_number);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thêm bài học con thành công!']);
    } else {
        throw new Exception($conn->error);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>