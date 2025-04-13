<?php
header('Content-Type: application/json');
include '../config/config.php';

$word = isset($_GET['word']) ? trim($_GET['word']) : '';
$language = isset($_GET['lang']) ? $_GET['lang'] : 'en';

if (empty($word)) {
    echo json_encode(['error' => 'Vui lòng nhập từ cần tra']);
    exit;
}

$sql = "SELECT * FROM dictionary WHERE word = ? AND language = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $word, $language);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Không tìm thấy từ']);
}

$stmt->close();
$conn->close();
?>