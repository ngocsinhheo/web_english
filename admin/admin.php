    <?php
   require_once '../config/config.php';


    if (!$conn) {
        die("Kết nối database thất bại: " . mysqli_connect_error());
    }

    // Truy vấn danh sách khóa học
    $sql_courses = "SELECT id, title, description, price, teacher_name, image, content_file, video_file FROM courses";
    $result_courses = $conn->query($sql_courses);

    if (!$result_courses) {
        die("Lỗi truy vấn: " . $conn->error);
    }
    ?>

    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quản lý Khóa học</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            header {
                background: #333;
                color: white;
                padding: 15px;
                text-align: center;
                font-size: 22px;
            }
            .container {
                width: 90%;
                max-width: 1000px;
                margin: 20px auto;
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            h2 {
                color: #444;
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
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 10px;
                text-align: center;
            }
            th {
                background: #007bff;
                color: white;
            }
            img {
                max-width: 100px;
                border-radius: 5px;
            }
            .delete-btn {
                color: red;
                text-decoration: none;
                font-weight: bold;
            }
            .delete-btn:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <header>Quản lý Khóa học</header>
        <div class="container">
            <h2>Thêm Khóa học</h2>
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
            
            <h2>Danh sách Khóa học</h2>
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
    </body>
    </html>