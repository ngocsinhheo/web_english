<?php

$conn = mysqli_connect('localhost','root','','toeicdb');



if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
