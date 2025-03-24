<?php
session_start();

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$testsDir = 'tests/';

// Hàm lấy danh sách bài test (lấy tất cả thư mục trong tests/)
function getTests($testsDir) {
    $dirs = array_filter(glob($testsDir . '*'), 'is_dir');
    return $dirs;
}

$tests = getTests($testsDir);

// Tạo bài kiểm tra mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    $testId = trim($_POST['test_id']);
    
    // Chuẩn hóa ID: Nếu không bắt đầu bằng "test", thêm tiền tố "test"
    if (!preg_match('/^test\d+$/', $testId)) {
        $testId = 'test' . $testId;
    }

    $uploadDir = "{$testsDir}{$testId}/";

    // Kiểm tra ID đã tồn tại chưa
    if (file_exists($uploadDir)) {
        die("<p style='color: red;'>Lỗi: ID bài kiểm tra '{$testId}' đã tồn tại. Vui lòng chọn ID khác!</p>");
    }

    // Tạo thư mục
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) || !mkdir("{$uploadDir}uploads/", 0777, true) || 
            !mkdir("{$uploadDir}img/", 0777, true) || !mkdir("{$uploadDir}audio/", 0777, true)) {
            die("<p style='color: red;'>Lỗi: Không thể tạo thư mục cho bài kiểm tra '{$testId}'. Kiểm tra quyền thư mục!</p>");
        }
    }

    // Upload file CSV
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csv_file'];
        $csvPath = "{$uploadDir}uploads/questions.csv";
        if (!move_uploaded_file($csvFile['tmp_name'], $csvPath)) {
            die("<p style='color: red;'>Lỗi: Không thể upload file CSV!</p>");
        }
    } else {
        die("<p style='color: red;'>Lỗi: Vui lòng chọn file CSV!</p>");
    }

    // Upload hình ảnh
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $imageName = $_FILES['images']['name'][$key];
                $imagePath = "{$uploadDir}img/{$imageName}";
                if (!move_uploaded_file($tmpName, $imagePath)) {
                    echo "<p style='color: orange;'>Cảnh báo: Không thể upload hình ảnh {$imageName}!</p>";
                }
            }
        }
    }

    // Upload audio
    if (isset($_FILES['audios']) && !empty($_FILES['audios']['name'][0])) {
        foreach ($_FILES['audios']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['audios']['error'][$key] === UPLOAD_ERR_OK) {
                $audioName = $_FILES['audios']['name'][$key];
                $audioPath = "{$uploadDir}audio/{$audioName}";
                if (!move_uploaded_file($tmpName, $audioPath)) {
                    echo "<p style='color: orange;'>Cảnh báo: Không thể upload audio {$audioName}!</p>";
                }
            }
        }
    }

    // Lưu thông báo thành công vào session và làm mới trang
    $_SESSION['success_message'] = "Bài kiểm tra {$testId} đã được tạo thành công!";
    header('Location: admin.php');
    exit;
}

// Xóa bài kiểm tra
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['test'])) {
    $testId = $_GET['test'];
    $testDir = "{$testsDir}{$testId}";
    if (is_dir($testDir)) {
        function deleteDirectory($dir) {
            if (!file_exists($dir)) return true;
            if (!is_dir($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
            }
            return rmdir($dir);
        }
        if (deleteDirectory($testDir)) {
            echo json_encode(['status' => 'success', 'message' => "Đã xóa {$testId}"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Không thể xóa {$testId}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => "Không tìm thấy {$testId}"]);
    }
    exit;
}

// Xóa file cụ thể (hình ảnh hoặc audio)
if (isset($_GET['action']) && $_GET['action'] === 'delete_file' && isset($_GET['test']) && isset($_GET['type']) && isset($_GET['file'])) {
    $testId = $_GET['test'];
    $type = $_GET['type'];
    $file = $_GET['file'];
    $filePath = "{$testsDir}{$testId}/{$type}/{$file}";
    if (file_exists($filePath) && unlink($filePath)) {
        echo json_encode(['status' => 'success', 'message' => "Đã xóa {$file}"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Không thể xóa {$file}"]);
    }
    exit;
}

// Lấy danh sách bài kiểm tra (luôn lấy mới nhất)
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    $tests = getTests($testsDir); // Lấy danh sách mới nhất
    echo json_encode(array_map('basename', $tests));
    exit;
}

// Xem chi tiết file CSV
if (isset($_GET['action']) && $_GET['action'] === 'view_csv' && isset($_GET['test'])) {
    $testId = $_GET['test'];
    $filePath = "{$testsDir}{$testId}/uploads/questions.csv";
    $data = [];
    if (file_exists($filePath)) {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        while ($row = fgetcsv($file)) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, array_map('trim', $row));
            }
        }
        fclose($file);
    }
    echo json_encode($data);
    exit;
}

