<?php
require 'connection.php';
$user_id = intval($_POST['user_id'] ?? 0);
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
if (!$user_id || !$name || !$phone || !$email) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$stmt = $conn->prepare("UPDATE user SET name=?, phone=?, email=? WHERE user_id=?");
$stmt->bind_param("sssi", $name, $phone, $email, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 