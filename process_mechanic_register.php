<?php
require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $locality = $conn->real_escape_string($_POST['county'] ?? '');

    if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($locality)) {
        echo "All fields are required.";
        exit;
    }

    // Check if email or phone already exists
    $check = $conn->prepare("SELECT mech_id FROM mechanic WHERE email = ? OR phone = ?");
    $check->bind_param("ss", $email, $phone);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "Mechanic with this email or phone already exists.";
        exit;
    }
    $check->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new mechanic
    $stmt = $conn->prepare("INSERT INTO mechanic (name, phone, email, locality, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $phone, $email, $locality, $password_hash);

    if ($stmt->execute()) {
        header('Location: MechLogIn.html');
        exit;
    } else {
        echo "Registration failed. Please try again.";
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>