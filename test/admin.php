<?php
session_start();
require_once '../config/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

// Kiểm tra quyền admin chỉ cho tạo/xóa
if (($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'delete')) && $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Không có quyền admin']);
    exit();
}

$testsDir = '../tests/';

function getTests($dir) {
    if (!is_dir($dir)) {
        return [];
    }
    return array_filter(glob($dir . '*'), 'is_dir');
}

// Xử lý action=list để trả về danh sách bài thi (cho cả user và admin)
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    $tests = getTests($testsDir);
    $testNames = array_map('basename', $tests);
    echo json_encode($testNames);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    try {
        $testId = preg_match('/^test\d+$/', trim($_POST['test_id'])) ? trim($_POST['test_id']) : 'test' . trim($_POST['test_id']);
        $uploadDir = "{$testsDir}{$testId}/";
        if (file_exists($uploadDir)) throw new Exception("ID '{$testId}' đã tồn tại!");

        $dirs = [$uploadDir, "{$uploadDir}uploads/", "{$uploadDir}img/", "{$uploadDir}audio/"];
        foreach ($dirs as $dir) if (!mkdir($dir, 0777, true)) throw new Exception("Không thể tạo thư mục!");

        $csvPath = "{$uploadDir}uploads/questions.csv";
        if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvPath)) throw new Exception("Lỗi upload CSV!");

        foreach (['images' => 'img', 'audios' => 'audio'] as $type => $folder) {
            if (isset($_FILES[$type]) && !empty($_FILES[$type]['name'][0])) {
                foreach ($_FILES[$type]['tmp_name'] as $key => $tmpName) {
                    if ($_FILES[$type]['error'][$key] === UPLOAD_ERR_OK) {
                        move_uploaded_file($tmpName, "{$uploadDir}{$folder}/" . $_FILES[$type]['name'][$key]);
                    }
                }
            }
        }
        $success = "Tạo bài kiểm tra {$testId} thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['test'])) {
    $testDir = "{$testsDir}{$_GET['test']}";
    function deleteDir($dir) {
        if (!file_exists($dir)) return true;
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            is_dir("$dir/$item") ? deleteDir("$dir/$item") : unlink("$dir/$item");
        }
        return rmdir($dir);
    }
    echo json_encode(deleteDir($testDir) ? ['status' => 'success'] : ['status' => 'error']);
    exit;
}

$tests = getTests($testsDir);
?>

<div class="test-admin">
    <h2>Quản Lý Bài Kiểm Tra</h2>
    <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
    <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <input type="text" name="test_id" placeholder="ID (VD: 2)" required>
        <input type="file" name="csv_file" accept=".csv" required>
        <input type="file" name="images[]" accept="image/*" multiple>
        <input type="file" name="audios[]" accept="audio/*" multiple>
        <button type="submit" name="create_test">Tạo</button>
    </form>
    <table>
        <tr><th>ID</th><th>Hành động</th></tr>
        <?php foreach ($tests as $test): ?>
            <tr>
                <td><?php echo basename($test); ?></td>
                <td><button class="delete-btn" onclick="deleteTest('<?php echo basename($test); ?>')">Xóa</button></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    function deleteTest(testId) {
        if (confirm(`Xóa ${testId}?`)) {
            fetch(`admin.php?action=delete&test=${testId}`).then(res => res.json()).then(data => {
                if (data.status === 'success') location.reload();
            });
        }
    }
</script>