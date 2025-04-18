<?php
header('Content-Type: application/json');

$test = isset($_GET['test']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['test']) : '';
$audioDir = "../tests/{$test}/audio/";

if (!is_dir($audioDir)) {
    echo json_encode(['error' => 'Thư mục âm thanh không tồn tại']);
    exit;
}

$files = array_filter(scandir($audioDir), function($file) {
    return !in_array($file, ['.', '..']) && is_file("../tests/{$_GET['test']}/audio/{$file}");
});

$audioFiles = array_map(function($file) use ($test) {
    return "/tests/{$test}/audio/{$file}";
}, array_values($files));

echo json_encode($audioFiles);
?>