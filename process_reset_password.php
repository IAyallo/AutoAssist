<?php
include('db_connect.php'); // Connect to the database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        die("All fields are required.");
    }

    if ($newPassword !== $confirmPassword) {
        die("Passwords do not match.");
    }

 
    $query = "SELECT * FROM user_password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("This password reset link is invalid or has expired.");
    }

    $resetData = $result->fetch_assoc();
    $userId = $resetData['user_id'];

  
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);


    $update = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
    $update->bind_param("si", $hashedPassword, $userId);
    $update->execute();

    $delete = $conn->prepare("DELETE FROM user_password_resets WHERE token = ?");
    $delete->bind_param("s", $token);
    $delete->execute();

    echo "Your password has been successfully reset. <a href='Login.html'>Login</a>";
}
?>
