<?php
require 'connection.php';
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$locality = $_POST['locality'] ?? '';
$password = $_POST['password'] ?? '';
if (!$name || !$phone || !$email || !$locality || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO mechanic (name, phone, email, locality, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $phone, $email, $locality, $hash);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 