<?php
session_start(); // [source: 401]
require_once '../config/config.php'; // [source: 401]

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { // [source: 401]
    header("Location: ../auth/login.php");
    exit();
}

// Lấy ID bài học và khóa học từ URL, lọc dữ liệu
$sub_lesson_id = filter_var($_GET['sub_lesson_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT); // [source: 402]
$course_id = filter_var($_GET['course_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT); // [source: 402]

// Kiểm tra ID hợp lệ
if (!$sub_lesson_id || !$course_id) { // [source: 403]
    // Ghi log lỗi hoặc hiển thị thông báo thân thiện hơn
    error_log("Thiếu thông tin bài học hoặc khóa học: sub_lesson_id=$sub_lesson_id, course_id=$course_id");
    die("Lỗi: Thiếu thông tin bài học hoặc khóa học cần thiết."); // [source: 403]
}

// Lấy thông tin bài học con
$stmt_lesson = $conn->prepare("SELECT title FROM sub_lessons WHERE id = ?"); // [source: 404] (Chỉ cần title)
if ($stmt_lesson === false) {
    error_log("Lỗi Prepare Statement (lesson): " . $conn->error);
    die("Lỗi hệ thống khi lấy thông tin bài học.");
}
$stmt_lesson->bind_param("i", $sub_lesson_id); // [source: 404]
$stmt_lesson->execute(); // [source: 404]
$result_lesson = $stmt_lesson->get_result();
$sub_lesson = $result_lesson->fetch_assoc(); // [source: 404]
$stmt_lesson->close();

if (!$sub_lesson) {
     error_log("Không tìm thấy bài học với ID: $sub_lesson_id");
     die("Lỗi: Bài học không tồn tại!"); // [source: 404]
}


// Lấy danh sách câu hỏi kiểm tra cho bài học này
$test_stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM sub_lesson_tests WHERE sub_lesson_id = ?"); // [source: 405] (Không lấy correct_answer ở đây nữa)
if ($test_stmt === false) {
    error_log("Lỗi Prepare Statement (questions): " . $conn->error);
    die("Lỗi hệ thống khi lấy câu hỏi kiểm tra.");
}
$test_stmt->bind_param("i", $sub_lesson_id); // [source: 405]
$test_stmt->execute(); // [source: 405]
$questions = $test_stmt->get_result(); // [source: 405]

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài kiểm tra: <?php echo htmlspecialchars($sub_lesson['title']); ?> - English Learning</title>
    <link rel="stylesheet" href="../style.css"> <style>
        body {
            margin: 0; /* [source: 407] */
            font-family: Arial, sans-serif; /* [source: 407] */
            background-color: #f4f4f4; /* [source: 407] */
        }

        .test-container {
            width: 90%; /* [source: 408] */
            max-width: 800px; /* [source: 408] */
            margin: 40px auto; /* [source: 408] */
            padding: 30px; /* [source: 408] (Tăng padding) */
            background: #fff; /* [source: 408] */
            border-radius: 10px; /* [source: 408] */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* [source: 409] (Tăng shadow) */
        }

        .test-container h2 {
            font-size: 28px; /* [source: 410] */
            font-weight: 700; /* [source: 410] */
            color: #2c3e50; /* [source: 410] */
            text-align: center; /* [source: 410] */
            margin-bottom: 30px; /* [source: 410] (Tăng margin) */
        }

        .question {
            margin-bottom: 25px; /* [source: 411] (Tăng margin) */
            padding-bottom: 20px; /* Thêm padding dưới */
            border-bottom: 1px solid #eee; /* Thêm đường kẻ phân cách */
        }
         .question:last-child {
             border-bottom: none; /* Bỏ đường kẻ cho câu cuối */
             margin-bottom: 0;
             padding-bottom: 0;
         }

        .question p { /* Nội dung câu hỏi */
            font-size: 18px; /* [source: 412] (Tăng font-size) */
            font-weight: 500; /* [source: 412] */
            color: #34495e; /* [source: 412] (Đổi màu) */
            margin-bottom: 15px; /* [source: 412] (Tăng margin) */
        }

        .option {
            margin: 8px 0; /* [source: 413] */
            display: flex; /* [source: 413] */
            align-items: center; /* [source: 413] */
        }

        .option input[type="radio"] {
            margin-right: 10px; /* [source: 414] */
            width: 18px; /* Phóng to radio button */
            height: 18px; /* Phóng to radio button */
            cursor: pointer;
        }
         .option input[type="radio"]:disabled {
             cursor: not-allowed; /* Con trỏ khi bị vô hiệu hóa */
         }

        .option label {
            font-size: 16px;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.2s ease; /* Hiệu ứng khi hover/chọn */
        }

        /* --- CSS CHO HIỂN THỊ ĐÚNG/SAI --- */
        .option label.user-correct {
            color: #28a745; /* Màu xanh lá */
            font-weight: bold;
        }
        .option label.user-incorrect {
            color: #dc3545; /* Màu đỏ */
            text-decoration: line-through; /* Gạch ngang câu sai */
        }
        .option label.correct-answer {
            background-color: #d4edda; /* Nền xanh lá nhạt */
            padding: 3px 6px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            font-weight: bold;
            margin-left: 5px; /* Khoảng cách nhỏ nếu là đáp án đúng */
        }
         .option label .feedback-text { /* Style cho text (Bạn chọn đúng/sai) */
             font-style: italic;
             font-size: 0.9em;
             margin-left: 8px;
         }
        /* --- HẾT CSS ĐÚNG/SAI --- */


        .test-container button[type="submit"] { /* Nút nộp bài */
            display: block; /* [source: 415] */
            background: linear-gradient(90deg, #3498db, #2980b9); /* [source: 415] */
            color: #fff; /* [source: 415] */
            border: none; /* [source: 415] */
            padding: 12px 25px; /* [source: 415] (Tăng padding) */
            border-radius: 25px; /* [source: 415] */
            font-weight: 600; /* [source: 415] */
            cursor: pointer; /* [source: 415] */
            margin: 30px auto 0; /* [source: 416] (Tăng margin top) */
            transition: background 0.3s, transform 0.2s; /* Thêm transition */
        }

        .test-container button[type="submit"]:hover {
            background: linear-gradient(90deg, #2980b9, #2471a3); /* [source: 417] */
            transform: translateY(-2px); /* Hiệu ứng hover */
        }

        .test-result { /* Khung hiển thị kết quả tổng */
            margin-top: 30px; /* [source: 418] (Tăng margin) */
            padding: 15px; /* [source: 418] */
            border-radius: 8px; /* [source: 418] */
            text-align: center; /* [source: 418] */
            font-weight: 600; /* [source: 418] */
            display: none; /* [source: 419] */
        }

        .test-result.success { /* Style khi thành công */
            background: #d4edda; /* [source: 420] */
            color: #155724; /* [source: 420] (Đổi màu đậm hơn) */
            border: 1px solid #c3e6cb; /* Thêm border */
        }

        .test-result.error { /* Style khi lỗi */
            background: #f8d7da; /* [source: 421] */
            color: #721c24; /* [source: 421] (Đổi màu đậm hơn) */
            border: 1px solid #f5c6cb; /* Thêm border */
        }

        .back-btn { /* Nút quay lại bài học */
            display: block; /* [source: 422] */
            width: fit-content; /* [source: 422] (Để nút vừa với nội dung) */
            margin: 30px auto 0; /* [source: 422] (Tăng margin top) */
            text-align: center; /* [source: 422] */
            background: linear-gradient(90deg, #6c757d, #5a6268); /* Màu xám */
            color: #fff; /* [source: 423] */
            padding: 12px 25px; /* [source: 423] (Đồng bộ padding) */
            border-radius: 25px; /* [source: 423] */
            text-decoration: none; /* [source: 423] */
            font-weight: 600; /* [source: 423] */
             transition: background 0.3s, transform 0.2s; /* Thêm transition */
        }

        .back-btn:hover {
            background: linear-gradient(90deg, #5a6268, #4e555b); /* [source: 424] (Màu xám đậm hơn) */
             transform: translateY(-2px); /* Hiệu ứng hover */
        }

        @media (max-width: 768px) { /* [source: 425] */
            .test-container {
                width: 95%; /* [source: 425] (Sử dụng % thay vì 100%) */
                margin: 20px auto; /* [source: 425] */
                padding: 20px; /* [source: 425] (Giảm padding) */
            }
             .test-container h2 {
                 font-size: 24px; /* Giảm size tiêu đề */
             }
             .question p {
                 font-size: 16px; /* Giảm size câu hỏi */
             }
             .option label {
                 font-size: 15px; /* Giảm size lựa chọn */
             }
             .test-container button[type="submit"], .back-btn {
                 padding: 10px 20px; /* Giảm padding nút */
             }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>Bài kiểm tra: <?php echo htmlspecialchars($sub_lesson['title']); ?></h2>

        <form id="test-form" data-lesson-id="<?php echo $sub_lesson_id; ?>">
            <?php if ($questions->num_rows > 0): ?>
                <?php $question_index = 1; ?>
                <?php while ($question = $questions->fetch_assoc()): ?>
                    <div class="question">
                        <p><?php echo $question_index++ . '. ' . htmlspecialchars($question['question_text']); ?></p>
                        <?php foreach (['A', 'B', 'C', 'D'] as $option_char): ?>
                            <?php $option_key = 'option_' . strtolower($option_char); ?>
                            <?php if (!empty($question[$option_key])): ?>
                                <div class="option">
                                    <input type="radio" name="q<?php echo $question['id']; ?>" value="<?php echo $option_char; ?>" id="q<?php echo $question['id']; ?>_<?php echo strtolower($option_char); ?>">
                                    <label for="q<?php echo $question['id']; ?>_<?php echo strtolower($option_char); ?>"><?php echo htmlspecialchars($question[$option_key]); ?></label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endwhile; ?>
                <button type="submit">Nộp bài</button>
            <?php else: ?>
                <p style="text-align: center; color: #6c757d;">Chưa có câu hỏi kiểm tra cho bài học này.</p>
            <?php endif; ?>
        </form>

        <div class="test-result" id="test-result"></div>

        <a href="course_detail.php?id=<?php echo $course_id; ?>" class="back-btn" id="back-btn">Quay lại bài học</a>
    </div>

    <script>
        document.getElementById('test-form').addEventListener('submit', async (e) => {
            e.preventDefault(); // Ngăn form gửi theo cách truyền thống
            const lessonId = e.target.dataset.lessonId; // Lấy lesson_id từ attribute
            const resultDiv = document.getElementById('test-result'); // Div hiển thị kết quả tổng
            const form = e.target; // Form làm bài
            const submitButton = form.querySelector('button[type="submit"]'); // Nút nộp bài
            const backButton = document.getElementById('back-btn'); // Nút quay lại

            // Vô hiệu hóa nút nộp bài để tránh gửi nhiều lần
            submitButton.disabled = true;
            submitButton.textContent = 'Đang xử lý...';

            // Thu thập câu trả lời của người dùng
            const formData = new FormData(form); // [source: 437]
            const answers = {};
            formData.forEach((value, key) => {
                answers[key] = value; // [source: 437]
            });

            try {
                 // Gửi dữ liệu lên server để chấm điểm
                const response = await fetch('check_test.php', {
                    method: 'POST', // [source: 438]
                    headers: { 'Content-Type': 'application/json' }, // [source: 438]
                    body: JSON.stringify({ lesson_id: lessonId, answers }) // [source: 438]
                });

                // Kiểm tra response từ server
                if (!response.ok) {
                    throw new Error(`Lỗi mạng: ${response.statusText}`);
                }

                const result = await response.json(); // Parse kết quả JSON // [source: 439]

                // Hiển thị thông báo kết quả tổng quát
                resultDiv.className = `test-result ${result.success ? 'success' : 'error'}`; // [source: 439]
                resultDiv.textContent = result.message || (result.success ? 'Nộp bài thành công!' : 'Có lỗi xảy ra.'); // [source: 439]
                resultDiv.style.display = 'block'; // [source: 439]

                // Xử lý hiển thị chi tiết đúng/sai nếu thành công và có details
                if (result.success && result.details) {
                    result.details.forEach(detail => {
                        const questionId = detail.question_id;
                        // Tìm khối div.question tương ứng bằng cách tìm input có name=q[id] rồi lấy cha gần nhất là .question
                        const questionBlock = form.querySelector(`input[name="q${questionId}"]`)?.closest('.question');

                        if (questionBlock) {
                            const options = questionBlock.querySelectorAll('input[type="radio"]');
                            options.forEach(option => {
                                option.disabled = true; // Vô hiệu hóa các lựa chọn sau khi nộp bài
                                const label = questionBlock.querySelector(`label[for="${option.id}"]`); // Tìm label tương ứng
                                if (!label) return; // Bỏ qua nếu không tìm thấy label

                                let feedbackText = ''; // Text thêm vào cuối label (vd: Bạn chọn đúng)
                                // Đánh dấu câu trả lời của người dùng
                                if (option.value === detail.user_answer) {
                                    label.classList.add(detail.is_correct ? 'user-correct' : 'user-incorrect');
                                    feedbackText = detail.is_correct ? ' (Bạn chọn đúng)' : ' (Bạn chọn sai)';
                                }

                                // Đánh dấu đáp án đúng (luôn hiển thị)
                                if (option.value === detail.correct_answer) {
                                    label.classList.add('correct-answer');
                                    // Chỉ thêm text "(Đáp án đúng)" nếu nó khác với lựa chọn của người dùng HOẶC người dùng không chọn câu này
                                    if (option.value !== detail.user_answer || detail.user_answer === null) {
                                         // Nếu đáp án đúng không phải là cái user chọn sai, hoặc user không chọn gì cả
                                         if (!label.classList.contains('user-incorrect')) {
                                             feedbackText += ' (Đáp án đúng)';
                                         }
                                    }
                                }
                                // Thêm text phản hồi vào cuối label nếu có
                                if (feedbackText) {
                                    label.innerHTML += `<span class="feedback-text">${feedbackText}</span>`;
                                }

                            });
                        } else {
                             console.warn(`Không tìm thấy khối câu hỏi cho ID: ${questionId}`);
                        }
                    });
                     // Ẩn form sau khi hiển thị chi tiết
                     // form.style.display = 'none'; // Giờ không ẩn form nữa mà chỉ vô hiệu hóa
                     submitButton.style.display = 'none'; // Ẩn nút nộp bài
                } else if (result.success) {
                    // Trường hợp thành công nhưng không có details (dự phòng)
                     // form.style.display = 'none';
                      submitButton.style.display = 'none'; // Ẩn nút nộp bài
                } else {
                     // Trường hợp có lỗi từ server (result.success = false)
                     submitButton.disabled = false; // Cho phép thử lại
                     submitButton.textContent = 'Nộp bài';
                }


                // Ẩn nút submit và hiển thị nút quay lại (di chuyển ra ngoài để luôn thực hiện)
                // form.style.display = 'none'; // [source: 440] - Không ẩn form nữa
                // submitButton.style.display = 'none'; // Ẩn nút submit sau khi xử lý xong
                // backButton.style.display = 'block'; // Hiển thị nút quay lại // [source: 440] - Nút này đã hiển thị sẵn

            } catch (error) {
                 console.error('Lỗi khi nộp bài:', error);
                 resultDiv.className = 'test-result error';
                 resultDiv.textContent = `Lỗi: ${error.message}. Vui lòng thử lại.`;
                 resultDiv.style.display = 'block';
                 // Cho phép người dùng thử lại nếu có lỗi mạng hoặc lỗi khác
                 submitButton.disabled = false;
                 submitButton.textContent = 'Nộp bài';
            }
        });
    </script>

</body>
</html>
<?php
// Đóng các kết nối CSDL
if (isset($test_stmt)) $test_stmt->close(); // [source: 440]
if (isset($conn)) $conn->close(); // [source: 440]
?>