// Lấy danh sách file (hình ảnh hoặc audio)
if (isset($_GET['action']) && $_GET['action'] === 'list_files' && isset($_GET['test']) && isset($_GET['type'])) {
    $testId = $_GET['test'];
    $type = $_GET['type'];
    $dir = "{$testsDir}{$testId}/{$type}/";
    $files = array_filter(glob($dir . '*'), 'is_file');
    echo json_encode(array_map('basename', $files));
    exit;
}

// Lấy lại danh sách bài test sau khi tạo (để hiển thị trong HTML)
$tests = getTests($testsDir);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý TOEIC Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Quản lý Bài Kiểm Tra</h2>

        <!-- Hiển thị thông báo thành công (nếu có) -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Form tạo bài kiểm tra mới -->
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <label for="test_id">ID Bài Kiểm Tra (ví dụ: 2, sẽ tự động thành test2):</label>
            <input type="text" name="test_id" id="test_id" required>

            <label for="csv_file">Tải lên file CSV:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>

            <label for="images">Tải lên hình ảnh (nhiều file):</label>
            <input type="file" name="images[]" id="images" accept="image/*" multiple>

            <label for="audios">Tải lên audio (nhiều file):</label>
            <input type="file" name="audios[]" id="audios" accept="audio/*" multiple>

            <button type="submit" name="create_test">Tạo Bài Kiểm Tra</button>
        </form>

        <!-- Danh sách bài kiểm tra -->
        <div class="test-list">
            <h3>Danh sách Bài Kiểm Tra</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="testListBody">
                    <?php if (empty($tests)): ?>
                        <tr>
                            <td colspan="2">Chưa có bài kiểm tra nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?php echo basename($test); ?></td>
                                <td>
                                    <button class="view-btn" onclick="viewTest('<?php echo basename($test); ?>')">Xem chi tiết</button>
                                    <button class="delete-btn" onclick="deleteTest('<?php echo basename($test); ?>')">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Chi tiết bài kiểm tra -->
        <div id="testDetails" style="display: none;">
            <h3>Chi tiết Bài Kiểm Tra: <span id="testDetailId"></span></h3>
            <h4>File CSV</h4>
            <table>
                <thead id="csvHeaders"></thead>
                <tbody id="csvBody"></tbody>
            </table>
            <h4>Hình ảnh</h4>
            <table>
                <thead>
                    <tr>
                        <th>Tên file</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="imagesList"></tbody>
            </table>
            <h4>Audio</h4>
            <table>
                <thead>
                    <tr>
                        <th>Tên file</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="audiosList"></tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteTest(testId) {
            if (confirm(`Bạn có chắc muốn xóa ${testId}?`)) {
                fetch(`admin.php?action=delete&test=${testId}`)
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') location.reload();
                    })
                    .catch(error => console.error('Lỗi khi xóa:', error));
            }
        }

        function deleteFile(testId, type, file) {
            if (confirm(`Bạn có chắc muốn xóa ${file}?`)) {
                fetch(`admin.php?action=delete_file&test=${testId}&type=${type}&file=${file}`)
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') viewTest(testId);
                    })
                    .catch(error => console.error('Lỗi khi xóa file:', error));
            }
        }

        function viewTest(testId) {
            document.getElementById('testDetails').style.display = 'block';
            document.getElementById('testDetailId').textContent = testId;

            // Tải nội dung CSV
            fetch(`admin.php?action=view_csv&test=${testId}`)
                .then(response => response.json())
                .then(data => {
                    const headers = Object.keys(data[0] || {});
                    const headersRow = document.getElementById('csvHeaders');
                    headersRow.innerHTML = headers.map(h => `<th>${h}</th>`).join('');

                    const body = document.getElementById('csvBody');
                    body.innerHTML = '';
                    data.forEach(row => {
                        const rowHtml = headers.map(h => `<td>${row[h] || ''}</td>`).join('');
                        body.innerHTML += `<tr>${rowHtml}</tr>`;
                    });
                });

            // Tải danh sách hình ảnh
            fetch(`admin.php?action=list_files&test=${testId}&type=img`)
                .then(response => response.json())
                .then(files => {
                    const imagesList = document.getElementById('imagesList');
                    imagesList.innerHTML = '';
                    files.forEach(file => {
                        imagesList.innerHTML += `
                            <tr>
                                <td>${file}</td>
                                <td><button class="delete-btn" onclick="deleteFile('${testId}', 'img', '${file}')">Xóa</button></td>
                            </tr>`;
                    });
                });

            // Tải danh sách audio
            fetch(`admin.php?action=list_files&test=${testId}&type=audio`)
                .then(response => response.json())
                .then(files => {
                    const audiosList = document.getElementById('audiosList');
                    audiosList.innerHTML = '';
                    files.forEach(file => {
                        audiosList.innerHTML += `
                            <tr>
                                <td>${file}</td>
                                <td><button class="delete-btn" onclick="deleteFile('${testId}', 'audio', '${file}')">Xóa</button></td>
                            </tr>`;
                    });
                });
        }
    </script>
</body>
</html>