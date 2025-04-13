<?php
header('Content-Type: application/json');
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$lesson_id = filter_var($data['lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$answers = $data['answers'] ?? [];

if (!$lesson_id) {
    echo json_encode(['success' => false, 'message' => 'ID bài học không hợp lệ']);
    exit();
}

// Lấy danh sách câu hỏi và đáp án đúng
$stmt = $conn->prepare("SELECT id, correct_answer FROM sub_lesson_tests WHERE sub_lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($questions)) {
    echo json_encode(['success' => false, 'message' => 'Không có câu hỏi nào']);
    exit();
}

// Tính điểm
$score = 0;
$total = count($questions);
foreach ($questions as $question) {
    $qid = 'q' . $question['id'];
    if (isset($answers[$qid]) && $answers[$qid] === $question['correct_answer']) {
        $score++;
    }
}

// Lưu kết quả (tùy chọn)
$stmt = $conn->prepare("INSERT INTO sub_lesson_test_results (user_id, sub_lesson_id, score, total_questions) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiii", $_SESSION['user_id'], $lesson_id, $score, $total);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'message' => "Bạn đạt $score/$total điểm!"
]);
?>