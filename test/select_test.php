<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Bài Thi</title>
    <link rel="stylesheet" href="../style.css"> 
</head>
<body>
    <div class="select-test-container">
        <h2>Chọn Bài Thi</h2>
        <button id="refreshBtn" class="refresh-btn">Làm mới</button>
        <div id="testList" class="test-grid"></div>
    </div>
    <script>
async function loadTests() {
    try {
        const response = await fetch('../test/admin.php?action=list', { 
            cache: 'no-store',
            credentials: 'include' // Gửi cookie session để duy trì đăng nhập
        });
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Lỗi HTTP: ${response.status} - ${errorText}`);
        }
        const data = await response.json();
        // Kiểm tra nếu API trả về lỗi trong JSON
        if (data.error) {
            document.getElementById('testList').innerHTML = `<p>${data.error}</p>`;
            return;
        }
        // Hiển thị danh sách bài thi
        document.getElementById('testList').innerHTML = data.length ? data.map(t => `
            <div class="test-card">
                <h3>${t.replace('test', 'Test ')}</h3>
                <div class="test-info"><span>⏰ 120 phút</span><span>✏️ 200 câu</span></div>
                <button class="action-btn" onclick="startTest('${t}')">Làm ngay</button>
            </div>`).join('') : '<p>Chưa có bài thi.</p>';
    } catch (error) {
        console.error('Lỗi khi tải danh sách bài thi:', error.message);
        document.getElementById('testList').innerHTML = '<p>Lỗi tải danh sách bài thi: ' + error.message + '</p>';
    }
}

        function startTest(testId) {
            localStorage.setItem('currentTest', testId);
            window.location.href = `index.html?test=${testId}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadTests();
            document.getElementById('refreshBtn').addEventListener('click', loadTests);
        });
    </script>
</body>
</html>