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
    $stmt = $conn->prepare("SELECT user_id, name, email, password FROM user WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $name, $email, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Output JS to set localStorage and redirect
            echo "<script>
                localStorage.setItem('isAuthenticated', 'true');
                localStorage.setItem('userName', " . json_encode($name) . ");
                localStorage.setItem('userPhone', " . json_encode($phone) . ");
                localStorage.setItem('userEmail', " . json_encode($email) . ");
                window.location.href = '/AutoAssist/home.html';
            </script>";
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