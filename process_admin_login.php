<?php
session_start();
require 'connection.php';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (!$email || !$password) {
    header('Location: AdminLogin.html?error=1');
    exit;
}
$stmt = $conn->prepare("SELECT admin_id, email, password FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($admin_id, $admin_email, $admin_password_hash);
    $stmt->fetch();
    if (password_verify($password, $admin_password_hash)) {
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['admin_email'] = $admin_email;
        header('Location: AdminDashboard.php');
        exit;
    }
}
$stmt->close();
$conn->close();
header('Location: AdminLogin.html?error=1');
exit; 