<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$course_id = filter_var($_GET['course_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
if ($course_id <= 0) {
    echo json_encode([]);
    exit;
}

$sub_lessons = $conn->query("SELECT id, title FROM sub_lessons WHERE course_id = {$course_id} ORDER BY order_number ASC");
$result = [];
while ($sub_lesson = $sub_lessons->fetch_assoc()) {
    $result[] = ['id' => $sub_lesson['id'], 'title' => $sub_lesson['title']];
}

echo json_encode($result);
?>