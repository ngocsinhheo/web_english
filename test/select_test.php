<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Bài Kiểm Tra</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="select-test-container">
        <h2>Chọn Bài Kiểm Tra</h2>
        <button id="refreshBtn" class="refresh-btn">Làm mới danh sách</button>
        <div id="testList" class="test-grid">
            <!-- Danh sách bài test sẽ được tải động -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadTests();

            // Thêm sự kiện cho nút Làm mới
            document.getElementById('refreshBtn').addEventListener('click', loadTests);
        });

        function loadTests() {
            fetch('admin.php?action=list', { cache: 'no-store' }) // Tắt cache
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Lỗi khi gọi API: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(tests => {
                    console.log('Danh sách bài test:', tests); // Debug
                    const testList = document.getElementById('testList');
                    testList.innerHTML = ''; // Xóa danh sách cũ
                    if (tests.length === 0) {
                        testList.innerHTML = '<p>Chưa có bài kiểm tra nào.</p>';
                        return;
                    }
                    tests.forEach((test, index) => {
                        const testItem = document.createElement('div');
                        testItem.classList.add('test-card');

                        // Giả lập dữ liệu lượt làm và thảo luận
                        const isCompleted = index === 0;

                        testItem.innerHTML = `
                            <h3>${test.replace('test', 'Test ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
                            <div class="test-info">
                                <span class="info-item">⏰ 120 phút</span>
                                <span class="info-item">✏️ 200 câu hỏi</span>
                            </div>
                            <div class="test-tags">
                                <span class="tag">#TOEIC</span>
                            </div>
                            <button class="action-btn " onclick="startTest('${test}')">
                              Làm Bài Ngay ! 
                            </button>
                        `;
                        testList.appendChild(testItem);
                    });
                })
                .catch(error => {
                    console.error('Lỗi khi tải danh sách bài kiểm tra:', error);
                    document.getElementById('testList').innerHTML = '<p>Lỗi khi tải danh sách bài kiểm tra. Vui lòng thử lại.</p>';
                });
        }

        function startTest(testId) {
            localStorage.setItem('currentTest', testId);
            window.location.href = `index.html?test=${testId}`;
        }
    </script>
</body>
</html>