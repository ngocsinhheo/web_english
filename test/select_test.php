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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #80deea);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        header {
            position: sticky;
            top: 0;
            background: linear-gradient(90deg, #006064, #00acc1);
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .logo a {
            color: #fff;
            text-decoration: none;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #00bcd4;
        }
        .select-test-container {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
            color: #006064;
        }
        .refresh-btn {
            display: block;
            margin: 0 auto 20px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }
        .refresh-btn:hover {
            background: linear-gradient(90deg, #2980b9, #2471a3);
            transform: translateY(-3px);
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .test-card {
            background: #f9fbfd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .test-card:hover {
            transform: translateY(-5px);
        }
        .test-card h3 {
            font-size: 24px;
            color: #34495e;
            margin-bottom: 10px;
        }
        .test-info {
            margin-bottom: 15px;
            color: #666;
        }
        .test-info span {
            margin: 0 10px;
        }
        .action-btn {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }
        .action-btn:hover {
            background: linear-gradient(90deg, #27ae60, #219653);
        }
        footer {
            background: linear-gradient(90deg, #006064, #00acc1);
            color: #fff;
            padding: 20px;
            text-align: center;
            width: 100%;
        }
        footer a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin: 5px 0;
        }
        footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            header {
                flex-direction: column;
            }
            nav {
                margin-top: 10px;
            }
            nav a {
                margin: 5px;
            }
            .select-test-container {
                padding: 10px;
            }
            .test-grid {
                grid-template-columns: 1fr;
            }
            footer div {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="../admin/dashboard.php">English Learning</a></div>
        <nav>
            <a href="../admin/dashboard.php">Trang chủ</a>
            <a href="select_test.php">Thi thử</a>
            <a href="../profile.php">Hồ sơ</a>
            <a href="../contact.php">Liên hệ</a>
            <a href="../auth/logout.php" style="color: #ff5252;">Đăng xuất</a>
        </nav>
    </header>

    <main>
        <div class="select-test-container">
            <h2>Chọn Bài Thi</h2>
            <button id="refreshBtn" class="refresh-btn">Làm mới</button>
            <div id="testList" class="test-grid"></div>
        </div>
    </main>

    <footer>
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div>
                    <h3>English Learning</h3>
                    <p>Học tiếng Anh dễ dàng và hiệu quả!</p>
                </div>
                <div>
                    <h3>Liên kết</h3>
                    <a href="../about.php">Giới thiệu</a>
                    <a href="../contact.php">Liên hệ</a>
                    <a href="../policy.php">Chính sách</a>
                </div>
                <div>
                    <h3>Liên hệ</h3>
                    <p>Email: support@englishlearning.com</p>
                    <p>Hotline: 0123 456 789</p>
                </div>
            </div>
            <p>© 2025 English Learning. All rights reserved.</p>
        </div>
    </footer>

    <script>
        async function loadTests() {
            try {
                const response = await fetch('../test/admin.php?action=list', { 
                    cache: 'no-store',
                    credentials: 'include'
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Lỗi HTTP: ${response.status} - ${errorText}`);
                }
                const data = await response.json();
                if (data.error) {
                    document.getElementById('testList').innerHTML = `<p>${data.error}</p>`;
                    return;
                }
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

        const scrollTopBtn = document.createElement('button');
        scrollTopBtn.textContent = '↑';
        scrollTopBtn.style.position = 'fixed';
        scrollTopBtn.style.bottom = '20px';
        scrollTopBtn.style.right = '20px';
        scrollTopBtn.style.background = '#0288d1';
        scrollTopBtn.style.color = '#fff';
        scrollTopBtn.style.padding = '10px';
        scrollTopBtn.style.border = 'none';
        scrollTopBtn.style.borderRadius = '50%';
        scrollTopBtn.style.cursor = 'pointer';
        scrollTopBtn.style.display = 'none';
        document.body.appendChild(scrollTopBtn);

        window.addEventListener('scroll', () => {
            scrollTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
        });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>