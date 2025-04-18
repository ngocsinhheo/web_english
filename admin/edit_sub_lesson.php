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
    $sub_lesson_id = filter_var($_POST['sub_lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $course_id = filter_var($_POST['course_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $order_number = filter_var($_POST['order_number'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $youtube_link = filter_var($_POST['youtube_link'] ?? '', FILTER_SANITIZE_URL);
    $target_dir = "../Uploads/";

    // Kiểm tra hợp lệ
    if ($sub_lesson_id <= 0 || $course_id <= 0 || empty($title) || empty($description)) {
        throw new Exception("ID bài học, khóa học, tiêu đề và mô tả không được để trống!");
    }

    // Kiểm tra định dạng link YouTube
    if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube_link)) {
        throw new Exception("Link YouTube không hợp lệ!");
    }

    // Xử lý file tài liệu (nếu có)
    $content_file = null;
    if (!empty($_FILES["content_file"]["name"])) {
        $content_file = handleFileUpload($_FILES["content_file"], $target_dir);
        if (!$content_file) {
            throw new Exception("Lỗi khi upload tài liệu!");
        }
    }

    // Cập nhật bài học con
    if ($content_file) {
        $stmt = $conn->prepare("UPDATE sub_lessons SET title = ?, description = ?, video_url = ?, content_file = ?, order_number = ? WHERE id = ? AND course_id = ?");
        $stmt->bind_param("ssssiii", $title, $description, $youtube_link, $content_file, $order_number, $sub_lesson_id, $course_id);
    } else {
        $stmt = $conn->prepare("UPDATE sub_lessons SET title = ?, description = ?, video_url = ?, order_number = ? WHERE id = ? AND course_id = ?");
        $stmt->bind_param("sssiii", $title, $description, $youtube_link, $order_number, $sub_lesson_id, $course_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Chỉnh sửa bài học con thành công!']);
    } else {
        throw new Exception($conn->error);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>