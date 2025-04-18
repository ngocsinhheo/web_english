<?php
session_start();
require_once '../config/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Received POST request for check_test.php");
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("POST input: " . print_r($input, true));

    $lesson_id = filter_var($input['lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $user_answers = $input['answers'] ?? [];

    if (!$lesson_id) {
        error_log("Missing lesson_id in POST data");
        echo json_encode(['success' => false, 'message' => 'Thiếu lesson_id']);
        exit;
    }

    try {
        // Lấy câu hỏi và đáp án đúng từ cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT id, correct_answer FROM sub_lesson_tests WHERE sub_lesson_id = ?");
        if ($stmt === false) {
            error_log("Prepare statement failed: " . $conn->error);
            throw new Exception("Lỗi hệ thống khi lấy câu hỏi.");
        }
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        $questions = $stmt->get_result();
        $stmt->close();

        if ($questions->num_rows === 0) {
            error_log("No questions found for sub_lesson_id=$lesson_id");
            echo json_encode(['success' => false, 'message' => 'Không có câu hỏi cho bài kiểm tra này']);
            exit;
        }

        $details = [];
        $score = 0;
        $total = $questions->num_rows;

        while ($question = $questions->fetch_assoc()) {
            $user_answer = $user_answers['q' . $question['id']] ?? null;
            $is_correct = $user_answer && $user_answer === $question['correct_answer'];
            if ($is_correct) $score++;
            $details[] = [
                'question_id' => $question['id'],
                'user_answer' => $user_answer,
                'correct_answer' => $question['correct_answer'],
                'is_correct' => $is_correct
            ];
        }

        error_log("Test result: score=$score, total=$total");
        echo json_encode([
            'success' => true,
            'message' => "Bạn đúng $score/$total câu!",
            'score' => $score,
            'total' => $total,
            'details' => $details
        ]);
    } catch (Exception $e) {
        error_log("Error processing test: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Lỗi xử lý bài kiểm tra: ' . $e->getMessage()]);
    }
    exit;
}

// Nếu không phải POST, trả về lỗi
echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
exit;
?>