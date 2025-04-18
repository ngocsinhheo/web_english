<?php
header('Content-Type: application/json');
require_once '../config/config.php';

try {
    $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
    if ($course_id <= 0) {
        throw new Exception("Khóa học không hợp lệ!");
    }

    $query = "SELECT id, title FROM sub_lessons WHERE course_id = ? ORDER BY order_number";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $sub_lessons = [];
    while ($row = $result->fetch_assoc()) {
        $sub_lessons[] = [
            'id' => $row['id'],
            'title' => $row['title']
        ];
    }

    echo json_encode($sub_lessons);
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>