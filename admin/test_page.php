<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$sub_lesson_id = filter_var($_GET['sub_lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$course_id = filter_var($_GET['course_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

if (!$sub_lesson_id || !$course_id) {
    die("Thiếu thông tin bài học!");
}

// Lấy thông tin bài học con
$stmt = $conn->prepare("SELECT * FROM sub_lessons WHERE id = ?");
$stmt->bind_param("i", $sub_lesson_id);
$stmt->execute();
$sub_lesson = $stmt->get_result()->fetch_assoc() ?: die("Bài học không tồn tại!");

// Lấy danh sách câu hỏi kiểm tra
$test_stmt = $conn->prepare("SELECT * FROM sub_lesson_tests WHERE sub_lesson_id = ?");
$test_stmt->bind_param("i", $sub_lesson_id);
$test_stmt->execute();
$questions = $test_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài kiểm tra: <?php echo htmlspecialchars($sub_lesson['title']); ?> - TOEIC Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .test-container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .test-container h2 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .question {
            margin-bottom: 20px;
        }

        .question p {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .option {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }

        .option input {
            margin-right: 10px;
        }

        .test-container button {
            display: block;
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            margin: 20px auto;
        }

        .test-container button:hover {
            background: linear-gradient(90deg, #2980b9, #2471a3);
        }

        .test-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            display: none;
        }

        .test-result.success {
            background: #d4edda;
            color: #28a745;
        }

        .test-result.error {
            background: #f8d7da;
            color: #dc3545;
        }

        .back-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            text-align: center;
            background: linear-gradient(90deg, #dc3545, #c82333);
            color: #fff;
            padding: 12px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn:hover {
            background: linear-gradient(90deg, #c82333, #bd2130);
        }

        @media (max-width: 768px) {
            .test-container {
                width: 100%;
                margin: 20px auto;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>Bài kiểm tra: <?php echo htmlspecialchars($sub_lesson['title']); ?></h2>
        <form id="test-form" data-lesson-id="<?php echo $sub_lesson_id; ?>">
            <?php if ($questions->num_rows > 0): ?>
                <?php while ($question = $questions->fetch_assoc()): ?>
                    <div class="question">
                        <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                        <div class="option">
                            <input type="radio" name="q<?php echo $question['id']; ?>" value="A" id="q<?php echo $question['id']; ?>_a">
                            <label for="q<?php echo $question['id']; ?>_a"><?php echo htmlspecialchars($question['option_a']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="q<?php echo $question['id']; ?>" value="B" id="q<?php echo $question['id']; ?>_b">
                            <label for="q<?php echo $question['id']; ?>_b"><?php echo htmlspecialchars($question['option_b']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="q<?php echo $question['id']; ?>" value="C" id="q<?php echo $question['id']; ?>_c">
                            <label for="q<?php echo $question['id']; ?>_c"><?php echo htmlspecialchars($question['option_c']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="q<?php echo $question['id']; ?>" value="D" id="q<?php echo $question['id']; ?>_d">
                            <label for="q<?php echo $question['id']; ?>_d"><?php echo htmlspecialchars($question['option_d']); ?></label>
                        </div>
                    </div>
                <?php endwhile; ?>
                <button type="submit">Nộp bài</button>
            <?php else: ?>
                <p>Chưa có câu hỏi kiểm tra.</p>
            <?php endif; ?>
        </form>
        <div class="test-result" id="test-result"></div>
        <a href="course_detail.php?id=<?php echo $course_id; ?>" class="back-btn" id="back-btn" style="display: none;">Quay lại bài học</a>
    </div>

    <script>
        document.getElementById('test-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const lessonId = e.target.dataset.lessonId;
            const resultDiv = document.getElementById('test-result');
            const formData = new FormData(e.target);

            const answers = {};
            formData.forEach((value, key) => {
                answers[key] = value;
            });

            const response = await fetch('check_test.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lesson_id: lessonId, answers })
            });

            const result = await response.json();
            resultDiv.className = `test-result ${result.success ? 'success' : 'error'}`;
            resultDiv.textContent = result.message;
            resultDiv.style.display = 'block';

            // Ẩn form và hiển thị nút quay lại
            document.getElementById('test-form').style.display = 'none';
            document.getElementById('back-btn').style.display = 'block';
        });
    </script>
</body>
</html>
<?php $stmt->close(); $test_stmt->close(); $conn->close(); ?>