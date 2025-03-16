<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để xem khóa học! <a href='login.php'>Đăng nhập</a>");
}

$sql = "SELECT * FROM courses";
$result = $conn->query($sql);
?>

<h2>Danh sách khóa học</h2>
<ul>
    <?php while ($course = $result->fetch_assoc()): ?>
        <li>
            <h3><?php echo $course['title']; ?></h3>
            <p><?php echo $course['description']; ?></p>
        </li>
    <?php endwhile; ?>
</ul>

<a href="logout.php">Đăng xuất</a>
<?php
include 'config.php';

// Lấy danh sách khóa học
$sql = "SELECT title, description, price FROM courses";
$result = $conn->query($sql);

while ($course = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$course['title']}</td>
            <td>{$course['description']}</td>
            <td>{$course['price']} VNĐ</td>
          </tr>";
}
?>
<?php
include 'config.php';

$sql = "SELECT id, title, description, price FROM courses ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['title']}</td>
                <td>{$row['description']}</td>
                <td>" . number_format($row['price']) . " VNĐ</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>Chưa có khóa học nào</td></tr>";
}
?>
<?php
include 'config.php';

$sql = "SELECT id, title, description, price FROM courses ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['title']}</td>
                <td>{$row['description']}</td>
                <td>" . number_format($row['price']) . " VNĐ</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>Chưa có khóa học nào</td></tr>";
}
?>
