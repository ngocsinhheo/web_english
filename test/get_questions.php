<?php
header('Content-Type: application/json');

$test = isset($_GET['test']) ? $_GET['test'] : 'test1';
$filePath = "tests/{$test}/uploads/questions.csv";

if (!file_exists($filePath)) {
    echo json_encode(['error' => 'File CSV không tồn tại: ' . $filePath]);
    exit;
}

if (!is_readable($filePath)) {
    echo json_encode(['error' => 'Không thể đọc file CSV: ' . $filePath]);
    exit;
}

$file = fopen($filePath, 'r');
if (!$file) {
    echo json_encode(['error' => 'Không thể mở file CSV']);
    exit;
}

$data = [];
$headers = fgetcsv($file);
if (!$headers) {
    echo json_encode(['error' => 'File CSV không có header']);
    fclose($file);
    exit;
}

while ($row = fgetcsv($file)) {
    if (count($row) === count($headers)) {
        $data[] = array_combine($headers, array_map('trim', $row));
    }
}
fclose($file);

if (empty($data)) {
    echo json_encode(['error' => 'File CSV trống hoặc dữ liệu không hợp lệ']);
    exit;
}

echo json_encode($data);
?>