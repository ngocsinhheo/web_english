
     <?php
     header('Content-Type: application/json');
     require_once '../config/config.php';

     try {
         $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
         $query = "SELECT t.id, t.sub_lesson_id, t.question_text, t.option_a, t.option_b, t.option_c, t.option_d, t.correct_answer, c.course_name, s.title AS sub_lesson_title 
                   FROM sub_lesson_tests t 
                   JOIN sub_lessons s ON t.sub_lesson_id = s.id 
                   JOIN courses c ON s.course_id = c.id";
         if ($course_id > 0) {
             $query .= " WHERE s.course_id = $course_id";
         }
         $query .= " ORDER BY c.id, s.order_number, t.id";

         $result = $conn->query($query);
         if (!$result) {
             throw new Exception("Lỗi truy vấn: " . $conn->error);
         }

         $questions = [];
         while ($row = $result->fetch_assoc()) {
             $questions[] = $row;
         }

         echo json_encode($questions);
     } catch (Exception $e) {
         http_response_code(500);
         echo json_encode(['error' => $e->getMessage()]);
     }
     ?>