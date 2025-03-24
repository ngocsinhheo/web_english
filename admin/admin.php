<?php
// admin.php
require_once '../config/config.php'; // Kết nối database

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql_add = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_add);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Thêm người dùng thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm người dùng: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql_delete = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Xóa người dùng thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa người dùng: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Truy vấn danh sách người dùng
$sql_users = "SELECT id, username, email FROM users";
$result_users = $conn->query($sql_users);

if (!$result_users) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Dữ liệu cho biểu đồ
$total_users = $result_users->num_rows;

// Xử lý thêm khóa học
if (isset($_POST['add_course'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $teacher_name = $_POST['teacher_name'];
    $price = $_POST['price'];

    $target_dir = "../uploads/";
    $image = $target_dir . basename($_FILES["course_image"]["name"]);
    $content_file = $target_dir . basename($_FILES["course_content"]["name"]);
    $video_file = $target_dir . basename($_FILES["course_video"]["name"]);

    move_uploaded_file($_FILES["course_image"]["tmp_name"], $image);
    move_uploaded_file($_FILES["course_content"]["tmp_name"], $content_file);
    move_uploaded_file($_FILES["course_video"]["tmp_name"], $video_file);

    $sql_add_course = "INSERT INTO courses (title, description, price, teacher_name, image, content_file, video_file) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_add_course);
    $stmt->bind_param("ssdssss", $title, $description, $price, $teacher_name, $image, $content_file, $video_file);

    if ($stmt->execute()) {
        echo "<script>alert('Thêm khóa học thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm khóa học: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Xử lý xóa khóa học
if (isset($_GET['delete_course'])) {
    $course_id = $_GET['delete_course'];
    $sql_delete_course = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql_delete_course);
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        echo "<script>alert('Xóa khóa học thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa khóa học: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Truy vấn danh sách khóa học
$sql_courses = "SELECT id, title, description, price, teacher_name, image, content_file, video_file FROM courses";
$result_courses = $conn->query($sql_courses);

if (!$result_courses) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Dữ liệu mẫu cho biểu đồ khóa học (có thể thay bằng dữ liệu thực tế nếu có bảng đánh giá)
$popular_courses = [];
while ($course = $result_courses->fetch_assoc()) {
    $popular_courses[$course['title']] = rand(10, 100); // Số lượng giả định
}
$result_courses->data_seek(0); // Reset con trỏ để sử dụng lại trong bảng
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            padding: 20px;
            color: white;
        }

        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            color: white; /* Đảm bảo màu chữ "Admin Panel" là trắng */
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            padding: 15px;
            margin: 5px 0;
            cursor: pointer;
            transition: all 0.3s;
        }

        .sidebar li:hover {
            background-color: #34495e;
        }

        .sidebar li.active {
            background-color: #3498db;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar a:hover {
            color: #ddd;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .content-section {
            display: none;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-section.active {
            display: block;
        }

        h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        h2 {
            margin: 15px 0;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
        }

        form input, form textarea, form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        form button:hover {
            background: #218838;
        }

        .delete-btn {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }

        .chart-container {
            width: 50%;
            margin: 20px auto;
        }

        img {
            max-width: 100px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li onclick="showSection('dashboard')" class="active">Dashboard</li>
                <li onclick="showSection('users')">Quản Lý Người Dùng</li>
                <li onclick="showSection('products')">Quản Lý Khóa Học</li>
                <li onclick="showSection('settings')">Settings</li>
                <li><a href="../auth/logout.php">Đăng xuất</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div id="dashboard" class="content-section active">
                <h1>Dashboard</h1>
                <p>Trang đánh giá mức độ thành công của WebEnglish !!</p>
                <div class="dashboard-info">
                 
                </div>

                <!-- Biểu đồ số người dùng -->
                <h2>Thống kê Người Dùng</h2>
                <div class="chart-container">
                    <canvas id="dashboardUserChart"></canvas>
                </div>

                <!-- Biểu đồ khóa học được ưa chuộng -->
                <h2>Khóa Học Được Ưa Chuộng</h2>
                <div class="chart-container">
                    <canvas id="popularCoursesChart"></canvas>
                </div>
            </div>
            
            <div id="users" class="content-section">
                <h1>Quản Lý Người Dùng</h1>
                <h2>Thống kê Người Dùng</h2>
                <div class="chart-container">
                    <canvas id="userChart"></canvas>
                </div>

                <h2>Thêm Người Dùng</h2>
                <form method="POST">
                    <input type="text" name="username" placeholder="Tên người dùng" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <button type="submit" name="add_user">Thêm người dùng</button>
                </form>
                
                <h2>Danh sách Người Dùng</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Tên người dùng</th>
                        <th>Email</th>
                        <th>Hành động</th>
                    </tr>
                    <?php if ($result_users->num_rows > 0): ?>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <a class="delete-btn" href="?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Không có người dùng nào!</td></tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <div id="products" class="content-section">
                <h1>Quản Lý Khóa Học</h1>
                
                <h2>Thêm Khóa Học</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Tên khóa học" required>
                    <textarea name="description" placeholder="Mô tả khóa học" required></textarea>
                    <input type="text" name="teacher_name" placeholder="Tên giáo viên" required>
                    <input type="number" name="price" placeholder="Giá khóa học" required>
                    <input type="file" name="course_image" accept="image/*" required>
                    <input type="file" name="course_content" accept="application/pdf" required>
                    <input type="file" name="course_video" accept="video/*" required>
                    <button type="submit" name="add_course">Thêm khóa học</button>
                </form>
                
                <h2>Danh sách Khóa Học</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Giá</th>
                        <th>Giáo viên</th>
                        <th>Hình ảnh</th>
                        <th>Bài giảng</th>
                        <th>Video</th>
                        <th>Hành động</th>
                    </tr>
                    <?php if ($result_courses->num_rows > 0): ?>
                        <?php while ($course = $result_courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo number_format($course['price'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                                <td><img src="<?php echo $course['image']; ?>" alt="Hình ảnh khóa học"></td>
                                <td><a href="<?php echo $course['content_file']; ?>" target="_blank">Xem</a></td>
                                <td><a href="<?php echo $course['video_file']; ?>" target="_blank">Xem</a></td>
                                <td>
                                    <a class="delete-btn" href="?delete_course=<?php echo $course['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">Không có khóa học nào!</td></tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <div id="settings" class="content-section">
                <h1>Settings</h1>
                <p>Configure your settings here.</p>
                <form method="post" action="">
                    <label>Site Name: <input type="text" name="site_name" value="My Site"></label><br><br>
                    <label>Status: 
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label><br><br>
                    <input type="submit" value="Save">
                </form>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_user']) && !isset($_POST['add_course'])) {
                    $site_name = $_POST['site_name'] ?? 'My Site';
                    $status = $_POST['status'] ?? 'active';
                    echo "<p>Settings saved - Site Name: $site_name, Status: $status</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.sidebar li').forEach(item => {
                item.classList.remove('active');
            });

            document.getElementById(sectionId).classList.add('active');
            document.querySelector(`li[onclick="showSection('${sectionId}')"]`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Biểu đồ người dùng trong Dashboard
            const ctxUser = document.getElementById('dashboardUserChart').getContext('2d');
            const dashboardUserChart = new Chart(ctxUser, {
                type: 'bar',
                data: {
                    labels: ['Tổng người dùng', 'Người dùng mới', 'Người dùng hoạt động'],
                    datasets: [{
                        label: 'Số lượng người dùng',
                        data: [<?php echo $total_users; ?>, <?php echo rand(0, $total_users); ?>, <?php echo rand(0, $total_users); ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Biểu đồ khóa học được ưa chuộng trong Dashboard
            const ctxCourses = document.getElementById('popularCoursesChart').getContext('2d');
            const popularCoursesChart = new Chart(ctxCourses, {
                type: 'bar',
                data: {
                    labels: [<?php echo "'" . implode("','", array_keys($popular_courses)) . "'"; ?>],
                    datasets: [{
                        label: 'Số lượt ưa thích',
                        data: [<?php echo implode(',', array_values($popular_courses)); ?>],
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Biểu đồ người dùng trong Quản Lý Người Dùng
            const ctxUserManage = document.getElementById('userChart').getContext('2d');
            const userChart = new Chart(ctxUserManage, {
                type: 'bar',
                data: {
                    labels: ['Tổng người dùng', 'Người dùng mới', 'Người dùng hoạt động'],
                    datasets: [{
                        label: 'Số lượng người dùng',
                        data: [<?php echo $total_users; ?>, <?php echo rand(0, $total_users); ?>, <?php echo rand(0, $total_users); ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>