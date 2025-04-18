<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$course_id = filter_var($_GET['course_id'] ?? '', FILTER_SANITIZE_NUMBER_INT);
$query = "SELECT sl.id, sl.course_id, sl.title, sl.description, sl.order_number, sl.content_file, sl.video_url, c.course_name 
          FROM sub_lessons sl JOIN courses c ON sl.course_id = c.id";
if ($course_id) {
    $query .= " WHERE sl.course_id = $course_id";
}
$query .= " ORDER BY c.id, sl.order_number";

$sub_lessons = $conn->query($query);
$result = [];
while ($sub_lesson = $sub_lessons->fetch_assoc()) {
    $result[] = $sub_lesson;
}

echo json_encode($result);
?>