<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$test = filter_var($_GET['test'] ?? 'test1', FILTER_SANITIZE_STRING);
$filePath = "../tests/{$test}/uploads/questions.csv";

try {
    if (!file_exists($filePath)) throw new Exception("File CSV không tồn tại!");
    $file = fopen($filePath, 'r');
    $headers = fgetcsv($file);
    $data = [];
    while ($row = fgetcsv($file)) {
        if (count($row) === count($headers)) $data[] = array_combine($headers, array_map('trim', $row));
    }
    fclose($file);
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;