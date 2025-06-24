<?php

require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo "Phone number and password are required.";
        exit;
    }

    // Find user by phone number
    $stmt = $conn->prepare("SELECT user_id, name, password FROM user WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $name, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Successful login
            // session_start();
            // $_SESSION['user_id'] = $user_id;
            // $_SESSION['name'] = $name;
            header("Location: UserHomePage.html");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found or invalid credentials.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>