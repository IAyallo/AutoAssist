<?php
require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo "Phone number and password are required.";
        exit;
    }

    // Find mechanic by phone number
    $stmt = $conn->prepare("SELECT mech_id, name, password, profile_pic FROM mechanic WHERE phone = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($mech_id, $name, $password_hash, $profile_pic);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Successful login
            session_start();
            $_SESSION['mech_id'] = $mech_id;
            $_SESSION['mech_name'] = $name;
            $_SESSION['mech_profile_pic'] = $profile_pic ?: 'profile.jpg'; // fallback if null
            header("Location: MechHomePage.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Mechanic not found or invalid credentials.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>